<?php
session_start();
include("bd.php");

// If already logged in, redirect to index
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: index.php");
    exit();
}

$error = null;
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

// Process login form submission
if ($_POST) {
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
    
    // Validate against database
    $sentencia = $conexion->prepare("SELECT * FROM tbl_usuarios WHERE correo = :correo LIMIT 1");
    $sentencia->bindParam(":correo", $correo);
    $sentencia->execute();
    
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if ($registro && $registro['password'] == $password) { // In production, use password_hash/verify
        // Create session
        $_SESSION['usuario'] = $registro['usuario'];
        $_SESSION['loggedin'] = true;
        $_SESSION['id'] = $registro['id'];
        
        // Redirect to original page or index
        header("Location: " . $redirect);
        exit();
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>

<!doctype html>
<html lang="es">
<head>
    <title>Login - Sistema de Empleados</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    
    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css" />
    
    <style>
        body {
            background-color: #f5f8fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .login-container {
            width: 400px;
            max-width: 100%;
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            transition: transform 0.3s ease;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .login-logo i {
            font-size: 3.5rem;
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .login-title {
            text-align: center;
            font-weight: 700;
            margin-bottom: 10px;
            color: #111827;
        }
        
        .login-subtitle {
            text-align: center;
            color: #6c757d;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-floating .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            margin-top: 10px;
            background: linear-gradient(45deg, #0d6efd, #0099ff);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .login-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <i class="fas fa-building"></i>
        </div>
        
        <h2 class="login-title">Sistema de Empleados</h2>
        <p class="login-subtitle">Inicie sesión para acceder al sistema</p>
        
        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?php echo $_GET['mensaje']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            
            <div class="form-floating">
                <input type="email" class="form-control" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
                <label for="correo"><i class="fas fa-envelope me-2"></i>Correo Electrónico</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Contraseña</label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-login">
                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
            </button>
            
            <div class="login-footer">
                <p>Universidad Domingo Savio</p>
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Sistema de Gestión de Empleados</p>
            </div>
        </form>
    </div>
    
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
