<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>F-Studio API</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #1a1a1a;
            color: #e5e5e5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background-color: #242424;
            border-radius: 12px;
            padding: 2.5rem 3rem;
            max-width: 680px;
            width: 100%;
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
        }
        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            color: #9ca3af;
            font-size: 0.95rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .api-root {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #9ca3af;
            margin-bottom: 2rem;
        }
        .api-root span {
            background: #333;
            padding: 2px 8px;
            border-radius: 4px;
            color: #e5e5e5;
        }
        h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 1rem;
        }
        .endpoint-list {
            list-style: disc;
            padding-left: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 0.55rem;
            margin-bottom: 2rem;
        }
        .endpoint-list li {
            font-size: 0.9rem;
            color: #d1d5db;
            line-height: 1.6;
        }
        .endpoint-list li strong {
            color: #ffffff;
            font-weight: 600;
        }
        .endpoint-list code {
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            color: #93c5fd;
        }
        .btn-login {
            display: inline-block;
            background-color: #3b82f6;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.65rem 1.75rem;
            border-radius: 9999px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn-login:hover {
            background-color: #2563eb;
        }
        hr {
            border: none;
            border-top: 1px solid #333;
            margin: 1.5rem 0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>F-Studio Management System</h1>
        <p class="subtitle">
            Backend API Laravel untuk manajemen peminjaman ruang kerja, inventory equipment, dan check-in studio.
        </p>
        <p class="api-root">API root: <span>/api/v1</span></p>

        <h2>Endpoint Utama</h2>
        <ul class="endpoint-list">
            <li>
                <strong>Public:</strong>
                <code>POST /api/v1/auth/login</code>
            </li>
            <li>
                <strong>Auth:</strong>
                <code>GET /api/v1/auth/me</code>,
                <code>POST /api/v1/auth/logout</code>
            </li>
            <li>
                <strong>Categories:</strong>
                <code>GET /api/v1/categories</code>,
                <code>GET /api/v1/categories/{id}</code>,
                <code>POST /api/v1/categories</code>,
                <code>PUT /api/v1/categories/{id}</code>,
                <code>DELETE /api/v1/categories/{id}</code>
            </li>
            <li>
                <strong>Equipment:</strong>
                <code>GET /api/v1/equipment</code>,
                <code>GET /api/v1/equipment/{id}</code>,
                <code>POST /api/v1/equipment</code>,
                <code>PUT /api/v1/equipment/{id}</code>,
                <code>DELETE /api/v1/equipment/{id}</code>
            </li>
            <li>
                <strong>Rooms:</strong>
                <code>GET /api/v1/rooms</code>,
                <code>GET /api/v1/rooms/{id}</code>,
                <code>POST /api/v1/rooms</code>,
                <code>PUT /api/v1/rooms/{id}</code>,
                <code>DELETE /api/v1/rooms/{id}</code>
            </li>
            <li>
                <strong>Bookings:</strong>
                <code>GET /api/v1/bookings</code>,
                <code>POST /api/v1/bookings</code>,
                <code>GET /api/v1/bookings/{id}</code>,
                <code>PUT /api/v1/bookings/{id}</code>,
                <code>DELETE /api/v1/bookings/{id}</code>,
                <code>POST /api/v1/bookings/{id}/approve</code>,
                <code>POST /api/v1/bookings/{id}/reject</code>
            </li>
            <li>
                <strong>Equipment Loans:</strong>
                <code>GET /api/v1/equipment-loans</code>,
                <code>POST /api/v1/equipment-loans</code>,
                <code>GET /api/v1/equipment-loans/{id}</code>,
                <code>PUT /api/v1/equipment-loans/{id}</code>,
                <code>POST /api/v1/equipment-loans/{id}/approve</code>,
                <code>POST /api/v1/equipment-loans/{id}/reject</code>
            </li>
            <li>
                <strong>Check-ins:</strong>
                <code>POST /api/v1/check-ins</code>,
                <code>GET /api/v1/check-ins</code>,
                <code>GET /api/v1/check-ins/{id}</code>
            </li>
            <li>
                <strong>Notifications:</strong>
                <code>GET /api/v1/notifications</code>,
                <code>POST /api/v1/notifications/{id}/read</code>,
                <code>POST /api/v1/notifications/read-all</code>
            </li>
        </ul>

        <hr>
        <a href="{{ route('login') }}" class="btn-login">Login ke F-Studio</a>
    </div>
</body>
</html>
