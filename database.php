<?php
/**
 * ====================================
 * CONEXIÓN A SQL SERVER (AZURE)
 * ====================================
 */

// --- Conexión con SQLSRV (para uso principal)
$connectionInfo = array(
    "UID" => "adminmoto",
    "PWD" => "leomoto25.",
    "Database" => "adminmotos",
    "LoginTimeout" => 30,
    "Encrypt" => 1,
    "TrustServerCertificate" => 0,
    "CharacterSet" => "UTF-8"
);
$serverName = "tcp:server-motos-sql.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Error de conexión SQL Server: " . print_r(sqlsrv_errors(), true));
}

/**
 * ====================================
 * FUNCIONES REUTILIZABLES
 * ====================================
 */

// 🔹 Limpia y protege los datos de entrada
function limpiar($dato) {
    return htmlspecialchars(trim($dato));
}

// 🔹 Sube una imagen al servidor
function subirImagen($imagen) {
    $directorio = "uploads/";

    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true);
    }

    if ($imagen['error'] === UPLOAD_ERR_OK) {
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $tipoMime = mime_content_type($imagen['tmp_name']);

        if (!in_array($tipoMime, $tiposPermitidos)) {
            return ['error' => 'Solo se permiten imágenes JPG, PNG, GIF o WEBP'];
        }

        // Límite de 2MB
        if ($imagen['size'] > 2097152) {
            return ['error' => 'La imagen es muy grande. Máximo 2MB'];
        }

        // Generar nombre único
        $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid('moto_', true) . '.' . $extension;
        $rutaDestino = $directorio . $nombreArchivo;

        if (move_uploaded_file($imagen['tmp_name'], $rutaDestino)) {
            return ['success' => $nombreArchivo];
        } else {
            return ['error' => 'Error al subir la imagen'];
        }
    }

    return ['success' => null];
}

// 🔹 Función para ejecutar consultas de forma segura
function ejecutarConsulta($sql, $params = []) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        return false;
    }
    
    $resultados = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $resultados[] = $row;
    }
    
    return $resultados;
}

// 🔹 Función para obtener un solo registro
function obtenerRegistro($sql, $params = []) {
    global $conn;
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        return false;
    }
    
    return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
}
?>