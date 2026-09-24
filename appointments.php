<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$sql = "
SELECT
    appointments.appointment_id,
    appointments.tracking_code,
    appointments.appointment_date,
    appointments.appointment_time,
    appointments.reason,
    appointments.status,
    patients.full_name,
    patients.contact_number,
    services.service_name
FROM appointments
INNER JOIN patients ON appointments.patient_id = patients.patient_id
INNER JOIN services ON appointments.service_id = services.service_id
ORDER BY appointments.appointment_date ASC, appointments.appointment_time ASC
";

$appointments = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-body">
<aside class="sidebar">
    <div class="brand"><div class="brand-icon">✚</div><div><strong>Pag-Asa</strong><span>Healthcare Center</span></div></div>
    <div class="side-label">STAFF MENU</div>
    <a href="dashboard.php">▦ <span>Dashboard</span></a>
    <a href="patients.php">♙ <span>Patients</span></a>
    <a class="active" href="appointments.php">◷ <span>Appointments</span></a>
    <a href="queue.php">⌁ <span>Patient Queue</span></a>
    <div class="sidebar-bottom">
        <div class="user-mini">👩‍⚕️<div><strong><?= htmlspecialchars($_SESSION["username"]) ?></strong><small>Staff</small></div></div>
        <button onclick="location.href='logout.php'">↪ Logout</button>
    </div>
</aside>

<main class="dash-main">
<header class="dash-header">
    <div><span class="kicker">APPOINTMENT MANAGEMENT</span><h1>Appointments</h1><p>Confirm requests and check patients into today's queue.</p></div>
</header>

<section class="panel">
<div class="table-wrap">
<table>
<thead>
<tr>
<th>Tracking Code</th><th>Patient</th><th>Contact</th><th>Service</th>
<th>Date</th><th>Time</th><th>Status</th><th>Action</th>
</tr>
</thead>
<tbody>
<?php while ($appointment = $appointments->fetch_assoc()): ?>
<tr>
<td><strong><?= htmlspecialchars($appointment["tracking_code"]) ?></strong></td>
<td><?= htmlspecialchars($appointment["full_name"]) ?></td>
<td><?= htmlspecialchars($appointment["contact_number"]) ?></td>
<td><?= htmlspecialchars($appointment["service_name"]) ?></td>
<td><?= htmlspecialchars($appointment["appointment_date"]) ?></td>
<td><?= htmlspecialchars($appointment["appointment_time"]) ?></td>
<td><span class="pill <?= $appointment["status"] === "Pending" ? "yellow" : ($appointment["status"] === "Completed" ? "green" : "blue") ?>">
<?= htmlspecialchars($appointment["status"]) ?>
</span></td>
<td>
<?php if ($appointment["status"] === "Pending"): ?>
<a href="appointment_status.php?id=<?= (int)$appointment["appointment_id"] ?>&status=Confirmed">Confirm</a>
&nbsp;|&nbsp;
<a href="appointment_status.php?id=<?= (int)$appointment["appointment_id"] ?>&status=Cancelled">Cancel</a>
<?php elseif ($appointment["status"] === "Confirmed"): ?>
<a href="queue_checkin.php?id=<?= (int)$appointment["appointment_id"] ?>">Check In</a>
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
