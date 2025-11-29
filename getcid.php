<?php
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/GetcidService.php';

$tokenCliente = $_GET['token'] ?? '';
$iid = $_GET['iid'] ?? '';

$servicio = new GetcidService($pdo, TOKEN_PROVEEDOR); // <--- esta línea es clave
$resultado = $servicio->procesarSolicitud($tokenCliente, $iid);
echo json_encode($resultado);