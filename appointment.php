<?php

require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

$name = trim($_POST["name"] ?? "");
$contact = trim($_POST["contact"] ?? "");\n$email = trim($_POST["email"] ?? "");
$service = trim($_POST["service"] ?? "");
$date = $_POST["date"] ?? "";
$time = $_POST["time"] ?? "";
$reason = trim($_POST["reason"] ?? "");

$parsedTime = strtotime($time);
if ($parsedTime === false) {
    die("Invalid appointment time.");
}
$time = date("H:i:s", $parsedTime);

if ($name === "" || $contact === "" || $email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL) || $service === "" || $date === "" || $time === "") {
    die("Please complete all required fields with a valid email address.");
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

$appointmentId = (int)$conn->insert_id;
$notify = $conn->prepare("INSERT INTO notifications (title, message, appointment_id, is_read) VALUES (?, ?, ?, 0)");
$notifyTitle = "New appointment booking";
$notifyMessage = $name . " booked " . $service . " for " . date("M j, Y", strtotime($date)) . " at " . date("g:i A", strtotime($time)) . " (Tracking: " . $tracking_code . ")";
$notify->bind_param("ssi", $notifyTitle, $notifyMessage, $appointmentId);
$notify->execute();

$emailSent = false;
$emailError = "Email delivery is not configured on this server yet.";
$configPath = __DIR__ . "/smtp_config.php";
$autoloadPath = __DIR__ . "/vendor/autoload.php";
if (file_exists($configPath) && file_exists($autoloadPath)) {
    require_once $autoloadPath;
    $smtp = require $configPath;
    try {
        $mail = new PHPMailer\\PHPMailer\\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtp["host"] ?? "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = $smtp["username"];
        $mail->Password = $smtp["app_password"];
        $mail->SMTPSecure = PHPMailer\\PHPMailer\\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)($smtp["port"] ?? 587);
        $mail->CharSet = "UTF-8";
        $mail->setFrom($smtp["username"], $smtp["sender_name"] ?? "Pag-Asa Healthcare Center");
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Your Pag-Asa Healthcare appointment tracking code";
        $safeName = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        $safeCode = htmlspecialchars($tracking_code, ENT_QUOTES, "UTF-8");
        $safeService = htmlspecialchars($service, ENT_QUOTES, "UTF-8");
        $safeDate = htmlspecialchars(date("F j, Y", strtotime($date)), ENT_QUOTES, "UTF-8");
        $safeTime = htmlspecialchars(date("g:i A", strtotime($time)), ENT_QUOTES, "UTF-8");
        $mail->Body = "<h2>Appointment request received</h2><p>Hello {$safeName},</p><p>Your appointment request was submitted to Pag-Asa Healthcare Center. It is currently <strong>Pending staff confirmation</strong>.</p><p><strong>Tracking code:</strong> {$safeCode}<br><strong>Service:</strong> {$safeService}<br><strong>Date:</strong> {$safeDate}<br><strong>Time:</strong> {$safeTime}</p><p>Keep this tracking code to check your appointment status. This email confirms submission only, not that the appointment schedule is confirmed.</p>";
        $mail->AltBody = "Hello {$name}. Your request was submitted and is Pending staff confirmation. Tracking code: {$tracking_code}. Service: {$service}. Date: {$date}. Time: {$time}.";
        $mail->send();
        $emailSent = true;
        $emailError = "";
    } catch (Throwable $e) {
        error_log("Appointment confirmation email error: " . $e->getMessage());
        $emailError = "The request was saved, but email delivery failed. Please check your address or contact the health center.";
    }
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
        <p><?= $emailSent ? "A tracking-code confirmation email was sent to " . htmlspecialchars($email) . "." : htmlspecialchars($emailError) ?></p>

        <a class="primary full" style="display:block;text-align:center;margin-top:16px;"
           href="resident_status.php?code=<?= urlencode($tracking_code) ?>">
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
