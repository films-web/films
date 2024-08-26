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

// Redirect to the home page if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../');
    exit;
}

// Initialize variables
$login_identifier = $login_password = "";
$login_message = "";

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $login_identifier = htmlspecialchars(trim($_POST['login_identifier']));
    $login_password = htmlspecialchars(trim($_POST['login_password']));

    if (empty($login_identifier) || empty($login_password)) {
        $login_message = "Username or email and password are required.";
    } else {
        // Check if the identifier is a username or email
        $stmt = $conn->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            $login_message = "SQL prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $login_identifier, $login_identifier);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($user_id, $username, $email, $hashed_password);
                $stmt->fetch();

                if (password_verify($login_password, $hashed_password)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email; // Store email in the session
                    $_SESSION['username'] = $username; // Store username in the session
                    header('Location: ../');
                    exit;
                } else {
                    $login_message = "Invalid username or password.";
                }
            } else {
                $login_message = "Invalid username or password.";
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
    <title>Login</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
            <nav>
                <form method="post" action="../" style="display:inline;">
                    <button type="submit">Home</button>
                </form>
            </nav>
        </header>

        <main>
            <div class="login-form">
                <?php if (!empty($login_message)) { ?>
                    <div id="login-message">
                        <p class="error"><?php echo htmlspecialchars($login_message); ?></p>
                    </div>
                <?php } ?>

                <form action="" method="post">
                    <label for="login_identifier">Username or Email:</label>
                    <input type="text" id="login_identifier" name="login_identifier" required>

                    <label for="login_password">Password:</label>
                    <input type="password" id="login_password" name="login_password" required>

                    <button type="submit" name="login_submit">Login</button>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Film & Music Hub</p>
        </footer>
    </div>
</body>
</html>
