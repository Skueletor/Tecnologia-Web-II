<?php
include("../../bd.php");

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

    $sentencia = $conexion->prepare("UPDATE tbl_usuarios SET usuario = :usuario, password = :password, correo = :correo WHERE id = :id");
    $sentencia->bindParam(":usuario", $usuario);
    $sentencia->bindParam(":password", $password);
    $sentencia->bindParam(":correo", $correo);
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();

    header("Location: index.php?mensaje=Usuario actualizado");
    exit();
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Editar Usuario</div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="txtID" value="<?php echo $registro['id']; ?>">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario"
                    value="<?php echo $registro['usuario']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password"
                    value="<?php echo $registro['password']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo</label>
                <input type="email" class="form-control" name="correo" id="correo"
                    value="<?php echo $registro['correo']; ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Actualizar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../Templates/footer.php"); ?>
