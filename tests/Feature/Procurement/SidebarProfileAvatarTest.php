<?php

namespace Tests\Feature\Procurement;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SidebarProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_bottom_profile_uses_uploaded_profile_image(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('Procurement');

        $profileMedia = $user
            ->addMedia(UploadedFile::fake()->image('profile-avatar.png'))
            ->toMediaCollection('profile-images');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('media.show', $profileMedia), false);
    }
}
