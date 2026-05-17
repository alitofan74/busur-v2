@extends('template')

@section('css')
<link rel="stylesheet" href="{{ asset('otika-assets/bundles/datatables/datatables.min.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div>
                    <h4 class="mb-1">Log Campaign Bulking</h4>
                    <p class="mb-0 text-muted">Ringkasan seluruh campaign bulking yang sudah dibuat.</p>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('bulking.index') }}" class="btn btn-primary">Buat Campaign Baru</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="bulkingLogTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Metode</th>
                                <th>Total</th>
                                <th>Terkirim</th>
                                <th>Gagal</th>
                                <th>Dibuat</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaigns as $campaign)
                                <tr>
                                    <td>{{ $campaign->nama }}</td>
                                    <td><span class="badge badge-light text-uppercase">{{ $campaign->status }}</span></td>
                                    <td class="text-uppercase">{{ $campaign->tipe_input }}</td>
                                    <td>{{ $campaign->total }}</td>
                                    <td>{{ $campaign->terkirim }}</td>
                                    <td>{{ $campaign->gagal }}</td>
                                    <td>{{ $campaign->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('bulking.show', $campaign) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Belum ada campaign bulking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('otika-assets/bundles/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#bulkingLogTable').DataTable({
            "order": [[ 6, "desc" ]],
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ baris",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(difilter dari _MAX_ total data)",
                "zeroRecords": "Tidak ada data yang cocok ditemukan",
                "paginate": {
                    "first": "Pertama",
                    "last": "Terakhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });
</script>
@endsection
