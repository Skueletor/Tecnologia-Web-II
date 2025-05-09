<?php
include("../../session.php");
include("../../bd.php");

$error = null;

if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("SELECT * FROM tbl_usuarios WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);
    if (!$registro) {
        header("Location: index.php?mensaje=Usuario no encontrado");
        exit();
    }
}

if ($_POST) {
    $txtID = $_POST["txtID"];
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    $correo = $_POST["correo"];

    // Validar que el correo no esté en uso por otro usuario
    $sentencia_validar = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE correo = :correo AND id != :id");
    $sentencia_validar->bindParam(":correo", $correo);
    $sentencia_validar->bindParam(":id", $txtID);
    $sentencia_validar->execute();
    
    if ($sentencia_validar->fetchColumn() > 0) {
        $error = "El correo electrónico ya está registrado por otro usuario.";
    } else {
        // Validar que el nombre de usuario no esté en uso por otro usuario
        $sentencia_validar = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE usuario = :usuario AND id != :id");
        $sentencia_validar->bindParam(":usuario", $usuario);
        $sentencia_validar->bindParam(":id", $txtID);
        $sentencia_validar->execute();
        
        if ($sentencia_validar->fetchColumn() > 0) {
            $error = "El nombre de usuario ya está en uso por otro usuario.";
        } else {
            $sentencia = $conexion->prepare("UPDATE tbl_usuarios SET usuario = :usuario, password = :password, correo = :correo WHERE id = :id");
            $sentencia->bindParam(":usuario", $usuario);
            $sentencia->bindParam(":password", $password);
            $sentencia->bindParam(":correo", $correo);
            $sentencia->bindParam(":id", $txtID);
            $sentencia->execute();

            header("Location: index.php?mensaje=Usuario actualizado");
            exit();
        }
    }
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Editar Usuario</div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="post" class="needs-validation" novalidate>
            <input type="hidden" name="txtID" value="<?php echo $registro['id']; ?>">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario"
                    value="<?php echo isset($_POST['usuario']) ? $_POST['usuario'] : $registro['usuario']; ?>" required>
                <div class="invalid-feedback">
                    Por favor ingrese un nombre de usuario.
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password"
                    value="<?php echo $registro['password']; ?>" required>
                <div class="invalid-feedback">
                    Por favor ingrese una contraseña.
                </div>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo"
                    value="<?php echo isset($_POST['correo']) ? $_POST['correo'] : $registro['correo']; ?>" required>
                <div class="invalid-feedback">
                    Por favor ingrese un correo electrónico válido.
                </div>
            </div>
            <button type="submit" class="btn btn-success">Actualizar</button>
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
