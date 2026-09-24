<?php

session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "Staff") {
    header("Location: login.php");
    exit;
}

require "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["full_name"] ?? "");
    $birth = $_POST["birth_date"] ?? null;
    $sex = $_POST["sex"] ?? "";
    $contact = trim($_POST["contact_number"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if ($name === "") {
        $message = "Full name is required.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO patients
             (full_name, birth_date, sex, contact_number, address)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssss", $name, $birth, $sex, $contact, $address);

        if ($stmt->execute()) {
            header("Location: patients.php");
            exit;
        }

        $message = "Unable to register patient.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register Patient | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-body">
<aside class="sidebar">
    <div class="brand"><div class="brand-icon">✚</div><div><strong>Pag-Asa</strong><span>Healthcare Center</span></div></div>
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
        <div><span class="kicker">PATIENT REGISTRATION</span><h1>Register Patient</h1><p>Add a resident to the patient records.</p></div>
    </header>

    <section class="panel" style="max-width:760px;">
        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name<input type="text" name="full_name" required></label>
            <div class="form-row">
                <label>Birth Date<input type="date" name="birth_date"></label>
                <label>Sex<select name="sex"><option value="">Select</option><option>Female</option><option>Male</option></select></label>
            </div>
            <label>Contact Number<input type="text" name="contact_number"></label>
            <label>Address<textarea name="address" rows="3"></textarea></label>
            <button class="primary" type="submit">Register Patient</button>
        </form>
    </section>
</main>
</div>
</body>
</html>
