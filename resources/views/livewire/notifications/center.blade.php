<div wire:poll.20s>
    @php
        $normalizeNotificationUrl = static function (?string $url): ?string {
            if ($url === null || $url === '') {
                return null;
            }

            $parsedHost = parse_url($url, PHP_URL_HOST);
            if ($parsedHost === null) {
                return $url;
            }

            $currentHost = request()->getHost();
            $appHost = parse_url(config('app.url') ?? '', PHP_URL_HOST);

            if (in_array($parsedHost, array_filter([$currentHost, $appHost]), true)) {
                $path = parse_url($url, PHP_URL_PATH) ?? '/';
                $query = parse_url($url, PHP_URL_QUERY);
                $fragment = parse_url($url, PHP_URL_FRAGMENT);

                return $path
                    .($query !== null ? '?'.$query : '')
                    .($fragment !== null ? '#'.$fragment : '');
            }

            return $url;
        };
    @endphp

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
                <div class="flex items-center gap-3">
                    <flux:modal.trigger name="notification-history-modal">
                        <button type="button" class="text-xs text-zinc-600 hover:underline dark:text-zinc-300">
                            {{ __('View history') }}
                        </button>
                    </flux:modal.trigger>

                    @if ($this->unreadCount > 0)
                        <button type="button" wire:click="markAllAsRead" class="text-xs text-blue-600 hover:underline dark:text-blue-400">
                            {{ __('Mark all as read') }}
                        </button>
                    @endif
                </div>
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
                            @php
                                $actionUrl = $normalizeNotificationUrl($notification->data['action_url'] ?? null);
                            @endphp
                            @if (! empty($actionUrl))
                                <a href="{{ $actionUrl }}" x-on:click="$wire.markAsRead('{{ $notification->id }}')" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $notification->data['action_label'] ?? __('Open') }}
                                </a>
                            @endif

                            @if ($notification->read_at === null)
                                <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="text-zinc-600 hover:underline dark:text-zinc-300">
                                    {{ __('Mark read') }}
                                </button>
                            @endif

                            <button
                                type="button"
                                class="text-red-600 hover:underline dark:text-red-400"
                                x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Hapus Notifikasi?', text: 'Notifikasi ini akan dihapus dari daftar Anda.', confirmButtonText: 'Ya, hapus' })) { $wire.deleteNotification('{{ $notification->id }}') } })()"
                            >
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

    <flux:modal name="notification-history-modal" class="max-w-2xl">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">{{ __('Notification History') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Read notifications history.') }}</flux:text>
            </div>

            <div class="max-h-[60vh] space-y-3 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                @forelse ($this->readNotifications as $notification)
                    <div class="space-y-2 rounded-md border border-zinc-100 p-3 dark:border-zinc-800">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $notification->data['title'] ?? __('Notification') }}
                                </div>
                                <div class="mt-0.5 text-xs text-zinc-600 dark:text-zinc-400">
                                    {{ __('Read:') }} {{ $notification->read_at?->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ \Illuminate\Support\Str::limit($notification->data['message'] ?? '', 180) }}
                        </p>

                        <div class="flex items-center gap-3 text-xs">
                            @php
                                $historyActionUrl = $normalizeNotificationUrl($notification->data['action_url'] ?? null);
                            @endphp
                            @if (! empty($historyActionUrl))
                                <a href="{{ $historyActionUrl }}" class="text-blue-600 hover:underline dark:text-blue-400">
                                    {{ $notification->data['action_label'] ?? __('Open') }}
                                </a>
                            @endif

                            <button
                                type="button"
                                class="text-red-600 hover:underline dark:text-red-400"
                                x-on:click.prevent="(async () => { if (await window.swalConfirmDialog({ title: 'Hapus Notifikasi?', text: 'Notifikasi ini akan dihapus dari riwayat.', confirmButtonText: 'Ya, hapus' })) { $wire.deleteNotification('{{ $notification->id }}') } })()"
                            >
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('No read notifications yet.') }}
                    </div>
                @endforelse
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
