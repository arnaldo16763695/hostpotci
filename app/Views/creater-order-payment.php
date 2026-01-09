<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <div class="pb-4">
                    <img src="<?= base_url('images/LogoGlobal-n.png') ?>" alt="Logo Globalsi" width="100" height="45">
                </div>
                <h5 class="card-title text-center fw-bold ">Selecciona el tiempo de Internet que deseas.</h5>
                <form action="<?= base_url('createOrder') ?>" method="POST">
                    <?= csrf_field(); ?>
                    <div class="mb-3  p-4">
                        <div class="mb-3">
                            <label class="mb-2" for="email">Su Email:</label>
                            <input type="email" class="form-control" name="email" id="email" value="<?= set_value('email'); ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="mb-2" for="phone">Teléfono:</label>
                            <input type="text" class="form-control" name="phone" id="phone" required>
                        </div>
                        <div class="d-flex flex-column  ">

                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="3000" value="3000" checked>
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
                            <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="1000" value="1000">
                                <label class="form-check-label" for="1000">
                                    1 Hora de Internet. $1.000
                                </label>
                            </div>
                        </div>

                        <!-- <input type="hidden" class="form-control" id="ip" name="ip" value=""> -->
                        <!-- <input type="hidden" class="form-control" id="mac" name="mac" value=""> -->
                        <div class="mb-3 mt-3 d-flex justify-content-between">
                            <form action="<?= base_url(); ?>">
                                <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $_GET['ip']; ?>">
                                <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $_GET['mac']; ?>">
                                <button type="submit" class="btn btn-danger">Atrás</button>
                            </form>
                            <button type="submit" class="btn btn-primary">Pagar con tarjeta</button>
                        </div>

                </form>
                <div class="mb-3 mt-3 d-flex justify-content-between">
                    <form action="<?= base_url(); ?>">
                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $_GET['ip']; ?>">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $_GET['mac']; ?>">
                        <button type="submit" class="btn btn-danger">Atrás</button>
                    </form>
                </div>
                <div class="mb-3 mt-3 d-flex justify-content-center">
                    <a id="transference" href="<?= base_url('contact-transference'); ?>" class="">
                        Pagar por transferencia
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