<?php
require_once 'database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de moto no especificado');
    exit();
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM motos WHERE id = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt || sqlsrv_has_rows($stmt) === false) {
    header('Location: index.php?error=Moto no encontrada');
    exit();
}

$moto = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-motorcycle"></i> Sistema de Motos
                </a>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="card">
            <div class="card-header">
                <h1><i class="fas fa-eye"></i> Detalles de la Moto</h1>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- Imagen -->
                    <div>
                        <?php if (!empty($moto['imagen'])): ?>
                            <div style="border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                                <img src="uploads/<?php echo htmlspecialchars($moto['imagen']); ?>" 
                                     alt="<?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?>" 
                                     style="width: 100%; height: 300px; object-fit: cover;"
                                     onerror="this.style.display='none'; document.getElementById('placeholder').style.display='flex';">
                            </div>
                            <div id="placeholder" class="moto-placeholder" style="display: none; height: 300px;">
                                <i class="fas fa-motorcycle fa-4x"></i>
                            </div>
                        <?php else: ?>
                            <div class="moto-placeholder" style="height: 300px;">
                                <div class="text-center">
                                    <i class="fas fa-motorcycle fa-4x mb-3"></i>
                                    <br>
                                    <span>Sin imagen disponible</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Información -->
                    <div>
                        <h2 style="color: #333; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></h2>
                        
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px;">
                            <h3 style="margin-bottom: 1rem; color: #667eea;">Información General</h3>
                            
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Año:</td>
                                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($moto['año']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Cilindrada:</td>
                                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($moto['cilindrada']); ?> cc</td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Color:</td>
                                    <td style="padding: 8px 0;"><?php echo htmlspecialchars($moto['color']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Tipo:</td>
                                    <td style="padding: 8px 0;">
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($moto['tipo_moto']); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold;">Precio:</td>
                                    <td style="padding: 8px 0; font-size: 1.2rem; color: #28a745; font-weight: bold;">
                                        $<?php echo number_format($moto['precio'], 2); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-top: 1rem;">
                            <h3 style="margin-bottom: 1rem; color: #667eea;">Información del Sistema</h3>
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Registrado:</td>
                                    <td style="padding: 8px 0;">
                                        <?php 
                                        $fecha = $moto['fecha_creacion'];
                                        if ($fecha instanceof DateTime) {
                                            echo $fecha->format('d/m/Y H:i');
                                        } else {
                                            echo date('d/m/Y H:i', strtotime($fecha));
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Botones de acción -->
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <a href="editar.php?id=<?php echo $moto['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar Moto
                            </a>
                            <button type="button" class="btn btn-danger" 
                                    onclick="confirmarEliminacion(<?php echo $moto['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar Moto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Motos</p>
        </div>
    </footer>

    <script>
        function confirmarEliminacion(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta moto? Esta acción no se puede deshacer.')) {
                window.location.href = 'eliminar.php?id=' + id;
            }
        }
    </script>
</body>
</html>