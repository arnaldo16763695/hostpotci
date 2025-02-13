<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <ol>
                    <li><?= $flow_order ?></li>
                    <li><?= $commerceOrder ?></li>
                    <li><?= $requestDate  ?></li>
                    <li><?= $status ?></li>
                    <li><?= $subject ?></li>
                    <li><?= $currency ?></li>
                    <li><?= $amount ?></li>
                    <li><?= $payer ?></li>
                    <li><?= $ip ?></li>
                    <li><?= $mac ?></li>
                    <li><?= $media ?></li>
                </ol>


            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>