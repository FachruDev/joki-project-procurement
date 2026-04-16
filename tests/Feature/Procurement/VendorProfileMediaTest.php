<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Vendor\Profile;
use App\Models\User;
use App\Models\Vendor;
use App\VendorStatus;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class VendorProfileMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_update_account_and_upload_profile_image(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        Storage::fake('public');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        Vendor::factory()->create([
            'user_id' => $vendorUser->id,
            'status' => VendorStatus::Approved,
        ]);

        $avatar = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        Livewire::actingAs($vendorUser)
            ->test(Profile::class)
            ->set('userName', 'Vendor Updated')
            ->set('userEmail', 'vendor.updated@example.test')
            ->set('companyName', 'Vendor Updated Company')
            ->set('address', 'Jakarta Barat')
            ->set('phone', '08123456789')
            ->set('profileImage', $avatar)
            ->call('saveProfile')
            ->assertHasNoErrors();

        $vendorUser = $vendorUser->fresh();
        $vendor = $vendorUser->vendor()->firstOrFail();
        $media = $vendorUser->getFirstMedia('profile-images');

        $this->assertSame('Vendor Updated', $vendorUser->name);
        $this->assertSame('vendor.updated@example.test', $vendorUser->email);
        $this->assertSame('Vendor Updated Company', $vendor->company_name);
        $this->assertNotNull($media);
        $this->assertNotNull($vendorUser->profile_image);

        $this->actingAs($vendorUser)
            ->get(route('media.show', $media))
            ->assertOk();
    }
}
