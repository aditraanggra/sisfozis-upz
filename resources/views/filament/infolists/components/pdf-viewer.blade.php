@php
    $url = $getState();
    $label = $getLabel();
    $uid = 'pv' . substr(md5(($url ?? '') . $label), 0, 10);
@endphp

{{-- Load PDF.js from CDN only once per page --}}
<script>
    if (!document.getElementById('pdfjs-lib-script')) {
        var _s = document.createElement('script');
        _s.id = 'pdfjs-lib-script';
        _s.src = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
        _s.onload = function () {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            window.dispatchEvent(new CustomEvent('pdfjsReady'));
        };
        document.head.appendChild(_s);
    }
</script>

<div class="pdf-viewer-entry mb-6">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">{{ $label }}</dt>

    @if ($url)
        <dd>
            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">

                {{-- ── Header bar ── --}}
                <div class="flex items-center justify-between px-4 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</span>
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        Buka / Unduh
                    </a>
                </div>

                {{-- ── PDF canvas container ── --}}
                <div
                    id="{{ $uid }}-wrap"
                    class="relative bg-gray-100 dark:bg-gray-900 overflow-y-auto"
                    style="height: 560px;"
                >
                    {{-- Loading spinner --}}
                    <div id="{{ $uid }}-loading" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-gray-400 dark:text-gray-500 text-sm">
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 3 12 3 12h1z"></path>
                        </svg>
                        <span>Memuat PDF…</span>
                    </div>

                    {{-- Error state --}}
                    <div id="{{ $uid }}-error" class="absolute inset-0 hidden flex-col items-center justify-center gap-2 text-red-500 text-sm">
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-400" />
                        <span>Gagal memuat PDF.</span>
                        <a href="{{ $url }}" target="_blank" class="text-primary-600 underline text-xs">
                            Klik di sini untuk membuka langsung
                        </a>
                    </div>

                    {{-- Pages rendered here --}}
                    <div id="{{ $uid }}-pages" class="flex flex-col items-center gap-4 py-4 px-2"></div>
                </div>

                {{-- ── Page-count footer ── --}}
                <div
                    id="{{ $uid }}-footer"
                    class="hidden items-center justify-center px-4 py-1.5 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700"
                >
                    <span id="{{ $uid }}-info" class="text-xs text-gray-400 dark:text-gray-500"></span>
                </div>

            </div>
        </dd>
    @else
        <dd class="text-sm text-gray-400 dark:text-gray-500 italic">Tidak ada dokumen</dd>
    @endif
</div>

@if ($url)
<script>
(function () {
    var UID     = {{ Js::from($uid) }};
    var PDF_URL = {{ Js::from($url) }};

    function el(suffix) { return document.getElementById(UID + suffix); }

    function showError() {
        el('-loading').style.display = 'none';
        var err = el('-error');
        err.style.display = 'flex';
    }

    function renderPage(pdf, pageNum, container, wrapWidth) {
        pdf.getPage(pageNum).then(function (page) {
            var scale    = Math.min((wrapWidth - 32) / page.getViewport({ scale: 1 }).width, 1.8);
            var viewport = page.getViewport({ scale: scale });

            var canvas       = document.createElement('canvas');
            canvas.width     = viewport.width;
            canvas.height    = viewport.height;
            canvas.style.cssText = 'max-width:100%;box-shadow:0 2px 8px rgba(0,0,0,.15);border-radius:4px;';

            container.appendChild(canvas);

            page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise.then(function () {
                if (pageNum === pdf.numPages) {
                    el('-loading').style.display = 'none';
                    // Show footer with page count
                    var footer = el('-footer');
                    footer.classList.remove('hidden');
                    footer.classList.add('flex');
                    el('-info').textContent = pdf.numPages + ' halaman';
                }
            });
        });
    }

    function initViewer() {
        if (!window.pdfjsLib) return;

        var wrap      = el('-wrap');
        var container = el('-pages');
        var wrapWidth = wrap.clientWidth || 800;

        pdfjsLib.getDocument({ url: PDF_URL, withCredentials: false }).promise
            .then(function (pdf) {
                container.innerHTML = '';
                var limit = Math.min(pdf.numPages, 30); // cap at 30 pages for perf
                for (var i = 1; i <= limit; i++) {
                    renderPage(pdf, i, container, wrapWidth);
                }
            })
            .catch(function (err) {
                console.error('[pdf-viewer] ' + UID, err);
                showError();
            });
    }

    if (window.pdfjsLib) {
        initViewer();
    } else {
        window.addEventListener('pdfjsReady', initViewer, { once: true });
        // Fallback poll in case the event fired before this script ran
        var _poll = setInterval(function () {
            if (window.pdfjsLib) { clearInterval(_poll); initViewer(); }
        }, 150);
    }
})();
</script>
@endif
