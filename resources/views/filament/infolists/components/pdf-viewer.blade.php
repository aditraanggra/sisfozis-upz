@php
    $url = $getState();
    $label = $getLabel();
@endphp

<div class="pdf-viewer-entry">
    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">
        {{ $label }}
    </dt>

    @if ($url)
        <dd>
            <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-50 dark:bg-gray-900">
                <div class="flex items-center justify-between px-4 py-2 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $label }}</span>
                    <a
                        href="{{ $url }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="w-3.5 h-3.5" />
                        Buka / Unduh
                    </a>
                </div>
                <iframe
                    src="{{ $url }}#toolbar=0&view=FitH"
                    class="w-full"
                    style="height: 520px; border: none;"
                    loading="lazy"
                    title="{{ $label }}"
                >
                    <p class="p-4 text-sm text-gray-500 dark:text-gray-400">
                        Browser Anda tidak mendukung tampilan PDF secara langsung.
                        <a href="{{ $url }}" target="_blank" class="text-primary-600 underline">Klik di sini untuk mengunduh</a>.
                    </p>
                </iframe>
            </div>
        </dd>
    @else
        <dd class="text-sm text-gray-400 dark:text-gray-500 italic">Tidak ada dokumen</dd>
    @endif
</div>
