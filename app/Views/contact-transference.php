<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>


<div class="d-flex justify-content-center align-items-center p-4">
    <div class="card shadow-lg form-signin w-50">
        <div class="card-body p-5 ">
            <h1 class="fs-4 card-title fw-bold mb-4 w-6">Introduzca sus datos</h1>
            <form method="POST" action="<?= base_url('sendNotification') ?>" autocomplete="off">
                <?= csrf_field(); ?>
                <div class="mb-3">
                    <label class="mb-2" for="name">Nombre</label>
                    <input type="text" class="form-control" name="name" id="name" value="" autofocus required>
                </div>

                <div class="mb-3">
                    <label class="mb-2" for="rut">Rut</label>
                    <input type="text" class="form-control" name="rut" id="rut" value="" required>
                </div>

                <div class="mb-3">
                    <label class="mb-2" for="email">Correo electrónico</label>
                    <input type="email" class="form-control" name="email" id="email" value="<?= $email;  ?>" required>
                </div>

                <div class="mb-3">
                    <label class="mb-2" for="phone">Telefono</label>
                    <input type="text" class="form-control" name="phone" id="phone" value="" required>
                    <input type="hidden" class="form-control" name="mac" id="mac" value="<?= $mac  ?>">
                </div>
                <div class="d-flex flex-column  ">

                    <div class="p-2 form-check">
                        <input class="form-check-input" type="radio" name="plan" id="3000" value="3000" <?php if ($plan === '3000') echo 'checked'  ?>>
                        <label class="form-check-label" for="3000">
                            1 Día de Internet. $3.000
                        </label>
                    </div>
                    <div class="p-2 form-check">
                        <input class="form-check-input" type="radio" name="plan" id="5000" value="5000" <?php if ($plan === '5000') echo 'checked'  ?>>
                        <label class="form-check-label" for="5000">
                            2 Días de Internet. $5.000
                        </label>
                    </div>
                    <div class="p-2 form-check">
                        <input class="form-check-input" type="radio" name="plan" id="10000" value="10000" <?php if ($plan === '10000') echo 'checked'  ?>>
                        <label class="form-check-label" for="10000">
                            7 Días de Internet. $10.000
                        </label>
                    </div>
                    <div class="p-2 form-check">
                        <input class="form-check-input" type="radio" name="plan" id="1000" value="1000" <?php if ($plan === '1000') echo 'checked'  ?>>
                        <label class="form-check-label" for="1000">
                            1 hora de Internet. $1.000
                        </label>
                    </div>
                    <?php if (session()->getFlashdata('errors') !== null): ?>
                        <div class="alert alert-danger my-3" role="alert">
                            <?= session()->getFlashdata(('errors'))  ?>
                        </div>

                    <?php endif; ?>
                </div>

                <div class="mb-3 mt-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Eviar</button>
                </div>
            </form>

        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>