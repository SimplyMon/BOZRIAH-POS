<?php
include '../config/dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_id = $_POST['item_id'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE POSDB.dbo.ITEM SET IsAvailable = :status WHERE ItemID = :item_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':item_id', $item_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } catch (PDOException $e) {
        echo "Query error: " . $e->getMessage();
    }
} else {
    echo "Invalid request";
}
