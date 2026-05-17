@extends('template')

@section('content')
<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible show fade">
                <div class="alert-body">
                    <button class="close" data-dismiss="alert"><span>&times;</span></button>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="mb-1">{{ $campaign->nama }}</h4>
                    <p class="mb-0 text-muted">Detail monitoring campaign bulking.</p>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('bulking.log') }}" class="btn btn-outline-primary">Lihat Semua Campaign</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Status</div>
                            <div class="h5 mb-0 text-uppercase">{{ $campaign->status }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Metode Input</div>
                            <div class="h5 mb-0 text-uppercase">{{ $campaign->tipe_input }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Total Target</div>
                            <div class="h5 mb-0">{{ $campaign->total }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Terkirim</div>
                            <div class="h5 mb-0 text-success">{{ $campaign->terkirim }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Gagal</div>
                            <div class="h5 mb-0 text-danger">{{ $campaign->gagal }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <div class="text-muted small">Terakhir Diproses</div>
                            <div class="h6 mb-0">{{ optional($campaign->last_processed_at)->format('d M Y H:i:s') ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="mb-3">Konfigurasi Anti-Ban</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th width="30%">Min Delay</th>
                                    <td>{{ data_get($campaign->settings, 'min_delay', '-') }} detik</td>
                                </tr>
                                <tr>
                                    <th>Max Delay</th>
                                    <td>{{ data_get($campaign->settings, 'max_delay', '-') }} detik</td>
                                </tr>
                                <tr>
                                    <th>Batch Size</th>
                                    <td>{{ data_get($campaign->settings, 'batch_size', '-') }}</td>
                                </tr>
                                <tr>
                                    <th>Rest After Batch</th>
                                    <td>{{ data_get($campaign->settings, 'rest_after_batch', '-') }} detik</td>
                                </tr>
                                <tr>
                                    <th>Retry Limit</th>
                                    <td>{{ data_get($campaign->settings, 'retry_limit', '-') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="mb-3">Daftar Pesan Campaign</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nomor</th>
                                    <th>Status</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaign->pesans as $index => $pesan)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $pesan->nomor }}</td>
                                        <td><span class="badge badge-light text-uppercase">{{ $pesan->status }}</span></td>
                                        <td>{{ $pesan->error_message ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada pesan yang terdaftar pada campaign ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
