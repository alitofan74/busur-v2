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
                    <h4 class="mb-1">Log Pesan Tunggal</h4>
                    <p class="mb-0 text-muted">Ringkasan riwayat seluruh pengiriman pesan tunggal.</p>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('pesan-tunggal.index') }}" class="btn btn-primary">Kirim Pesan Baru</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tunggalLogTable">
                        <thead>
                            <tr>
                                <th>Nomor WhatsApp</th>
                                <th>Isi Pesan</th>
                                <th>Media</th>
                                <th>Status</th>
                                <th>Waktu</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesans as $pesan)
                                <tr>
                                    <td>{{ $pesan->nomor }}</td>
                                    <td>{{ Str::limit($pesan->pesan, 60) }}</td>
                                    <td>
                                        @if($pesan->media_path)
                                            <span class="badge badge-info"><i class="fas fa-paperclip"></i> Ada</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pesan->status === 'sent')
                                            <span class="badge badge-success">TERKIRIM</span>
                                        @elseif($pesan->status === 'failed')
                                            <span class="badge badge-danger">GAGAL</span>
                                        @elseif($pesan->status === 'processing')
                                            <span class="badge badge-info">DIPROSES</span>
                                        @else
                                            <span class="badge badge-warning">PENDING</span>
                                        @endif
                                    </td>
                                    <td>{{ $pesan->created_at?->format('d M Y H:i') }}</td>
                                    <td>
                                        @if($pesan->status === 'failed')
                                            <span class="text-danger small">{{ $pesan->error_message ?? 'Kesalahan sistem' }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat pengiriman pesan tunggal.</td>
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
        $('#tunggalLogTable').DataTable({
            "order": [[ 4, "desc" ]],
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
