<?php
include("../../session.php");
include("../../bd.php");

$error = null;

if ($_POST) {
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    $correo = $_POST["correo"];

    // Validar que el correo no exista ya en la base de datos
    $sentencia_validar = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE correo = :correo");
    $sentencia_validar->bindParam(":correo", $correo);
    $sentencia_validar->execute();
    
    if ($sentencia_validar->fetchColumn() > 0) {
        $error = "El correo electrónico ya está registrado. Por favor utilice otro.";
    } else {
        // Validar que el nombre de usuario no exista
        $sentencia_validar = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE usuario = :usuario");
        $sentencia_validar->bindParam(":usuario", $usuario);
        $sentencia_validar->execute();
        
        if ($sentencia_validar->fetchColumn() > 0) {
            $error = "El nombre de usuario ya está en uso. Por favor elija otro.";
        } else {
            $sentencia = $conexion->prepare("INSERT INTO tbl_usuarios (usuario, password, correo) VALUES (:usuario, :password, :correo)");
            $sentencia->bindParam(":usuario", $usuario);
            $sentencia->bindParam(":password", $password); // ⚠️ Sin hash, en proyecto real usar password_hash()
            $sentencia->bindParam(":correo", $correo);
            $sentencia->execute();

            header("Location: index.php?mensaje=Usuario agregado");
            exit();
        }
    }
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Nuevo Usuario</div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form action="" method="post" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required 
                       value="<?php echo isset($_POST['usuario']) ? $_POST['usuario'] : ''; ?>">
                <div class="invalid-feedback">
                    Por favor ingrese un nombre de usuario.
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password" required>
                <div class="invalid-feedback">
                    Por favor ingrese una contraseña.
                </div>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo" required
                       value="<?php echo isset($_POST['correo']) ? $_POST['correo'] : ''; ?>">
                <div class="invalid-feedback">
                    Por favor ingrese un correo electrónico válido.
                </div>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()
</script>

<?php include("../../Templates/footer.php"); ?>
