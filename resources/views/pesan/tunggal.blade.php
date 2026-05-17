@extends('template')

@section('css')
<style>
    .wa-preview-container {
        background: #e5ddd5;
        background-image: url('{{asset("imgs/wachat-bg.png")}}');
        background-repeat: repeat;
        flex-grow: 1;
        padding: 15px;
        position: relative;
        overflow-y: auto;
    }

    .wa-header {
        background: #075e54;
        color: white;
        padding: 10px 15px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    .wa-header i { font-size: 18px; }
    .wa-header-info { line-height: 1.2; }
    .wa-header-info span { font-size: 10px; opacity: 0.8; display: block; }

    .wa-bubble {
        background: #ffffff;
        border-radius: 8px;
        padding: 6px;
        max-width: 85%;
        position: relative;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        margin-bottom: 10px;
        font-size: 13px;
        line-height: 1.4;
        word-wrap: break-word;
        align-self: flex-end;
    }

    .wa-media-preview {
        width: 100%;
        border-radius: 6px;
        margin-bottom: 5px;
        display: none; /* hidden by default */
        object-fit: cover;
        max-height: 200px;
    }

    .wa-bubble-content {
        padding: 2px 6px;
    }

    .wa-bubble-sent {
        background: #dcf8c6;
        margin-left: auto;
        border-top-right-radius: 0;
    }

    .wa-bubble-sent::after {
        content: "";
        position: absolute;
        top: 0;
        right: -8px;
        width: 0;
        height: 0;
        border-left: 10px solid #dcf8c6;
        border-bottom: 10px solid transparent;
    }

    .wa-time {
        font-size: 10px;
        color: rgba(0,0,0,0.45);
        text-align: right;
        margin-top: 4px;
    }

    /* WhatsApp Formatting Styles */
    .wa-format-bold { font-weight: bold; }
    .wa-format-italic { font-style: italic; }
    .wa-format-strike { text-decoration: line-through; }
    .wa-format-code { font-family: monospace; background: rgba(0,0,0,0.05); padding: 2px 4px; border-radius: 3px; }

    .format-btn {
        padding: 5px 10px;
        border-radius: 6px;
        border: 1px solid #ddd;
        background: #f8f9fa;
        margin-right: 5px;
        transition: all 0.2s;
    }

    .format-btn:hover {
        background: #e9ecef;
        border-color: #6777ef;
    }

    .phone-mockup {
        border: 12px solid #1a1a1a;
        border-radius: 40px;
        overflow: hidden;
        width: 300px;
        height: 600px;
        margin: 0 auto;
        background: #000;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        position: sticky;
        top: 20px;
    }

    .phone-notch {
        height: 20px;
        width: 150px;
        background: #1a1a1a;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
        z-index: 10;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-7">
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

        <div class="card" style="position: relative;">
            @include('pesan.partials.active-campaign-overlay')
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

                    <div class="form-group">
                        <label>Pesan</label>
                        <div class="mb-2">
                            <button type="button" class="format-btn" onclick="insertFormat('*', '*')"><b>B</b></button>
                            <button type="button" class="format-btn" onclick="insertFormat('_', '_')"><i>I</i></button>
                            <button type="button" class="format-btn" onclick="insertFormat('~', '~')"><strike>S</strike></button>
                            <button type="button" class="format-btn" onclick="insertFormat('```', '```')"><i class="fas fa-code"></i></button>
                        </div>
                        <textarea class="form-control @error('pesan') is-invalid @enderror" id="pesan" name="pesan" rows="6" placeholder="Ketik pesan Anda di sini...">{{ old('pesan') }}</textarea>
                        @error('pesan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Media (Optional)</label>
                        <input type="file" class="form-control" name="media">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-paper-plane mr-2"></i> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="phone-mockup">
            <div class="phone-notch"></div>
            
            <div class="wa-header mt-3">
                <i class="fas fa-arrow-left"></i>
                <div class="wa-header-avatar">
                    <img src="{{asset('otika-assets/img/users/user-1.png')}}" class="rounded-circle" width="35" height="35">
                </div>
                <div class="wa-header-info">
                    <strong>Customer</strong>
                    <span>Online</span>
                </div>
                <div class="ml-auto">
                    <i class="fas fa-video mr-3"></i>
                    <i class="fas fa-phone-alt"></i>
                </div>
            </div>

            <div class="wa-preview-container">
                <div class="text-center mb-3">
                    <small class="badge badge-secondary" style="opacity: 0.7; font-size: 10px; background: rgba(0,0,0,0.2); color: #555;">HARI INI</small>
                </div>
                
                <div class="wa-bubble wa-bubble-sent">
                    <img id="media-preview-img" class="wa-media-preview" src="">
                    <div class="wa-bubble-content">
                        <div id="preview-content" class="text-dark">Ketik pesan untuk melihat pratinjau...</div>
                        <div class="wa-time">{{ date('H:i') }} <i class="fas fa-check-double text-primary" style="font-size: 8px;"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-center mt-3 text-muted"><i class="fas fa-eye"></i> Live Preview</p>
    </div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        const $inputPesan = $('#pesan');
        const $previewContent = $('#preview-content');
        const $inputMedia = $('input[name="media"]');
        const $mediaPreviewImg = $('#media-preview-img');
        const $inputNomor = $('#nomor');
        const $statusNomor = $('#status-nomor');
        const $nomorFeedback = $('#nomor-feedback');

        function formatWhatsApp(text) {
            if (!text) return 'Ketik pesan untuk melihat pratinjau...';
            
            // Escape HTML
            let formatted = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            // Bold: *text*
            formatted = formatted.replace(/\*([^*]+)\*/g, '<span class="wa-format-bold">$1</span>');
            
            // Italic: _text_
            formatted = formatted.replace(/_([^_]+)_/g, '<span class="wa-format-italic">$1</span>');
            
            // Strike: ~text~
            formatted = formatted.replace(/~([^~]+)~/g, '<span class="wa-format-strike">$1</span>');
            
            // Code: ```text```
            formatted = formatted.replace(/```([^`]+)```/g, '<span class="wa-format-code">$1</span>');
            
            // Newlines
            formatted = formatted.replace(/\n/g, '<br>');

            return formatted;
        }

        // Live Preview Event
        $inputPesan.on('input', function() {
            $previewContent.html(formatWhatsApp($(this).val()));
        });

        // Handle Media Preview
        $inputMedia.on('change', function() {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $mediaPreviewImg.attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            } else {
                $mediaPreviewImg.attr('src', "").hide();
            }
        });

        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        // Check WhatsApp Number
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
                    let msg = xhr.responseJSON ? xhr.responseJSON.message : "Gagal mengecek nomor";
                    $nomorFeedback.html('<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + msg + '</span>');
                }
            });
        }, 800);

        // Auto format nomor & trigger check
        $inputNomor.on('input', function() {
            let val = $(this).val().replace(/\D/g, '');
            if (val.startsWith('0')) {
                val = '62' + val.substring(1);
            }
            $(this).val(val);
            checkWA();
        });

        // Handle Form Submission via AJAX
        $('#pesanForm').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const formData = new FormData(this);

            // Reset previous errors
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
                    
                    // Show success notification (using alert for now)
                    const successHtml = `
                        <div class="alert alert-success alert-dismissible show fade ajax-alert">
                            <div class="alert-body">
                                <button class="close" data-dismiss="alert"><span>&times;</span></button>
                                ${response.message}
                            </div>
                        </div>`;
                    
                    $('.col-md-7').prepend(successHtml);
                    
                    // Reset Form & Preview
                    $form[0].reset();
                    $previewContent.html('Ketik pesan untuk melihat pratinjau...');
                    $mediaPreviewImg.hide().attr('src', '');
                    $statusNomor.html('<i class="fas fa-phone"></i>').removeClass('bg-success bg-danger text-white');
                    $nomorFeedback.html('');

                    // Scroll to top to see alert
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

        // Global function for formatting buttons
        window.insertFormat = function(startTag, endTag) {
            const el = $inputPesan[0];
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = $inputPesan.val();
            const before = text.substring(0, start);
            const after = text.substring(end, text.length);
            const selected = text.substring(start, end);

            const newText = before + startTag + selected + endTag + after;
            $inputPesan.val(newText);
            
            // Reset focus and update preview
            $inputPesan.focus();
            el.setSelectionRange(start + startTag.length, end + startTag.length);
            $previewContent.html(formatWhatsApp(newText));
        };
    });
</script>
@endsection
