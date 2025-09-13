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
        $dateCondition = "EntryDate >= CAST(GETDATE() AS DATE) AND EntryDate < DATEADD(DAY, 1, CAST(GETDATE() AS DATE))";
        break;
    case 'this_week':
        $dateCondition = "YEAR(EntryDate) = YEAR(GETDATE()) AND DATEPART(WEEK, EntryDate) = DATEPART(WEEK, GETDATE())";
        break;
    case 'this_month':
        $dateCondition = "YEAR(EntryDate) = YEAR(GETDATE()) AND MONTH(EntryDate) = MONTH(GETDATE())";
        break;
    case 'this_year':
        $dateCondition = "YEAR(EntryDate) = YEAR(GETDATE())";
        break;
    default:
        echo json_encode(["error" => "Invalid filter"]);
        exit;
}

$sql = "SELECT COUNT(SeqNo) AS total_orders FROM TRAD2 WHERE $dateCondition";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
exit;
