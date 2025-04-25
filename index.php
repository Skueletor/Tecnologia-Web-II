<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

include("Templates/header.php"); 
?>
<br>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="mb-4">Bienvenido, <?php echo $_SESSION['usuario']; ?></h2>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Gestión de Empleados</h5>
                                <p class="card-text">Administre la información del personal</p>
                                <a href="<?php echo $url_base; ?>Secciones/Empleados/" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Puestos de Trabajo</h5>
                                <p class="card-text">Configure los puestos disponibles</p>
                                <a href="<?php echo $url_base; ?>Secciones/Puestos/" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-user-cog fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Usuarios del Sistema</h5>
                                <p class="card-text">Gestione los accesos y permisos</p>
                                <a href="<?php echo $url_base; ?>Secciones/Usuarios/" class="btn btn-primary">Acceder</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-download fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Centro de Descargas</h5>
                                <p class="card-text">Descargue informes y documentos del sistema</p>
                                <a href="<?php echo $url_base; ?>descargas.php" class="btn btn-success">Acceder</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center">
                                <i class="fas fa-rss fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Noticias y Anuncios</h5>
                                <p class="card-text">Consulte las últimas novedades</p>
                                <div class="rss-feed" data-feed-url="https://www.upds.edu.bo/feed/" data-max-items="3">
                                    <div class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
<?php include("Templates/footer.php"); ?>