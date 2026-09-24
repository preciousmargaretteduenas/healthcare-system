<?php

session_start();

if (isset($_SESSION["user_id"]) && $_SESSION["role"] === "Staff") {
    header("Location: dashboard.php");
    exit;
}

require "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT user_id, username, password, role
         FROM users
         WHERE username = ?
         LIMIT 1"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (
            $user["role"] === "Staff" &&
            password_verify($password, $user["password"])
        ) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Incorrect username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Login | Pag-Asa Healthcare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="modal show" style="display:grid;position:fixed;">
    <div class="modal-box" style="max-width:430px;">
        <div class="brand">
            <div class="brand-icon">✚</div>
            <div>
                <strong>Pag-Asa</strong>
                <span>Healthcare Center</span>
            </div>
        </div>

        <span class="kicker" style="display:block;margin-top:28px;">STAFF ACCESS</span>
        <h2>Staff Login</h2>
        <p>Only authorized healthcare staff can access the staff dashboard.</p>

        <?php if ($error): ?>
            <div style="padding:12px;border-radius:10px;background:#fbeaea;color:#bd5555;font-size:12px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>
                Username
                <input type="text" name="username" required autocomplete="username">
            </label>

            <label>
                Password
                <input type="password" name="password" required autocomplete="current-password">
            </label>

            <button class="primary full" type="submit">Login</button>
        </form>

        <a class="secondary" style="display:block;text-align:center;margin-top:16px;"
           href="index.html">
            ← Back to Website
        </a>
    </div>
</div>
</body>
</html>
