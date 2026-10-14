<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fuego vs Hielo</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        :root {
            --bg: #0b1120;
            --card: #1e293b;
            --line: #334155;
            --fuego: #fb923c;
            --hielo: #38bdf8;
            --txt: #e2e8f0;
            --muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            color: var(--txt);
            font-family: system-ui, sans-serif;
            background: radial-gradient(circle at 15% 20%, rgba(251, 146, 60, .18), transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(56, 189, 248, .18), transparent 45%), var(--bg);
        }

        .card {
            background: var(--card);
            padding: 34px 28px;
            border-radius: 22px;
            width: min(92vw, 400px);
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
        }

        h1 {
            margin: 0;
            font-size: 2.1rem;
        }

        h1 .f {
            color: var(--fuego);
        }

        h1 .h {
            color: var(--hielo);
        }

        .sub {
            color: var(--muted);
            margin: 8px 0 26px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            border: 0;
            font-size: 1.05rem;
            cursor: pointer;
            background: linear-gradient(90deg, var(--fuego), var(--hielo));
            color: #0b1120;
            font-weight: 800;
        }

        .sep {
            color: var(--muted);
            margin: 22px 0 10px;
            font-size: .9rem;
        }

        input {
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--bg);
            color: var(--txt);
            font-size: 1.2rem;
            text-align: center;
            letter-spacing: 6px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .btn.sec {
            background: var(--bg);
            color: var(--txt);
            border: 1px solid var(--line);
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>🔥 <span class="f">Fuego</span> vs <span class="h">Hielo</span> ❄️</h1>
        <p class="sub">Tres en raya online, en tiempo real</p>

        <form method="POST" action="{{ route('crear') }}">
            @csrf
            <button type="submit" class="btn">⚔️ Crear sala</button>
        </form>

        <p class="sep">¿Te pasaron un código?</p>
        <input id="codigo" placeholder="ABCDE" maxlength="5" autocomplete="off">
        <button class="btn sec" onclick="unirme()">Entrar a la sala</button>
    </div>

    <script>
        function unirme() {
            const c = document.getElementById('codigo').value.trim().toUpperCase();
            if (c) location.href = '/partidas/' + c;
        }
        document.getElementById('codigo').addEventListener('keydown', e => {
            if (e.key === 'Enter') unirme();
        });
    </script>
</body>

</html>