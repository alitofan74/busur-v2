@extends('template')

@section('css')
<style>
    .qr-placeholder {
        width: 260px;
        height: 260px;
        margin: 0 auto;
        border: 2px dashed #d6d6d6;
        border-radius: 18px;
        background: #fafafa;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #6c6c6c;
    }

    .qr-placeholder i {
        font-size: 3rem;
    }

    .status-badge {
        min-width: 110px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Whatsapp QR Code</h4>
                <span id="status-badge" class="text-uppercase badge badge-{{ in_array($qrData['status'], ['Terhubung']) ? 'success' : (in_array($qrData['status'], ['Menunggu Scan', 'Menunggu Inisialisasi']) ? 'warning' : 'danger') }} status-badge">{{ $qrData['status'] }}</span>
            </div>
            <div class="card-body text-center">
                <div id="qr-container">
                    @if($qrData['qr_image'])
                        <img src="{!! $qrData['qr_image'] !!}" alt="QR Code" class="img-fluid mb-3" style="max-width: 260px; max-height: 260px;">
                    @else
                        <div class="qr-placeholder mb-3">
                            <i class="fas fa-qrcode"></i>
                            <span class="mt-3">QR Code akan tampil di sini</span>
                        </div>
                    @endif
                </div>

                <div id="refresh-container" style="{{ $qrData['status'] === 'Terhubung' ? 'display: none;' : '' }}">
                    <p class="text-muted">Scan QR code ini melalui WhatsApp di ponsel Anda untuk menggunakan layanan ini.</p>
                    <button id="btn-refresh-qr" class="btn btn-primary btn-lg">Segarkan QR</button>
                    <button onclick="location.reload()" class="btn btn-outline-primary btn-lg ml-2">
                        <i class="fas fa-sync-alt"></i> Refresh Halaman
                    </button>
                    <hr>
                </div>


                <div class="row text-left mb-3">
                    <div class="col-6">
                        <strong>Status sesi</strong>
                        <p id="session-info" class="text-muted mb-0">{{ $qrData['session'] }}</p>
                    </div>
                    <div class="col-6">
                        <strong>Nomor WA</strong>
                        <p id="phone-info" class="text-muted mb-0">{{ $qrData['phone'] }}</p>
                    </div>
                </div>

                <div class="row text-left mb-3">
                    <div class="col-6">
                        <strong>Terakhir diperbarui</strong>
                        <p id="last-updated" class="text-muted mb-0">{{ $qrData['last_updated'] }}</p>
                    </div>
                    <div class="col-6">
                        <strong>Exp. QR</strong>
                        <p id="expires-in" class="text-muted mb-0">{{ $qrData['expires_in'] }}</p>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Petunjuk Scan</h4>
                    </div>
                    <div class="card-body">
                        <ol class="pl-3">
                            @foreach($qrData['scan_instructions'] as $step)
                                <li class="mb-2">{{ $step }}</li>
                            @endforeach
                        </ol>
                        <div class="alert alert-info">
                            <strong>Tip:</strong> Pastikan koneksi internet lancar dan WhatsApp Anda telah aktif.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Syarat & Ketentuan</h4>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            @foreach($qrData['terms'] as $term)
                                <li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i>{{ $term }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        const $statusBadge = $('#status-badge');
        const $qrContainer = $('#qr-container');
        const $sessionInfo = $('#session-info');
        const $phoneInfo = $('#phone-info');
        const $lastUpdated = $('#last-updated');
        const $expiresIn = $('#expires-in');
        const $btnRefresh = $('#btn-refresh-qr');
        const $refreshContainer = $('#refresh-container');

        let currentStatus = "{{ $qrData['status'] }}";
        let pollingTimer;

        function updateDashboard() {
            // Stop if tab is hidden
            if (document.hidden) {
                pollingTimer = setTimeout(updateDashboard, 5000); // Check again in 5s if still hidden
                return;
            }

            $.getJSON("{{ route('dashboard.status') }}", function(data) {
                // Update status badge
                if ($statusBadge.text() !== data.status) {
                    $statusBadge.text(data.status);
                    
                    // Update badge color
                    $statusBadge.removeClass('badge-success badge-warning badge-danger');
                    if (data.status === 'Terhubung') {
                        $statusBadge.addClass('badge-success');
                        $refreshContainer.fadeOut();
                    } else if (['Menunggu Scan', 'Menunggu Inisialisasi'].includes(data.status)) {
                        $statusBadge.addClass('badge-warning');
                        $refreshContainer.fadeIn();
                    } else {
                        $statusBadge.addClass('badge-danger');
                        $refreshContainer.fadeIn();
                    }

                    currentStatus = data.status;
                }


                // Update QR Image / Status Illustration
                const hasImg = $qrContainer.find('img').length > 0;
                const hasSuccess = $qrContainer.find('.text-success').length > 0;
                const hasPlaceholder = $qrContainer.find('.qr-placeholder').length > 0 && !hasSuccess;

                if (data.qr_image) {
                    if (!hasImg || $qrContainer.find('img').attr('src') !== data.qr_image) {
                        $qrContainer.html(`<img src="${data.qr_image}" alt="QR Code" class="img-fluid mb-3" style="max-width: 260px; max-height: 260px;">`);
                    }
                } else if (data.status === 'Terhubung') {
                    if (!hasSuccess) {
                        $qrContainer.html(`
                            <div class="qr-placeholder mb-3 border-success" style="background: #f6fff9; border: 2px solid #28a745 !important;">
                                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                                <span class="mt-3 text-success font-weight-bold">WhatsApp Terhubung</span>
                            </div>`);
                    }
                } else {
                    // Show generic placeholder for initialization or error states without a QR
                    if (!hasPlaceholder) {
                        $qrContainer.html(`
                            <div class="qr-placeholder mb-3">
                                <i class="fas fa-qrcode"></i>
                                <span class="mt-3">QR Code akan tampil di sini</span>
                            </div>`);
                    }
                }


                // Update textual info
                $sessionInfo.text(data.session);
                $phoneInfo.text(data.phone);
                $lastUpdated.text(data.last_updated);
                $expiresIn.text(data.expires_in);

                // Set next poll based on status
                let nextPoll = data.status === 'Terhubung' ? 15000 : 2000;
                pollingTimer = setTimeout(updateDashboard, nextPoll);
            }).fail(function() {
                // On error, try again in 10s
                pollingTimer = setTimeout(updateDashboard, 10000);
            });
        }

        // Refresh QR button click
        $btnRefresh.on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            
            $this.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: "{{ route('dashboard.refresh') }}",
                type: 'GET',
                success: function(response) {
                    clearTimeout(pollingTimer);
                    updateDashboard();
                },
                complete: function() {
                    $this.prop('disabled', false).text('Segarkan QR');
                }
            });
        });

        // Start initial poll
        pollingTimer = setTimeout(updateDashboard, 2000);

        // Resume immediately when tab becomes visible
        document.addEventListener("visibilitychange", function() {
            if (!document.hidden) {
                clearTimeout(pollingTimer);
                updateDashboard();
            }
        });
    });
</script>
@endsection