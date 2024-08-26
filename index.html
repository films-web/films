<?php
// Start the session
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
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$welcomeMessage = "";

// Fetch all films from the database
$sql = "SELECT id, title, file_path, thumbnail_path, upload_date, uploader_id FROM films ORDER BY upload_date DESC";
$result = $conn->query($sql);

// Get username from session if available
if (isset($_SESSION['email'])) {
    $userEmail = $_SESSION['email'];

    // Fetch username based on email
    $stmt = $conn->prepare("SELECT username FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $userEmail);
        $stmt->execute();
        $stmt->bind_result($username);
        if ($stmt->fetch()) {
            $welcomeMessage = "Welcome, " . htmlspecialchars($username) . "!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Film & Music Hub</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .film-link {
            text-decoration: none;
            color: inherit;
            display: inline-block; /* Ensure proper alignment */
        }

        .film-card {
            position: relative;
            display: inline-block;
            width: 100%;
            margin: 10px;
        }

        .video-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .thumbnail {
            display: block;
            width: 100%;
        }

        .unmute-button {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 5px;
            border: none;
            cursor: pointer;
            display: none;
        }

        .video-container:hover .unmute-button {
            display: block;
        }

        .video-container video {
            width: 100%;
        }

        .progress-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: rgba(0, 0, 0, 0.3);
            cursor: pointer;
        }

        .progress-bar {
            height: 100%;
            background: red;
            width: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Film & Music Hub</h1>
            <nav class="main-nav">
                <form method="post" action="./" style="display:inline;">
                    <button type="submit">Home</button>
                </form>

                <?php if (!isset($_SESSION['user_id'])): ?>
                    <form method="post" action="./login/" style="display:inline;">
                        <button type="submit">Login</button>
                    </form>
                    <form method="post" action="./signup/" style="display:inline;">
                        <button type="submit">Sign Up</button>
                    </form>
                <?php else: ?>
                    <form method="post" action="./upload/" style="display:inline;">
                        <button type="submit">Upload Videos</button>
                    </form>
                    <form method="post" action="./logout.php" style="display:inline;">
                        <button type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </nav>

            <?php if ($welcomeMessage): ?>
                <p class="welcome-message"><?php echo htmlspecialchars($welcomeMessage); ?></p>
            <?php endif; ?>
        </header>

        <main>
            <div class="film-gallery">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                        // Fetch the uploader's username
                        $uploader_id = $row['uploader_id'];
                        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("i", $uploader_id);
                            $stmt->execute();
                            $stmt->bind_result($uploader_username);
                            $stmt->fetch();
                            $stmt->close();
                        }
                        ?>
                        <div class="film-card">
                            <a href="./watch/?v=<?php echo urlencode($row['id']); ?>" class="film-link">
                                <div class="video-container">
                                    <!-- Display thumbnail if available -->
                                    <?php if (!empty($row['thumbnail_path'])): ?>
                                        <img src="./upload/uploads/thumbnails/<?php echo htmlspecialchars($row['thumbnail_path']); ?>" alt="Thumbnail" class="thumbnail">
                                    <?php endif; ?>
                                    <video autoplay muted>
                                        <source src="./upload/uploads/<?php echo htmlspecialchars($row['file_path']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <button class="unmute-button">Unmute</button>
                                    <div class="progress-container">
                                        <div class="progress-bar"></div>
                                    </div>
                                </div>
                                <div class="film-info">
                                    <h3><?php echo htmlspecialchars($row['title']); ?></h3><br>
                                    <small>Uploaded by: <?php echo htmlspecialchars($uploader_username); ?></small><br>
                                    <small>Uploaded on: <?php echo date('F j, Y', strtotime($row['upload_date'])); ?></small>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No films have been uploaded yet.</p>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Film & Music Hub</p>
        </footer>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const videoContainers = document.querySelectorAll('.video-container');

        videoContainers.forEach(container => {
            const video = container.querySelector('video');
            const unmuteButton = container.querySelector('.unmute-button');
            const progressBar = container.querySelector('.progress-bar');
            const progressContainer = container.querySelector('.progress-container');

            // Play video on hover
            container.addEventListener('mouseover', function () {
                video.play();
            });

            // Pause video on mouse out
            container.addEventListener('mouseout', function () {
                video.pause();
            });

            // Toggle mute/unmute and stop event propagation
            unmuteButton.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent link navigation
                video.muted = !video.muted;
                unmuteButton.textContent = video.muted ? 'Unmute' : 'Mute';
            });

            // Update progress bar as video plays
            video.addEventListener('timeupdate', function () {
                const progress = (video.currentTime / video.duration) * 100;
                progressBar.style.width = progress + '%';
            });

            // Seek video on progress bar click and stop event propagation
            progressContainer.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent link navigation
                const rect = progressContainer.getBoundingClientRect();
                const offsetX = event.clientX - rect.left;
                const totalWidth = rect.width;
                const percentage = offsetX / totalWidth;
                video.currentTime = video.duration * percentage;
            });
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>
