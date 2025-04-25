<?php
session_start();
header('Content-Type: application/json');

include("../bd.php");

$field = isset($_GET['field']) ? $_GET['field'] : '';
$query = isset($_GET['query']) ? $_GET['query'] : '';

if (empty($field) || empty($query)) {
    echo json_encode([]);
    exit;
}

$results = [];

try {
    switch ($field) {
        case 'employees':
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    CONCAT(primernombre, ' ', primerapellido) as text
                FROM 
                    empleados
                WHERE 
                    primernombre LIKE :query OR 
                    primerapellido LIKE :query OR
                    CONCAT(primernombre, ' ', primerapellido) LIKE :query
                LIMIT 10
            ");
            $searchTerm = "%$query%";
            $stmt->bindParam(':query', $searchTerm);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'value' => $row['id'],
                    'text' => $row['text']
                ];
            }
            break;
            
        case 'positions':
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    nombredelpuesto as text
                FROM 
                    tbl_puesto
                WHERE 
                    nombredelpuesto LIKE :query
                LIMIT 10
            ");
            $searchTerm = "%$query%";
            $stmt->bindParam(':query', $searchTerm);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'value' => $row['id'],
                    'text' => $row['text']
                ];
            }
            break;
            
        case 'users':
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    usuario as text
                FROM 
                    tbl_usuarios
                WHERE 
                    usuario LIKE :query OR
                    correo LIKE :query
                LIMIT 10
            ");
            $searchTerm = "%$query%";
            $stmt->bindParam(':query', $searchTerm);
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = [
                    'value' => $row['id'],
                    'text' => $row['text']
                ];
            }
            break;
            
        default:
            $results = [
                ['value' => '1', 'text' => 'Result 1 for ' . $query],
                ['value' => '2', 'text' => 'Another result for ' . $query],
                ['value' => '3', 'text' => 'Sample item ' . $query]
            ];
    }
} catch (Exception $e) {
    $results = [];
}

echo json_encode($results);
exit;
