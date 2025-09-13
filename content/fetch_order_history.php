<?php
include '../config/dbconn.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

try {

    $sql = "SELECT
                [SeqNo], 
                dbo.d1([InvoiceNo]) as InvoiceNo,
                [InvoiceDate],
                [CashierID],
                [SONumber],
                [FinalInvoiceAmount],
                [BranchCode],
                [VATableAmount],
                [VATAmount],
                [DineType],
                [DiscPercent],
                [GoodsOrService]
            FROM [POSDB].[dbo].[TRAH]
            WHERE [SONumber] IS NOT NULL 
            ORDER BY [SeqNo] DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
