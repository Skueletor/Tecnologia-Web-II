<?php
include("../../bd.php");

// Eliminar puesto
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];

    $sentencia = $conexion->prepare("DELETE FROM tbl_puesto WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();

    $mensaje = "Puesto eliminado";
    header("Location: index.php?mensaje=" . $mensaje);
    exit();
}

// Listar puestos
$sentencia = $conexion->prepare("SELECT * FROM tbl_puesto");
$sentencia->execute();
$lista_puestos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
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
                <i class="fas fa-briefcase"></i>
            </div>
            <div>
                <h2 class="section-header mb-0">Puestos de Trabajo</h2>
                <p class="text-muted">Gestiona los diferentes cargos de la empresa</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a class="btn btn-primary" href="crear.php">
            <i class="fas fa-plus-circle me-2"></i>Nuevo Puesto
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body table-container">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th>Nombre del Puesto</th>
                    <th width="15%">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista_puestos as $puesto) { ?>
                    <tr>
                        <td><?php echo $puesto['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-briefcase text-primary me-2"></i>
                                <span><?php echo $puesto['nombredelpuesto']; ?></span>
                            </div>
                        </td>
                        <td class="action-buttons">
                            <a href="editar.php?txtID=<?php echo $puesto['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmarEliminar(<?php echo $puesto['id']; ?>)" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
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
                <p>¿Estás seguro que deseas eliminar este puesto? Esta acción no se puede deshacer.</p>
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
