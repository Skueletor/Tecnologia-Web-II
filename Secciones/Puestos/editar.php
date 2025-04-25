<?php
include("../../session.php");
include("../../bd.php");

$error = null;

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

    // Validar que el nombre del puesto no esté en uso por otro puesto
    $sentencia_validar = $conexion->prepare("SELECT COUNT(*) FROM tbl_puesto WHERE nombredelpuesto = :nombredelpuesto AND id != :id");
    $sentencia_validar->bindParam(":nombredelpuesto", $nombredelpuesto);
    $sentencia_validar->bindParam(":id", $txtID);
    $sentencia_validar->execute();
    
    if ($sentencia_validar->fetchColumn() > 0) {
        $error = "El nombre del puesto ya está en uso. Por favor elija otro nombre.";
    } else {
        $sentencia = $conexion->prepare("UPDATE tbl_puesto SET nombredelpuesto = :nombredelpuesto WHERE id = :id");
        $sentencia->bindParam(":nombredelpuesto", $nombredelpuesto);
        $sentencia->bindParam(":id", $txtID);
        $sentencia->execute();

        header("Location: index.php?mensaje=Actualizado");
        exit();
    }
}
?>

<?php include("../../Templates/header.php"); ?>

<div class="card">
    <div class="card-header">Editar Puesto</div>
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form action="" method="post" class="needs-validation" novalidate>
            <input type="hidden" name="txtID" value="<?php echo $registro['id']; ?>">
            <div class="mb-3">
                <label for="nombredelpuesto" class="form-label">Nombre del Puesto</label>
                <input type="text" class="form-control" name="nombredelpuesto" id="nombredelpuesto"
                    value="<?php echo isset($_POST['nombredelpuesto']) ? $_POST['nombredelpuesto'] : $registro['nombredelpuesto']; ?>" required>
                <div class="invalid-feedback">
                    Por favor ingrese el nombre del puesto.
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
