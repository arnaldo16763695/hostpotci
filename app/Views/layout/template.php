<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema hostpot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>