(() => {
  const banner = document.getElementById('offline-status-banner');
  const content = document.getElementById('offline-status-content');
  const dot = document.getElementById('offline-status-dot');
  const message = document.getElementById('offline-status-message');
  const retryButton = document.getElementById('offline-retry-button');

  if (!banner || !content || !dot || !message || !retryButton) {
    return;
  }

  const setOnlineState = () => {
    banner.classList.remove('hidden');
    content.className = 'mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 text-sm font-medium shadow-lg sm:px-6 lg:px-8 bg-emerald-600 text-white';
    dot.className = 'h-2.5 w-2.5 rounded-full bg-emerald-200';
    message.textContent = 'Koneksi kembali online. Anda dapat melanjutkan sinkronisasi data.';

    setTimeout(() => {
      banner.classList.add('hidden');
    }, 2500);
  };

  const setOfflineState = () => {
    banner.classList.remove('hidden');
    content.className = 'mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 text-sm font-medium shadow-lg sm:px-6 lg:px-8 bg-amber-600 text-white';
    dot.className = 'h-2.5 w-2.5 rounded-full bg-amber-200';
    message.textContent = 'Anda sedang offline. Data form penting tetap disimpan lokal sementara.';
  };

  const syncNow = () => {
    if (navigator.onLine) {
      window.location.reload();
      return;
    }

    setOfflineState();
  };

  retryButton.addEventListener('click', syncNow);

  window.addEventListener('online', setOnlineState);
  window.addEventListener('offline', setOfflineState);

  if (!navigator.onLine) {
    setOfflineState();
  }
})();
