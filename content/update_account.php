<?php
include '../config/dbconn.php';
session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['UserID'])) {
    header("Location: ../index.php");
    exit;
}

$userID = $_SESSION['user']['UserID'];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

$stmt = $conn->prepare("SELECT dbo.d1(FirstName) AS FirstName, dbo.d1(LastName) AS LastName, dbo.d1(EmailAddress) AS EmailAddress FROM [posTalapointMaster].[dbo].[USRR] WHERE dbo.d1(UserID) = :userID");
$stmt->bindParam(':userID', $userID, PDO::PARAM_STR);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'User not found.'];
    header("Location: accounts.php");
    exit;
}

$firstName = $user['FirstName'] ?? '';
$lastName = $user['LastName'] ?? '';
$email = $user['EmailAddress'] ?? '';

try {
    $passwordChanged = false;
    if (!empty($newPassword)) {
        $stmt = $conn->prepare("SELECT dbo.d1(Password) AS Password FROM [posTalapointMaster].[dbo].[USRR] WHERE dbo.d1(UserID) = :userID");
        $stmt->bindParam(':userID', $userID, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'User not found.'];
            header("Location: accounts.php");
            exit;
        }

        $storedPassword = $row['Password'];

        if ($currentPassword !== $storedPassword) {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Incorrect current password.'];
            header("Location: accounts.php");
            exit;
        }

        $passwordChanged = true;
    }

    // Update user details
    $sql = "UPDATE [posTalapointMaster].[dbo].[USRR] 
            SET FirstName = dbo.e1(:firstName),
                LastName = dbo.e1(:lastName),
                EmailAddress = dbo.e1(:email)";

    if ($passwordChanged) {
        $sql .= ", Password = dbo.e1(:newPassword)";
    }

    $sql .= " WHERE dbo.d1(UserID) = :userID";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
    $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_STR);

    if ($passwordChanged) {
        $stmt->bindParam(':newPassword', $newPassword, PDO::PARAM_STR);
    }

    $stmt->execute();

    if ($passwordChanged) {
        session_unset();
        session_destroy();
        $_SESSION = [];
        header("Location: ../index.php?message=Password changed successfully. Please log in again.");
        exit;
    }

    $_SESSION['alert'] = ['type' => 'success', 'message' => 'Account details updated successfully!'];
    header("Location: accounts.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['alert'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    header("Location: accounts.php");
    exit;
}
