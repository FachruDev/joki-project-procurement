<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Notifications\Center;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationHistoryModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure notification history modal state can be opened and closed.
     */
    public function test_notification_history_modal_state_updates_correctly(): void
    {
        $user = User::factory()->create();

        $user->notify(new InAppNotification(
            title: 'Notification One',
            message: 'First notification message',
        ));

        $user->notify(new InAppNotification(
            title: 'Notification Two',
            message: 'Second notification message',
        ));

        $user->notifications()->first()?->markAsRead();

        Livewire::actingAs($user)
            ->test(Center::class)
            ->assertSet('showHistoryModal', false)
            ->call('openHistoryModal')
            ->assertSet('showHistoryModal', true)
            ->assertSee('Notification History')
            ->call('closeHistoryModal')
            ->assertSet('showHistoryModal', false);
    }
}
