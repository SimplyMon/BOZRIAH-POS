<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$serverName = "SIMON";
$database   = "posTalapointMaster";

try {
    // Windows Authentication (no username/password)
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(["success" => false, "error" => "Database connection failed."]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userID   = $_POST['userID']   ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($userID) || empty($password)) {
        echo json_encode(["success" => false, "error" => "Please enter both User ID and Password."]);
        exit;
    }

    try {
        $stmt = $conn->prepare("
            SELECT UserID, Password, FirstName, LastName, Role
            FROM USRR
            WHERE UserID = :userID
        ");
        $stmt->execute(['userID' => $userID]);
        $user = $stmt->fetch();

        if ($user) {
            if ($password === $user['Password']) {
                $_SESSION['user'] = [
                    'UserID'    => $user['UserID'],
                    'FirstName' => $user['FirstName'],
                    'LastName'  => $user['LastName'],
                    'Role'      => $user['Role'],
                ];
                echo json_encode(["success" => true, "redirect" => "./content/index.php"]);
            } else {
                echo json_encode(["success" => false, "error" => "Incorrect User ID or Password."]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Incorrect User ID or Password."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "We are experiencing technical difficulties."]);
    }
}
