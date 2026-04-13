<div wire:poll.20s>
    <flux:dropdown position="bottom" align="end">
        <flux:button variant="subtle" square class="relative" :title="__('Notifications')">
            <flux:icon.bell class="size-5" />
            @if ($this->unreadCount > 0)
                <span class="absolute -right-1 -top-1 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-[23rem] max-w-[90vw]">
            <div class="flex items-center justify-between px-3 py-2">
                <flux:heading>{{ __('Notifications') }}</flux:heading>
                @if ($this->unreadCount > 0)
                    <button type="button" wire:click="markAllAsRead" class="text-xs text-blue-600 hover:underline dark:text-blue-400">
                        {{ __('Mark all as read') }}
                    </button>
                @endif
            </div>

            <flux:menu.separator />

            <div class="max-h-96 overflow-y-auto">
                @forelse ($this->notifications as $notification)
                    <div class="space-y-2 border-b border-zinc-100 px-3 py-3 last:border-b-0 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $notification->data['title'] ?? __('Notification') }}
                                </div>
                                <div class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </div>
                            </div>

                            @if ($notification->read_at === null)
                                <span class="mt-1 inline-block size-2 rounded-full bg-blue-600"></span>
                            @endif
                        </div>

                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 140) }}
                        </p>

                        <div class="flex items-center gap-3 text-xs">
                            @if (! empty($notification->data['action_url']))
                                <a href="{{ $notification->data['action_url'] }}" wire:navigate wire:click="markAsRead('{{ $notification->id }}')" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $notification->data['action_label'] ?? __('Open') }}
                                </a>
                            @endif

                            @if ($notification->read_at === null)
                                <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="text-zinc-600 hover:underline dark:text-zinc-300">
                                    {{ __('Mark read') }}
                                </button>
                            @endif

                            <button type="button" wire:click="deleteNotification('{{ $notification->id }}')" class="text-red-600 hover:underline dark:text-red-400">
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No notifications yet.') }}
                    </div>
                @endforelse
            </div>
        </flux:menu>
    </flux:dropdown>
</div>
