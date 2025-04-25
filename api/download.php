<?php
session_start();
include("../bd.php");

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

try {
    switch ($type) {
        case 'employee_card':
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $stmt = $conexion->prepare("
                SELECT 
                    e.*,
                    p.nombredelpuesto
                FROM 
                    empleados e
                LEFT JOIN 
                    tbl_puesto p ON e.id_puesto = p.id
                WHERE 
                    e.id = :id
            ");
            
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $empleado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$empleado) {
                throw new Exception('Empleado no encontrado');
            }
            
            $nombre_completo = trim($empleado['primernombre'] . ' ' . $empleado['segundonombre'] . ' ' . 
                               $empleado['primerapellido'] . ' ' . $empleado['segundoapellido']);
            
            if ($format === 'pdf') {
                require_once __DIR__ . '/../vendor/dompdf/vendor/autoload.php';
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml(generateEmployeeCardHTML($empleado, $nombre_completo));
                $dompdf->setPaper('letter', 'portrait');
                $dompdf->render();
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="tarjeta_empleado_' . $id . '.pdf"');
                echo $dompdf->output();
                exit;
            } else {
                header('Content-Type: text/html; charset=utf-8');
                echo generateEmployeeCardHTML($empleado, $nombre_completo);
                exit;
            }
            break;
            
        case 'employee_list':
            $stmt = $conexion->prepare("
                SELECT 
                    e.id,
                    e.primernombre,
                    e.segundonombre,
                    e.primerapellido,
                    e.segundoapellido,
                    e.fechaingreso,
                    p.nombredelpuesto
                FROM 
                    empleados e
                LEFT JOIN 
                    tbl_puesto p ON e.id_puesto = p.id
                ORDER BY 
                    e.primerapellido, e.primernombre
            ");
            
            $stmt->execute();
            $empleados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($format === 'csv') {
                $filename = 'lista_empleados_' . date('Ymd_His') . '.csv';
                
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                
                $output = fopen('php://output', 'w');
                
                fputcsv($output, [
                    'ID', 
                    'Primer Nombre', 
                    'Segundo Nombre', 
                    'Primer Apellido', 
                    'Segundo Apellido', 
                    'Puesto', 
                    'Fecha de Ingreso'
                ]);
                
                foreach ($empleados as $empleado) {
                    fputcsv($output, [
                        $empleado['id'],
                        $empleado['primernombre'],
                        $empleado['segundonombre'],
                        $empleado['primerapellido'],
                        $empleado['segundoapellido'],
                        $empleado['nombredelpuesto'],
                        $empleado['fechaingreso']
                    ]);
                }
                
                fclose($output);
                exit;
            } elseif ($format === 'excel') {
                $filename = 'lista_empleados_' . date('Ymd_His') . '.xls';
                
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Cache-Control: max-age=0');
                
                echo '<table border="1">';
                echo '<tr>
                    <th>ID</th>
                    <th>Primer Nombre</th>
                    <th>Segundo Nombre</th>
                    <th>Primer Apellido</th>
                    <th>Segundo Apellido</th>
                    <th>Puesto</th>
                    <th>Fecha de Ingreso</th>
                </tr>';
                
                foreach ($empleados as $empleado) {
                    echo '<tr>';
                    echo '<td>' . $empleado['id'] . '</td>';
                    echo '<td>' . $empleado['primernombre'] . '</td>';
                    echo '<td>' . $empleado['segundonombre'] . '</td>';
                    echo '<td>' . $empleado['primerapellido'] . '</td>';
                    echo '<td>' . $empleado['segundoapellido'] . '</td>';
                    echo '<td>' . $empleado['nombredelpuesto'] . '</td>';
                    echo '<td>' . $empleado['fechaingreso'] . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
                exit;
            } elseif ($format === 'pdf') {
                require_once __DIR__ . '/../vendor/dompdf/vendor/autoload.php';
                
                $html = '<h1>Lista de Empleados</h1>';
                $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
                $html .= '<tr style="background-color: #f0f0f0">
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Puesto</th>
                    <th>Fecha de Ingreso</th>
                </tr>';
                
                foreach ($empleados as $empleado) {
                    $nombre_completo = trim($empleado['primernombre'] . ' ' . $empleado['segundonombre'] . ' ' . 
                                      $empleado['primerapellido'] . ' ' . $empleado['segundoapellido']);
                    
                    $html .= '<tr>';
                    $html .= '<td>' . $empleado['id'] . '</td>';
                    $html .= '<td>' . $nombre_completo . '</td>';
                    $html .= '<td>' . $empleado['nombredelpuesto'] . '</td>';
                    $html .= '<td>' . $empleado['fechaingreso'] . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
                
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('letter', 'landscape');
                $dompdf->render();
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="lista_empleados_' . date('Ymd_His') . '.pdf"');
                echo $dompdf->output();
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => $empleados
                ]);
                exit;
            }
            break;
            
        case 'positions':
            $stmt = $conexion->prepare("SELECT * FROM tbl_puesto ORDER BY nombredelpuesto");
            $stmt->execute();
            $puestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($format === 'pdf') {
                require_once __DIR__ . '/../vendor/dompdf/vendor/autoload.php';
                
                $html = '<h1>Lista de Puestos</h1>';
                $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
                $html .= '<tr style="background-color: #f0f0f0">
                    <th>ID</th>
                    <th>Nombre del Puesto</th>
                </tr>';
                
                foreach ($puestos as $puesto) {
                    $html .= '<tr>';
                    $html .= '<td>' . $puesto['id'] . '</td>';
                    $html .= '<td>' . $puesto['nombredelpuesto'] . '</td>';
                    $html .= '</tr>';
                }
                
                $html .= '</table>';
                
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('letter', 'portrait');
                $dompdf->render();
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="lista_puestos_' . date('Ymd_His') . '.pdf"');
                echo $dompdf->output();
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'data' => $puestos
                ]);
                exit;
            }
            break;
            
        default:
            throw new Exception('Tipo de descarga no reconocido');
    }
} catch (Exception $e) {
    if (strpos($format, 'json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<div style="color:red; font-family:Arial,sans-serif; padding:20px;">Error: ' . $e->getMessage() . '</div>';
    }
}
exit;

function generateEmployeeCardHTML($employee, $fullName) {
    $fechaIngreso = new DateTime($employee['fechaingreso']);
    $fechaActual = new DateTime();
    $intervalo = $fechaIngreso->diff($fechaActual);
    $antiguedad = $intervalo->y . ' años, ' . $intervalo->m . ' meses';
    
    $fotoUrl = !empty($employee['foto']) ? $employee['foto'] : 'assets/images/no-photo.png';
    
    return '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Tarjeta de Empleado</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background-color: #f5f8fa;
                color: #333;
            }
            .card {
                background-color: white;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                overflow: hidden;
                max-width: 600px;
                margin: 0 auto;
            }
            .card-header {
                background-color: #0d6efd;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .card-body {
                padding: 20px;
            }
            .employee-photo {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                margin: -60px auto 10px;
                display: block;
                background-color: #f0f0f0;
            }
            .employee-name {
                text-align: center;
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .employee-position {
                text-align: center;
                color: #666;
                margin-bottom: 20px;
            }
            .info-section {
                margin-bottom: 20px;
                border-bottom: 1px solid #eee;
                padding-bottom: 20px;
            }
            .info-section:last-child {
                border-bottom: none;
                margin-bottom: 0;
            }
            .info-label {
                font-weight: bold;
                color: #555;
                margin-bottom: 5px;
            }
            .info-value {
                margin-bottom: 10px;
            }
            .footer {
                text-align: center;
                padding: 15px;
                background-color: #f9f9f9;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class="card">
            <div class="card-header">
                <h1>Tarjeta de Empleado</h1>
                <h3>Universidad Domingo Savio</h3>
            </div>
            <div class="card-body">
                <img class="employee-photo" src="' . $fotoUrl . '" alt="Foto de empleado">
                <div class="employee-name">' . $fullName . '</div>
                <div class="employee-position">' . $employee['nombredelpuesto'] . '</div>
                
                <div class="info-section">
                    <div class="info-label">ID de Empleado:</div>
                    <div class="info-value">' . $employee['id'] . '</div>
                    <div class="info-label">Fecha de Ingreso:</div>
                    <div class="info-value">' . $employee['fechaingreso'] . '</div>
                    <div class="info-label">Antigüedad:</div>
                    <div class="info-value">' . $antiguedad . '</div>
                </div>
                
                <div class="info-section">
                    <div class="info-label">Información Personal:</div>
                    <div class="info-value">
                        <strong>Primer Nombre:</strong> ' . $employee['primernombre'] . '<br>
                        <strong>Segundo Nombre:</strong> ' . $employee['segundonombre'] . '<br>
                        <strong>Primer Apellido:</strong> ' . $employee['primerapellido'] . '<br>
                        <strong>Segundo Apellido:</strong> ' . $employee['segundoapellido'] . '
                    </div>
                </div>
            </div>
            <div class="footer">
                Documento generado el ' . date('d/m/Y H:i:s') . ' | Sistema de Gestión de Empleados
            </div>
        </div>
    </body>
    </html>';
}
