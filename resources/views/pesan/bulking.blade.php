@extends('template')

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Connection Status Bar --}}
        <div class="alert {{ $connection['connected'] ? 'alert-success' : 'alert-danger' }} alert-has-icon p-3 mb-4 shadow-sm">
            <div class="alert-icon"><i class="fas fa-comment-dots"></i></div>
            <div class="alert-body">
                @if(!$connection['connected'])
                    <div class="float-right">
                        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-white">Perbaiki Koneksi</a>
                    </div>
                @endif
                <div class="alert-title">Status Koneksi: {{ $connection['status'] }}</div>
                WhatsApp Gateway: <strong>{{ $connection['number'] }}</strong>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Bulking Messages</h4>
            </div>
            <div class="card-body">
                <p>Halaman Bulking sedang dalam pengembangan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
