<?= $this->extend('layout/template'); ?>

<?= $this->section('content'); ?>

<div class="container mt-3 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow-lg form-signin">
                <div class="card-body p-4 p-md-5">
                    <h1 class="card-title font-weight-bold mb-4">Introduzca sus datos</h1>

                    <form method="POST" action="<?= base_url('create-user-mikrotik') ?>" autocomplete="off" id="hotspotForm">
                        <?= csrf_field(); ?>

                        <div class="form-group mb-4">
                            <label for="name">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                name="name"
                                id="name"
                                value=""
                                required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="rut">Rut</label>


                            <input
                                id="rut"
                                name="rut"
                                class="form-control"
                                type="text"
                                required
                                minlength="8"
                                maxlength="12"
                                autocomplete="off" />


                        </div>

                        <div class="form-group mb-4">
                            <label for="email">Correo electrónico</label>
                            <input
                                type="email"
                                class="form-control"
                                name="email"
                                id="email"
                                value="<?= $email; ?>"
                                required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="phone">Teléfono</label>
                            <!-- <input
                                type="text"
                                class="form-control"
                                name="phone"
                                id="phone"
                                value=""
                                required
                            >                         -->
                            <input
                                class="form-control"
                                type="text"
                                name="phone"
                                required
                                inputmode="numeric"
                                autocomplete="tel"
                                maxlength="9"
                                pattern="^9[0-9]{8}$"
                                title="Debe ser 9XXXXXXXX (9 dígitos, sin espacios)"
                                autofocus
                                value="<?= $phone; ?>"
                                id="phone">
                            <input
                                type="hidden"
                                class="form-control"
                                name="mac"
                                id="mac"
                                value="<?= $mac; ?>">
                            <input
                                type="hidden"
                                class="form-control"
                                name="ip"
                                id="ip"
                                value="<?= $ip; ?>">
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
                                        <?php if ($plan === '3000') echo 'checked'; ?>>
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
                                        <?php if ($plan === '5000') echo 'checked'; ?>>
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
                                        <?php if ($plan === '10000') echo 'checked'; ?>>
                                    <label class="form-check-label" for="10000">
                                        7 Días de Internet. $10.000
                                    </label>
                                </div>
                            </div>

                            <?php if (session()->getFlashdata('errors') !== null): ?>
                                <div class="alert alert-danger my-3" role="alert">
                                    <?= session()->getFlashdata('errors'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4">
                            <div class="card border-info shadow-sm">
                                <div class="card-body p-3">

                                    <h6 class="fw-bold mb-2 text-info">
                                        Datos para la transferencia
                                    </h6>

                                    <div class="small lh-sm">
                                        <div><strong>Empresa:</strong> Movinet Comunicaciones SPA</div>
                                        <div><strong>RUT:</strong> 77.008.345-1</div>
                                        <div><strong>Banco:</strong> Mercado Pago</div>
                                        <div><strong>Cuenta:</strong> Vista</div>
                                        <div><strong>N°:</strong> 1075053672</div>
                                        <div>
                                            <strong>Email:</strong>
                                            <a href="mailto:ventas@globalsi.cl">ventas@globalsi.cl</a>
                                        </div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="alert alert-warning py-2 px-3 mb-0 small text-center">
                                        📲 Envía el comprobante de pago al WhatsApp<br>
                                        <a href="https://wa.me/56976452046" class="fw-bold text-dark text-decoration-none">
                                            +56 9 7645 2046
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
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