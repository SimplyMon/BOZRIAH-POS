<?php
$serverName = "SIMON";
$database   = "POSDB";

try {
    $conn = new PDO(
        "sqlsrv:Server=$serverName;Database=$database"
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
