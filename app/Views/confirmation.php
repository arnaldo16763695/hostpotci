<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>

<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <h1 class='fs-4'>Bienvenido <?= esc($status) === 2 ? 'puede conectarse a Internet' : 'revise los datos a continuación:' ?></h1>
                <h3 class='fs-6'>Datos de tu orden, tambien puedes visualizarlo en tu correo:</h3>
                <ul>
                    <li><span class="fw-bold">Nº orden: </span><?= esc($flow_order) ?></li>
                    <li><span class="fw-bold">Fecha: </span><?= esc($requestDate)  ?></li>
                    <li><span class="fw-bold">Status:</span><?= esc($status) === 2 ? 'Pagada' : '' ?></li>
                    <li><span class="fw-bold">Asunto: </span><?= esc($subject) ?></li>
                    <li><span class="fw-bold">Moneda</span><?= esc($currency) ?></li>
                    <li><span class="fw-bold">Cantidad:</span><?= esc($amount) ?></li>
                    <li><span class="fw-bold">Su email:</span><?= esc($payer) ?></li>
                    <li><span class="fw-bold">Su IP: </span><?= esc($ip) ?></li>
                    <li><span class="fw-bold">Su MAC: </span><?= esc($mac) ?></li>
                </ul>
                <button type="button" onclick="window.location.href='https://www.google.com'" class="btn btn-primary">Continuar</button>

            </div>

        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>