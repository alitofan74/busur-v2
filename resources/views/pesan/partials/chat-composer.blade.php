@php
    $textareaId = $textareaId ?? 'pesan';
    $textareaName = $textareaName ?? 'pesan';
    $textareaValue = $textareaValue ?? old($textareaName);
    $textareaRows = $textareaRows ?? 8;
    $textareaLabel = $textareaLabel ?? 'Pesan';
    $textareaPlaceholder = $textareaPlaceholder ?? 'Ketik pesan Anda di sini...';
    $textareaErrorKey = $textareaErrorKey ?? $textareaName;
    $mediaId = $mediaId ?? 'media';
    $mediaName = $mediaName ?? 'media';
    $mediaLabel = $mediaLabel ?? 'Media (Optional)';
    $mediaAccept = $mediaAccept ?? '';
    $mediaHelper = $mediaHelper ?? null;
@endphp

<div class="form-group">
    <label>{{ $textareaLabel }}</label>
    <div class="mb-2">
        <button type="button" class="format-btn" data-format-target="#{{ $textareaId }}" data-format-start="*" data-format-end="*"><b>B</b></button>
        <button type="button" class="format-btn" data-format-target="#{{ $textareaId }}" data-format-start="_" data-format-end="_"><i>I</i></button>
        <button type="button" class="format-btn" data-format-target="#{{ $textareaId }}" data-format-start="~" data-format-end="~"><strike>S</strike></button>
        <button type="button" class="format-btn" data-format-target="#{{ $textareaId }}" data-format-start="```" data-format-end="```"><i class="fas fa-code"></i></button>
    </div>
    <textarea
        class="form-control {{ $errors->has($textareaErrorKey) ? 'is-invalid' : '' }}"
        id="{{ $textareaId }}"
        name="{{ $textareaName }}"
        rows="{{ $textareaRows }}"
        placeholder="{{ $textareaPlaceholder }}"
        style="height: auto; min-height: 120px;"
    >{{ $textareaValue }}</textarea>
    @if($errors->has($textareaErrorKey))
        <div class="invalid-feedback d-block">{{ $errors->first($textareaErrorKey) }}</div>
    @endif
</div>

<div class="form-group">
    <label for="{{ $mediaId }}">{{ $mediaLabel }}</label>
    <input type="file" class="form-control" id="{{ $mediaId }}" name="{{ $mediaName }}" @if($mediaAccept) accept="{{ $mediaAccept }}" @endif>
    @if($mediaHelper)
        <small class="form-text text-muted">{{ $mediaHelper }}</small>
    @endif
</div>
