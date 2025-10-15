<?php
require_once 'database.php';

$errores = [];
$valores = [
    'marca' => '', 'modelo' => '', 'año' => date('Y'), 
    'cilindrada' => '', 'color' => '', 'precio' => '', 'tipo_moto' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger y limpiar datos
    $marca = limpiar($_POST['marca']);
    $modelo = limpiar($_POST['modelo']);
    $año = (int)$_POST['año'];
    $cilindrada = (int)$_POST['cilindrada'];
    $color = limpiar($_POST['color']);
    $precio = (float)$_POST['precio'];
    $tipo_moto = limpiar($_POST['tipo_moto']);
    
    // Guardar valores para repoblar el formulario
    $valores = compact('marca', 'modelo', 'año', 'cilindrada', 'color', 'precio', 'tipo_moto');
    
    // Validaciones
    if (empty($marca)) $errores[] = "La marca es obligatoria";
    if (empty($modelo)) $errores[] = "El modelo es obligatorio";
    if (empty($año) || $año < 1900 || $año > 2030) $errores[] = "El año debe ser válido";
    if (empty($cilindrada) || $cilindrada < 50) $errores[] = "La cilindrada debe ser válida";
    if (empty($precio) || $precio < 0) $errores[] = "El precio debe ser válido";
    if (empty($tipo_moto)) $errores[] = "El tipo de moto es obligatorio";
    
    // Procesar imagen
    $imagen_nombre = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resultado_imagen = subirImagen($_FILES['imagen']);
        if (isset($resultado_imagen['error'])) {
            $errores[] = $resultado_imagen['error'];
        } else {
            $imagen_nombre = $resultado_imagen['success'];
        }
    }
    
    // Si no hay errores, insertar en la base de datos
    if (empty($errores)) {
        $sql = "INSERT INTO motos (marca, modelo, año, cilindrada, color, precio, tipo_moto, imagen) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Preparar parámetros correctamente
        $params = array(
            $marca,
            $modelo,
            $año,
            $cilindrada,
            $color,
            $precio,
            $tipo_moto,
            $imagen_nombre
        );
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            header('Location: index.php?success=Moto agregada exitosamente');
            exit();
        } else {
            $error_info = sqlsrv_errors();
            $errores[] = "Error al guardar la moto: " . $error_info[0]['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nueva Moto</title>
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
                <h1><i class="fas fa-plus"></i> Agregar Nueva Moto</h1>
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

                <form action="crear.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="marca" class="form-label">Marca *</label>
                        <input type="text" id="marca" name="marca" class="form-control" 
                               value="<?php echo htmlspecialchars($valores['marca']); ?>" required
                               placeholder="Ej: Honda, Yamaha, Kawasaki">
                    </div>

                    <div class="form-group">
                        <label for="modelo" class="form-label">Modelo *</label>
                        <input type="text" id="modelo" name="modelo" class="form-control" 
                               value="<?php echo htmlspecialchars($valores['modelo']); ?>" required
                               placeholder="Ej: CBR 600RR, MT-07, Ninja 650">
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
                                   min="50" max="2500" required
                                   placeholder="Ej: 600, 750, 1000">
                        </div>

                        <div class="form-group">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" id="color" name="color" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['color']); ?>"
                                   placeholder="Ej: Rojo, Negro, Azul">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="precio" class="form-label">Precio ($) *</label>
                            <input type="number" step="0.01" id="precio" name="precio" class="form-control" 
                                   value="<?php echo htmlspecialchars($valores['precio']); ?>" required
                                   placeholder="Ej: 12500.00">
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
                        <input type="file" id="imagen" name="imagen" class="form-control" 
                               accept="image/jpeg, image/png, image/gif, image/webp">
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Tamaño máximo: 2MB</small>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Moto
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