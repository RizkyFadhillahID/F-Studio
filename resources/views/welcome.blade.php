<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>F-Studio API</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #111827;
            color: #e5e7eb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        .container { max-width: 860px; margin: 0 auto; }

        /* Header */
        .header { margin-bottom: 2rem; }
        .header h1 { font-size: 1.8rem; font-weight: 700; color: #fff; }
        .header p { color: #9ca3af; font-size: 0.9rem; margin-top: 0.4rem; }
        .api-root {
            display: inline-block;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 0.3rem 0.8rem;
            font-family: monospace;
            font-size: 0.82rem;
            color: #93c5fd;
            margin-top: 0.75rem;
        }

        /* Action buttons */
        .actions { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 2rem; }
        .btn {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.55rem 1.2rem;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            border: none; cursor: pointer;
            transition: opacity 0.15s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-postman { background: #ff6c37; color: #fff; }
        .btn-login   { background: #3b82f6; color: #fff; }

        /* Section */
        .section { margin-bottom: 2rem; }
        .section-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 0.5rem;
            padding-bottom: 0.4rem;
            border-bottom: 1px solid #1f2937;
        }

        /* Endpoint row */
        .endpoint {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.55rem 0.75rem;
            border-radius: 7px;
            transition: background 0.1s;
            cursor: default;
        }
        .endpoint:hover { background: #1f2937; }

        .method {
            display: inline-block;
            font-family: monospace;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.18rem 0.5rem;
            border-radius: 4px;
            min-width: 52px;
            text-align: center;
            flex-shrink: 0;
        }
        .GET    { background: #064e3b; color: #34d399; }
        .POST   { background: #1e3a5f; color: #93c5fd; }
        .PUT    { background: #3d2700; color: #fbbf24; }
        .DELETE { background: #450a0a; color: #f87171; }

        .ep-url {
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            color: #d1d5db;
            flex: 1;
        }
        .ep-url .base { color: #6b7280; }

        .copy-btn {
            background: none;
            border: 1px solid #374151;
            color: #9ca3af;
            font-size: 0.7rem;
            padding: 0.2rem 0.55rem;
            border-radius: 5px;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s;
        }
        .copy-btn:hover { border-color: #6b7280; color: #e5e7eb; }
        .copy-btn.copied { border-color: #34d399; color: #34d399; }

        /* Toast */
        #toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            background: #1f2937; border: 1px solid #374151;
            color: #34d399; padding: 0.6rem 1.2rem;
            border-radius: 8px; font-size: 0.85rem;
            opacity: 0; transition: opacity 0.2s;
            pointer-events: none;
        }
        #toast.show { opacity: 1; }
    </style>
</head>

<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>F-Studio Management System</h1>
            <p>Backend API Laravel · manajemen ruang, equipment, peminjaman, dan check-in studio.</p>
            <div class="api-root">Base URL: http://localhost:8000/api/v1</div>
        </div>

        <!-- Action Buttons -->
        <div class="actions">
            <a href="{{ route('postman.collection') }}" class="btn btn-postman">
                ⬇ Download Postman Collection
            </a>
            <a href="{{ route('login') }}" class="btn btn-login">
                → Login ke F-Studio
            </a>
        </div>

        <!-- AUTH -->
        <div class="section">
            <div class="section-title">Auth</div>
            @php $endpoints = [
                ['POST','POST /api/v1/auth/login','http://localhost:8000/api/v1/auth/login'],
                ['GET', 'GET /api/v1/auth/me','http://localhost:8000/api/v1/auth/me'],
                ['POST','POST /api/v1/auth/logout','http://localhost:8000/api/v1/auth/logout'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- CATEGORIES -->
        <div class="section">
            <div class="section-title">Categories</div>
            @php $endpoints = [
                ['GET',   'GET /api/v1/categories',        'http://localhost:8000/api/v1/categories'],
                ['GET',   'GET /api/v1/categories/{id}',   'http://localhost:8000/api/v1/categories/1'],
                ['POST',  'POST /api/v1/categories',       'http://localhost:8000/api/v1/categories'],
                ['PUT',   'PUT /api/v1/categories/{id}',   'http://localhost:8000/api/v1/categories/1'],
                ['DELETE','DELETE /api/v1/categories/{id}','http://localhost:8000/api/v1/categories/1'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- EQUIPMENT -->
        <div class="section">
            <div class="section-title">Equipment</div>
            @php $endpoints = [
                ['GET',   'GET /api/v1/equipment',          'http://localhost:8000/api/v1/equipment'],
                ['GET',   'GET /api/v1/equipment/{id}',     'http://localhost:8000/api/v1/equipment/1'],
                ['POST',  'POST /api/v1/equipment',         'http://localhost:8000/api/v1/equipment'],
                ['PUT',   'PUT /api/v1/equipment/{id}',     'http://localhost:8000/api/v1/equipment/1'],
                ['DELETE','DELETE /api/v1/equipment/{id}',  'http://localhost:8000/api/v1/equipment/1'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- ROOMS -->
        <div class="section">
            <div class="section-title">Rooms</div>
            @php $endpoints = [
                ['GET',   'GET /api/v1/rooms',        'http://localhost:8000/api/v1/rooms'],
                ['GET',   'GET /api/v1/rooms/{id}',   'http://localhost:8000/api/v1/rooms/1'],
                ['POST',  'POST /api/v1/rooms',       'http://localhost:8000/api/v1/rooms'],
                ['PUT',   'PUT /api/v1/rooms/{id}',   'http://localhost:8000/api/v1/rooms/1'],
                ['DELETE','DELETE /api/v1/rooms/{id}','http://localhost:8000/api/v1/rooms/1'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- BOOKINGS -->
        <div class="section">
            <div class="section-title">Bookings</div>
            @php $endpoints = [
                ['GET',   'GET /api/v1/bookings',              'http://localhost:8000/api/v1/bookings'],
                ['POST',  'POST /api/v1/bookings',             'http://localhost:8000/api/v1/bookings'],
                ['GET',   'GET /api/v1/bookings/{id}',         'http://localhost:8000/api/v1/bookings/1'],
                ['PUT',   'PUT /api/v1/bookings/{id}',         'http://localhost:8000/api/v1/bookings/1'],
                ['DELETE','DELETE /api/v1/bookings/{id}',      'http://localhost:8000/api/v1/bookings/1'],
                ['POST',  'POST /api/v1/bookings/{id}/approve','http://localhost:8000/api/v1/bookings/1/approve'],
                ['POST',  'POST /api/v1/bookings/{id}/reject', 'http://localhost:8000/api/v1/bookings/1/reject'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- EQUIPMENT LOANS -->
        <div class="section">
            <div class="section-title">Equipment Loans</div>
            @php $endpoints = [
                ['GET', 'GET /api/v1/equipment-loans',               'http://localhost:8000/api/v1/equipment-loans'],
                ['POST','POST /api/v1/equipment-loans',              'http://localhost:8000/api/v1/equipment-loans'],
                ['GET', 'GET /api/v1/equipment-loans/{id}',          'http://localhost:8000/api/v1/equipment-loans/1'],
                ['PUT', 'PUT /api/v1/equipment-loans/{id}',          'http://localhost:8000/api/v1/equipment-loans/1'],
                ['POST','POST /api/v1/equipment-loans/{id}/approve', 'http://localhost:8000/api/v1/equipment-loans/1/approve'],
                ['POST','POST /api/v1/equipment-loans/{id}/reject',  'http://localhost:8000/api/v1/equipment-loans/1/reject'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- CHECK-INS -->
        <div class="section">
            <div class="section-title">Check-ins</div>
            @php $endpoints = [
                ['POST','POST /api/v1/check-ins',          'http://localhost:8000/api/v1/check-ins'],
                ['GET', 'GET /api/v1/check-ins',           'http://localhost:8000/api/v1/check-ins'],
                ['GET', 'GET /api/v1/check-ins/{id}',      'http://localhost:8000/api/v1/check-ins/1'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

        <!-- NOTIFICATIONS -->
        <div class="section">
            <div class="section-title">Notifications</div>
            @php $endpoints = [
                ['GET', 'GET /api/v1/notifications',               'http://localhost:8000/api/v1/notifications'],
                ['POST','POST /api/v1/notifications/{id}/read',    'http://localhost:8000/api/v1/notifications/1/read'],
                ['POST','POST /api/v1/notifications/read-all',     'http://localhost:8000/api/v1/notifications/read-all'],
            ]; @endphp
            @foreach($endpoints as [$m,$label,$url])
            <div class="endpoint">
                <span class="method {{ $m }}">{{ $m }}</span>
                <span class="ep-url">{{ $label }}</span>
                <button class="copy-btn" onclick="copyUrl('{{ $url }}', this)">Copy</button>
            </div>
            @endforeach
        </div>

    </div>

    <div id="toast">✓ URL disalin!</div>

    <script>
        function copyUrl(url, btn) {
            navigator.clipboard.writeText(url).then(() => {
                btn.textContent = '✓ Copied';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.remove('copied');
                }, 1500);

                const toast = document.getElementById('toast');
                toast.classList.add('show');
                setTimeout(() => toast.classList.remove('show'), 2000);
            });
        }
    </script>
</body>

</html>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
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
