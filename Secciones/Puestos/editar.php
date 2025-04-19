<?php
include("../../bd.php");

if (isset($_GET["txtID"])) {
    $txtID = $_GET["txtID"];

    $sentencia = $conexion->prepare("SELECT * FROM tbl_puesto WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        header("Location: index.php?mensaje=No encontrado");
        exit();
    }
}

if ($_POST) {
    $txtID = $_POST["txtID"];
    $nombredelpuesto = $_POST["nombredelpuesto"];

    $sentencia = $conexion->prepare("UPDATE tbl_puesto SET nombredelpuesto = :nombredelpuesto WHERE id = :id");
    $sentencia->bindParam(":nombredelpuesto", $nombredelpuesto);
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();

    header("Location: index.php?mensaje=Actualizado");
    exit();
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Editar Puesto</div>
    <div class="card-body">
        <form action="" method="post">
            <input type="hidden" name="txtID" value="<?php echo $registro['id']; ?>">
            <div class="mb-3">
                <label for="nombredelpuesto" class="form-label">Nombre del Puesto</label>
                <input type="text" class="form-control" name="nombredelpuesto" id="nombredelpuesto"
                    value="<?php echo $registro['nombredelpuesto']; ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Actualizar</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../Templates/footer.php"); ?>
