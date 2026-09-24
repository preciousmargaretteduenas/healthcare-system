<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$queue_id = intval($_GET["id"] ?? 0);

if ($queue_id <= 0) {
    die("Invalid queue.");
}

$stmt = $conn->prepare(
    "UPDATE queue
     SET status = 'Completed', completed_time = NOW()
     WHERE queue_id = ? AND status = 'Serving'"
);
$stmt->bind_param("i", $queue_id);
$stmt->execute();

$getAppointment = $conn->prepare(
    "SELECT appointment_id FROM queue WHERE queue_id = ? LIMIT 1"
);
$getAppointment->bind_param("i", $queue_id);
$getAppointment->execute();
$result = $getAppointment->get_result();

if ($result->num_rows > 0) {
    $appointment_id = (int)$result->fetch_assoc()["appointment_id"];

    $updateAppointment = $conn->prepare(
        "UPDATE appointments SET status = 'Completed'
         WHERE appointment_id = ?"
    );
    $updateAppointment->bind_param("i", $appointment_id);
    $updateAppointment->execute();
}

header("Location: queue.php");
exit;
?>
