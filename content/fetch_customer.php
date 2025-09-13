<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include '../config/dbconn.php';


if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$filter = $_GET['filter'] ?? 'today';

$dateCondition = "";
switch ($filter) {
    case 'today':
        $dateCondition = "OSDate >= CAST(GETDATE() AS DATE) AND OSDate < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))";
        break;
    case 'this_week':
        $dateCondition = "YEAR(OSDate) = YEAR(GETDATE()) AND DATEPART(WEEK, OSDate) = DATEPART(WEEK, GETDATE())";
        break;
    case 'this_month':
        $dateCondition = "YEAR(OSDate) = YEAR(GETDATE()) AND MONTH(OSDate) = MONTH(GETDATE())";
        break;
    case 'this_year':
        $dateCondition = "YEAR(OSDate) = YEAR(GETDATE())";
        break;
    default:
        echo json_encode(["error" => "Invalid filter"]);
        exit;
}

// count customers
$sql = "SELECT COUNT(DISTINCT SeqNo) AS total_customers FROM OSEH WHERE $dateCondition";


error_log("DEBUG SQL QUERY: $sql");

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);


    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
exit;
