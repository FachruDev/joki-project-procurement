<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Notifications\Center;
use App\Models\User;
use App\Notifications\InAppNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationActionUrlTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_action_url_is_normalized_with_app_subdirectory_prefix(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        config()->set('app.url', 'https://study.kyzee.tech/vendor');

        $vendorUser = User::factory()->create();
        $vendorUser->assignRole('Vendor');

        $vendorUser->notify(new InAppNotification(
            title: 'Vendor Approved',
            message: 'Your account is approved.',
            actionUrl: '/dashboard',
            actionLabel: 'View in Dashboard',
        ));

        Livewire::actingAs($vendorUser)
            ->test(Center::class)
            ->assertSee('href="/vendor/dashboard"', false);
    }
}
