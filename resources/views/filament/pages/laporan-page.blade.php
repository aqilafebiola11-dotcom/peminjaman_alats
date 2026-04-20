<x-filament-panels::page>
    {{-- Preview area: fills the empty space below the header actions --}}
    <div class="laporan-preview-container">
        @if ($previewUrl)
            {{-- Header bar with title, download button, and close button --}}
            <div class="preview-toolbar">
                <div class="preview-toolbar-left">
                    <svg xmlns="http://www.w3.org/2000/svg" class="preview-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="preview-title">{{ $previewTitle }}</span>
                </div>
                <div class="preview-toolbar-right">
                    <a href="{{ $downloadUrl }}" class="preview-download-btn" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download PDF
                    </a>
                    <button wire:click="closePreview" class="preview-close-btn" title="Tutup Preview">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- PDF iframe preview --}}
            <div class="preview-frame-wrapper">
                <iframe
                    src="{{ $previewUrl }}"
                    class="preview-iframe"
                    frameborder="0"
                ></iframe>
            </div>
        @else
            {{-- Empty state placeholder --}}
            <div class="preview-empty-state">
                <div class="empty-state-icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <h3 class="empty-state-title">Belum Ada Preview Laporan</h3>
                <p class="empty-state-description">
                    Klik salah satu tombol di atas untuk menghasilkan preview laporan.<br>
                    Anda dapat memilih filter periode tanggal sebelum menampilkan preview.
                </p>
            </div>
        @endif
    </div>

    <style>
        .laporan-preview-container {
            min-height: 500px;
            display: flex;
            flex-direction: column;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
            border: 1px solid rgb(229, 231, 235);
        }

        .dark .laporan-preview-container {
            background: rgb(31, 41, 55);
            border-color: rgb(55, 65, 81);
        }

        /* ---- Toolbar ---- */
        .preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            color: white;
            flex-shrink: 0;
        }

        .preview-toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-icon {
            width: 22px;
            height: 22px;
            opacity: 0.9;
        }

        .preview-title {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .preview-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-download-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: white;
            color: #4f46e5;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .preview-download-btn:hover {
            background: #eef2ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            color: #4338ca;
        }

        .preview-close-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .preview-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .btn-icon {
            width: 18px;
            height: 18px;
        }

        /* ---- Iframe ---- */
        .preview-frame-wrapper {
            flex: 1;
            min-height: 600px;
            position: relative;
        }

        .preview-iframe {
            width: 100%;
            height: 100%;
            min-height: 600px;
            border: none;
            display: block;
        }

        /* ---- Empty state ---- */
        .preview-empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state-icon-wrapper {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            background: linear-gradient(135deg, #ede9fe, #dbeafe);
            margin-bottom: 20px;
        }

        .dark .empty-state-icon-wrapper {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(14, 165, 233, 0.15));
        }

        .empty-state-icon {
            width: 40px;
            height: 40px;
            color: #6366f1;
        }

        .dark .empty-state-icon {
            color: #818cf8;
        }

        .empty-state-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 8px 0;
        }

        .dark .empty-state-title {
            color: #e5e7eb;
        }

        .empty-state-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            max-width: 400px;
            margin: 0;
        }

        .dark .empty-state-description {
            color: #9ca3af;
        }
    </style>
</x-filament-panels::page>
