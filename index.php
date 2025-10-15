<?php
require_once 'database.php';

// Obtener todas las motos
$sql = "SELECT * FROM motos ORDER BY fecha_creacion DESC";
$motos = ejecutarConsulta($sql);

if ($motos === false) {
    $motos = [];
    $error = sqlsrv_errors();
    // Solo para debugging - quitar en producción
    echo "<!-- Error: " . print_r($error, true) . " -->";
}

// Manejar búsqueda
$termino = '';
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $termino = limpiar($_GET['q']);
    $sql = "SELECT * FROM motos WHERE 
            marca LIKE ? OR 
            modelo LIKE ? OR 
            color LIKE ? OR 
            tipo_moto LIKE ?
            ORDER BY fecha_creacion DESC";
    
    $searchTerm = "%$termino%";
    $params = array($searchTerm, $searchTerm, $searchTerm, $searchTerm);
    $motos = ejecutarConsulta($sql, $params);
    
    if ($motos === false) {
        $motos = [];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Motos</title>
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
                <a href="crear.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar Moto
                </a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="d-flex justify-content-between mb-3">
            <h1>
                <i class="fas fa-list"></i>
                <?php echo $termino ? 'Resultados de: "' . htmlspecialchars($termino) . '"' : 'Lista de Motos'; ?>
            </h1>
            <span class="badge badge-primary" style="font-size: 1rem;">
                Total: <?php echo count($motos); ?> motos
            </span>
        </div>

        <!-- Buscador -->
        <div class="card mb-3">
            <div class="card-body">
                <form action="index.php" method="get" class="d-flex" style="gap: 10px;">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por marca, modelo, color..." 
                           value="<?php echo htmlspecialchars($termino); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <?php if ($termino): ?>
                        <a href="index.php" class="btn btn-secondary">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Mensajes -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Lista de Motos -->
        <?php if (empty($motos)): ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                    <h3>No se encontraron motos</h3>
                    <p class="text-muted"><?php echo $termino ? 'Intenta con otros términos de búsqueda.' : 'Comienza agregando tu primera moto.'; ?></p>
                    <?php if (!$termino): ?>
                        <a href="crear.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Primera Moto
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($motos as $moto): ?>
                    <div class="moto-card">
                        <?php if (!empty($moto['imagen'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($moto['imagen']); ?>" 
                                 alt="<?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?>" 
                                 class="moto-img"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="moto-placeholder" style="display: none;">
                                <i class="fas fa-motorcycle fa-3x"></i>
                            </div>
                        <?php else: ?>
                            <div class="moto-placeholder">
                                <div class="text-center">
                                    <i class="fas fa-motorcycle fa-3x mb-2"></i>
                                    <br>
                                    <small>Sin imagen</small>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="moto-info">
                            <h3 class="card-title"><?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></h3>
                            <p><strong>Año:</strong> <?php echo htmlspecialchars($moto['año']); ?></p>
                            <p><strong>Cilindrada:</strong> <?php echo htmlspecialchars($moto['cilindrada']); ?> cc</p>
                            <p><strong>Color:</strong> <?php echo htmlspecialchars($moto['color']); ?></p>
                            <p><strong>Tipo:</strong> <span class="badge badge-primary"><?php echo htmlspecialchars($moto['tipo_moto']); ?></span></p>
                            <p><strong>Precio:</strong> $<?php echo number_format($moto['precio'], 2); ?></p>
                        </div>
                        
                        <div class="moto-actions">
                            <a href="ver.php?id=<?php echo $moto['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="editar.php?id=<?php echo $moto['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="confirmarEliminacion(<?php echo $moto['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Motos. Todos los derechos reservados.</p>
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