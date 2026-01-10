<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-lg form-signin">
                <div class="card-body p-4 p-md-5">
                    <h2 class="mb-3"><?= esc($title) ?></h2>

                    <p style="font-size: 15px; margin: 0;">
                        <?= $message ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>
