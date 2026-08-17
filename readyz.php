<?php
$mysqli = @new mysqli("mariadb-service.helm.svc.cluster.local", "ecomuser", "ecompassword", "ecomdb");

if ($mysqli->connect_error) {
    http_response_code(503);
    echo "not ready";
    exit;
}
