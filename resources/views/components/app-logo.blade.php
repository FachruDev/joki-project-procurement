@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="APK Vendor" {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-8 rounded-md object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="APK Vendor" {{ $attributes }}>
        <x-slot name="logo">
            <x-app-logo-icon class="size-8 rounded-md object-contain" />
        </x-slot>
    </flux:brand>
@endif
