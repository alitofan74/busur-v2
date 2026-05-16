<script>
    window.initWhatsappPreview = function(config) {
        const namespace = config.namespace || 'default';
        const $inputPesan = $(config.messageSelector);
        const $previewContent = $(config.previewSelector);
        const $inputMedia = $(config.mediaSelector);
        const $mediaPreviewImg = $(config.mediaPreviewSelector);
        const emptyText = config.emptyText || 'Ketik pesan untuk melihat pratinjau...';

        function formatWhatsApp(text) {
            if (!text) {
                return emptyText;
            }

            let formatted = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            formatted = formatted.replace(/\*([^*]+)\*/g, '<span class="wa-format-bold">$1</span>');
            formatted = formatted.replace(/_([^_]+)_/g, '<span class="wa-format-italic">$1</span>');
            formatted = formatted.replace(/~([^~]+)~/g, '<span class="wa-format-strike">$1</span>');
            formatted = formatted.replace(/```([^`]+)```/g, '<span class="wa-format-code">$1</span>');
            formatted = formatted.replace(/\n/g, '<br>');

            return formatted;
        }

        function updatePreview(text) {
            const value = typeof text === 'string' ? text : $inputPesan.val();
            $previewContent.html(formatWhatsApp(value));
        }

        function updateMediaPreview() {
            const file = $inputMedia[0] ? $inputMedia[0].files[0] : null;

            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $mediaPreviewImg.attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
                return;
            }

            $mediaPreviewImg.attr('src', '').hide();
        }

        function resetPreview() {
            updatePreview('');
            $mediaPreviewImg.attr('src', '').hide();
        }

        function insertFormat(startTag, endTag) {
            const el = $inputPesan[0];

            if (!el) {
                return;
            }

            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = $inputPesan.val();
            const before = text.substring(0, start);
            const after = text.substring(end);
            const selected = text.substring(start, end);
            const newText = before + startTag + selected + endTag + after;

            $inputPesan.val(newText);
            $inputPesan.focus();
            el.setSelectionRange(start + startTag.length, end + startTag.length);
            updatePreview(newText);
        }

        $inputPesan.off('input.whatsappPreview.' + namespace).on('input.whatsappPreview.' + namespace, function() {
            updatePreview($(this).val());
        });

        $inputMedia.off('change.whatsappPreview.' + namespace).on('change.whatsappPreview.' + namespace, function() {
            updateMediaPreview();
        });

        $(document)
            .off('click.whatsappFormat.' + namespace)
            .on('click.whatsappFormat.' + namespace, '[data-format-target="' + config.messageSelector + '"]', function() {
                insertFormat($(this).data('format-start'), $(this).data('format-end'));
            });

        updatePreview($inputPesan.val());
        updateMediaPreview();

        return {
            formatWhatsApp: formatWhatsApp,
            updatePreview: updatePreview,
            updateMediaPreview: updateMediaPreview,
            resetPreview: resetPreview,
            insertFormat: insertFormat
        };
    };
</script>
