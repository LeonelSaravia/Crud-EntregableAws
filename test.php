<?php
require_once 'database.php';

// Probar conexión
echo "Probando conexión...<br>";

// Probar consulta simple
$sql = "SELECT COUNT(*) as total FROM motos";
$result = ejecutarConsulta($sql);

if ($result !== false) {
    echo "✅ Conexión exitosa. Total de motos: " . $result[0]['total'] . "<br>";
} else {
    echo "❌ Error en consulta: " . print_r(sqlsrv_errors(), true) . "<br>";
}

// Probar estructura de la tabla
$sql = "SELECT TOP 1 * FROM motos";
$result = ejecutarConsulta($sql);

if ($result !== false) {
    echo "✅ Estructura de tabla correcta<br>";
    echo "<pre>";
    print_r($result[0]);
    echo "</pre>";
} else {
    echo "❌ Error en estructura: " . print_r(sqlsrv_errors(), true) . "<br>";
}
?>