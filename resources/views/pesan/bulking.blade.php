@extends('template')

@section('css')
@include('pesan.partials.wa-preview-styles')
<style>
    .bulking-builder-card .card-header {
        align-items: flex-start;
    }

    .target-method-picker {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .target-method-option {
        position: relative;
        margin: 0;
        cursor: pointer;
    }

    .target-method-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .target-method-option-body {
        border: 1px solid #dfe4ea;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        background: #fff;
        transition: all 0.2s ease;
        min-height: 100%;
    }

    .target-method-option-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.35rem;
        font-weight: 700;
        color: #34395e;
    }

    .target-method-option-copy {
        margin: 0;
        font-size: 12px;
        color: #98a6ad;
        line-height: 1.5;
    }

    .target-method-option input:checked + .target-method-option-body {
        border-color: #6777ef;
        box-shadow: 0 0 0 3px rgba(103, 119, 239, 0.12);
        background: #fbfbff;
    }

    .target-method-card {
        border: 1px solid #f1f3f5;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: none;
    }

    .target-method-card .card-header {
        padding: 0;
        background: #fff;
        border-bottom: 1px solid #f1f3f5;
    }

    .target-method-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 1rem 1.25rem;
        color: #34395e;
        text-decoration: none;
        font-weight: 600;
    }

    .target-method-toggle:hover,
    .target-method-toggle:focus {
        color: #6777ef;
        text-decoration: none;
    }

    .target-method-toggle .method-copy {
        font-size: 12px;
        color: #98a6ad;
        font-weight: 500;
    }

    .bulking-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .bulking-meta .badge {
        font-size: 11px;
        padding: 0.45rem 0.7rem;
    }

    .bulking-summary-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .bulking-summary-list li {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0;
        border-bottom: 1px dashed #e9ecef;
        font-size: 13px;
    }

    .bulking-summary-list li:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .bulking-summary-inline {
        border: 1px solid #eef1f7;
        border-radius: 12px;
        background: #fcfcfd;
        padding: 1rem 1.1rem;
    }

    .bulking-summary-inline-title {
        font-size: 13px;
        font-weight: 700;
        color: #34395e;
        margin-bottom: 0.75rem;
    }

    .bulking-summary-inline-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .bulking-summary-inline-item {
        background: #fff;
        border: 1px solid #eef1f7;
        border-radius: 10px;
        padding: 0.8rem 0.9rem;
    }

    .bulking-summary-inline-label {
        display: block;
        font-size: 11px;
        color: #98a6ad;
        margin-bottom: 0.2rem;
    }

    .bulking-summary-inline-value {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #34395e;
        word-break: break-word;
    }

    .bulking-drop-note {
        background: #f8f9fa;
        border: 1px dashed #dfe4ea;
        border-radius: 10px;
        padding: 0.9rem 1rem;
    }

    @media (max-width: 767.98px) {
        .target-method-picker {
            grid-template-columns: 1fr;
        }

        .bulking-summary-inline-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @include('pesan.partials.connection-status', ['connection' => $connection])
    </div>

    <div class="col-lg-7">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="card bulking-builder-card" style="position: relative;">
            @include('pesan.partials.active-campaign-overlay')
            <div class="card-header">
                <div>
                    <h4>Form Pesan Bulking</h4>
                    <p class="mb-0 text-muted">Siapkan target, susun isi pesan, lalu cek preview sebelum pesan bulking anda dikirim</p>
                </div>
            </div>
            <div class="card-body">
                <form id="bulkingForm" action="{{ route('bulking.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="nama_campaign">Nama Campaign</label>
                        <input
                            type="text"
                            class="form-control @error('nama_campaign') is-invalid @enderror"
                            id="nama_campaign"
                            name="nama_campaign"
                            value="{{ old('nama_campaign') }}"
                            placeholder="Contoh: Promo Mei 2026"
                        >
                    </div>

                    <div class="mb-4">
                        <label class="d-block font-weight-600 mb-2">Pilih Metode Input Target</label>
                        <div class="target-method-picker">
                            <label class="target-method-option">
                                <input
                                    type="radio"
                                    name="target_method"
                                    value="manual"
                                    {{ old('target_method', 'manual') === 'manual' ? 'checked' : '' }}
                                >
                                <div class="target-method-option-body">
                                    <div class="target-method-option-title">
                                        <span>Input Manual</span>
                                        <span class="badge badge-primary">Aktif</span>
                                    </div>
                                    <p class="target-method-option-copy">Masukkan beberapa nomor WhatsApp langsung ke form.</p>
                                </div>
                            </label>

                            <label class="target-method-option">
                                <input
                                    type="radio"
                                    name="target_method"
                                    value="excel"
                                    {{ old('target_method') === 'excel' ? 'checked' : '' }}
                                >
                                <div class="target-method-option-body">
                                    <div class="target-method-option-title">
                                        <span>Import Excel / CSV</span>
                                        <span class="badge badge-light">Alternatif</span>
                                    </div>
                                    <p class="target-method-option-copy">Upload file daftar penerima untuk campaign yang lebih besar.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="card target-method-card mb-4" id="manual-target-panel">
                        <div class="card-header">
                            <div class="target-method-toggle">
                                <span>
                                    Input Nomor WhatsApp Manual
                                    <span class="d-block method-copy">Masukkan beberapa nomor sekaligus, dipisahkan dengan titik koma atau baris baru.</span>
                                </span>
                                <span class="badge badge-primary" id="manual-target-count">0 nomor</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label for="manual_numbers">Daftar Nomor</label>
                                <textarea class="form-control @error('manual_numbers') is-invalid @enderror" id="manual_numbers" name="manual_numbers" rows="6" placeholder="6281234567890;6289876543210&#10;628111222333">{{ old('manual_numbers') }}</textarea>
                            </div>
                            <small class="text-muted d-block mb-3">Contoh input: <code>6281234567890;6289876543210</code> atau satu nomor per baris.</small>
                        </div>
                    </div>

                    <div class="card target-method-card mb-4 d-none" id="excel-target-panel">
                        <div class="card-header">
                            <div class="target-method-toggle">
                                <span>
                                    Import Target dari Excel / CSV
                                    <span class="d-block method-copy">Upload file daftar penerima untuk campaign dengan target lebih banyak.</span>
                                </span>
                                <span class="badge badge-light" id="excel-file-badge">Belum ada file</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="excel_file">File Excel / CSV</label>
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror" id="excel_file" name="excel_file" accept=".xlsx,.csv" disabled>
                                <small class="form-text text-muted">Gunakan file dengan format <code>.xlsx</code> atau <code>.csv</code>.</small>
                            </div>
                            <div class="bulking-drop-note mb-3">
                                <strong class="d-block mb-1">Format file yang disarankan</strong>
                                Gunakan kolom <code>nomor</code>, <code>nama</code>, atau kolom data penerima lain agar daftar target lebih rapi.
                            </div>
                            <a href="{{ asset('templates/bulking-template.csv') }}" class="btn btn-outline-primary">
                                <i class="fas fa-download mr-2"></i> Download Template CSV
                            </a>
                        </div>
                    </div>

                    <div class="card mb-0 shadow-none border">
                        <div class="card-header">
                            <div>
                                <h4 class="mb-1">Panel Chat</h4>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="bulking-summary-inline mb-4">
                                <div class="bulking-summary-inline-title">Ringkasan Target</div>
                                <div class="bulking-summary-inline-grid">
                                    <div class="bulking-summary-inline-item">
                                        <span class="bulking-summary-inline-label">Metode target aktif</span>
                                        <span class="bulking-summary-inline-value" id="active-target-method">Manual</span>
                                    </div>
                                    <div class="bulking-summary-inline-item">
                                        <span class="bulking-summary-inline-label">Jumlah nomor manual</span>
                                        <span class="bulking-summary-inline-value" id="manual-target-count-summary">0 nomor</span>
                                    </div>
                                    <div class="bulking-summary-inline-item">
                                        <span class="bulking-summary-inline-label">File Excel dipilih</span>
                                        <span class="bulking-summary-inline-value" id="excel-file-name">Belum ada file</span>
                                    </div>
                                    <div class="bulking-summary-inline-item">
                                        <span class="bulking-summary-inline-label">Panjang pesan</span>
                                        <span class="bulking-summary-inline-value" id="message-length">0 karakter</span>
                                    </div>
                                </div>
                            </div>

                            @include('pesan.partials.chat-composer', [
                                'textareaId' => 'bulking_pesan',
                                'textareaName' => 'bulking_pesan',
                                'textareaValue' => old('bulking_pesan'),
                                'textareaLabel' => 'Isi Pesan Bulking',
                                'textareaPlaceholder' => 'Tulis pesan broadcast di sini...',
                                'mediaId' => 'bulking_media',
                                'mediaName' => 'bulking_media',
                                'mediaHelper' => 'Preview gambar akan tampil real-time di panel kanan. Untuk file non-gambar, preview visual tidak bisa ditampilkan.',
                            ])

                            <div class="card border mb-4">
                                <div class="card-header">
                                    <h4 class="mb-0">Pengaturan Anti-Ban</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="min_delay">Min Delay (detik)</label>
                                                <input type="number" class="form-control @error('min_delay') is-invalid @enderror" id="min_delay" name="min_delay" min="0" value="{{ old('min_delay', 10) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="max_delay">Max Delay (detik)</label>
                                                <input type="number" class="form-control @error('max_delay') is-invalid @enderror" id="max_delay" name="max_delay" min="0" value="{{ old('max_delay', 30) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="batch_size">Batch Size</label>
                                                <input type="number" class="form-control @error('batch_size') is-invalid @enderror" id="batch_size" name="batch_size" min="1" value="{{ old('batch_size', 10) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="rest_after_batch">Cooldown (detik)</label>
                                                <input type="number" class="form-control @error('rest_after_batch') is-invalid @enderror" id="rest_after_batch" name="rest_after_batch" min="0" value="{{ old('rest_after_batch', 120) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="retry_limit">Retry Limit</label>
                                                <input type="number" class="form-control @error('retry_limit') is-invalid @enderror" id="retry_limit" name="retry_limit" min="0" value="{{ old('retry_limit', 0) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between border-top pt-3">
                                <div class="bulking-meta mb-3 mb-md-0">
                                    <span class="badge badge-light" id="selected-method-badge">Mode Manual</span>
                                    <span class="badge badge-light">Live Preview Aktif</span>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-paper-plane mr-2"></i> Proses Pesan Bulking
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @include('pesan.partials.wa-live-preview', [
            'previewContentId' => 'bulking-preview-content',
            'mediaPreviewId' => 'bulking-media-preview-img',
        ])
    </div>
</div>
@endsection

@section('javascript')
@include('pesan.partials.wa-preview-script')
<script>
    $(document).ready(function() {
        const $manualNumbers = $('#manual_numbers');
        const $excelFile = $('#excel_file');
        const $message = $('#bulking_pesan');
        const $methodInputs = $('input[name="target_method"]');
        const $manualPanel = $('#manual-target-panel');
        const $excelPanel = $('#excel-target-panel');
        const $manualCountBadge = $('#manual-target-count');
        const $manualCountSummary = $('#manual-target-count-summary');
        const $excelFileBadge = $('#excel-file-badge');
        const $excelFileName = $('#excel-file-name');
        const $activeTargetMethod = $('#active-target-method');
        const $messageLength = $('#message-length');
        const $selectedMethodBadge = $('#selected-method-badge');

        window.initWhatsappPreview({
            namespace: 'bulking',
            messageSelector: '#bulking_pesan',
            previewSelector: '#bulking-preview-content',
            mediaSelector: '#bulking_media',
            mediaPreviewSelector: '#bulking-media-preview-img'
        });

        function extractManualTargets() {
            return $manualNumbers.val()
                .split(/[\n;,]+/)
                .map(function(item) {
                    return item.trim();
                })
                .filter(function(item) {
                    return item.length > 0;
                });
        }

        function updateManualSummary() {
            const count = extractManualTargets().length;
            const text = count + ' nomor';
            $manualCountBadge.text(text);
            $manualCountSummary.text(getSelectedMethod() === 'manual' ? text : 'Tidak dipakai');
        }

        function updateExcelSummary() {
            const file = $excelFile[0].files[0];
            const text = file ? file.name : 'Belum ada file';
            $excelFileBadge.text(text);
            $excelFileName.text(getSelectedMethod() === 'excel' ? text : 'Tidak dipakai');
        }

        function updateMessageLength() {
            $messageLength.text($message.val().length + ' karakter');
        }

        function getSelectedMethod() {
            return $methodInputs.filter(':checked').val();
        }

        function applyTargetMethod() {
            const method = getSelectedMethod();
            const isManual = method === 'manual';

            $manualPanel.toggleClass('d-none', !isManual);
            $excelPanel.toggleClass('d-none', isManual);
            $manualNumbers.prop('disabled', !isManual);
            $excelFile.prop('disabled', isManual);
            $activeTargetMethod.text(isManual ? 'Manual Target' : 'Excel / CSV');
            $selectedMethodBadge.text(isManual ? 'Mode Manual Target' : 'Mode Excel / CSV');

            updateManualSummary();
            updateExcelSummary();
        }

        $methodInputs.on('change', applyTargetMethod);
        $manualNumbers.on('input', updateManualSummary);
        $excelFile.on('change', updateExcelSummary);
        $message.on('input', updateMessageLength);

        applyTargetMethod();
        updateManualSummary();
        updateExcelSummary();
        updateMessageLength();
    });
</script>
@endsection
