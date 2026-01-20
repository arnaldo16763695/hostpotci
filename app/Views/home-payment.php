<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <div class="pb-4">
                    <img src="<?= base_url('images/LogoGlobal-n.png') ?>" alt="Logo Globalsi" width="100" height="45">
                </div>
                <!-- <h5 class="card-title text-center fw-bold ">Si ya estas registrado ingresa tu Nº de teléfono</h5> -->
                <form action="<?= base_url('login-to-mik') ?>" method="POST" id="hotspotForm">
                    <?= csrf_field(); ?>

                    <!-- Mensaje superior -->
                    <div class="alert alert-light border mb-3 p-3" role="alert">
                        <small class="text-muted lh-sm d-block">
                            Si ya te registraste, ingresa tu número y presiona <strong>Conectar</strong>.
                            Si tu tiempo de internet ya venció, necesitas registrarte nuevamente con el mismo número.
                        </small>
                    </div>

                    <div class="mb-3 p-4">
                        <div class="mb-3">
                            <label class="mb-2" for="phone">Ingrese su número de teléfono:</label>

                            <input
                                class="form-control"
                                type="text"
                                name="phone"
                                required
                                placeholder="9XXXXXXXX"
                                inputmode="numeric"
                                autocomplete="tel"
                                maxlength="9"
                                pattern="^9[0-9]{8}$"
                                title="Debe ser 9XXXXXXXX (9 dígitos, sin espacios)"
                                autofocus
                                id="phone">
                        </div>

                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= esc($ip ?? '') ?>">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= esc($mac ?? '') ?>">

                        <div class="mb-3 mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">Conectar</button>
                        </div>
                    </div>
                </form>


                <?php if (session()->getFlashdata('errors') !== null): ?>
                    <div class="alert alert-danger my-3" role="alert">
                        <?= session()->getFlashdata(('errors'))  ?>
                    </div>

                <?php endif; ?>
            </div>
            <div class="mb-3 mt-3 d-flex justify-content-center">
                <a id="transference" href="<?= base_url('create-order-payment'); ?>?ip=<?= esc($ip ?? '') ?>&mac=<?= esc($mac ?? '') ?>" class="">
                    ¿ No estás registrado ? regístrate
                </a>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>