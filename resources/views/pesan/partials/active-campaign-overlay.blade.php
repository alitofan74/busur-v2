@php
    $activeCampaign = \App\Models\Campaign::whereIn('status', ['queued', 'running', 'resting'])->first();
@endphp

@if($activeCampaign)
<div class="active-campaign-overlay-container">
    <div class="active-campaign-blur-bg"></div>
    <div class="active-campaign-overlay-card text-center p-4">
        <div class="active-campaign-icon mb-3">
            <div class="spinner-grow text-primary" role="status" style="width: 3.5rem; height: 3.5rem;">
                <span class="sr-only">Loading...</span>
            </div>
            <i class="fas fa-lock active-campaign-lock-icon"></i>
        </div>
        <h4 class="font-weight-bold text-dark mb-2">Sistem Sedang Mengirim Pesan Bulking</h4>
        <p class="text-muted px-lg-3" style="font-size: 13px; line-height: 1.6;">
            Saat ini, WhatsApp Gateway sedang memproses campaign bulking aktif: <strong>"{{ $activeCampaign->nama }}"</strong>. 
            Fitur <strong>Pesan Tunggal</strong> dan pembuatan <strong>Pesan Bulking</strong> baru dikunci sementara waktu demi kelancaran pengiriman pesan massal dan menjaga stabilitas koneksi nomor Anda.
        </p>
        <div class="card border bg-light my-3 p-3 text-left shadow-none" style="border-radius: 10px;">
            <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                <span class="text-muted">Nama Campaign:</span>
                <strong class="text-dark">{{ $activeCampaign->nama }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2" style="font-size: 13px;">
                <span class="text-muted">Status Pengiriman:</span>
                <span class="badge badge-warning text-capitalize" style="padding: 0.35rem 0.6rem; font-size: 11px;">{{ $activeCampaign->status }}</span>
            </div>
            <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                <span class="text-muted">Progress Pengiriman:</span>
                <strong class="text-dark">{{ $activeCampaign->terkirim + $activeCampaign->gagal }} / {{ $activeCampaign->total }} Pesan</strong>
            </div>
            <div class="progress mt-2" style="height: 8px; border-radius: 4px; background: #e9ecef;">
                @php
                    $percentage = $activeCampaign->total > 0 ? (($activeCampaign->terkirim + $activeCampaign->gagal) / $activeCampaign->total) * 100 : 0;
                @endphp
                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('bulking.show', $activeCampaign) }}" class="btn btn-primary btn-lg btn-block" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(103, 119, 239, 0.3);">
                <i class="fas fa-eye mr-2"></i> Lihat Progress Campaign
            </a>
        </div>
    </div>
</div>

<style>
.active-campaign-overlay-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    min-height: 400px;
    z-index: 1050;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    overflow: hidden;
}
.active-campaign-blur-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 1;
}
.active-campaign-overlay-card {
    position: relative;
    z-index: 2;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.08);
    max-width: 480px;
    width: 90%;
}
.active-campaign-icon {
    position: relative;
    display: inline-block;
}
.active-campaign-lock-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.25rem;
    color: #6777ef;
}
</style>
@endif
