<?php
include("../../bd.php");

if ($_POST) {
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    $correo = $_POST["correo"];

    $sentencia = $conexion->prepare("INSERT INTO tbl_usuarios (usuario, password, correo) VALUES (:usuario, :password, :correo)");
    $sentencia->bindParam(":usuario", $usuario);
    $sentencia->bindParam(":password", $password); // ⚠️ Sin hash, en proyecto real usar password_hash()
    $sentencia->bindParam(":correo", $correo);
    $sentencia->execute();

    header("Location: index.php?mensaje=Usuario agregado");
    exit();
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Nuevo Usuario</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../Templates/footer.php"); ?>
