<?php
include '../config/dbconn.php';
session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['UserID'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user']['UserID'];

try {
    $sql = "SELECT dbo.d1(UserID) as UserID, 
                dbo.d1(FirstName) as FirstName, 
                dbo.d1(LastName) as LastName, 
                dbo.d1(EmailAddress) as EmailAddress
        FROM [posTalapointMaster].[dbo].[USRR] 
            WHERE dbo.d1(UserID) = :user_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$user) {
        header("Location: ../index.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
