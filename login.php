<?php
session_start(); // Start the session for user authentication

// Database connection parameters
$host = "localhost";
$dbUserName = "root"; // Default username for XAMPP/WAMP
$dbPassword = ""; // Default password for XAMPP/WAMP
$dbName = "registration";

// Create a connection to the database
$conn = mysqli_connect($host, $dbUserName, $dbPassword, $dbName);

// Check for connection errors
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize the statement variable
$stmt = null;

try {
    // Check if the request method is POST
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve email and password from the POST request
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Validate that both fields are filled
        if (empty($email) || empty($password)) {
            header("Location: login.html?error=Please fill in all fields");
            exit();
        }

        // Prepare a SQL statement to fetch the user based on the email
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        // Bind the email parameter to the SQL query
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Get the result of the query
        $result = $stmt->get_result();

        // Check if a user with the provided email exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc(); // Fetch the user details

            // Verify the password using the hashed password from the database
            if (password_verify($password, $user['password'])) {
                // Store user information in session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['logged_in'] = true;

                // Redirect the user to the welcome page
                header("Location: welcome.php");
                exit();
            } else {
                // If the password is incorrect
                header("Location: login.html?error=Invalid password");
                exit();
            }
        } else {
            // If no user is found with the provided email
            header("Location: login.html?error=User not found");
            exit();
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Ensure resources are released
    if ($stmt) {
        $stmt->close(); // Close the statement
    }
    $conn->close(); // Close the database connection
}

