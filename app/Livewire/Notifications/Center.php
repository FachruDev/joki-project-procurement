<?php

namespace App\Livewire\Notifications;

use Flux\Flux;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Center extends Component
{
    public bool $compact = false;

    public int $limit = 8;

    public int $historyLimit = 30;

    public bool $showHistoryModal = false;

    /**
     * Mark one notification as read.
     */
    public function markAsRead(string $notificationId): void
    {
        $notification = Auth::user()?->notifications()->whereKey($notificationId)->first();

        if ($notification !== null && $notification->read_at === null) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()->update([
            'read_at' => now(),
        ]);

        Flux::toast(text: __('All notifications marked as read.'));
    }

    /**
     * Delete a notification from inbox.
     */
    public function deleteNotification(string $notificationId): void
    {
        Auth::user()?->notifications()->whereKey($notificationId)->delete();
    }

    /**
     * Open notification history modal.
     */
    public function openHistoryModal(): void
    {
        $this->showHistoryModal = true;
    }

    /**
     * Close notification history modal.
     */
    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
    }

    /**
     * Get unread notification count.
     */
    #[Computed]
    public function unreadCount(): int
    {
        return Auth::user()?->unreadNotifications()->count() ?? 0;
    }

    /**
     * Get latest notifications.
     *
     * @return Collection<int, DatabaseNotification>
     */
    #[Computed]
    public function notifications(): Collection
    {
        return Auth::user()?->notifications()
            ->latest()
            ->limit($this->limit)
            ->get() ?? collect();
    }

    /**
     * Get read notification history.
     *
     * @return Collection<int, DatabaseNotification>
     */
    #[Computed]
    public function readNotifications(): Collection
    {
        return Auth::user()?->notifications()
            ->whereNotNull('read_at')
            ->latest()
            ->limit($this->historyLimit)
            ->get() ?? collect();
    }

    public function render(): View
    {
        return view('livewire.notifications.center');
    }
}
