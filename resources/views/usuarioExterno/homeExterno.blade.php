<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="referrer" content="no-referrer-when-downgrade">
    <title>El Amanecer · Cafetería & Restaurante</title>
    <link rel="icon" href="data:,">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
            background: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .home-loader {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #ffffff;
            z-index: 10;
            transition: opacity .4s ease;
        }

        .home-loader.is-hidden {
            opacity: 0;
            pointer-events: none;
        }

        .home-loader .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e5e7eb;
            border-top-color: #f59e0b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .home-frame {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            border: 0;
            display: block;
        }
    </style>
</head>
<body>
    <div id="homeLoader" class="home-loader" aria-hidden="false">
        <div class="spinner" role="status" aria-label="Cargando sitio"></div>
    </div>

    <iframe
        id="homeFrame"
        class="home-frame"
        src="https://lexington-maria-visitors-pillow.trycloudflare.com/"
        title="El Amanecer · Cafetería & Restaurante"
        allow="clipboard-write; geolocation; payment"
        referrerpolicy="no-referrer-when-downgrade"
        loading="eager">
    </iframe>

    <script>
        (function () {
            var frame  = document.getElementById('homeFrame');
            var loader = document.getElementById('homeLoader');

            if (frame && loader) {
                frame.addEventListener('load', function () {
                    loader.classList.add('is-hidden');
                });

                setTimeout(function () {
                    loader.classList.add('is-hidden');
                }, 8000);
            }
        })();
    </script>
</body>
</html>
