<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema hostpot</title>
    <link href="<?= base_url('assets/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('css/style.css') ?>" rel="stylesheet">
</head>

<body>
    <div class="container  min-vh-100">
        <?= $this->renderSection('content'); ?>
        <div class="row d-flex align-items-center justify-content-center">
            <div class="d-flex flex-column flex-md-row col col-md-6 col-12 text-center fs-6 align-items-center justify-content-center">
                <div class="p-1">
                    WhatsApp Soporte:
                    <a href="tel:+1234567890">+56 9 8230 3053</a> 
                </div>
                <div class="px-2 d-none d-md-block">
                    |
                </div>
                <div class="p-1">
                    Correo:
                    <a href="mailto:ejemplo@soporte@globalsi.cl">soporte@globalsi.cl</a>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url('assets/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script src="<?= base_url('js/helpers.js') ?>"></script>
</body>

</html>