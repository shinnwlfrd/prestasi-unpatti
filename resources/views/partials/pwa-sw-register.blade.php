{{-- PWA Service Worker Registration --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/service-worker.js')
                .then((registration) => {
                    console.log('[PWA] Service Worker registered:', registration.scope);

                    // Cek update service worker secara berkala
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'activated') {
                                console.log('[PWA] Service Worker updated');
                            }
                        });
                    });
                })
                .catch((error) => {
                    console.log('[PWA] Service Worker registration failed:', error);
                });
        });
    }
</script>