<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <h5 class="card-title text-center">Selecciona el tiempo de Internet que deseas.</h5>
                <form action="<?= base_url('createOrder') ?>" method="POST">
                    <?= csrf_field(); ?>
                    <div class="mb-3  p-4">
                        <div class="mb-3">
                            <label class="mb-2" for="email">Su Email:</label>
                            <input type="email" class="form-control" name="email" id="email" value="<?= set_value('email'); ?>" required autofocus>
                        </div>
                        <div class="d-flex flex-column  ">
                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="1000" value="1000" checked>
                                <label class="form-check-label" for="1000">
                                    1 Hora de Internet. $1.000 
                                </label>
                            </div>
                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="3000" value="3000">
                                <label class="form-check-label" for="3000">
                                    1 Día de Internet. $3.000 
                                </label>
                            </div>
                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="5000" value="5000">
                                <label class="form-check-label" for="5000">
                                    2 Días de Internet. $5.000 
                                </label>
                            </div>
                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="10000" value="10000">
                                <label class="form-check-label" for="10000">
                                    7 Días de Internet. $10.000 
                                </label>
                            </div>
                        </div>

                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $_POST['ip']; ?>" placeholder="">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $_POST['mac']; ?>" placeholder="">
                        <div class="mb-3 mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Siguiente</button>
                        </div>

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