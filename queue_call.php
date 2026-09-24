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

$serving = $conn->query(
    "SELECT queue_id FROM queue
     WHERE queue_date = CURDATE() AND status = 'Serving'
     LIMIT 1"
);

if ($serving->num_rows > 0) {
    die("<h2>A patient is already being served.</h2><a href='queue.php'>Back to Queue</a>");
}

$stmt = $conn->prepare(
    "UPDATE queue
     SET status = 'Serving', called_time = NOW()
     WHERE queue_id = ? AND status = 'Waiting' AND queue_date = CURDATE()"
);
$stmt->bind_param("i", $queue_id);
$stmt->execute();

header("Location: queue.php");
exit;
?>
