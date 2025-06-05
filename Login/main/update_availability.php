<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$specific_date = $_POST['specific_date'];
$status = $_POST['status'];

$query = "INSERT INTO availability (user_id, specific_date, start_time, end_time, status) 
          VALUES (?, ?, '09:00:00', '17:00:00', ?) 
          ON DUPLICATE KEY UPDATE status = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("isss", $user_id, $specific_date, $status, $status);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
?>