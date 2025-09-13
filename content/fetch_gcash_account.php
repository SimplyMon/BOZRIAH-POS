<?php
include '../config/dbconn.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
       SELECT 
            dbo.d1(AccountNo) AS AccountNo, 
            dbo.d1(AccountName) AS AccountName 
        FROM [POSDB].[dbo].[POS_EWLT]
    ");
    $stmt->execute();
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($accounts) {
        echo json_encode($accounts);
    } else {
        echo json_encode(["error" => "No accounts found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
