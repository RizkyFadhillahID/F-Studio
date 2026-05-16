<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — F-Studio SmartHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .4);
        }

        .brand {
            color: #e94560;
            font-size: 2rem;
            font-weight: 800;
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand"><i class="bi bi-camera-reels"></i> F-Studio</div>
            <p class="text-muted small mt-1">Smart-Hub Management System</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" placeholder="admin@fstudio.id" autofocus
                    required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                    required>
            </div>
            <button type="submit" class="btn w-100 text-white" style="background:#e94560">
                Masuk
            </button>
        </form>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>