<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="container mt-3 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-lg form-signin">
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title font-weight-bold mb-4">Introduzca sus datos</h1>

                    <form method="POST" action="<?= base_url('create-user-mikrotik') ?>" autocomplete="off">
                        <?= csrf_field(); ?>

                        <div class="form-group mb-4">
                            <label for="name">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                name="name"
                                id="name"
                                value=""
                                required
                            >
                        </div>

                        <div class="form-group mb-4">
                            <label for="rut">Rut</label>
                            <input
                                type="text"
                                class="form-control"
                                name="rut"
                                id="rut"
                                value=""
                                required
                            >
                        </div>

                        <div class="form-group mb-4">
                            <label for="email">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                id="email"
                                value="<?= $email; ?>"
                                required
                            >
                        </div>

                        <div class="form-group mb-4">
                            <label for="phone">Teléfono</label>
                            <input
                                type="text"
                                class="form-control"
                                name="phone"
                                id="phone"
                                value="<?= $phone; ?>"
                                required
                            >                        
                            <input
                                type="hidden"
                                class="form-control"
                                name="mac"
                                id="mac"
                                value="<?= $mac; ?>"
                            >                        
                            <input
                                type="hidden"
                                class="form-control"
                                name="ip"
                                id="ip"
                                value="<?= $ip; ?>"
                            >                        
                        </div>

                        <div class="form-group mb-4">
                            <label class="d-block mb-2">Plan de Internet</label>

                            <div class="pl-4">
                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="plan"
                                        id="3000"
                                        value="3000"
                                        <?php if ($plan === '3000') echo 'checked'; ?>
                                    >
                                    <label class="form-check-label" for="3000">
                                        1 Día de Internet. $3.000
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="plan"
                                        id="5000"
                                        value="5000"
                                        <?php if ($plan === '5000') echo 'checked'; ?>
                                    >
                                    <label class="form-check-label" for="5000">
                                        2 Días de Internet. $5.000
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="plan"
                                        id="10000"
                                        value="10000"
                                        <?php if ($plan === '10000') echo 'checked'; ?>
                                    >
                                    <label class="form-check-label" for="10000">
                                        7 Días de Internet. $10.000
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="radio"
                                        name="plan"
                                        id="1000"
                                        value="1000"
                                        <?php if ($plan === '1000') echo 'checked'; ?>
                                    >
                                    <label class="form-check-label" for="1000">
                                        1 hora de Internet. $1.000
                                    </label>
                                </div>
                            </div>

                            <?php if (session()->getFlashdata('errors') !== null): ?>
                                <div class="alert alert-danger my-3" role="alert">
                                    <?= session()->getFlashdata('errors'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                Enviar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection('content'); ?>
