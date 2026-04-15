<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $applicationName = config('app.name') === 'Laravel'
        ? 'APK Vendor'
        : config('app.name');
@endphp

<title>
    {{ filled($title ?? null) ? $title.' - '.$applicationName : $applicationName }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    if (! window.localStorage.getItem('flux.appearance')) {
        window.localStorage.setItem('flux.appearance', 'light');
    }

    window.swalConfirmDialog = async function (options = {}) {
        if (typeof window.Swal === 'undefined') {
            return window.confirm(options.text ?? 'Are you sure?');
        }

        const result = await window.Swal.fire({
            title: options.title ?? 'Konfirmasi Aksi',
            text: options.text ?? 'Pastikan data yang Anda kirim sudah benar.',
            icon: options.icon ?? 'warning',
            showCancelButton: true,
            confirmButtonText: options.confirmButtonText ?? 'Ya, lanjutkan',
            cancelButtonText: options.cancelButtonText ?? 'Batal',
            reverseButtons: true,
            background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#18181b',
        });

        return result.isConfirmed === true;
    };

    if (!window.__swalSubmitInterceptorBound) {
        window.__swalSubmitInterceptorBound = true;

        document.addEventListener('submit', async (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!form.hasAttribute('data-swal-confirm')) {
                return;
            }

            if (form.dataset.swalConfirmed === '1') {
                form.dataset.swalConfirmed = '0';
                return;
            }

            if (form.dataset.swalProcessing === '1') {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();

                return;
            }

            event.preventDefault();
            event.stopPropagation();
            event.stopImmediatePropagation();

            form.dataset.swalProcessing = '1';

            const confirmed = await window.swalConfirmDialog({
                title: form.dataset.swalTitle,
                text: form.dataset.swalText,
                icon: form.dataset.swalIcon,
                confirmButtonText: form.dataset.swalConfirmText,
                cancelButtonText: form.dataset.swalCancelText,
            });

            if (!confirmed) {
                form.dataset.swalProcessing = '0';

                return;
            }

            form.dataset.swalConfirmed = '1';
            form.dataset.swalProcessing = '0';

            if (typeof form.requestSubmit === 'function') {
                if (event.submitter !== undefined) {
                    form.requestSubmit(event.submitter);
                } else {
                    form.requestSubmit();
                }

                return;
            }

            form.submit();
        }, true);
    }
</script>
@fluxAppearance
