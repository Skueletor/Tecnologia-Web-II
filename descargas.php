<?php
include("session.php");
include("Templates/header.php");
?>

<div class="container my-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-download me-2"></i>Centro de Descargas</h5>
                </div>
                <div class="card-body">
                    <p class="lead">Desde esta sección puede descargar informes y documentos del sistema en diferentes formatos.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Lista de Empleados -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Lista de Empleados</h5>
                </div>
                <div class="card-body">
                    <p>Descargue una lista completa de todos los empleados registrados en el sistema.</p>
                    
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="api/download.php?type=employee_list&format=pdf" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                        <a href="api/download.php?type=employee_list&format=excel" class="btn btn-success">
                            <i class="fas fa-file-excel me-2"></i>Excel
                        </a>
                        <a href="api/download.php?type=employee_list&format=csv" class="btn btn-info text-white">
                            <i class="fas fa-file-csv me-2"></i>CSV
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de Puestos -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Lista de Puestos</h5>
                </div>
                <div class="card-body">
                    <p>Descargue una lista completa de todos los puestos disponibles en el sistema.</p>
                    
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="api/download.php?type=positions&format=pdf" class="btn btn-danger">
                            <i class="fas fa-file-pdf me-2"></i>PDF
                        </a>
                        <a href="api/download.php?type=positions&format=json" class="btn btn-secondary">
                            <i class="fas fa-file-code me-2"></i>JSON
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjetas de Empleados -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Tarjetas de Empleados</h5>
                </div>
                <div class="card-body">
                    <p>Descargue la tarjeta individual de un empleado. Seleccione el empleado de la lista:</p>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="input-group mb-3">
                                <select id="employeeSelect" class="form-select">
                                    <option value="">Seleccione un empleado...</option>
                                    <?php
                                    $stmt = $conexion->prepare("
                                        SELECT 
                                            id, 
                                            CONCAT(primernombre, ' ', primerapellido) AS nombre
                                        FROM 
                                            empleados
                                        ORDER BY 
                                            primerapellido, primernombre
                                    ");
                                    $stmt->execute();
                                    
                                    while ($empleado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="' . $empleado['id'] . '">' . $empleado['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                                <button id="btnDownloadCard" class="btn btn-primary" disabled>
                                    <i class="fas fa-download me-2"></i>Descargar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="formatOptions" class="d-none">
                        <hr>
                        <h6>Formato de descarga:</h6>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="formatRadio" id="formatPDF" value="pdf" checked>
                            <label class="btn btn-outline-danger" for="formatPDF">PDF</label>
                            
                            <input type="radio" class="btn-check" name="formatRadio" id="formatHTML" value="html">
                            <label class="btn btn-outline-primary" for="formatHTML">HTML</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const employeeSelect = document.getElementById('employeeSelect');
    const btnDownloadCard = document.getElementById('btnDownloadCard');
    const formatOptions = document.getElementById('formatOptions');
    
    employeeSelect.addEventListener('change', function() {
        btnDownloadCard.disabled = !this.value;
        if (this.value) {
            formatOptions.classList.remove('d-none');
        } else {
            formatOptions.classList.add('d-none');
        }
    });
    
    btnDownloadCard.addEventListener('click', function() {
        const employeeId = employeeSelect.value;
        if (!employeeId) return;
        
        const format = document.querySelector('input[name="formatRadio"]:checked').value;
        
        window.open(`api/download.php?type=employee_card&id=${employeeId}&format=${format}`, '_blank');
    });
});
</script>

<?php include("Templates/footer.php"); ?>
