<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$search = trim($_GET["search"] ?? "");

if ($search !== "") {
    $stmt = $conn->prepare(
        "SELECT patient_id, full_name, birth_date, sex, contact_number, address
         FROM patients
         WHERE full_name LIKE ? OR contact_number LIKE ?
         ORDER BY patient_id DESC"
    );
    $keyword = "%" . $search . "%";
    $stmt->bind_param("ss", $keyword, $keyword);
    $stmt->execute();
    $patients = $stmt->get_result();
} else {
    $patients = $conn->query(
        "SELECT patient_id, full_name, birth_date, sex, contact_number, address
         FROM patients
         ORDER BY patient_id DESC"
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Patients | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-body">
<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">✚</div>
        <div><strong>Pag-Asa</strong><span>Healthcare Center</span></div>
    </div>
    <div class="side-label">STAFF MENU</div>
    <a href="dashboard.php">▦ <span>Dashboard</span></a>
    <a class="active" href="patients.php">♙ <span>Patients</span></a>
    <a href="appointments.php">◷ <span>Appointments</span></a>
    <a href="queue.php">⌁ <span>Patient Queue</span></a>
    <div class="sidebar-bottom">
        <div class="user-mini">👩‍⚕️<div><strong><?= htmlspecialchars($_SESSION["username"]) ?></strong><small>Staff</small></div></div>
        <button onclick="location.href='logout.php'">↪ Logout</button>
    </div>
</aside>

<main class="dash-main">
    <header class="dash-header">
        <div>
            <span class="kicker">PATIENT RECORDS</span>
            <h1>Patients</h1>
            <p>View and register resident records.</p>
        </div>
    </header>

    <section class="panel">
        <div class="panel-head">
            <div>
                <h2>Registered Patients</h2>
                <p><?= (int)$patients->num_rows ?> record(s) found</p>
            </div>
            <a class="outline" href="patient_add.php">+ Register Patient</a>
        </div>

        <form method="GET" style="display:flex;gap:10px;margin:0 0 20px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search name or contact number">
            <button class="outline" type="submit">Search</button>
        </form>

        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Birth Date</th><th>Sex</th>
                    <th>Contact</th><th>Address</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($patient = $patients->fetch_assoc()): ?>
                <tr>
                    <td><?= (int)$patient["patient_id"] ?></td>
                    <td><strong><?= htmlspecialchars($patient["full_name"]) ?></strong></td>
                    <td><?= htmlspecialchars($patient["birth_date"] ?? "") ?></td>
                    <td><?= htmlspecialchars($patient["sex"] ?? "") ?></td>
                    <td><?= htmlspecialchars($patient["contact_number"] ?? "") ?></td>
                    <td><?= htmlspecialchars($patient["address"] ?? "") ?></td>
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
