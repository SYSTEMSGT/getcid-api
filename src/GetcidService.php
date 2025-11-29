<?php
class GetcidService
{
    private $pdo;
    private $tokenProveedor;

    public function __construct($pdo, $tokenProveedor)
    {
        $this->pdo = $pdo;
        $this->tokenProveedor = $tokenProveedor;
    }

    public function procesarSolicitud($tokenCliente, $iid)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (empty($tokenCliente) || empty($iid)) {
            http_response_code(400);
            return ["error" => "Faltan parámetros: token o iid"];
        }

        $stmt = $this->pdo->prepare("SELECT tempcode, attempts, status_api FROM membresias WHERE token = :token");
        $stmt->execute([':token' => $tokenCliente]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            http_response_code(401);
            return ["error" => "Token no válido"];
        }

        if ((int)$cliente['status_api'] !== 1) {
            http_response_code(403);
            return ["error" => "El token no está disponible para el servicio API"];
        }

        if ((int)$cliente['attempts'] <= 0) {
            http_response_code(403);
            return ["error" => "Este token ya no tiene créditos disponibles"];
        }

        // Verificar si está bloqueado actualmente
        $now = new DateTime();
        if (!empty($cliente['bloqueado_hasta']) && $now < new DateTime($cliente['bloqueado_hasta'])) {
            http_response_code(429);
            return ["error" => "Demasiados intentos. Intenta nuevamente después de " . $cliente['bloqueado_hasta']];
        }

        // Contar intentos en el último minuto
        $stmt = $this->pdo->prepare("
    SELECT COUNT(*) FROM api_log 
    WHERE token = :token AND created_at >= (NOW() - INTERVAL 1 MINUTE)
");
        $stmt->execute([':token' => $tokenCliente]);
        $intentos = (int)$stmt->fetchColumn();

        // Si son 5 o más, bloquear por 15 minutos
        if (
            $intentos >= 5 &&
            (empty($cliente['bloqueado_hasta']) || $now > new DateTime($cliente['bloqueado_hasta']))
        ) {
            $bloqueadoHasta = (new DateTime())->modify('+15 minutes')->format('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare("
        UPDATE membresias SET bloqueado_hasta = :bloqueado WHERE token = :token
    ");
            $stmt->execute([
                ':bloqueado' => $bloqueadoHasta,
                ':token' => $tokenCliente
            ]);

            http_response_code(429);
            return ["error" => "Has realizado demasiadas solicitudes. El acceso está bloqueado por unos minutos"];
        }

        $url = "https://bs.getcid.xyz/webapi/get-cid/?token=" . urlencode($this->tokenProveedor) . "&onlycid=1&iid=" . urlencode($iid);
        $context = stream_context_create(["http" => ["method" => "GET", "timeout" => 5]]);
        $response = @file_get_contents($url, false, $context);

        if (!$response) {
            http_response_code(502);
            return ["error" => "No se pudo contactar a la API del proveedor"];
        }

        $data = json_decode($response, true);
        $cid = $data['cid'] ?? null;

        if (!$cid) {
            http_response_code(500);
            return ["error" => "Respuesta inválida del proveedor"];
        }

        // Verifica si ya existe en log
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM api_log WHERE cid = :cid");
        $stmt->execute([':cid' => $cid]);
        $existe = $stmt->fetchColumn() > 0;

        if (!$existe) {
            $stmt = $this->pdo->prepare("UPDATE membresias SET attempts = attempts - 1 WHERE token = :token");
            $stmt->execute([':token' => $tokenCliente]);
        }

        $stmt = $this->pdo->prepare("
    INSERT INTO api_log (token, ip_addr, iid, cid, created_at)
    VALUES (:token, :ip_addr, :iid, :cid, NOW())
");
        $stmt->execute([
            ':token'    => $tokenCliente,
            ':ip_addr'  => $ip,
            ':iid'      => $iid,
            ':cid'      => $cid
        ]);
        http_response_code(200);
        return $data;
    }
}
