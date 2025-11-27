<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema hotspot</title>
    <link href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <!-- Contenido principal -->
    <main class="container my-3 flex-fill">
        <?= $this->renderSection('content'); ?>
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container">
            <div class="row d-flex align-items-center justify-content-center py-3">
                <div class="d-flex flex-column flex-md-row col col-md-6 col-12 text-center align-items-center justify-content-center">
                    <div class="p-1">
                        WhatsApp Soporte:
                        <a href="tel:+56982303053">+56 9 8230 3053</a>
                    </div>
                    <div class="px-2 d-none d-md-block">
                        |
                    </div>
                    <div class="p-1">
                        Correo:
                        <a href="mailto:soporte@globalsi.cl">soporte@globalsi.cl</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="<?= base_url('assets/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('js/helpers.js') ?>"></script>
</body>

</html>
