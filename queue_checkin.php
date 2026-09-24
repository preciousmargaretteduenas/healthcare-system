<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$appointment_id = intval($_GET["id"] ?? 0);

if ($appointment_id <= 0) {
    die("Invalid appointment.");
}

$stmt = $conn->prepare(
    "SELECT appointment_id, appointment_date, status
     FROM appointments
     WHERE appointment_id = ?
     LIMIT 1"
);
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Appointment not found.");
}

$appointment = $result->fetch_assoc();

if ($appointment["status"] !== "Confirmed") {
    die("Only confirmed appointments can be checked in.");
}

$queue_date = $appointment["appointment_date"];

$checkQueue = $conn->prepare(
    "SELECT queue_number FROM queue WHERE appointment_id = ? LIMIT 1"
);
$checkQueue->bind_param("i", $appointment_id);
$checkQueue->execute();
$queueResult = $checkQueue->get_result();

if ($queueResult->num_rows > 0) {
    $existing = $queueResult->fetch_assoc();
    $number = "Q-" . str_pad($existing["queue_number"], 3, "0", STR_PAD_LEFT);
    header("Location: queue.php");
    exit;
}

$getNumber = $conn->prepare(
    "SELECT COALESCE(MAX(queue_number), 0) AS last_number
     FROM queue WHERE queue_date = ?"
);
$getNumber->bind_param("s", $queue_date);
$getNumber->execute();
$numberRow = $getNumber->get_result()->fetch_assoc();
$nextNumber = ((int)$numberRow["last_number"]) + 1;

$insertQueue = $conn->prepare(
    "INSERT INTO queue
     (appointment_id, queue_number, queue_date, status, check_in_time)
     VALUES (?, ?, ?, 'Waiting', NOW())"
);
$insertQueue->bind_param("iis", $appointment_id, $nextNumber, $queue_date);

if (!$insertQueue->execute()) {
    die("Unable to create queue number: " . htmlspecialchars($conn->error));
}

$updateAppointment = $conn->prepare(
    "UPDATE appointments SET status = 'Checked In' WHERE appointment_id = ?"
);
$updateAppointment->bind_param("i", $appointment_id);
$updateAppointment->execute();

header("Location: queue.php");
exit;
?>
