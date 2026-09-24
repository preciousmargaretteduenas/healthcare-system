<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

$name = trim($_POST["name"] ?? "");
$contact = trim($_POST["contact"] ?? "");
$service = trim($_POST["service"] ?? "");
$date = $_POST["date"] ?? "";
$time = $_POST["time"] ?? "";
$reason = trim($_POST["reason"] ?? "");

if ($name === "" || $contact === "" || $service === "" || $date === "" || $time === "") {
    die("Please complete all required fields.");
}

if (!preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $date)) {
    die("Invalid appointment date.");
}

$check = $conn->prepare(
    "SELECT patient_id FROM patients
     WHERE full_name = ? AND contact_number = ?
     LIMIT 1"
);
$check->bind_param("ss", $name, $contact);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $patient_id = (int)$result->fetch_assoc()["patient_id"];
} else {
    $insertPatient = $conn->prepare(
        "INSERT INTO patients (full_name, contact_number)
         VALUES (?, ?)"
    );
    $insertPatient->bind_param("ss", $name, $contact);
    $insertPatient->execute();
    $patient_id = $conn->insert_id;
}

$getService = $conn->prepare(
    "SELECT service_id FROM services
     WHERE service_name = ? AND status = 'Active'
     LIMIT 1"
);
$getService->bind_param("s", $service);
$getService->execute();
$serviceResult = $getService->get_result();

if ($serviceResult->num_rows === 0) {
    die("Service not found.");
}

$service_id = (int)$serviceResult->fetch_assoc()["service_id"];

$tracking_code = "PA-" . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

$insertAppointment = $conn->prepare(
    "INSERT INTO appointments
     (tracking_code, patient_id, service_id, appointment_date,
      appointment_time, reason, status)
     VALUES (?, ?, ?, ?, ?, ?, 'Pending')"
);

$insertAppointment->bind_param(
    "siisss",
    $tracking_code,
    $patient_id,
    $service_id,
    $date,
    $time,
    $reason
);

if (!$insertAppointment->execute()) {
    die("Unable to submit appointment: " . htmlspecialchars($conn->error));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointment Submitted | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="modal show" style="display:grid;position:fixed;">
    <div class="modal-box">
        <span class="kicker">APPOINTMENT REQUEST</span>
        <h2>Appointment Submitted!</h2>
        <p>Your appointment request has been saved and is waiting for healthcare staff confirmation.</p>

        <div class="success">
            Your tracking code is <strong><?= htmlspecialchars($tracking_code) ?></strong>.
            Please save it.
        </div>

        <p><strong>Status:</strong> Pending</p>

        <a class="primary full" style="display:block;text-align:center;margin-top:16px;"
           href="resident_status.php">
            Check Appointment Status
        </a>

        <a class="secondary" style="display:block;text-align:center;margin-top:15px;"
           href="index.html">
            ← Back to Healthcare Center
        </a>
    </div>
</div>
</body>
</html>
