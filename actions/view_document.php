<?php
session_start(); // Ensure session is started
include 'db_config.php';

// Check if document ID is provided
if (!isset($_GET['id'])) {
    echo "No document ID provided.";
    exit;
}

// Get the document ID from the URL
$document_id = intval($_GET['id']);

// Fetch the document details from the database
$sql = "SELECT d.*, dv.version_number, dv.content, dv.created_at 
        FROM documents d 
        LEFT JOIN document_versions dv ON d.id = dv.document_id 
        WHERE d.id = ? 
        ORDER BY dv.version_number DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $document_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $document = $result->fetch_assoc();
    } else {
        echo "Document not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Failed to prepare the SQL statement.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Document</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 20px auto;
        }

        .document-header {
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .document-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }

        .document-description {
            font-style: italic;
            color: #555;
        }

        .document-meta p {
            margin: 5px 0;
            color: #666;
        }

        .document-content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .pdf-viewer {
            margin-top: 20px;
        }

        .button-container {
            margin-top: 20px;
            text-align: right;
        }

        .button-container .btn {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            color: white;
            margin-left: 10px;
            display: inline-block;
        }

        .button-container .btn-primary {
            background-color: #4CAF50;
        }

        .button-container .btn-primary:hover {
            background-color: #45a049;
        }

        .button-container .btn-secondary {
            background-color: #2196F3;
        }

        .button-container .btn-secondary:hover {
            background-color: #0b7dda;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <img src="../logo.png" alt="Logo">
            <span>CEIT e-Guidelines</span>
        </div>
        <ul class="navbar-list">
            <li class="navbar-item"><a href="../index.php">Home</a></li>
            <li class="navbar-item"><a href="#">Forms</a></li>
            <li class="navbar-item"><a href="../profile.php">Profile</a></li>
            <li class="navbar-item"><a href="#">About</a></li>
            <li class="navbar-item"><a href="logout.php" id="logoutBtn">Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <div class="document-header">
            <h1><?php echo htmlspecialchars($document['title']); ?></h1>
            <p class="document-description"><?php echo htmlspecialchars($document['description']); ?></p>
            <div class="document-meta">
                <p><strong>Version:</strong> <?php echo htmlspecialchars($document['version_number']); ?></p>
                <p><strong>Created At:</strong> <?php echo htmlspecialchars($document['created_at']); ?></p>
            </div>
        </div>
        <div class="button-container">
            <a href="../index.php" class="btn btn-primary">Back to Search</a>
            <a href="download.php?file=<?php echo urlencode($document['content']); ?>"
                class="btn btn-secondary">Download</a>
        </div>
        <!-- <div class="document-content">
            <?php echo nl2br(htmlspecialchars($document['content'])); ?>
        </div> -->
        <div class="pdf-viewer">
            <iframe src="<?php echo htmlspecialchars($document['content']); ?>" width="100%" height="700px"></iframe>
        </div>
    </div>
</body>

</html>