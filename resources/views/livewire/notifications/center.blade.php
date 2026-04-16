<div @if (! $showHistoryModal) wire:poll.20s @endif>
    @php
        $normalizeNotificationUrl = static function (?string $url): ?string {
            if ($url === null || $url === '') {
                return null;
            }

            $basePath = trim((string) parse_url(config('app.url') ?? '', PHP_URL_PATH));
            $basePath = $basePath !== '' && $basePath !== '/' ? '/'.trim($basePath, '/') : '';

            $parsedHost = parse_url($url, PHP_URL_HOST);
            if ($parsedHost === null) {
                if ($basePath !== '' && ! str_starts_with($url, $basePath.'/') && $url !== $basePath) {
                    $url = '/'.ltrim($url, '/');
                    $url = $basePath.$url;
                }

                return $url;
            }

            $currentHost = request()->getHost();
            $appHost = parse_url(config('app.url') ?? '', PHP_URL_HOST);

            if (in_array($parsedHost, array_filter([$currentHost, $appHost]), true)) {
                $path = parse_url($url, PHP_URL_PATH) ?? '/';
                $query = parse_url($url, PHP_URL_QUERY);
                $fragment = parse_url($url, PHP_URL_FRAGMENT);

                if ($basePath !== '' && ! str_starts_with($path, $basePath.'/') && $path !== $basePath) {
                    $path = $basePath.('/'.ltrim($path, '/'));
                }

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
                    <button type="button" wire:click="openHistoryModal" class="text-xs text-zinc-600 hover:underline dark:text-zinc-300">
                        {{ __('View history') }}
                    </button>

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

    @if ($showHistoryModal)
        <div class="fixed inset-0 z-[90] flex items-center justify-center px-4 py-6">
            <button type="button" class="absolute inset-0 bg-black/55" wire:click="closeHistoryModal" aria-label="{{ __('Close') }}"></button>

            <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">
                    <div>
                        <flux:heading size="lg">{{ __('Notification History') }}</flux:heading>
                        <flux:text class="mt-1">{{ __('Read notifications history.') }}</flux:text>
                    </div>

                    <flux:button size="sm" variant="ghost" wire:click="closeHistoryModal">
                        {{ __('Close') }}
                    </flux:button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-4">
                    <div class="space-y-3">
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
                            <div class="rounded-lg border border-zinc-200 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                {{ __('No read notifications yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
