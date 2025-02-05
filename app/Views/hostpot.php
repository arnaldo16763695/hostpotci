<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-11 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <h5 class="card-title text-center">Bienvenido</h5>
                <h6 class="card-title text-center pb-4">Presione siguiente para elegir su plan de Internet</h6>
                <form action="<?= base_url('creater-order-payment') ?>" class="border" method="POST">
                    <?= csrf_field(); ?>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Siguiente</button>
                    </div>
                    <input type="hidden" class="form-control" id="ip" name="ip" value="<?= '192.168.0.7' ?>" placeholder="">
                    <input type="hidden" class="form-control" id="mac" name="mac" value="<?= 'df:34:67:09:ab:23' ?>" placeholder="">

                </form>
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