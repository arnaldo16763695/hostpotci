<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <ol>
                    <li><?= esc($flow_order) ?></li>
                    <li><?= esc($commerceOrder) ?></li>
                    <li><?= esc($requestDate)  ?></li>
                    <li><?= esc($status) ?></li>
                    <li><?= esc($subject) ?></li>
                    <li><?= esc($currency) ?></li>
                    <li><?= esc($amount) ?></li>
                    <li><?= esc($payer) ?></li>
                    <li><?= esc($ip) ?></li>
                    <li><?= esc($mac) ?></li>

                </ol>


            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>