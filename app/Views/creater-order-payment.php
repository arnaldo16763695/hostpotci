<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>

<?php
/** @var string|null $error */
$error = session()->getFlashdata('hotspot_error');
?>

<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <div class="pb-4">
                    <img src="<?= base_url('images/LogoGlobal-n.png') ?>" alt="Logo Globalsi" width="100" height="45">
                </div>
                <h5 class="card-title text-center fw-bold ">Selecciona el tiempo de Internet que deseas.</h5>
                <form action="<?= base_url('createOrder') ?>" method="POST" id="hotspotForm">
                    <?= csrf_field(); ?>
                    <div class="mb-3  p-4">
                        <div class="mb-3">
                            <label class="mb-2" for="email">Su Email:</label>
                            <input type="email" class="form-control" name="email" id="email" value="<?= set_value('email'); ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="mb-2" for="phone">Teléfono:</label>
                            <!-- <input type="text" class="form-control" name="phone" id="phone" required> -->
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
                                id="phone">
                        </div>
                        <div class="mb-3">
                            <label class="mb-2" for="phone">Confirma el teléfono</label>
                            <!-- <input type="text" class="form-control" name="phone" id="phone" required> -->
                            <input
                                class="form-control"
                                type="text"
                                name="phoneConfirm"
                                required
                                inputmode="numeric"
                                autocomplete="tel"
                                maxlength="9"
                                pattern="^9[0-9]{8}$"
                                title="Debe ser 9XXXXXXXX (9 dígitos, sin espacios)"
                                id="phoneConfirm">
                            <div class="invalid-feedback">
                                Los teléfonos no coinciden.
                            </div>
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
                            <!-- <div class="p-2 form-check">
                                <input class="form-check-input" type="radio" name="plan" id="1000" value="1000">
                                <label class="form-check-label" for="1000">
                                    1 Hora de Internet. $1.000
                                </label>
                            </div> -->
                        </div>

                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $ip ?>">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $mac ?>">
                        <div class="mb-3 mt-3 d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">Pagar con tarjeta</button>
                        </div>

                </form>
                <?php
                // Ajusta la zona horaria según tu país/servidor:

                $now = new DateTime('now');
                $start = (new DateTime('today'))->setTime(6, 0, 0);      // 06:00
                $end   = (new DateTime('today'))->setTime(23, 59, 59);   // 23:59:59 (equivale a "hasta 12:00 AM")
                $showTransference = ($now >= $start && $now <= $end);
                ?>

                <?php if ($showTransference): ?>
                    <div class="mb-3 mt-3 d-flex justify-content-center">
                        <a id="transference" href="<?= base_url('contact-transference'); ?>" class="">
                            Pagar por transferencia
                        </a>
                    </div>
                <?php else: ?>
                    <div class="mb-3 mt-3 text-center">
                        <small class="text-muted">
                            La opción de pago por transferencia solo está habilitada en el horario de 6:00 am a 12:00 am.
                        </small>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors') !== null): ?>
                    <div class="alert alert-danger my-3" role="alert">
                        <?= session()->getFlashdata(('errors'))  ?>
                    </div>

                <?php endif; ?>



                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger my-3" role="alert">
                        <?php
                        if (stripos($error, 'uptime limit') !== false) {
                            echo 'Tu tiempo de internet expiró, debes actualizar tu pago.';
                        } elseif (stripos($error, 'invalid user') !== false) {
                            echo 'Usuario o contraseña incorrectos.';
                        } else {
                            echo 'No fue posible conectarte. Por favor intenta nuevamente.';
                        }
                        ?>
                    </div>
                <?php endif; ?>
                <div class="mb-3 mt-3 d-flex justify-content-between">
                    <form method="POST" action="<?= base_url(); ?>">
                        <input type="hidden" class="form-control" id="ip" name="ip" value="<?= $_GET['ip']; ?>">
                        <input type="hidden" class="form-control" id="mac" name="mac" value="<?= $_GET['mac']; ?>">
                        <button type="submit" class="btn btn-danger">Atrás</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        const form = document.getElementById('hotspotForm');
        const phone = document.getElementById('phone');
        const phoneConfirm = document.getElementById('phoneConfirm');
        const btnSubmit = document.getElementById('btnSubmit');

        function validatePhonesMatch() {
            const a = (phone.value || '').trim();
            const b = (phoneConfirm.value || '').trim();

            const match = a !== '' && b !== '' && a === b;

            // Si el confirm está vacío, no lo marcamos como error todavía
            if (b === '') {
                phoneConfirm.setCustomValidity('');
                phoneConfirm.classList.remove('is-invalid');
                btnSubmit.disabled = false;
                return true;
            }

            if (!match) {
                phoneConfirm.setCustomValidity('no-match');
                phoneConfirm.classList.add('is-invalid');
                btnSubmit.disabled = true;
                return false;
            }

            phoneConfirm.setCustomValidity('');
            phoneConfirm.classList.remove('is-invalid');
            btnSubmit.disabled = false;
            return true;
        }

        phone.addEventListener('input', validatePhonesMatch);
        phoneConfirm.addEventListener('input', validatePhonesMatch);

        form.addEventListener('submit', function(e) {
            // dispara validación nativa + la nuestra
            const ok = form.checkValidity() && validatePhonesMatch();
            if (!ok) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
                phoneConfirm.reportValidity();
            }
        });
    })();
</script>


<?= $this->endSection('content'); ?>