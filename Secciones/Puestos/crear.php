<?php
include("../../bd.php");

if ($_POST) {
    $nombredelpuesto = $_POST["nombredelpuesto"];

    $sentencia = $conexion->prepare("INSERT INTO tbl_puesto (nombredelpuesto) VALUES (:nombredelpuesto)");
    $sentencia->bindParam(":nombredelpuesto", $nombredelpuesto);
    $sentencia->execute();

    header("Location: index.php?mensaje=Puesto agregado");
    exit();
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Nuevo Puesto</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="nombredelpuesto" class="form-label">Nombre del Puesto</label>
                <input type="text" class="form-control" name="nombredelpuesto" id="nombredelpuesto" required>
            </div>
            <button type="submit" class="btn btn-success">Guardar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../Templates/footer.php"); ?>
