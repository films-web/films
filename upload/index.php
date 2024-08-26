<?php
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and process input
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $file = $_FILES['film'];
    $thumbnail = $_FILES['thumbnail'];

    $video_dir = "./uploads/";
    $thumbnail_dir = "./uploads/thumbnails/";

    $file_name = basename($file["name"]);
    $target_file = $video_dir . $file_name;
    $thumbnail_name = basename($thumbnail["name"]);
    $thumbnail_file = $thumbnail_dir . $thumbnail_name;

    $uploadOk = 1;
    $videoFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $thumbnailFileType = strtolower(pathinfo($thumbnail_file, PATHINFO_EXTENSION));

    // Check if file is a valid video format
    $allowed_video_types = ["mp4", "avi", "mov", "wmv"];
    if (!in_array($videoFileType, $allowed_video_types)) {
        $upload_message = "Sorry, only MP4, AVI, MOV, and WMV files are allowed.";
        $uploadOk = 0;
    }

    // Check if thumbnail is a valid image format
    $allowed_image_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($thumbnailFileType, $allowed_image_types)) {
        $upload_message = "Sorry, only JPG, JPEG, PNG, and GIF files are allowed for thumbnails.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => $upload_message]);
        exit;
    } else {
        // Attempt to move uploaded files
        if (move_uploaded_file($file["tmp_name"], $target_file) && move_uploaded_file($thumbnail["tmp_name"], $thumbnail_file)) {
            // Insert film data into the database
            $stmt = $conn->prepare("INSERT INTO films (title, description, file_path, thumbnail_path, upload_date, uploader_id) VALUES (?, ?, ?, ?, NOW(), ?)");
            if (!$stmt) {
                header('Content-Type: application/json');
                echo json_encode(["status" => "error", "message" => "SQL prepare failed: " . $conn->error]);
                exit;
            }

            $stmt->bind_param("ssssi", $title, $description, $file_name, $thumbnail_name, $_SESSION['user_id']);
            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(["status" => "success", "message" => "The file " . htmlspecialchars($file_name) . " and thumbnail have been uploaded."]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(["status" => "error", "message" => "Error uploading file: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            header('Content-Type: application/json');
            echo json_encode(["status" => "error", "message" => "Sorry, there was an error uploading your file."]);
        }
    }

    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload a Film/Music</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <form method="post" action="../" style="display:inline;">
                    <button type="submit">Home</button>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" action="../logout.php" style="display:inline;">
                        <button type="submit">Logout</button>
                    </form>
                <?php endif; ?>
            </nav>
            <h1>Upload a Film/Music</h1>
        </header>

        <main>
            <div class="upload-form">
                <div id="upload-message"></div>

                <form id="upload-form" method="post" enctype="multipart/form-data">
                    <label for="title">Film/Music Title:</label>
                    <input type="text" id="title" name="title" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>

                    <label for="film">Select Film/Music:</label>
                    <input type="file" id="film" name="film" accept="video/*" required>

                    <label for="thumbnail">Select Thumbnail:</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>

                    <button type="submit">Upload</button>
                </form>

                <!-- Progress bar -->
                <div id="progress-container">
                    <div id="progress-bar"></div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Film & Music Hub</p>
        </footer>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("upload-form");
    const progressBar = document.getElementById("progress-bar");
    const uploadMessage = document.getElementById("upload-message");
    const videoInput = document.getElementById("film");

    form.addEventListener("submit", function(event) {
        event.preventDefault(); // Prevent default form submission

        const file = videoInput.files[0];
        if (file) {
            const videoElement = document.createElement('video');
            videoElement.preload = 'metadata';
            videoElement.src = URL.createObjectURL(file);

            videoElement.addEventListener('loadedmetadata', function() {
                URL.revokeObjectURL(videoElement.src); // Free memory
                const duration = videoElement.duration;

                // Duration requirement
                const minDuration = 60; // 1 minute in seconds

                if (duration < minDuration) {
                    uploadMessage.innerHTML = `<p class="error">The video is too short. Minimum duration is ${minDuration} seconds.</p>`;
                    return;
                }

                // Proceed with the upload if duration is valid
                const formData = new FormData(form);
                const xhr = new XMLHttpRequest();
                xhr.open("POST", "", true);

                xhr.upload.addEventListener("progress", function(event) {
                    if (event.lengthComputable) {
                        const percentComplete = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = percentComplete + "%";
                        progressBar.textContent = percentComplete + "%";
                    }
                });

                xhr.addEventListener("load", function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === "success") {
                                uploadMessage.innerHTML = `<p class="success">${response.message}</p>`;
                                form.reset();
                            } else {
                                uploadMessage.innerHTML = `<p class="error">${response.message}</p>`;
                            }
                            progressBar.style.width = "0%";
                            progressBar.textContent = "";
                        } catch (e) {
                            console.error("Failed to parse JSON:", e);
                            uploadMessage.innerHTML = `<p class="error">An error occurred while processing the response.</p>`;
                        }
                    } else {
                        uploadMessage.innerHTML = `<p class="error">Upload failed. Please try again.</p>`;
                    }
                });

                xhr.send(formData);
            });
        }
    });
});
</script>
</body>
</html>
