<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>



<div class="row align-items-center justify-content-center min-vh-100 ">
    <div class="col col-12 col-md-5 col-lg-4 ">
        <div class="card shadow-lg rounded">
            <div class="card-body p-4">
                <ol>
                    <li><?= $myData['flow_order'] ?></li>
                    <li><?= $myData['commerceOrder'] ?></li>
                    <li><?= $myData['requestDate']  ?></li>
                    <li><?= $myData['status'] ?></li>
                    <li><?= $myData['subject'] ?></li>
                    <li><?= $myData['currency'] ?></li>
                    <li><?= $myData['amount'] ?></li>
                    <li><?= $myData['payer'] ?></li>
                    <li><?= $myData['ip'] ?></li>
                    <li><?= $myData['mac'] ?></li>

                </ol>


            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>