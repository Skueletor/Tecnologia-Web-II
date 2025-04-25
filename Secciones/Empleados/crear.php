<?php
include("../../session.php");
include("../../bd.php");

$error = null;

// 1. Procesar el formulario al enviar
if ($_POST) {
    $primernombre = $_POST["primernombre"];
    $segundonombre = $_POST["segundonombre"] ?? '';
    $primerapellido = $_POST["primerapellido"];
    $segundoapellido = $_POST["segundoapellido"] ?? '';
    $idpuesto = $_POST["idpuesto"];
    $fechaingreso = $_POST["fechaingreso"];

    // Validar que no exista un empleado con el mismo nombre y apellidos
    $sentencia_validar = $conexion->prepare("
        SELECT COUNT(*) FROM empleados 
        WHERE primernombre = :primernombre 
        AND primerapellido = :primerapellido
        AND (segundonombre = :segundonombre OR (segundonombre IS NULL AND :segundonombre = ''))
        AND (segundoapellido = :segundoapellido OR (segundoapellido IS NULL AND :segundoapellido = ''))
    ");
    $sentencia_validar->bindParam(":primernombre", $primernombre);
    $sentencia_validar->bindParam(":segundonombre", $segundonombre);
    $sentencia_validar->bindParam(":primerapellido", $primerapellido);
    $sentencia_validar->bindParam(":segundoapellido", $segundoapellido);
    $sentencia_validar->execute();
    
    if ($sentencia_validar->fetchColumn() > 0) {
        $error = "Ya existe un empleado registrado con ese nombre y apellidos.";
    } else {
        // Manejo de foto
        $foto = null;
        if (isset($_FILES["foto"]["name"]) && $_FILES["foto"]["tmp_name"] != "") {
            $foto_nombre = date("Ymd_His") . "_" . $_FILES["foto"]["name"];
            $foto_ruta = "imagenes/" . $foto_nombre;
            move_uploaded_file($_FILES["foto"]["tmp_name"], $foto_ruta);
            $foto = $foto_ruta;
        }

        // Manejo del CV
        $cv = null;
        if (isset($_FILES["cv"]["name"]) && $_FILES["cv"]["tmp_name"] != "") {
            $cv_nombre = date("Ymd_His") . "_" . $_FILES["cv"]["name"];
            $cv_ruta = "cv/" . $cv_nombre;
            move_uploaded_file($_FILES["cv"]["tmp_name"], $cv_ruta);
            $cv = $cv_ruta;
        }

        // Insertar en base de datos
        $sentencia = $conexion->prepare("INSERT INTO empleados 
            (primernombre, segundonombre, primerapellido, segundoapellido, foto, cv, id_puesto, fechaingreso) 
            VALUES (:primernombre, :segundonombre, :primerapellido, :segundoapellido, :foto, :cv, :idpuesto, :fechaingreso)");

        $sentencia->bindParam(":primernombre", $primernombre);
        $sentencia->bindParam(":segundonombre", $segundonombre);
        $sentencia->bindParam(":primerapellido", $primerapellido);
        $sentencia->bindParam(":segundoapellido", $segundoapellido);
        $sentencia->bindParam(":foto", $foto);
        $sentencia->bindParam(":cv", $cv);
        $sentencia->bindParam(":idpuesto", $idpuesto);
        $sentencia->bindParam(":fechaingreso", $fechaingreso);

        $sentencia->execute();

        $mensaje = "Registro agregado correctamente";
        header("Location: index.php?mensaje=" . $mensaje);
        exit();
    }
}

// 2. Obtener lista de puestos
$sentencia_puestos = $conexion->prepare("SELECT * FROM tbl_puesto");
$sentencia_puestos->execute();
$lista_puestos = $sentencia_puestos->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../Templates/header.php"); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="section-header"><i class="fas fa-user-plus me-2"></i>Nuevo Empleado</h2>
    <a class="btn btn-outline-secondary" href="index.php">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form action="" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="primernombre" class="form-label">Primer Nombre</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="primernombre" id="primernombre" required
                                   value="<?php echo isset($_POST['primernombre']) ? $_POST['primernombre'] : ''; ?>" />
                            <div class="invalid-feedback">
                                Por favor ingrese el primer nombre.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="segundonombre" class="form-label">Segundo Nombre</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="segundonombre" id="segundonombre" 
                                   value="<?php echo isset($_POST['segundonombre']) ? $_POST['segundonombre'] : ''; ?>" />
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="primerapellido" class="form-label">Primer Apellido</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="primerapellido" id="primerapellido" required
                                   value="<?php echo isset($_POST['primerapellido']) ? $_POST['primerapellido'] : ''; ?>" />
                            <div class="invalid-feedback">
                                Por favor ingrese el primer apellido.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="segundoapellido" class="form-label">Segundo Apellido</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" name="segundoapellido" id="segundoapellido"
                                   value="<?php echo isset($_POST['segundoapellido']) ? $_POST['segundoapellido'] : ''; ?>" />
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="idpuesto" class="form-label">Puesto</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                            <select class="form-select" name="idpuesto" id="idpuesto" required>
                                <option value="">Seleccione un puesto</option>
                                <?php foreach ($lista_puestos as $puesto) { ?>
                                    <option value="<?php echo $puesto['id']; ?>" 
                                            <?php echo (isset($_POST['idpuesto']) && $_POST['idpuesto'] == $puesto['id']) ? 'selected' : ''; ?>>
                                        <?php echo $puesto['nombredelpuesto']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un puesto.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fechaingreso" class="form-label">Fecha de Ingreso</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                            <input type="date" class="form-control" name="fechaingreso" id="fechaingreso" required
                                   value="<?php echo isset($_POST['fechaingreso']) ? $_POST['fechaingreso'] : ''; ?>" />
                            <div class="invalid-feedback">
                                Por favor seleccione la fecha de ingreso.
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-4">
                        <label for="foto" class="form-label">Foto del Empleado</label>
                        <div class="card p-3 mb-2">
                            <div class="text-center mb-3">
                                <img id="preview-image" src="https://via.placeholder.com/150" class="img-preview border" alt="Vista previa">
                            </div>
                            <input type="file" class="form-control" name="foto" id="foto" accept="image/*" onchange="previewImage(this);" />
                            <small class="text-muted mt-1">Seleccione una imagen de perfil</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cv" class="form-label">Curriculum Vitae (PDF)</label>
                        <div class="card p-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-file-pdf"></i></span>
                                <input type="file" class="form-control" name="cv" id="cv" accept="application/pdf" />
                            </div>
                            <small class="text-muted mt-1">El archivo debe estar en formato PDF</small>
                            <div id="pdf-name" class="mt-2 text-primary"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-2 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Guardar Empleado
                </button>
                <a class="btn btn-outline-secondary ms-2" href="index.php">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    document.getElementById('cv').addEventListener('change', function(e) {
        var fileName = e.target.files[0]?.name || 'Ningún archivo seleccionado';
        document.getElementById('pdf-name').innerHTML = '<i class="fas fa-check-circle me-1"></i>' + fileName;
    });
    
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
