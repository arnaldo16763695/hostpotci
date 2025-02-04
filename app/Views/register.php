<?= $this->extend('layout/template'); ?>


<?= $this->section('content'); ?>


<div class="d-flex justify-content-center align-items-center p-4">
    <div class="card shadow-lg form-signin w-50">
        <div class="card-body p-5 ">
            <h1 class="fs-4 card-title fw-bold mb-4 w-6">Registro</h1>
            <form method="POST" action="#" autocomplete="off">
    
                <div class="mb-3">
                    <label class="mb-2" for="name">Nombre</label>
                    <input type="text" class="form-control" name="name" id="name" value="" required autofocus>
                </div>
    
                <div class="mb-3">
                    <label class="mb-2" for="email">Correo electrónico</label>
                    <input type="email" class="form-control" name="email" id="email" value="" required>
                </div>
    
                <div class="mb-3">
                    <label class="mb-2" for="user">Usuario</label>
                    <input type="text" class="form-control" name="user" id="user" value="" required>
                </div>
    
                <div class="mb-3">
                    <label for="password">Contraseña</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
    
                <div class="mb-3">
                    <label for="repassword">Confirmar contraseña</label>
                    <input type="password" class="form-control" name="repassword" id="repassword" required>
                </div>
    
                <button type="submit" class="btn btn-primary">
                    Registrar
                </button>
            </form>
    
        </div>
        <div class="card-footer py-3 border-0">
            <div class="text-center">
                <a href="login.html">Iniciar sesión</a>
            </div>
        </div>
    </div>
</div>



<?= $this->endSection('content'); ?>