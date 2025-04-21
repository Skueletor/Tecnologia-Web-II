<?php
include("../../bd.php");

require_once __DIR__ . '/../../vendor/dompdf/vendor/autoload.php';
use Dompdf\Dompdf;

// Verificar si se proporcionó un ID
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    
    // Obtener datos del empleado
    $sentencia = $conexion->prepare("
        SELECT e.*, 
            (SELECT p.nombredelpuesto FROM tbl_puesto p WHERE p.id = e.id_puesto) AS puesto 
        FROM empleados e 
        WHERE e.id = :id
    ");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $empleado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if (!$empleado) {
        header("Location: index.php?mensaje=Empleado no encontrado");
        exit();
    }
    
    // Calcular años de servicio
    $fechaIngreso = new DateTime($empleado['fechaingreso']);
    $fechaActual = new DateTime();
    $intervalo = $fechaIngreso->diff($fechaActual);
    $anosServicio = $intervalo->y;
    $mesesServicio = $intervalo->m;
    
    // Nombres y apellidos formateados
    $nombreCompleto = trim($empleado['primernombre'] . ' ' . $empleado['segundonombre'] . ' ' . 
                      $empleado['primerapellido'] . ' ' . $empleado['segundoapellido']);
    
    // Formato fecha actual
    setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');
    $meses = array("enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre");
    $fechaActual = date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');
    
    // Generar PDF si se solicita
    if (isset($_GET['format']) && $_GET['format'] === 'pdf') {
        // Inicializar dompdf
        $dompdf = new Dompdf();
        
        // Obtener imagen de perfil en base64 si existe
        $fotoBase64 = '';
        if (!empty($empleado['foto']) && file_exists("./" . $empleado['foto'])) {
            $imagenTipo = pathinfo($empleado['foto'], PATHINFO_EXTENSION);
            $imagenData = file_get_contents("./" . $empleado['foto']);
            $fotoBase64 = 'data:image/' . $imagenTipo . ';base64,' . base64_encode($imagenData);
        }
        
        // Contenido HTML del PDF
        $contenidoHTML = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Carta de Recomendación - ' . $nombreCompleto . '</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px 40px;
                }
                .header {
                    text-align: right;
                    margin-bottom: 30px;
                }
                .title {
                    text-align: center;
                    font-weight: bold;
                    font-size: 18px;
                    margin-bottom: 5px;
                }
                .subtitle {
                    text-align: center;
                    margin-bottom: 30px;
                    font-size: 16px;
                }
                .content {
                    text-align: justify;
                    margin-bottom: 40px;
                }
                .signature {
                    text-align: center;
                    margin-top: 60px;
                    width: 200px;
                    border-top: 1px solid #000;
                    padding-top: 10px;
                    margin-left: auto;
                    margin-right: auto;
                }
                .photo {
                    position: absolute;
                    top: 20px;
                    left: 40px;
                    width: 80px;
                    height: 100px;
                    border: 1px solid #ddd;
                }
                .logo {
                    position: absolute;
                    top: 20px;
                    right: 40px;
                    width: 120px;
                }
                .footer {
                    position: fixed;
                    bottom: 20px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                }
                p {
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            ' . (!empty($fotoBase64) ? '<img class="photo" src="' . $fotoBase64 . '" alt="Foto">' : '') . '
            
            <div class="header">
                <p>Santa Cruz de la Sierra, ' . $fechaActual . '</p>
            </div>
            
            <div class="title">CARTA DE RECOMENDACIÓN</div>
            <div class="subtitle">A QUIEN CORRESPONDA:</div>
            
            <div class="content">
                <p>
                    Por medio de la presente, me permito recomendar ampliamente a <strong>' . $nombreCompleto . '</strong>, 
                    quien ha laborado en nuestra institución desempeñando el cargo de <strong>' . $empleado['puesto'] . '</strong> 
                    desde el <strong>' . date("d/m/Y", strtotime($empleado['fechaingreso'])) . '</strong> hasta la fecha actual, 
                    acumulando ' . $anosServicio . ' año(s) y ' . $mesesServicio . ' mes(es) de servicio.
                </p>
                
                <p>
                    Durante este tiempo ha demostrado ser una persona responsable, honesta, eficiente y comprometida con sus labores diarias.
                    Se ha caracterizado por su puntualidad, iniciativa y capacidad para trabajar en equipo, cualidades que le han permitido
                    destacarse en su desempeño profesional.
                </p>
                
                <p>
                    Asimismo, ha mantenido excelentes relaciones interpersonales con sus compañeros de trabajo y superiores,
                    mostrando siempre una actitud positiva y colaboradora ante los retos y responsabilidades asignadas.
                </p>
                
                <p>
                    Por lo anterior, no tengo inconveniente alguno en recomendarlo ampliamente para cualquier posición donde
                    se requieran los servicios de un profesional con sus características y competencias.
                </p>
                
                <p>
                    Sin más por el momento, quedo a sus órdenes para cualquier información adicional relacionada con esta recomendación.
                </p>
            </div>
            
            <p>Atentamente,</p>
            
            <div class="signature">
                <p><strong>Ing. Pablo Alvaro Moscoso</strong></p>
                <p>Director General</p>
                <p>Universidad Privada Domingo Savio</p>
            </div>
            
            <div class="footer">
                Universidad Privada Domingo Savio | Sistema de Gestión de Empleados | Documento generado el ' . date('d/m/Y H:i:s') . '
            </div>
        </body>
        </html>';
        
        // Cargar HTML en dompdf
        $dompdf->loadHtml($contenidoHTML);
        
        // Configurar opciones
        $dompdf->setPaper('letter', 'portrait');
        
        // Renderizar PDF
        $dompdf->render();
        
        // Descargar el PDF
        $dompdf->stream('carta_recomendacion_' . $empleado['primerapellido'] . '.pdf', array('Attachment' => 0));
        exit();
    }
} else {
    header("Location: index.php?mensaje=ID no proporcionado");
    exit();
}
?>

<?php include("../../Templates/header.php"); ?>

<!-- Sección superior con botones y título -->
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <div class="section-icon">
                <i class="fas fa-file-signature"></i>
            </div>
            <div>
                <h2 class="section-header mb-0">Carta de Recomendación</h2>
                <p class="text-muted">Vista previa del documento para <?php echo $nombreCompleto; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="carta_recomendacion.php?txtID=<?php echo $txtID; ?>&format=pdf" class="btn btn-danger me-2" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>Descargar PDF
        </a>
        <button class="btn btn-primary me-2" id="btnImprimir">
            <i class="fas fa-print me-2"></i>Imprimir
        </button>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<!-- Vista previa de la carta con diseño moderno -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Vista previa del documento</h5>
    </div>
    <div class="card-body">
        <div class="carta-contenido p-4 bg-white shadow-sm rounded" id="cartaImprimir">
            <!-- Encabezado con logo y datos -->
            <div class="row mb-5">
                <div class="col-3">
                    <?php if (!empty($empleado['foto'])): ?>
                    <div class="foto-empleado">
                        <img src="<?php echo $empleado['foto']; ?>" alt="Foto de <?php echo $nombreCompleto; ?>" class="img-thumbnail">
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-6 text-center">
                    <img src="<?php echo $url_base; ?>assets/images/logo-upds.png" alt="Logo Universidad" class="img-fluid mb-2" style="max-height: 60px;">
                    <h6 class="text-muted">UNIVERSIDAD PRIVADA DOMINGO SAVIO</h6>
                </div>
                <div class="col-3 text-end">
                    <p class="mb-0"><?php echo $fechaActual; ?></p>
                    <small class="text-muted">Ref: UDPS-RH-<?php echo date('Y'); ?>-<?php echo str_pad($txtID, 3, '0', STR_PAD_LEFT); ?></small>
                </div>
            </div>

            <!-- Título del documento -->
            <div class="mb-5 text-center">
                <h3 class="fw-bold">CARTA DE RECOMENDACIÓN</h3>
                <h5 class="text-muted">A QUIEN CORRESPONDA:</h5>
            </div>
            
            <!-- Contenido principal -->
            <div class="contenido-carta">
                <p class="text-justify">
                    Por medio de la presente, me permito recomendar ampliamente a <strong><?php echo $nombreCompleto; ?></strong>, 
                    quien ha laborado en nuestra institución desempeñando el cargo de <strong><?php echo $empleado['puesto']; ?></strong> 
                    desde el <strong><?php echo date("d/m/Y", strtotime($empleado['fechaingreso'])); ?></strong> hasta la fecha actual, 
                    acumulando <?php echo $anosServicio; ?> año(s) y <?php echo $mesesServicio; ?> mes(es) de servicio.
                </p>
                
                <p class="text-justify">
                    Durante este tiempo ha demostrado ser una persona responsable, honesta, eficiente y comprometida con sus labores diarias.
                    Se ha caracterizado por su puntualidad, iniciativa y capacidad para trabajar en equipo, cualidades que le han permitido
                    destacarse en su desempeño profesional.
                </p>
                
                <p class="text-justify">
                    Asimismo, ha mantenido excelentes relaciones interpersonales con sus compañeros de trabajo y superiores,
                    mostrando siempre una actitud positiva y colaboradora ante los retos y responsabilidades asignadas.
                </p>
                
                <p class="text-justify">
                    Por lo anterior, no tengo inconveniente alguno en recomendarlo ampliamente para cualquier posición donde
                    se requieran los servicios de un profesional con sus características y competencias.
                </p>
                
                <p class="text-justify">
                    Sin más por el momento, quedo a sus órdenes para cualquier información adicional relacionada con esta recomendación.
                </p>
            </div>
            
            <!-- Firma -->
            <div class="mt-5">
                <p>Atentamente,</p>
            </div>
            
            <div class="firma-contenedor mt-5">
                <div class="firma">
                    <p><strong>Ing. Pablo Alvaro Moscoso</strong></p>
                    <p>Director General</p>
                    <p>Universidad Privada Domingo Savio</p>
                </div>
            </div>
            
            <!-- Pie de página -->
            <div class="mt-5 pt-3 text-center border-top">
                <small class="text-muted">Este documento es válido sin firma y sello por haber sido generado digitalmente.</small>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos para la carta */
    .carta-contenido {
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        line-height: 1.6;
        position: relative;
        background-color: white;
    }
    
    .text-justify {
        text-align: justify;
        margin-bottom: 20px;
    }
    
    .firma-contenedor {
        display: flex;
        justify-content: center;
    }
    
    .firma {
        border-top: 1px solid #000;
        padding-top: 10px;
        text-align: center;
        width: 250px;
    }
    
    .firma p {
        margin: 0;
        line-height: 1.4;
    }
    
    .foto-empleado img {
        max-width: 100px;
        height: auto;
        object-fit: cover;
        border-radius: 5px;
    }
    
    /* Estilos para impresión */
    @media print {
        body * {
            visibility: hidden;
        }
        
        .carta-contenido, .carta-contenido * {
            visibility: visible;
        }
        
        .carta-contenido {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            padding: 40px;
        }
        
        .card {
            box-shadow: none !important;
        }
    }
</style>

<script>
    document.getElementById('btnImprimir').addEventListener('click', function() {
        window.print();
    });
</script>

<?php include("../../Templates/footer.php"); ?>
