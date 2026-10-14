# 🔥 Fuego vs Hielo ❄️ — Tres en raya en tiempo real

Juego de tres en raya para dos jugadores online, construido con **Laravel 12**, **Laravel Reverb** y **Laravel Echo**. Cada jugada se transmite por WebSockets, así que los dos jugadores ven el tablero actualizarse al instante, sin recargar la página.

El **Jugador 1** juega con 🔥 Fuego y el **Jugador 2** con ❄️ Hielo.

> Trabajo práctico de WebSockets con Laravel — Tecnicatura Universitaria en Programación (UTN FRRe).

## Características

- Salas con código de 5 caracteres: se crea una sala y se comparte el código con el rival.
- Tablero sincronizado en tiempo real mediante Reverb y Echo.
- **Servidor como autoridad**: valida quién juega, de quién es el turno, si la casilla está libre y quién ganó. El navegador solo envía el número de casilla.
- **Revancha** en la misma sala, alternando quién empieza.
- **Marcador** de victorias de cada jugador y empates durante toda la sala.
- **Indicador de presencia**: muestra si el rival está conectado (canal de presencia).
- Avisos con **SweetAlert2**, **confeti** al ganar, **sonidos** (WebAudio, sin archivos) y animaciones.
- Sin base de datos: el estado de cada partida vive en la caché y vence a las 2 horas.

## Tecnologías

| Parte | Tecnología |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| WebSockets | Laravel Reverb (servidor) y Laravel Echo (cliente) |
| Frontend | Blade, JavaScript, Vite |
| Extras | SweetAlert2, canvas-confetti |

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 20 o superior y npm

No hace falta XAMPP, Apache ni MySQL: se usa el servidor integrado de Laravel.

## Instalación

```bash
git clone https://github.com/antodbenitez97/Websocket.git
cd Websocket

composer install
npm install

copy .env.example .env      # en Linux/macOS: cp .env.example .env
php artisan key:generate
```

Abrí el archivo `.env` y revisá estos valores:

```env
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
CACHE_STORE=file

REVERB_APP_ID=...
REVERB_APP_KEY=...
REVERB_APP_SECRET=...
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Si faltan las variables `REVERB_*`, ejecutá `php artisan reverb:install` para generarlas.

## Cómo ejecutarlo

Se necesitan **tres terminales** abiertas a la vez, todas dentro de la carpeta del proyecto:

```bash
# 1. Servidor web
php artisan serve --host=0.0.0.0 --port=8000

# 2. Servidor WebSocket
php artisan reverb:start

# 3. Compilación de assets
npm run dev
```

Después entrá a **http://localhost:8000**.

## Cómo jugar

1. El Jugador 1 entra y toca **Crear sala**.
2. Copia el código de la sala (botón **Copiar**) y se lo pasa al rival.
3. El Jugador 2 lo ingresa en **Entrar a la sala**.
4. Juegan por turnos. Al terminar la ronda pueden pedir la **revancha**.

> Para probarlo solo, abrí al Jugador 2 en una **ventana de incógnito** o en otro navegador: las pestañas del mismo navegador comparten la sesión y cuentan como el mismo jugador.

## Cómo funciona

```
Navegador (Jugador)                Servidor Laravel                 Reverb
        │                                 │                            │
        │── POST /partidas/{c}/jugar ────▶│                            │
        │                                 │ valida turno, casilla,     │
        │                                 │ ganador y actualiza caché  │
        │                                 │── broadcast(evento) ──────▶│
        │◀────────────────────── WebSocket (Echo) ─────────────────────│
        │   dibuja el nuevo estado        │                            │
```

- **Evento** `PartidaActualizada` (`ShouldBroadcastNow`): se emite por el canal público `partida.{codigo}` con el estado del tablero, el turno, el marcador y la ronda. No incluye identificadores de nadie.
- **Canal de presencia** `presencia.{codigo}`: solo pueden entrar los dos jugadores de la sala (autorización en `routes/channels.php`). Se usa para mostrar quién está conectado.
- **Middleware `JugadorInvitado`**: como no hay login, asigna a cada visitante una identidad anónima (UUID guardado en la sesión) que los canales de presencia necesitan.
- **`Cache::lock`**: evita que dos jugadas simultáneas se pisen entre sí.
- **Seguridad**: el cliente nunca decide el resultado. Aunque alguien habilite un botón desde la consola, el servidor rechaza las jugadas inválidas.

## Estructura principal

```
app/
├── Events/PartidaActualizada.php          # Evento que se transmite por WebSocket
├── Http/Controllers/PartidaController.php # Lógica del juego y validaciones
└── Http/Middleware/JugadorInvitado.php    # Identidad anónima del jugador
routes/
├── web.php                                # Rutas del lobby y de la partida
└── channels.php                           # Autorización del canal de presencia
resources/
├── js/echo.js                             # Configuración de Echo + Reverb
└── views/
    ├── lobby.blade.php                    # Pantalla de inicio
    └── partida.blade.php                  # Tablero, marcador y efectos
```

## Ideas para seguir mejorando

- Canales privados con usuarios registrados.
- Modo contra la computadora.
- Ranking histórico guardado en base de datos.

## Autor

Anto — proyecto de WebSockets con Laravel.