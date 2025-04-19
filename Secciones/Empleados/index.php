<?php include("../../bd.php");

// Eliminar un empleado y sus archivos
if (isset($_GET["txtID"])) {
    $txtID = $_GET["txtID"];

    // Buscar archivos relacionados
    $sentencia = $conexion->prepare("SELECT foto, cv FROM empleados WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro_recuperado = $sentencia->fetch(PDO::FETCH_LAZY);

    // Borrar foto si existe
    if (!empty($registro_recuperado["foto"]) && file_exists("./" . $registro_recuperado["foto"])) {
        unlink("./" . $registro_recuperado["foto"]);
    }

    // Borrar CV si existe
    if (!empty($registro_recuperado["cv"]) && file_exists("./" . $registro_recuperado["cv"])) {
        unlink("./" . $registro_recuperado["cv"]);
    }

    // Borrar registro
    $sentencia = $conexion->prepare("DELETE FROM empleados WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();

    $mensaje = "Registro eliminado";
    header("Location:index.php?mensaje=" . $mensaje);
}

// Obtener lista de empleados
$sentencia = $conexion->prepare("
    SELECT e.*, 
        (SELECT p.nombredelpuesto FROM tbl_puesto p WHERE p.id = e.id_puesto LIMIT 1) AS puesto 
    FROM empleados e
");
$sentencia->execute();
$lista_empleados = $sentencia->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include("../../Templates/header.php"); ?>

<!-- Mensaje de alerta -->
<?php if(isset($_GET['mensaje'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i> <?php echo $_GET['mensaje']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Encabezado de sección mejorado -->
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <div class="section-icon">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <h2 class="section-header mb-0">Gestión de Empleados</h2>
                <p class="text-muted">Administra la información del personal de la empresa</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a class="btn btn-primary" href="crear.php">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Empleado
        </a>
    </div>
</div>

<!-- Card con tabla en diseño minimalista -->
<div class="card">
    <div class="card-body table-container">
        <table class="table table-hover align-middle data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Foto</th>
                    <th>Información</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($lista_empleados as $registro) { ?>
                <tr>
                    <td><?php echo $registro['id']; ?></td>
                    <td>
                        <span class="fw-bold"><?php echo $registro['primernombre'] . ' ' . $registro['primerapellido']; ?></span><br>
                        <small class="text-muted"><?php echo $registro['segundonombre'] . ' ' . $registro['segundoapellido']; ?></small>
                    </td>
                    <td>
                        <?php if (!empty($registro['foto'])) { ?>
                            <img src="<?php echo $registro['foto']; ?>" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" alt="Foto">
                        <?php } else { ?>
                            <div class="text-center">
                                <i class="fas fa-user-circle text-secondary" style="font-size: 2rem;"></i>
                            </div>
                        <?php } ?>
                    </td>
                    <td>
                        <div><i class="fas fa-briefcase me-1 text-primary"></i> <?php echo $registro['puesto']; ?></div>
                        <div><i class="fas fa-calendar me-1 text-secondary"></i> <?php echo $registro['fechaingreso']; ?></div>
                        <?php if (!empty($registro['cv'])) { ?>
                            <a href="<?php echo $registro['cv']; ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-file-pdf me-1"></i>Ver CV
                            </a>
                        <?php } ?>
                    </td>
                    <td class="action-buttons">
                        <a class="btn btn-sm btn-outline-secondary" href="carta_recomendacion.php?txtID=<?php echo $registro['id']; ?>" data-bs-toggle="tooltip" title="Carta de Recomendación">
                            <i class="fas fa-file-alt"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-primary" href="editar.php?txtID=<?php echo $registro['id']; ?>" data-bs-toggle="tooltip" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-danger" href="javascript:void(0)" onclick="confirmarEliminar(<?php echo $registro['id']; ?>)" data-bs-toggle="tooltip" title="Eliminar">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro que deseas eliminar este empleado? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnEliminar" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id) {
        document.getElementById('btnEliminar').href = 'index.php?txtID=' + id;
        var modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
        modal.show();
    }
</script>

<?php include("../../Templates/footer.php"); ?>
