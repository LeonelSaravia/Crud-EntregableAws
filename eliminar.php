<?php
require_once 'database.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php?error=ID de moto no especificado');
    exit();
}

$id = (int)$_GET['id'];

// Obtener información de la moto para eliminar su imagen
$sql = "SELECT imagen FROM motos WHERE id = ?";
$params = array($id);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt && sqlsrv_has_rows($stmt)) {
    $moto = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    // Eliminar la imagen si existe
    if (!empty($moto['imagen']) && file_exists('uploads/' . $moto['imagen'])) {
        unlink('uploads/' . $moto['imagen']);
    }
    
    // Eliminar la moto de la base de datos
    $sql = "DELETE FROM motos WHERE id = ?";
    $params = array($id);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt) {
        header('Location: index.php?success=Moto eliminada exitosamente');
        exit();
    } else {
        header('Location: index.php?error=Error al eliminar la moto: ' . print_r(sqlsrv_errors(), true));
        exit();
    }
} else {
    header('Location: index.php?error=Moto no encontrada');
    exit();
}
?>