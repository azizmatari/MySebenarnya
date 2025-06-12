<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'System Message' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 3rem;
            text-align: center;
            max-width: 500px;
        }

        .icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: white;
        }
    </style>
</head>

<body>
    <div class="message-card">
        <i class="fas fa-tools icon"></i>
        <h2 class="mb-3">{{ $title ?? 'System Message' }}</h2>
        <p class="text-muted mb-4">{{ $message ?? 'The system is currently under maintenance.' }}</p>

        @if(isset($redirect_url))
        <a href="{{ $redirect_url }}" class="btn btn-custom">
            <i class="fas fa-arrow-right me-2"></i>
            {{ $redirect_text ?? 'Continue' }}
        </a>
        @endif

        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i>
                This is a temporary message for testing purposes.
            </small>
        </div>
    </div>

    <script>
        // Auto-redirect after 5 seconds if redirect URL is provided
        @if(isset($redirect_url))
        setTimeout(function() {
            window.location.href = '{{ $redirect_url }}';
        }, 5000);
        @endif
    </script>
</body>

</html>