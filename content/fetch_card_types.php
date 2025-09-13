<?php
include '../config/dbconn.php';

header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("
        SELECT 
            SeqNo AS CardType,
            dbo.d1(CreditCardName) AS CreditCardName 
        FROM [POSDB].[dbo].[CCRD]
    ");
    $stmt->execute();
    $cardTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($cardTypes) {
        echo json_encode($cardTypes);
    } else {
        echo json_encode(["error" => "No card types found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
