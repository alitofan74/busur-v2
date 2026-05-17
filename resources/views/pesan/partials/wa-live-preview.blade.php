@php
    $previewContentId = $previewContentId ?? 'preview-content';
    $mediaPreviewId = $mediaPreviewId ?? 'media-preview-img';
    $caption = $caption ?? 'Live Preview';
@endphp

<div class="phone-mockup">
    <div class="phone-notch"></div>

    <div class="wa-header mt-3">
        <i class="fas fa-arrow-left"></i>
        <div class="wa-header-avatar">
            <img src="{{ asset('otika-assets/img/users/user-1.png') }}" class="rounded-circle" width="35" height="35" alt="Customer">
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
            <img id="{{ $mediaPreviewId }}" class="wa-media-preview" src="" alt="Preview media">
            <div class="wa-bubble-content">
                <div id="{{ $previewContentId }}" class="text-dark">Ketik pesan untuk melihat pratinjau...</div>
                <div class="wa-time">{{ date('H:i') }} <i class="fas fa-check-double text-primary" style="font-size: 8px;"></i></div>
            </div>
        </div>
    </div>
</div>
<p class="text-center mt-3 text-muted"><i class="fas fa-eye"></i> {{ $caption }}</p>
