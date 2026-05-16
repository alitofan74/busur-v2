@extends('template')

@section('css')
@include('pesan.partials.wa-preview-styles')
@endsection

@section('content')
<div class="row">
    <div class="col-md-7">
        @include('pesan.partials.connection-status', ['connection' => $connection])

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

        <div class="card">
            <div class="card-header">
                <h4>Kirim Pesan Tunggal</h4>
            </div>
            <div class="card-body">
                <form id="pesanForm" action="{{ route('pesan-tunggal.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Nomor WhatsApp</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text" id="status-nomor">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control @error('nomor') is-invalid @enderror" id="nomor" name="nomor" value="{{ old('nomor') }}" placeholder="6281234567890">
                        </div>
                        <div id="nomor-feedback" class="mt-1 small"></div>
                        @error('nomor')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @include('pesan.partials.chat-composer', [
                        'textareaId' => 'pesan',
                        'textareaName' => 'pesan',
                        'textareaErrorKey' => 'pesan',
                        'mediaId' => 'media',
                        'mediaName' => 'media',
                    ])

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        @include('pesan.partials.wa-live-preview')
    </div>
</div>
@endsection

@section('javascript')
@include('pesan.partials.wa-preview-script')
<script>
    $(document).ready(function() {
        const $inputNomor = $('#nomor');
        const $statusNomor = $('#status-nomor');
        const $nomorFeedback = $('#nomor-feedback');
        const preview = window.initWhatsappPreview({
            namespace: 'single',
            messageSelector: '#pesan',
            previewSelector: '#preview-content',
            mediaSelector: '#media',
            mediaPreviewSelector: '#media-preview-img'
        });

        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        const checkWA = debounce(function() {
            let nomor = $inputNomor.val();
            if (nomor.length < 10) {
                $statusNomor.html('<i class="fas fa-phone"></i>').removeClass('bg-success bg-danger text-white');
                $nomorFeedback.html('');
                return;
            }

            $statusNomor.html('<i class="fas fa-spinner fa-spin"></i>').removeClass('bg-success bg-danger text-white');
            $nomorFeedback.html('<span class="text-muted">Mengecek nomor...</span>');

            $.ajax({
                url: "{{ route('pesan-tunggal.check') }}",
                method: "GET",
                data: { number: nomor },
                success: function(response) {
                    $statusNomor.html('<i class="fas fa-check"></i>').addClass('bg-success text-white').removeClass('bg-danger');
                    $nomorFeedback.html('<span class="text-success"><i class="fas fa-check-circle"></i> ' + response.message + '</span>');
                },
                error: function(xhr) {
                    $statusNomor.html('<i class="fas fa-times"></i>').addClass('bg-danger text-white').removeClass('bg-success');
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : "Gagal mengecek nomor";
                    $nomorFeedback.html('<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + msg + '</span>');
                }
            });
        }, 800);

        $inputNomor.on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val.startsWith('0')) {
                val = '62' + val.substring(1);
            }
            $(this).val(val);
            checkWA();
        });

        $('#pesanForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const formData = new FormData(this);

            $('.invalid-feedback').remove();
            $('.form-control').removeClass('is-invalid');

            $btn.addClass('btn-progress').attr('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $btn.removeClass('btn-progress').attr('disabled', false);

                    const successHtml = `
                        <div class="alert alert-success alert-dismissible show fade ajax-alert">
                            <div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                ${response.message}
                            </div>
                        </div>`;

                    $('.col-md-7').prepend(successHtml);

                    $form[0].reset();
                    preview.resetPreview();
                    $statusNomor.html('<i class="fas fa-phone"></i>').removeClass('bg-success bg-danger text-white');
                    $nomorFeedback.html('');

                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                },
                error: function(xhr) {
                    $btn.removeClass('btn-progress').attr('disabled', false);

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        Object.keys(errors).forEach(key => {
                            const $input = $(`[name="${key}"]`);
                            $input.addClass('is-invalid');
                            $input.closest('.form-group').append(`<div class="invalid-feedback">${errors[key][0]}</div>`);
                        });
                    } else {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                        alert(errorMsg);
                    }
                }
            });
        });
    });
</script>
@endsection
