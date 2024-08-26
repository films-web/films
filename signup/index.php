<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "films";
$password = "ZfkE62vsOn15F/Zg";
$dbname = "films";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}


if (isset($_SESSION['user_id'])) {
        header("Location: ../"); // Redirect to the home page if not logged in
        exit;
    }
// Initialize variables
$signup_username = $signup_password = $signup_confirm_password = $signup_email = "";
$signup_message = "";

// Handle signup form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup_submit'])) {
    $signup_username = htmlspecialchars(trim($_POST['signup_username']));
    $signup_password = htmlspecialchars(trim($_POST['signup_password']));
    $signup_confirm_password = htmlspecialchars(trim($_POST['signup_confirm_password']));
    $signup_email = htmlspecialchars(trim($_POST['signup_email']));

    if (empty($signup_username) || empty($signup_password) || empty($signup_confirm_password) || empty($signup_email)) {
        $signup_message = "All fields are required.";
    } elseif ($signup_password !== $signup_confirm_password) {
        $signup_message = "Passwords do not match.";
    } elseif (!filter_var($signup_email, FILTER_VALIDATE_EMAIL)) {
        $signup_message = "Invalid email format.";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $signup_message = "SQL prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $signup_username, $signup_email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $signup_message = "Username or email already exists.";
            } else {
                // Insert new user
                $hashed_password = password_hash($signup_password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO users (username, password_hash, email) VALUES (?, ?, ?)");
                if (!$stmt) {
                    $signup_message = "SQL prepare failed: " . $conn->error;
                } else {
                    $stmt->bind_param("sss", $signup_username, $hashed_password, $signup_email);
                    if ($stmt->execute()) {
                        $signup_message = "Registration successful! You can now <a href='../login/'>log in</a>.";
                    } else {
                        $signup_message = "Error during registration: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $stmt->close();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sign Up</h1>
	    <nav>
		<form method="post" action="../" style="display:inline;">
                        <button type="submit">Home</button>
                    </form>
	    </nav>
        </header>

        <main>
            <div class="signup-form">
                <?php if (!empty($signup_message)) { ?>
                    <div id="signup-message">
                        <p class="error"><?php echo htmlspecialchars($signup_message); ?></p>
                    </div>
                <?php } ?>

                <form action="" method="post">
                    <label for="signup_username">Username:</label>
                    <input type="text" id="signup_username" name="signup_username" required>

                    <label for="signup_password">Password:</label>
                    <input type="password" id="signup_password" name="signup_password" required>

                    <label for="signup_confirm_password">Confirm Password:</label>
                    <input type="password" id="signup_confirm_password" name="signup_confirm_password" required>

                    <label for="signup_email">Email:</label>
                    <input type="email" id="signup_email" name="signup_email" required>

                    <button type="submit" name="signup_submit">Sign Up</button>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Film & Music Hub</p>
        </footer>
    </div>
</body>
</html>
