<?php
include '../config/dbconn.php';

if (isset($_GET['seqno'])) {
    $seqno = $_GET['seqno'];

    $sql = "SELECT 
                OSED.ItemID, 
                OSED.Quantity, 
                OSED.Amount, 
                dbo.d1(ITEM.ItemDesc) AS ItemDesc, 
                OSEH.isTakeout  
            FROM OSED 
            LEFT JOIN ITEM ON dbo.d1(ITEM.ItemID) = dbo.d1(OSED.ItemID)
            LEFT JOIN OSEH ON OSEH.SeqNo = OSED.OSEHSeqNo
            WHERE OSED.OSEHSeqNo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$seqno]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
}
