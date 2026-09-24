<?php

require "db.php";

$appointment = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tracking_code = strtoupper(trim($_POST["tracking_code"] ?? ""));
    $contact = trim($_POST["contact"] ?? "");

    if ($tracking_code === "" || $contact === "") {
        $error = "Please enter your tracking code and contact number.";
    } else {
        $stmt = $conn->prepare(
            "SELECT
                appointments.tracking_code,
                appointments.appointment_date,
                appointments.appointment_time,
                appointments.status AS appointment_status,
                patients.full_name,
                patients.contact_number,
                services.service_name,
                queue.queue_number,
                queue.status AS queue_status
             FROM appointments
             INNER JOIN patients ON appointments.patient_id = patients.patient_id
             INNER JOIN services ON appointments.service_id = services.service_id
             LEFT JOIN queue ON appointments.appointment_id = queue.appointment_id
             WHERE appointments.tracking_code = ?
               AND patients.contact_number = ?
             LIMIT 1"
        );

        $stmt->bind_param("ss", $tracking_code, $contact);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $appointment = $result->fetch_assoc();
        } else {
            $error = "Appointment not found. Check your tracking code and contact number.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointment Status | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="modal show" style="display:grid;position:fixed;">
<div class="modal-box">
    <span class="kicker">RESIDENT PORTAL</span>
    <h2>Check Appointment Status</h2>
    <p>Use the tracking code you received after submitting your appointment.</p>

    <?php if ($error): ?>
        <div style="padding:12px;border-radius:10px;background:#fbeaea;color:#bd5555;font-size:12px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>
            Tracking Code
            <input type="text" name="tracking_code" placeholder="PA-XXXXXXXX" required>
        </label>

        <label>
            Contact Number
            <input type="text" name="contact" placeholder="09XX XXX XXXX" required>
        </label>

        <button class="primary full" type="submit">Check Status</button>
    </form>

    <?php if ($appointment): ?>
        <hr style="border:0;border-top:1px solid #dcebe6;margin:24px 0;">

        <h3><?= htmlspecialchars($appointment["full_name"]) ?></h3>
        <p><strong>Tracking Code:</strong> <?= htmlspecialchars($appointment["tracking_code"]) ?></p>
        <p><strong>Service:</strong> <?= htmlspecialchars($appointment["service_name"]) ?></p>
        <p><strong>Date:</strong> <?= htmlspecialchars($appointment["appointment_date"]) ?></p>
        <p><strong>Time:</strong> <?= htmlspecialchars($appointment["appointment_time"]) ?></p>

        <div class="success">
            <strong>Appointment Status:</strong>
            <?= htmlspecialchars($appointment["appointment_status"]) ?>
        </div>

        <?php if ($appointment["queue_number"] !== null): ?>
            <div class="success" style="margin-top:12px;">
                <strong>Queue Number:</strong>
                Q-<?= str_pad($appointment["queue_number"], 3, "0", STR_PAD_LEFT) ?><br>
                <strong>Queue Status:</strong>
                <?= htmlspecialchars($appointment["queue_status"]) ?>
            </div>
        <?php else: ?>
            <p style="font-size:12px;color:#68827c;">
                You have not been checked into the queue yet.
            </p>
        <?php endif; ?>
    <?php endif; ?>

    <a class="secondary" style="display:block;text-align:center;margin-top:18px;"
       href="index.html">← Back to Healthcare Center</a>
</div>
</div>
</body>
</html>
