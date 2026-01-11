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
                <form action="<?= base_url('login-to-mik') ?>" method="POST">
                    <?= csrf_field(); ?>
                    <div class="mb-3  p-4">
                        <div class="mb-3">
                            <label class="mb-2" for="email">Teléfono:</label>
                            <input type="text" class="form-control" name="phone" id="phone" value="" required autofocus>
                        </div>


                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $_POST['ip']; ?>">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $_POST['mac']; ?>">
                        <div class="mb-3 mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Conectar</button>
                        </div>

                </form> 
                <div class="mb-3 mt-3 d-flex justify-content-center">
                    <a id="transference" href="<?= base_url('create-order-payment'); ?>?ip=<?= $_POST['ip']; ?>&mac=<?= $_POST['mac']; ?>" class="">
                        ¿ No estás registrado ? regístrate
                    </a>
                </div>
                <?php if (session()->getFlashdata('errors') !== null): ?>
                    <div class="alert alert-danger my-3" role="alert">
                        <?= session()->getFlashdata(('errors'))  ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>