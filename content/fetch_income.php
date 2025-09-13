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

$sql = "SELECT FORMAT(EntryDate, 'yyyy-MMM-dd') AS date, SUM(AmountDue) AS total_income 
        FROM TRAD2 WHERE $dateCondition GROUP BY FORMAT(EntryDate, 'yyyy-MMM-dd') ORDER BY date ASC";


error_log("DEBUG SQL QUERY: $sql");

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
exit;
