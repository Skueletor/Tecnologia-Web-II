<?php
$url_base = "http://localhost:80/app/";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in - for consistent header display
$logged_in = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
?>

<!doctype html>
<html lang="en">
    <head>
        <title>Sistema de Empleados</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <!-- DataTables Bootstrap 5 CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <!-- Custom CSS -->
        <link rel="stylesheet" href="<?php echo $url_base; ?>assets/css/styles.css" />
    </head>

    <body>
        <header>
            <!-- Modern navbar -->
            <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
                <div class="container">
                    <a class="navbar-brand fw-bold text-primary" href="<?php echo $url_base; ?>">
                        <i class="fas fa-building me-2"></i>Sistema de Empleados
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <?php if ($logged_in): ?>
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?php echo $url_base; ?>">
                                    <i class="fas fa-home me-1"></i> Inicio
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?php echo $url_base; ?>secciones/empleados/">
                                    <i class="fas fa-users me-1"></i> Empleados
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?php echo $url_base; ?>secciones/puestos/">
                                    <i class="fas fa-briefcase me-1"></i> Puestos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3" href="<?php echo $url_base; ?>secciones/usuarios/">
                                    <i class="fas fa-user-cog me-1"></i> Usuarios
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user-circle me-1"></i> <?php echo $_SESSION['usuario']; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="<?php echo $url_base; ?>cerrar.php">
                                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                                    </a></li>
                                </ul>
                            </li>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </header>
        <main class="container py-4">