<?php
session_start();
header('Content-Type: application/json');

include("../bd.php");

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pageSize = isset($_GET['pageSize']) ? (int)$_GET['pageSize'] : 10;
$sortField = isset($_GET['sortField']) ? $_GET['sortField'] : 'id';
$sortDirection = isset($_GET['sortDirection']) && strtolower($_GET['sortDirection']) === 'desc' ? 'DESC' : 'ASC';
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

$offset = ($page - 1) * $pageSize;
$response = [];

try {
    switch ($endpoint) {
        case 'employees':
            $columns = [
                ['field' => 'id', 'title' => 'ID', 'sortable' => true, 'width' => '60px'],
                ['field' => 'nombre', 'title' => 'Nombre', 'sortable' => true],
                ['field' => 'puesto', 'title' => 'Puesto', 'sortable' => true],
                ['field' => 'fechaingreso', 'title' => 'Fecha Ingreso', 'sortable' => true, 'width' => '150px'],
                ['field' => 'acciones', 'title' => 'Acciones', 'sortable' => false, 'width' => '120px']
            ];
            
            $whereClause = "1=1";
            $params = [];
            
            if (isset($_GET['filter'])) {
                $filters = $_GET['filter'];
                foreach ($filters as $field => $value) {
                    if (!empty($value)) {
                        switch ($field) {
                            case 'nombre':
                                $whereClause .= " AND (primernombre LIKE :nombre OR primerapellido LIKE :nombre)";
                                $params[':nombre'] = "%$value%";
                                break;
                            case 'puesto':
                                $whereClause .= " AND p.nombredelpuesto LIKE :puesto";
                                $params[':puesto'] = "%$value%";
                                break;
                            case 'fechaingreso':
                                $whereClause .= " AND fechaingreso = :fechaingreso";
                                $params[':fechaingreso'] = $value;
                                break;
                        }
                    }
                }
            }
            
            $sqlSort = "e.id";
            if ($sortField == 'nombre') {
                $sqlSort = "primernombre $sortDirection, primerapellido";
            } elseif ($sortField == 'puesto') {
                $sqlSort = "p.nombredelpuesto";
            } elseif ($sortField == 'fechaingreso') {
                $sqlSort = "fechaingreso";
            }
            
            $countStmt = $conexion->prepare("
                SELECT COUNT(*) 
                FROM empleados e 
                LEFT JOIN tbl_puesto p ON e.id_puesto = p.id 
                WHERE $whereClause
            ");
            
            foreach ($params as $param => $value) {
                $countStmt->bindValue($param, $value);
            }
            
            $countStmt->execute();
            $totalRows = $countStmt->fetchColumn();
            
            $stmt = $conexion->prepare("
                SELECT 
                    e.id,
                    CONCAT(e.primernombre, ' ', e.primerapellido) as nombre,
                    p.nombredelpuesto as puesto,
                    e.fechaingreso,
                    'acciones' as acciones
                FROM 
                    empleados e
                LEFT JOIN 
                    tbl_puesto p ON e.id_puesto = p.id
                WHERE 
                    $whereClause
                ORDER BY 
                    $sqlSort $sortDirection
                LIMIT :offset, :pageSize
            ");
            
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            $rows = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['acciones'] = '<div class="grid-actions">
                    <a href="../Empleados/editar.php?txtID='.$row['id'].'" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fas fa-edit"></i></a>
                    <button onclick="eliminarRegistro('.$row['id'].')" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>';
                $rows[] = $row;
            }
            
            $response = [
                'columns' => $columns,
                'rows' => $rows,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalRows' => $totalRows,
                'totalPages' => ceil($totalRows / $pageSize)
            ];
            break;
            
        case 'positions':
            $columns = [
                ['field' => 'id', 'title' => 'ID', 'sortable' => true, 'width' => '60px'],
                ['field' => 'nombredelpuesto', 'title' => 'Nombre del Puesto', 'sortable' => true],
                ['field' => 'acciones', 'title' => 'Acciones', 'sortable' => false, 'width' => '120px']
            ];
            
            $whereClause = "1=1";
            $params = [];
            
            if (isset($_GET['filter'])) {
                $filters = $_GET['filter'];
                foreach ($filters as $field => $value) {
                    if (!empty($value)) {
                        if ($field == 'nombredelpuesto') {
                            $whereClause .= " AND nombredelpuesto LIKE :nombredelpuesto";
                            $params[':nombredelpuesto'] = "%$value%";
                        }
                    }
                }
            }
            
            $sqlSort = $sortField == 'nombredelpuesto' ? 'nombredelpuesto' : 'id';
            
            $countStmt = $conexion->prepare("SELECT COUNT(*) FROM tbl_puesto WHERE $whereClause");
            
            foreach ($params as $param => $value) {
                $countStmt->bindValue($param, $value);
            }
            
            $countStmt->execute();
            $totalRows = $countStmt->fetchColumn();
            
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    nombredelpuesto,
                    'acciones' as acciones
                FROM 
                    tbl_puesto
                WHERE 
                    $whereClause
                ORDER BY 
                    $sqlSort $sortDirection
                LIMIT :offset, :pageSize
            ");
            
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            $rows = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['acciones'] = '<div class="grid-actions">
                    <a href="../Puestos/editar.php?txtID='.$row['id'].'" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fas fa-edit"></i></a>
                    <button onclick="eliminarRegistro('.$row['id'].')" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>';
                $rows[] = $row;
            }
            
            $response = [
                'columns' => $columns,
                'rows' => $rows,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalRows' => $totalRows,
                'totalPages' => ceil($totalRows / $pageSize)
            ];
            break;
            
        case 'users':
            $columns = [
                ['field' => 'id', 'title' => 'ID', 'sortable' => true, 'width' => '60px'],
                ['field' => 'usuario', 'title' => 'Usuario', 'sortable' => true],
                ['field' => 'correo', 'title' => 'Correo', 'sortable' => true],
                ['field' => 'acciones', 'title' => 'Acciones', 'sortable' => false, 'width' => '120px']
            ];
            
            $whereClause = "1=1";
            $params = [];
            
            if (isset($_GET['filter'])) {
                $filters = $_GET['filter'];
                foreach ($filters as $field => $value) {
                    if (!empty($value)) {
                        if ($field == 'usuario') {
                            $whereClause .= " AND usuario LIKE :usuario";
                            $params[':usuario'] = "%$value%";
                        } elseif ($field == 'correo') {
                            $whereClause .= " AND correo LIKE :correo";
                            $params[':correo'] = "%$value%";
                        }
                    }
                }
            }
            
            $sqlSort = $sortField;
            if (!in_array($sortField, ['id', 'usuario', 'correo'])) {
                $sqlSort = 'id';
            }
            
            $countStmt = $conexion->prepare("SELECT COUNT(*) FROM tbl_usuarios WHERE $whereClause");
            
            foreach ($params as $param => $value) {
                $countStmt->bindValue($param, $value);
            }
            
            $countStmt->execute();
            $totalRows = $countStmt->fetchColumn();
            
            $stmt = $conexion->prepare("
                SELECT 
                    id,
                    usuario,
                    correo,
                    'acciones' as acciones
                FROM 
                    tbl_usuarios
                WHERE 
                    $whereClause
                ORDER BY 
                    $sqlSort $sortDirection
                LIMIT :offset, :pageSize
            ");
            
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
            
            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
            
            $stmt->execute();
            $rows = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['acciones'] = '<div class="grid-actions">
                    <a href="../Usuarios/editar.php?txtID='.$row['id'].'" class="btn btn-sm btn-outline-primary me-1" title="Editar"><i class="fas fa-edit"></i></a>
                    <button onclick="eliminarRegistro('.$row['id'].')" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>';
                $rows[] = $row;
            }
            
            $response = [
                'columns' => $columns,
                'rows' => $rows,
                'page' => $page,
                'pageSize' => $pageSize,
                'totalRows' => $totalRows,
                'totalPages' => ceil($totalRows / $pageSize)
            ];
            break;
            
        default:
            $columns = [
                ['field' => 'id', 'title' => 'ID', 'sortable' => true],
                ['field' => 'nombre', 'title' => 'Nombre', 'sortable' => true],
                ['field' => 'descripcion', 'title' => 'Descripción', 'sortable' => false]
            ];
            
            $rows = [
                ['id' => 1, 'nombre' => 'Ejemplo 1', 'descripcion' => 'Descripción de ejemplo 1'],
                ['id' => 2, 'nombre' => 'Ejemplo 2', 'descripcion' => 'Descripción de ejemplo 2'],
                ['id' => 3, 'nombre' => 'Ejemplo 3', 'descripcion' => 'Descripción de ejemplo 3'],
            ];
            
            $response = [
                'columns' => $columns,
                'rows' => $rows,
                'page' => 1,
                'pageSize' => 10,
                'totalRows' => 3,
                'totalPages' => 1
            ];
    }
} catch (Exception $e) {
    $response = [
        'error' => true,
        'message' => 'Error al obtener los datos: ' . $e->getMessage()
    ];
}

echo json_encode($response);
exit;
