<?php
include("../../bd.php");

// Eliminar usuario
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("DELETE FROM tbl_usuarios WHERE id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    header("Location: index.php?mensaje=Usuario eliminado");
    exit();
}

// Obtener lista de usuarios
$sentencia = $conexion->prepare("SELECT * FROM tbl_usuarios");
$sentencia->execute();
$lista_usuarios = $sentencia->fetchAll(PDO::FETCH_ASSOC);
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
                <i class="fas fa-user-cog"></i>
            </div>
            <div>
                <h2 class="section-header mb-0">Usuarios del Sistema</h2>
                <p class="text-muted">Administra las cuentas y permisos de acceso</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a class="btn btn-primary" href="crear.php">
            <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body table-container">
        <table class="table table-hover data-table">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th width="15%">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lista_usuarios as $usuario) { ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-icon me-2">
                                    <i class="fas fa-user-circle text-primary"></i>
                                </div>
                                <span class="fw-bold"><?php echo $usuario['usuario']; ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-envelope text-secondary me-2"></i>
                                <?php echo $usuario['correo']; ?>
                            </div>
                        </td>
                        <td class="action-buttons">
                            <a href="editar.php?txtID=<?php echo $usuario['id']; ?>" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="confirmarEliminar(<?php echo $usuario['id']; ?>)" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="Eliminar">
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
                <p>¿Estás seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>
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
