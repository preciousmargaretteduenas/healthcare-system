<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$totalPatients = (int)$conn->query("SELECT COUNT(*) AS total FROM patients")->fetch_assoc()["total"];

$todayAppointments = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE()"
)->fetch_assoc()["total"];

$pendingAppointments = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM appointments WHERE appointment_date = CURDATE() AND status = 'Pending'"
)->fetch_assoc()["total"];

$waitingQueue = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM queue WHERE queue_date = CURDATE() AND status = 'Waiting'"
)->fetch_assoc()["total"];

$completedToday = (int)$conn->query(
    "SELECT COUNT(*) AS total FROM queue WHERE queue_date = CURDATE() AND status = 'Completed'"
)->fetch_assoc()["total"];

$todaySql = "
SELECT
    appointments.tracking_code,
    appointments.appointment_time,
    appointments.status,
    patients.full_name,
    services.service_name
FROM appointments
INNER JOIN patients ON appointments.patient_id = patients.patient_id
INNER JOIN services ON appointments.service_id = services.service_id
WHERE appointments.appointment_date = CURDATE()
ORDER BY appointments.appointment_time ASC
LIMIT 8
";
$todayAppointmentsRows = $conn->query($todaySql);

$queueSql = "
SELECT
    queue.queue_number,
    queue.status,
    patients.full_name,
    services.service_name
FROM queue
INNER JOIN appointments ON queue.appointment_id = appointments.appointment_id
INNER JOIN patients ON appointments.patient_id = patients.patient_id
INNER JOIN services ON appointments.service_id = services.service_id
WHERE queue.queue_date = CURDATE()
ORDER BY queue.queue_number ASC
LIMIT 8
";
$queueRows = $conn->query($queueSql);

$recentPatients = $conn->query(
    "SELECT patient_id, full_name, sex, created_at
     FROM patients
     ORDER BY patient_id DESC
     LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Staff Dashboard | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">✚</div>
        <div><strong>Pag-Asa</strong><span>Healthcare Center</span></div>
    </div>

    <div class="side-label">MAIN MENU</div>
    <a class="active" href="dashboard.php">▦ <span>Dashboard</span></a>
    <a href="patients.php">♙ <span>Patients</span></a>
    <a href="appointments.php">◷ <span>Appointments</span></a>
    <a href="queue.php">⌁ <span>Patient Queue</span></a>

    <div class="side-label">SYSTEM</div>
    <a href="index.html#services">✚ <span>Services</span></a>
    <a href="index.html#announcements">◉ <span>Announcements</span></a>

    <div class="sidebar-bottom">
        <div class="user-mini">
            👩‍⚕️
            <div>
                <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong>
                <small>Staff</small>
            </div>
        </div>
        <button onclick="location.href='logout.php'">↪ Logout</button>
    </div>
</aside>

<main class="dash-main">

<header class="dash-header">
    <div>
        <span class="kicker">STAFF DASHBOARD</span>
        <h1>Good day, Health Staff 👋</h1>
        <p>Manage appointments, patients, and today's queue.</p>
    </div>

    <div class="header-actions">
        <a class="outline" href="index.html">Back to Website</a>
        <a class="profile" href="logout.php">Logout</a>
    </div>
</header>

<section class="metric-grid">

    <div class="metric">
        <span class="metric-icon">♙</span>
        <div>
            <small>Total Patients</small>
            <strong><?= $totalPatients ?></strong>
            <em>Registered residents</em>
        </div>
    </div>

    <div class="metric">
        <span class="metric-icon">◷</span>
        <div>
            <small>Today's Appointments</small>
            <strong><?= $todayAppointments ?></strong>
            <em><?= $pendingAppointments ?> pending confirmation</em>
        </div>
    </div>

    <div class="metric">
        <span class="metric-icon">⌁</span>
        <div>
            <small>Waiting in Queue</small>
            <strong><?= $waitingQueue ?></strong>
            <em class="warn">Today's queue</em>
        </div>
    </div>

    <div class="metric">
        <span class="metric-icon">✓</span>
        <div>
            <small>Completed Today</small>
            <strong><?= $completedToday ?></strong>
            <em>Completed queue visits</em>
        </div>
    </div>

</section>

<section class="dash-grid">

<div class="panel" id="appointments">
    <div class="panel-head">
        <div>
            <h2>Today's Appointments</h2>
            <p>Appointment requests and confirmed visits</p>
        </div>
        <a class="outline" href="appointments.php">View All →</a>
    </div>

    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Patient</th>
                <th>Service</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($todayAppointmentsRows->num_rows === 0): ?>
            <tr>
                <td colspan="4">No appointments for today.</td>
            </tr>
        <?php else: ?>

        <?php while ($row = $todayAppointmentsRows->fetch_assoc()): ?>
            <tr>
                <td>
                    <strong><?= htmlspecialchars($row["full_name"]) ?></strong>
                    <small><?= htmlspecialchars($row["tracking_code"]) ?></small>
                </td>
                <td><?= htmlspecialchars($row["service_name"]) ?></td>
                <td><?= htmlspecialchars($row["appointment_time"]) ?></td>
                <td>
                    <span class="pill <?= $row["status"] === "Completed" ? "green" : ($row["status"] === "Pending" ? "yellow" : "blue") ?>">
                        <?= htmlspecialchars($row["status"]) ?>
                    </span>
                </td>
            </tr>
        <?php endwhile; ?>

        <?php endif; ?>

        </tbody>
    </table>
    </div>
</div>

<div class="panel" id="queue">

    <div class="panel-head">
        <div>
            <h2>Today's Queue</h2>
            <p>Manage patients waiting for service</p>
        </div>
        <a class="outline" href="queue.php">Manage →</a>
    </div>

    <?php if ($queueRows->num_rows === 0): ?>

        <p style="font-size:12px;color:#68827c;">No patients are currently in the queue.</p>

    <?php else: ?>

        <?php while ($queue = $queueRows->fetch_assoc()): ?>

        <div class="stock-item">

            <div class="stock-icon">
                <?= (int)$queue["queue_number"] ?>
            </div>

            <div>
                <strong><?= htmlspecialchars($queue["full_name"]) ?></strong>
                <small><?= htmlspecialchars($queue["service_name"]) ?></small>
            </div>

            <span class="pill <?= $queue["status"] === "Serving" ? "green" : ($queue["status"] === "Completed" ? "blue" : "yellow") ?>">
                <?= htmlspecialchars($queue["status"]) ?>
            </span>

        </div>

        <?php endwhile; ?>

    <?php endif; ?>

</div>

</section>

<section class="dash-grid lower">

<div class="panel" id="patients">

    <div class="panel-head">
        <div>
            <h2>Recent Patients</h2>
            <p>Latest registered residents</p>
        </div>
        <a class="outline" href="patients.php">View Records →</a>
    </div>

    <div class="patient-list">

    <?php if ($recentPatients->num_rows === 0): ?>

        <p style="font-size:12px;color:#68827c;">No patients registered yet.</p>

    <?php else: ?>

        <?php while ($patient = $recentPatients->fetch_assoc()): ?>

        <div>
            <span class="avatar">
                <?= strtoupper(substr($patient["full_name"], 0, 1)) ?>
            </span>

            <div>
                <strong><?= htmlspecialchars($patient["full_name"]) ?></strong>
                <small><?= htmlspecialchars($patient["sex"] ?? "Not specified") ?></small>
            </div>

            <time><?= htmlspecialchars(date("M d, Y", strtotime($patient["created_at"]))) ?></time>
        </div>

        <?php endwhile; ?>

    <?php endif; ?>

    </div>
</div>

<div class="panel">

    <div class="panel-head">
        <div>
            <h2>Quick Actions</h2>
            <p>Common staff tasks</p>
        </div>
    </div>

    <div class="quick-grid">
        <a href="patient_add.php">＋<span>Register Patient</span></a>
        <a href="appointments.php">◷<span>Manage Appointments</span></a>
        <a href="queue.php">⌁<span>Manage Queue</span></a>
        <a href="index.html#announcements">◉<span>View Announcements</span></a>
    </div>

</div>

</section>

</main>
</body>
</html>
