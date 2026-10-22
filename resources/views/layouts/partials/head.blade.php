<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#020617">
    <title>{{ $title ?? 'FAMFIN' }} — Gestão Financeira Familiar</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icon.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 Dark Themed -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .swal2-popup {
            background: #0f172a !important;
            border: 1px solid #1e293b !important;
            border-radius: 1.25rem !important;
            color: #f8fafc !important;
            font-family: inherit !important;
        }
        .swal2-title {
            color: #ffffff !important;
            font-size: 1.15rem !important;
            font-weight: 700 !important;
        }
        .swal2-html-container {
            color: #94a3b8 !important;
            font-size: 0.875rem !important;
        }
        .swal2-confirm {
            background-color: #10b981 !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
            color: #020617 !important;
        }
        .swal2-deny {
            background-color: #8b5cf6 !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
        }
        .swal2-cancel {
            background-color: #334155 !important;
            border-radius: 0.75rem !important;
            font-weight: 600 !important;
            font-size: 0.75rem !important;
            padding: 0.625rem 1.25rem !important;
            color: #cbd5e1 !important;
        }
        .swal2-toast {
            background: #0f172a !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.5) !important;
        }
    </style>
</head>

