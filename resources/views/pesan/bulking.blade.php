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
                <h4>Bulking Messages</h4>
            </div>
            <div class="card-body">
                <p>Halaman Bulking sedang dalam pengembangan.</p>
            </div>
        </div>
    </div>
</div>
@endsection
