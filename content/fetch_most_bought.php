<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include '../config/dbconn.php';

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$sql = "SELECT TOP 20 dbo.d1(Description) AS Description, SUM(Quantity) AS TotalQuantity 
        FROM TRAD 
        WHERE Description IS NOT NULL
        GROUP BY dbo.d1(Description)
        ORDER BY TotalQuantity DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
exit;
