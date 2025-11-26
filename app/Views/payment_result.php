<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="container py-4 py-md-5">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">

                    <?php
                    // Normalizamos
                    $statusKey = $status ?? 'unknown';

                    $badgeClass = 'badge-secondary';
                    $icon = '';
                    switch ($statusKey) {
                        case 'rejected':
                            $badgeClass = 'badge-danger';
                            $icon = '✖';
                            break;
                        case 'canceled':
                            $badgeClass = 'badge-dark';
                            $icon = '⚠';
                            break;
                        case 'pending':
                            $badgeClass = 'badge-warning';
                            $icon = '⏳';
                            break;
                        default: // unknown
                            $badgeClass = 'badge-secondary';
                            $icon = 'ℹ';
                            break;
                    }
                    ?>

                    <div class="text-center mb-3">
                        <span class="badge <?= $badgeClass ?> px-3 py-2">
                            <?= esc(strtoupper($statusKey)) ?>
                        </span>
                    </div>

                    <h1 class="h4 mb-2 text-center">
                        <?= esc($title ?? 'Estado del pago') ?>
                    </h1>

                    <p class="text-muted text-center mb-4">
                        <?= esc($message ?? '') ?>
                    </p>

                    <ul class="list-group mb-4">
                        <?php if (!empty($subject)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Servicio:</span>
                                <span class="text-right"><?= esc($subject) ?></span>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($amount)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Monto:</span>
                                <span><?= esc($amount) ?></span>
                            </li>
                        <?php endif; ?>

                        <?php if (!empty($payer)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold">Email:</span>
                                <span class="text-right"><?= esc($payer) ?></span>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                        <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary btn-block mb-2 mb-md-0">
                            Volver al inicio
                        </a>

                        <?php if ($statusKey === 'pending'): ?>
                            <button
                                type="button"
                                class="btn btn-primary btn-block"
                                onclick="window.location.reload();">
                                Volver a comprobar
                            </button>
                        <?php else: ?>
                            <a href="<?= base_url('/') ?>" class="btn btn-primary btn-block">
                                Intentar nuevamente
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>