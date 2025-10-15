<?php
require_once 'database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de moto no especificado');
    exit();
}

$id = (int)$_GET['id'];

// Obtener datos actuales de la moto
$sql = "SELECT * FROM motos WHERE id = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);

if (!$stmt || sqlsrv_has_rows($stmt) === false) {
    header('Location: index.php?error=Moto no encontrada');
    exit();
}

$moto = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$valores = $moto;
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y limpiar datos
    $marca = limpiar($_POST['marca']);
    $modelo = limpiar($_POST['modelo']);
    $año = (int)$_POST['año'];
    $cilindrada = (int)$_POST['cilindrada'];
    $color = limpiar($_POST['color']);
    $precio = (float)$_POST['precio'];
    $tipo_moto = limpiar($_POST['tipo_moto']);
    
    // Actualizar valores
    $valores = compact('marca', 'modelo', 'año', 'cilindrada', 'color', 'precio', 'tipo_moto');
    $valores['id'] = $id;
    
    // Validaciones
    if (empty($marca)) $errores[] = "La marca es obligatoria";
    if (empty($modelo)) $errores[] = "El modelo es obligatorio";
    if (empty($año) || $año < 1900 || $año > 2030) $errores[] = "El año debe ser válido";
    if (empty($cilindrada) || $cilindrada < 50) $errores[] = "La cilindrada debe ser válida";
    if (empty($precio) || $precio < 0) $errores[] = "El precio debe ser válido";
    if (empty($tipo_moto)) $errores[] = "El tipo de moto es obligatorio";
    
    // Procesar imagen si se subió una nueva
    $imagen_nombre = $moto['imagen']; // Mantener la imagen actual por defecto
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resultado_imagen = subirImagen($_FILES['imagen']);
        if (isset($resultado_imagen['error'])) {
            $errores[] = $resultado_imagen['error'];
        } elseif ($resultado_imagen['success']) {
            // Eliminar imagen anterior si existe
            if (!empty($moto['imagen']) && file_exists('uploads/' . $moto['imagen'])) {
                unlink('uploads/' . $moto['imagen']);
            }
            $imagen_nombre = $resultado_imagen['success'];
        }
    }
    
    // Si no hay errores, actualizar en la base de datos
    if (empty($errores)) {
        $sql = "UPDATE motos SET marca=?, modelo=?, año=?, cilindrada=?, color=?, precio=?, tipo_moto=?, imagen=? WHERE id=?";
        $params = array($marca, $modelo, $año, $cilindrada, $color, $precio, $tipo_moto, $imagen_nombre, $id);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            header('Location: index.php?success=Moto actualizada exitosamente');
            exit();
        } else {
            $errores[] = "Error al actualizar la moto: " . print_r(sqlsrv_errors(), true);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar <?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></title>
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
                <h1><i class="fas fa-edit"></i> Editar Moto: <?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></h1>
            </div>
            <div class="card-body">
                <?php if (!empty($errores)): ?>
                    <div class="alert alert-danger">
                        <h3>Errores:</h3>
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="editar.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="marca" class="form-label">Marca *</label>
                        <input type="text" id="marca" name="marca" class="form-control" 
                               value="<?php echo htmlspecialchars($valores['marca']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" id="modelo" name="modelo" class="form-control" 
                               value="<?php echo htmlspecialchars($valores['modelo']); ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="año" class="form-label">Año *</label>
                            <input type="number" id="año" name="año" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['año']); ?>" 
                                   min="1900" max="2030" required>
                        </div>

                        <div class="form-group">
                            <label for="cilindrada" class="form-label">Cilindrada (cc) *</label>
                            <input type="number" id="cilindrada" name="cilindrada" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['cilindrada']); ?>" 
                                   min="50" max="2500" required>
                        </div>

                        <div class="form-group">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" id="color" name="color" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['color']); ?>">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="precio" class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" id="precio" name="precio" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['precio']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo_moto" class="form-label">Tipo de Moto *</label>
                            <select id="tipo_moto" name="tipo_moto" class="form-control" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="Deportiva" <?php echo $valores['tipo_moto'] == 'Deportiva' ? 'selected' : ''; ?>>Deportiva</option>
                                <option value="Naked" <?php echo $valores['tipo_moto'] == 'Naked' ? 'selected' : ''; ?>>Naked</option>
                                <option value="Custom" <?php echo $valores['tipo_moto'] == 'Custom' ? 'selected' : ''; ?>>Custom</option>
                                <option value="Scooter" <?php echo $valores['tipo_moto'] == 'Scooter' ? 'selected' : ''; ?>>Scooter</option>
                                <option value="Enduro" <?php echo $valores['tipo_moto'] == 'Enduro' ? 'selected' : ''; ?>>Enduro</option>
                                <option value="Adventure" <?php echo $valores['tipo_moto'] == 'Adventure' ? 'selected' : ''; ?>>Adventure</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="imagen" class="form-label">Imagen de la Moto</label>
                        
                        <?php if (!empty($moto['imagen'])): ?>
                            <div style="margin-bottom: 1rem;">
                                <p><strong>Imagen actual:</strong></p>
                                <img src="uploads/<?php echo htmlspecialchars($moto['imagen']); ?>" 
                                     alt="Imagen actual" 
                                     style="max-height: 150px; border-radius: 5px; border: 1px solid #ddd;">
                                <br>
                                <small>
                                    <a href="uploads/<?php echo htmlspecialchars($moto['imagen']); ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i> Ver imagen actual
                                    </a>
                                </small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> No hay imagen actual
                            </div>
                        <?php endif; ?>
                        
                        <input type="file" id="imagen" name="imagen" class="form-control" 
                               accept="image/jpeg, image/png, image/gif, image/webp">
                        <small class="text-muted">Dejar vacío para mantener la imagen actual. Formatos: JPG, PNG, GIF, WEBP. Máximo 2MB</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="ver.php?id=<?php echo $id; ?>" class="btn btn-info" style="color: white;">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Actualizar Moto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Motos</p>
        </div>
    </footer>
</body>
</html>