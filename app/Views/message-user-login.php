<?= $this->extend('layout/template'); ?>

<?php
/** @var string|null $error */
$error = session()->getFlashdata('hotspot_error');
$user_loged = session()->getFlashdata('user_loged');
?>

<?= $this->section('content'); ?>
<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger my-3" role="alert">
                    <?php
                    if (stripos($error, 'uptime limit') !== false) {
                        echo 'Tu tiempo de internet expiró, debes actualizar tu pago.';
                    } elseif (stripos($error, 'invalid user') !== false) {
                        echo 'Usuario o contraseña incorrectos.';
                    } else {
                        echo 'No fue posible conectarte. Por favor intenta nuevamente.';
                    }
                    ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($user_loged)): ?>
                <div class="alert alert-danger my-3" role="alert">
                    <?= 
                     esc(session()->getFlashdata('user_loged')); 
                    ?>
                </div>
            <?php endif; ?>
            <form action="<?= base_url(); ?>" method="POST">

                <input type="hidden" name="ip" value="<?= $ip ?>">
                <input type="hidden" name="mac" value="<?= $mac ?>">
                <input type="submit" class="btn btn-primary" value="Volver">

            </form>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>