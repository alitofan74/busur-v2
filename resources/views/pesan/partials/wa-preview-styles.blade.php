<style>
    .wa-preview-container {
        background: #e5ddd5;
        background-image: url('{{ asset("imgs/wachat-bg.png") }}');
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

    .wa-header i {
        font-size: 18px;
    }

    .wa-header-info {
        line-height: 1.2;
    }

    .wa-header-info span {
        font-size: 10px;
        opacity: 0.8;
        display: block;
    }

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
        display: none;
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

    .wa-format-bold {
        font-weight: bold;
    }

    .wa-format-italic {
        font-style: italic;
    }

    .wa-format-strike {
        text-decoration: line-through;
    }

    .wa-format-code {
        font-family: monospace;
        background: rgba(0,0,0,0.05);
        padding: 2px 4px;
        border-radius: 3px;
    }

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
        max-width: 100%;
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

    @media (max-width: 991.98px) {
        .phone-mockup {
            margin-top: 1rem;
            position: relative;
            top: 0;
        }
    }
</style>
