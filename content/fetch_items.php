<?php
include '../config/dbconn.php';


session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

try {
    $checkColumnSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_NAME = 'ITEM' AND COLUMN_NAME = 'IsAvailable'";
    $stmt = $conn->query($checkColumnSql);
    $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$columnExists) {
        $alterTableSql = "ALTER TABLE POSDB.dbo.ITEM ADD IsAvailable BIT NOT NULL DEFAULT 1";
        $conn->exec($alterTableSql);

        $conn->exec("UPDATE POSDB.dbo.ITEM SET IsAvailable = 1");
    }

    $sql = "SELECT ItemID, dbo.d1(ItemDesc) as ItemDesc, IsAvailable FROM POSDB.dbo.ITEM";
    $stmt = $conn->query($sql);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query error: " . $e->getMessage());
}
