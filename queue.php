<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$today = date("Y-m-d");

$stmt = $conn->prepare(
    "SELECT
        queue.queue_id,
        queue.queue_number,
        queue.status,
        queue.check_in_time,
        patients.full_name,
        services.service_name
     FROM queue
     INNER JOIN appointments ON queue.appointment_id = appointments.appointment_id
     INNER JOIN patients ON appointments.patient_id = patients.patient_id
     INNER JOIN services ON appointments.service_id = services.service_id
     WHERE queue.queue_date = ?
     ORDER BY
        CASE queue.status
            WHEN 'Serving' THEN 1
            WHEN 'Waiting' THEN 2
            WHEN 'Completed' THEN 3
            WHEN 'Skipped' THEN 4
            ELSE 5
        END,
        queue.queue_number ASC"
);
$stmt->bind_param("s", $today);
$stmt->execute();
$queues = $stmt->get_result();

$serving = $conn->query(
    "SELECT queue.queue_number, patients.full_name, services.service_name
     FROM queue
     INNER JOIN appointments ON queue.appointment_id = appointments.appointment_id
     INNER JOIN patients ON appointments.patient_id = patients.patient_id
     INNER JOIN services ON appointments.service_id = services.service_id
     WHERE queue.queue_date = CURDATE() AND queue.status = 'Serving'
     LIMIT 1"
)->fetch_assoc();

$waitingCount = $conn->query(
    "SELECT COUNT(*) AS total FROM queue
     WHERE queue_date = CURDATE() AND status = 'Waiting'"
)->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patient Queue | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-body">
<aside class="sidebar">
    <div class="brand"><div class="brand-icon">✚</div><div><strong>Pag-Asa</strong><span>Healthcare Center</span></div></div>
    <div class="side-label">STAFF MENU</div>
    <a href="dashboard.php">▦ <span>Dashboard</span></a>
    <a href="patients.php">♙ <span>Patients</span></a>
    <a href="appointments.php">◷ <span>Appointments</span></a>
    <a class="active" href="queue.php">⌁ <span>Patient Queue</span></a>
    <div class="sidebar-bottom">
        <div class="user-mini">👩‍⚕️<div><strong><?= htmlspecialchars($_SESSION["username"]) ?></strong><small>Staff</small></div></div>
        <button onclick="location.href='logout.php'">↪ Logout</button>
    </div>
</aside>

<main class="dash-main">
<header class="dash-header">
    <div><span class="kicker">QUEUE MANAGEMENT</span><h1>Patient Queue</h1><p>Today's checked-in residents.</p></div>
    <div class="header-actions">
        <a class="outline" href="dashboard.php">← Dashboard</a>
    </div>
</header>

<section class="metric-grid">
    <div class="metric">
        <span class="metric-icon">⌁</span>
        <div><small>Waiting</small><strong><?= (int)$waitingCount ?></strong><em>patients waiting</em></div>
    </div>
    <div class="metric">
        <span class="metric-icon">✓</span>
        <div><small>Now Serving</small><strong><?= $serving ? "Q-" . str_pad($serving["queue_number"], 3, "0", STR_PAD_LEFT) : "—" ?></strong><em><?= $serving ? htmlspecialchars($serving["full_name"]) : "No patient" ?></em></div>
    </div>
</section>

<section class="panel" style="margin-top:18px;">
<div class="panel-head">
    <div>
        <h2>Today's Queue</h2>
        <p>Call patients in order and complete service.</p>
    </div>
    <a class="outline" href="queue.php">Refresh</a>
</div>

<?php if ($serving): ?>
<div class="success" style="margin-bottom:18px;">
    Now serving <strong>Q-<?= str_pad($serving["queue_number"], 3, "0", STR_PAD_LEFT) ?></strong>
    — <?= htmlspecialchars($serving["full_name"]) ?>
    (<?= htmlspecialchars($serving["service_name"]) ?>)
</div>
<?php endif; ?>

<div class="table-wrap">
<table>
<thead>
<tr><th>Queue</th><th>Patient</th><th>Service</th><th>Check-in</th><th>Status</th><th>Action</th></tr>
</thead>
<tbody>
<?php while ($queue = $queues->fetch_assoc()): ?>
<tr>
<td><strong>Q-<?= str_pad($queue["queue_number"], 3, "0", STR_PAD_LEFT) ?></strong></td>
<td><?= htmlspecialchars($queue["full_name"]) ?></td>
<td><?= htmlspecialchars($queue["service_name"]) ?></td>
<td><?= htmlspecialchars($queue["check_in_time"]) ?></td>
<td><span class="pill <?= $queue["status"] === "Completed" ? "green" : ($queue["status"] === "Serving" ? "blue" : "yellow") ?>">
<?= htmlspecialchars($queue["status"]) ?></span></td>
<td>
<?php if ($queue["status"] === "Waiting"): ?>
<a href="queue_call.php?id=<?= (int)$queue["queue_id"] ?>">Call Next</a>
<?php elseif ($queue["status"] === "Serving"): ?>
<a href="queue_complete.php?id=<?= (int)$queue["queue_id"] ?>">Complete</a>
<?php else: ?>
—
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</section>
</main>
</div>
</body>
</html>
