<?php include '../config/dbconn.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

$sql = "SELECT SeqNo, OSNumber, FORMAT(OSDate, 'MMM. dd, yyyy') AS OSDate, isFromKiosk, isTakeout
        FROM OSEH
        WHERE isFromKiosk = 1
        AND EXISTS (SELECT 1 FROM OSED WHERE OSED.OSEHSeqNo = OSEH.SeqNo)
        AND NOT EXISTS (SELECT 1 FROM TRAH WHERE TRAH.SONumber = OSEH.OSNumber)
        ORDER BY OSNumber DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}
