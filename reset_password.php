
<?php
session_start();

$host = "localhost";
$dbUserName = "root";
$dbPassword = "";
$dbName = "registration";

$conn = mysqli_connect($host, $dbUserName, $dbPassword, $dbName);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set SMTP settings to ensure mail function works
ini_set('SMTP', 'localhost');
ini_set('smtp_port', '25');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    $email = $_POST['email'];

    // Check if email exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Generate a unique token for password reset
        $token = bin2hex(random_bytes(50));

        // Store token in database with expiration time
        $expireTime = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $updateTokenSql = "UPDATE users SET reset_token = ?, reset_expire = ? WHERE email = ?";
        $updateStmt = $conn->prepare($updateTokenSql);

        if ($updateStmt === false) {
            die("Update SQL Error: " . $conn->error);
        }

        $updateStmt->bind_param("sss", $token, $expireTime, $email);
        $updateStmt->execute();

        // Send reset link to user email
        $resetLink = "http://localhost/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the link below to reset your password:\n" . $resetLink;
        $headers = "From: no-reply@yourwebsite.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "A password reset link has been sent to your email.";
        } else {
            echo "Failed to send email.";
        }
    } else {
        echo "No account found with that email.";
    }

    $stmt->close();
}

$conn->close();

