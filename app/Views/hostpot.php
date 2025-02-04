<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-11 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <h5 class="card-title text-center">Sistema Hostpot</h5>
                <h5 class="card-title text-center">Para continuar presione el boton Siguiente</h5>
                <form action="<?= base_url('creater-order-payment') ?>" method="POST">
                    <?= csrf_field(); ?>
                    <!-- <div class="mb-3">
                        <label for="name" class="form-label">Nombre:</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= set_value('name'); ?>" placeholder="">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Rut:</label>
                        <input type="text" class="form-control" id="rut" name="rut" value="<?= set_value('rut'); ?>" placeholder="">
                    </div> -->
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Siguiente</button>
                    </div>
                    <input type="hidden" class="form-control" id="ip" name="ip" value="<?= '192.168.0.7' ?>" placeholder="">
                    <input type="hidden" class="form-control" id="mac" name="mac" value="<?= 'df:34:67:09:ab:23' ?>" placeholder="">

                </form>
               <?php if(session()->getFlashdata('errors') !== null): ?>
                    <div class="alert alert-danger my-3" role="alert">
                         <?= session()->getFlashdata(('errors'))  ?>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>