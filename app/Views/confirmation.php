<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <ol>
                    <li><?= $json_response['flow_order'] ?></li>
                    <li><?= $json_response['commerceOrder'] ?></li>
                    <li><?= $json_response['requestDate']  ?></li>
                    <li><?= $json_response['status'] ?></li>
                    <li><?= $json_response['subject'] ?></li>
                    <li><?= $json_response['currency'] ?></li>
                    <li><?= $json_response['amount'] ?></li>
                    <li><?= $json_response['payer'] ?></li>
                    <li><?= $json_response['optional']['ip'] ?></li>
                    <li><?= $json_response['optional']['mac'] ?></li>

                </ol>


            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>