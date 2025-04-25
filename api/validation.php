<?php
session_start();
header('Content-Type: application/json');

include("../bd.php");

$response = [
    'success' => true,
    'errors' => []
];

$form = isset($_POST['form_id']) ? $_POST['form_id'] : '';

try {
    if (empty($_POST)) {
        throw new Exception("No data received");
    }
    
    switch ($form) {
        case 'user_form':
            validateUserForm();
            break;
        case 'employee_form':
            validateEmployeeForm();
            break;
        case 'position_form':
            validatePositionForm();
            break;
        default:
            $allValid = true;
            
            foreach ($_POST as $field => $value) {
                if ($field === 'form_id') continue;
                
                if (empty(trim($value)) && isset($_POST[$field.'_required']) && $_POST[$field.'_required'] === '1') {
                    $response['errors'][$field] = "Este campo es obligatorio";
                    $allValid = false;
                }
                
                if ($field === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $response['errors'][$field] = "Email inválido";
                    $allValid = false;
                }
            }
            
            $response['success'] = $allValid;
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;

function validateUserForm() {
    global $response, $conexion;
    
    $usuario = trim($_POST['usuario'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario)) {
        $response['errors']['usuario'] = "El nombre de usuario es obligatorio";
    } elseif (strlen($usuario) < 4) {
        $response['errors']['usuario'] = "El usuario debe tener al menos 4 caracteres";
    }
    
    if (empty($correo)) {
        $response['errors']['correo'] = "El correo es obligatorio";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $response['errors']['correo'] = "El formato del correo es inválido";
    } else {
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE correo = :correo AND id != :id");
        $id = isset($_POST['txtID']) ? $_POST['txtID'] : 0;
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $response['errors']['correo'] = "Este correo ya está en uso";
        }
    }
    
    if (empty($password) && !isset($_POST['txtID'])) {
        $response['errors']['password'] = "La contraseña es obligatoria";
    } elseif (!empty($password) && strlen($password) < 6) {
        $response['errors']['password'] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    $response['success'] = empty($response['errors']);
}

function validateEmployeeForm() {
    global $response;
    
    $primernombre = trim($_POST['primernombre'] ?? '');
    $primerapellido = trim($_POST['primerapellido'] ?? '');
    $idpuesto = $_POST['idpuesto'] ?? '';
    $fechaingreso = $_POST['fechaingreso'] ?? '';
    
    if (empty($primernombre)) {
        $response['errors']['primernombre'] = "El primer nombre es obligatorio";
    }
    
    if (empty($primerapellido)) {
        $response['errors']['primerapellido'] = "El primer apellido es obligatorio";
    }
    
    if (empty($idpuesto)) {
        $response['errors']['idpuesto'] = "Debe seleccionar un puesto";
    }
    
    if (empty($fechaingreso)) {
        $response['errors']['fechaingreso'] = "La fecha de ingreso es obligatoria";
    } elseif (!validateDate($fechaingreso)) {
        $response['errors']['fechaingreso'] = "Formato de fecha inválido";
    }
    
    $response['success'] = empty($response['errors']);
}

function validatePositionForm() {
    global $response;
    
    $nombredelpuesto = trim($_POST['nombredelpuesto'] ?? '');
    
    if (empty($nombredelpuesto)) {
        $response['errors']['nombredelpuesto'] = "El nombre del puesto es obligatorio";
    }
    
    $response['success'] = empty($response['errors']);
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
