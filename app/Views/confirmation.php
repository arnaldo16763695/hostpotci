<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">

                    <h1 class="h4 mb-2 text-center">¡Bienvenido!</h1>
                    <p class="text-muted text-center mb-4">
                        Estos son los datos de tu orden. También puedes revisar esta información en tu correo.
                    </p>

                    <?php
                        // Normalizamos el status a entero para evitar problemas de comparación
                        $statusInt = (int) ($status ?? 0);

                        // Texto del estado
                        $statusText = 'Desconocido';
                        $statusBadgeClass = 'badge-secondary';

                        switch ($statusInt) {
                            case 1:
                                $statusText = 'Pendiente';
                                $statusBadgeClass = 'badge-warning';
                                break;
                            case 2:
                                $statusText = 'Pagada';
                                $statusBadgeClass = 'badge-success';
                                break;
                            case 3:
                                $statusText = 'Rechazada';
                                $statusBadgeClass = 'badge-danger';
                                break;
                            case 4:
                                $statusText = 'Anulada';
                                $statusBadgeClass = 'badge-dark';
                                break;
                        }
                    ?>

                    <ul class="list-group mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Nº orden:</span>
                            <span><?= esc($flow_order) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Fecha:</span>
                            <span><?= esc($requestDate) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Estado:</span>
                            <span class="badge <?= $statusBadgeClass ?> px-3 py-1">
                                <?= esc($statusText) ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Asunto:</span>
                            <span class="text-right"><?= esc($subject) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Moneda:</span>
                            <span><?= esc($currency) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Monto:</span>
                            <span><?= esc($amount) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">Email:</span>
                            <span class="text-right"><?= esc($payer) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">IP:</span>
                            <span><?= esc($ip) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold">MAC:</span>
                            <span class="text-monospace"><?= esc($mac) ?></span>
                        </li>
                    </ul>

                    <?php if ($statusInt === 2): ?>
                        <button
                            type="button"
                            class="btn btn-primary btn-block"
                            onclick="window.location.href='https://www.google.com'">
                            Continuar a Internet
                        </button>
                    <?php else: ?>
                        <div class="alert alert-info text-center mb-0">
                            Cuando el pago sea confirmado, podrás continuar a Internet.
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>
