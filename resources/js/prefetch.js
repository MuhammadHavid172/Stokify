/**
 * Stokify Instant Prefetch (Hover Intent & Pointer Down)
 * Memungkinkan navigasi antar menu terasa instan (0 detik)
 * dengan memuat awal snapshot ke dalam Turbo Cache.
 */

const PREFETCH_DELAY = 65; // ms hover intent delay
const CACHE_TTL_MS = 15000; // 15 detik masa simpan cache sebelum boleh di-prefetch ulang

const prefetchedUrls = new Map();
let hoverTimer = null;
let currentHoveredLink = null;

/**
 * Memeriksa apakah tautan aman dan layak untuk di-prefetch.
 */
function isPrefetchable(link) {
    if (!link || !link.href) return false;

    // Abaikan jika tautan berada di dalam form atau form submitter
    if (link.closest('form')) return false;

    // Cek protokol & domain (hanya same-origin)
    let url;
    try {
        url = new URL(link.href, window.location.origin);
    } catch {
        return false;
    }

    if (!['http:', 'https:'].includes(url.protocol)) return false;
    if (url.origin !== window.location.origin) return false;

    // Abaikan tautan ke halaman yang sedang aktif saat ini
    if (url.pathname === window.location.pathname && url.search === window.location.search) {
        return false;
    }

    // Abaikan hash anchor (#)
    const hrefAttr = link.getAttribute('href') || '';
    if (hrefAttr.startsWith('#') || (url.hash && url.pathname === window.location.pathname)) {
        return false;
    }

    // Abaikan tautan keluar (logout), file download, dan metode non-GET
    if (url.pathname.includes('/logout')) return false;
    if (link.hasAttribute('download')) return false;

    const fileExtensionPattern = /\.(pdf|xlsx|xls|csv|zip|rar|jpg|jpeg|png|webp|svg|gif)$/i;
    if (fileExtensionPattern.test(url.pathname)) return false;

    // Hormati atribut opt-out Turbo & kustom
    if (link.matches('[data-turbo="false"], [data-no-prefetch], [data-no-instant]')) {
        return false;
    }
    const turboMethod = link.getAttribute('data-turbo-method');
    if (turboMethod && turboMethod.toLowerCase() !== 'get') {
        return false;
    }
    if (link.closest('[data-turbo="false"], [data-no-prefetch]')) {
        return false;
    }

    return true;
}

/**
 * Melakukan prefetch untuk elemen tautan.
 */
export function prefetch(link) {
    if (!isPrefetchable(link)) return;

    const url = new URL(link.href, window.location.origin);
    const key = url.pathname + url.search;
    const now = Date.now();

    // Hindari prefetch berulang dalam jendela TTL
    const lastTime = prefetchedUrls.get(key);
    if (lastTime && (now - lastTime < CACHE_TTL_MS)) {
        return;
    }
    prefetchedUrls.set(key, now);

    // 1. Integrasi Turbo Preloader bawaan
    if (window.Turbo?.session?.preloader?.preloadURL) {
        try {
            // Jika ada snapshot lama di snapshotCache yang sudah melebihi TTL, bersihkan dulu
            if (window.Turbo.session.view?.snapshotCache?.has(url)) {
                window.Turbo.session.view.snapshotCache.evict(url);
            }
            window.Turbo.session.preloader.preloadURL(link);
            return;
        } catch (err) {
            // Lanjut ke fallback di bawah jika terjadi kendala
        }
    }

    // 2. Fallback: Browser prefetch menggunakan link rel="prefetch"
    try {
        if (document.createElement('link').relList?.supports?.('prefetch')) {
            const linkElem = document.createElement('link');
            linkElem.rel = 'prefetch';
            linkElem.href = url.href;
            linkElem.as = 'document';
            document.head.appendChild(linkElem);
        }
    } catch {
        // Fallback aman
    }
}

/**
 * Menginisialisasi listener interaksi hover dan sentuhan.
 */
export function initInstantPrefetch() {
    // 1. Hover intent (65ms) saat kursor diarahkan ke tautan
    document.addEventListener('mouseover', (e) => {
        const link = e.target.closest('a');
        if (!link || !isPrefetchable(link)) return;

        if (currentHoveredLink === link) return;
        currentHoveredLink = link;

        if (hoverTimer) clearTimeout(hoverTimer);
        hoverTimer = setTimeout(() => {
            prefetch(link);
            hoverTimer = null;
        }, PREFETCH_DELAY);
    }, { capture: true, passive: true });

    // 2. Mouseout: Batalkan jika kursor berpindah sebelum 65ms
    document.addEventListener('mouseout', (e) => {
        if (!currentHoveredLink) return;
        const related = e.relatedTarget;
        if (related && currentHoveredLink.contains(related)) return; // Masih di dalam elemen tautan

        if (hoverTimer) {
            clearTimeout(hoverTimer);
            hoverTimer = null;
        }
        currentHoveredLink = null;
    }, { capture: true, passive: true });

    // 3. Touchstart / Pointerdown: Prefetch seketika (0ms) saat ditekan
    const instantHandler = (e) => {
        const link = e.target.closest('a');
        if (!link || !isPrefetchable(link)) return;
        prefetch(link);
    };

    document.addEventListener('touchstart', instantHandler, { capture: true, passive: true });
    document.addEventListener('pointerdown', instantHandler, { capture: true, passive: true });

    // 4. Invalidate cache saat ada submit non-GET agar data inventori & keuangan selalu akurat
    document.addEventListener('turbo:submit-end', () => {
        prefetchedUrls.clear();
        if (window.Turbo?.cache) {
            window.Turbo.cache.clear();
        }
    });
}
