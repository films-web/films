<?php
// Start the session
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
    die("Connection failed: " . $conn->connect_error);
}

// Get the video ID from the query string
$video_id = isset($_GET['v']) ? intval($_GET['v']) : 0;

if ($video_id <= 0) {
    echo "Invalid video ID.";
    exit;
}

// Fetch video details from the database
$stmt = $conn->prepare("SELECT title, file_path, thumbnail_path, description, upload_date, uploader_id FROM films WHERE id = ?");
$stmt->bind_param("i", $video_id);
$stmt->execute();
$stmt->bind_result($title, $file_path, $thumbnail_path, $description, $upload_date, $uploader_id);
$stmt->fetch();
$stmt->close();

// If no video found
if (!$title) {
    echo "Video not found.";
    exit;
}

// Fetch the uploader's username
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $uploader_id);
$stmt->execute();
$stmt->bind_result($uploader_username);
$stmt->fetch();
$stmt->close();

// Fetch comments for the video
function getComments($conn, $video_id) {
    $stmt = $conn->prepare("SELECT id, username, comment, created_at, edited_at FROM comments WHERE video_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
    return $comments;
}

$comments = getComments($conn, $video_id);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo "User not logged in.";
        exit;
    }

    // Handle comment submission
    if (isset($_POST['comment']) && !empty(trim($_POST['comment']))) {
        $comment = trim($_POST['comment']);
        $username = $_SESSION['username']; // Assuming you store the username in the session

        $stmt = $conn->prepare("INSERT INTO comments (video_id, username, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $video_id, $username, $comment);

        if ($stmt->execute()) {
            header("Location: ../watch?v=" . $video_id); // Redirect to avoid form resubmission
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Comment cannot be empty.";
    }
}

// Handle comment edit and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['username'])) {
        echo "User not logged in.";
        exit;
    }

    $comment_id = intval($_POST['comment_id']);
    $action = $_POST['action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
        $stmt->bind_param("is", $comment_id, $_SESSION['username']);
        if ($stmt->execute()) {
            header("Location: ../watch?v=" . $video_id); // Redirect to avoid form resubmission
            exit;
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'edit') {
        if (isset($_POST['new_comment']) && !empty(trim($_POST['new_comment']))) {
            $new_comment = trim($_POST['new_comment']);
            $stmt = $conn->prepare("UPDATE comments SET comment = ?, edited_at = NOW() WHERE id = ? AND username = ?");
            $stmt->bind_param("sis", $new_comment, $comment_id, $_SESSION['username']);
            if ($stmt->execute()) {
                header("Location: ../watch?v=" . $video_id); // Redirect to avoid form resubmission
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Comment cannot be empty.";
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Ensure the video container is always visible */
/* Ensure the video container is always visible */
.video-container {
    position: relative;
    max-width: 100%;
    margin: auto;
    background: black; /* Black background for video container */
}

video {
    width: 100%;
    height: auto;
}

.video-info {
    margin-top: 10px;
    color: white; /* Adjust text color to ensure readability on black background */
}

.video-info h2 {
    margin: 0;
}

.video-info small {
    color: #ccc; /* Light color for additional info */
}

/* Styling for the thumbnail container */
.thumbnail-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url("../upload/uploads/thumbnails/<?php echo htmlspecialchars($thumbnail_path); ?>") no-repeat center center;
    background-size: cover;
    display: block;
    z-index: 1;
}

.video-container video {
    position: relative;
    z-index: 2;
}

/* Comments section */
.comments-section {
    margin-top: 20px;
    background: #222; /* Dark background for comments section */
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3); /* Slightly darker shadow for better contrast */
    color: white; /* Adjust text color for readability on dark background */
}

.comments-section h3 {
    margin-top: 0;
    font-size: 1.5em;
    color: #ddd; /* Light color for section title */
}

.comment {
    border: 1px solid #444; /* Darker border color for better contrast */
    padding: 15px;
    margin-bottom: 15px;
    position: relative;
    background: #333; /* Darker background for individual comments */
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    transition: background 0.3s, box-shadow 0.3s;
}

.comment:hover {
    background: #444; /* Slightly lighter background on hover */
    box-shadow: 0 6px 12px rgba(0,0,0,0.4);
}

.comment strong {
    display: block;
    font-size: 1.2em;
    color: #eee; /* Light color for usernames */
}

.comment p {
    margin: 10px 0;
    color: #ddd; /* Light color for comment text */
}

.comment small {
    color: #aaa; /* Light gray for timestamps */
}

.comment .actions {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    align-items: center;
}

.comment .actions button {
    background: none;
    border: 2px solid #007BFF; /* Border color */
    color: #007BFF;
    cursor: pointer;
    font-size: 1em;
    margin-right: 10px;
    padding: 5px 10px;
    border-radius: 5px;
    transition: background 0.3s, color 0.3s, transform 0.3s;
}

.comment .actions button:hover {
    background: #007BFF;
    color: white;
    text-decoration: none;
    transform: scale(1.05); /* Slight zoom effect */
}

.comment .actions button:active {
    transform: scale(0.95); /* Slight shrink effect */
}


.edit-form {
    display: none;
    margin-top: 10px;
    background: #333;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    transition: opacity 0.3s ease;
}

.edit-form.show {
    display: block;
    opacity: 1;
}

.edit-form.hide {
    opacity: 0;
    display: none;
}

/* Form buttons styling */
.comments-section form button {
    background-color: #007BFF;
    color: white;
    border: 2px solid #007BFF; /* Matching border color */
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s, color 0.3s, transform 0.3s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Light shadow for depth */
}

.comments-section form button:hover {
    background-color: #0056b3;
    color: white;
    transform: scale(1.05); /* Slight zoom effect */
}

.comments-section form button:active {
    background-color: #004494;
    transform: scale(0.95); /* Slight shrink effect */
}

/* Enhanced textarea styling */
.comments-section form textarea {
    width: 100%;
    height: 80px;
    margin-bottom: 10px;
    padding: 10px;
    border: 2px solid #444; /* Darker border for better contrast */
    border-radius: 5px;
    box-sizing: border-box;
    font-size: 1em;
    color: #eee; /* Light color for textarea text */
    background: #222; /* Dark background for textarea */
    transition: border-color 0.3s, box-shadow 0.3s;
}

.comments-section form textarea:focus {
    border-color: #007BFF; /* Highlight border color */
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5); /* Subtle glow effect */
}
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <nav>
                <form method="post" action="../" style="display:inline;">
                    <button type="submit">Home</button>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" action="../logout.php" style="display:inline;">
                        <button type="submit">Logout</button>
                    </form>
                </nav>
            <?php endif; ?>
        </header>

        <main>
            <div class="video-container">
                <div class="thumbnail-container"></div>
                <video id="videoPlayer" controls autoplay>
                    <source src="../upload/uploads/<?php echo htmlspecialchars($file_path); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            <div class="video-info">
                <h2><?php echo htmlspecialchars($title); ?></h2>
                <small>Uploaded by: <?php echo htmlspecialchars($uploader_username); ?></small><br>
                <small>Uploaded on: <?php echo date('F j, Y', strtotime($upload_date)); ?></small><br>
                <p><?php echo htmlspecialchars($description); ?></p>
            </div>

            <!-- Comments Section -->
            <div class="comments-section">
                <h3>Comments:</h3>
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <small>Posted on: <?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?></small>
                            <?php if ($comment['edited_at']): ?>
                                <small><em>(Edited on: <?php echo date('F j, Y g:i A', strtotime($comment['edited_at'])); ?>)</em></small>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === $comment['username']): ?>
                                <div class="actions">
                                    <button onclick="toggleEditForm(<?php echo $comment['id']; ?>)">Edit</button>
                                    <form method="post" style="display:inline;" action="">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit">Delete</button>
                                    </form>
                                </div>
                                <div id="editForm<?php echo $comment['id']; ?>" class="edit-form hide">
                                    <form method="post" action="">
                                        <textarea name="new_comment" placeholder="Edit your comment..."><?php echo htmlspecialchars($comment['comment']); ?></textarea>
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <input type="hidden" name="action" value="edit">
                                        <button type="submit">Save</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No comments yet.</p>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <h4>Add a Comment:</h4>
                    <form method="post" action="">
                        <textarea name="comment" required placeholder="Write your comment..."></textarea>
                        <button type="submit">Submit</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="../login.php">log in</a> to add a comment.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

<script>
        function toggleEditForm(commentId) {
            const editForm = document.getElementById('editForm' + commentId);
            if (editForm.classList.contains('show')) {
                editForm.classList.remove('show');
                editForm.classList.add('hide');
            } else {
                editForm.classList.remove('hide');
                editForm.classList.add('show');
            }
        }
    </script>
</body>
</html>


