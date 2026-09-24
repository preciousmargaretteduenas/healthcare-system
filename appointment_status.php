<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$id = intval($_GET["id"] ?? 0);
$status = $_GET["status"] ?? "";

$allowed = ["Confirmed", "Cancelled"];

if ($id <= 0 || !in_array($status, $allowed, true)) {
    die("Invalid request.");
}

$stmt = $conn->prepare(
    "UPDATE appointments SET status = ? WHERE appointment_id = ?"
);
$stmt->bind_param("si", $status, $id);
$stmt->execute();

header("Location: appointments.php");
exit;
?>
