<?php
session_start();
include 'db_config.php'; // Ensure this file sets up the $conn variable

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    error_log("Session user_id: $user_id"); // Debugging: Log the user ID from the session

    // Fetch user details based on the logged-in user's ID
    $sql_user = "SELECT u.fname, u.mname, u.lname, u.email, u.role, 
                    s.student_number, s.program, s.department, u.profile_photo
                    FROM users u
                    LEFT JOIN users_student s ON u.id = s.userId
                    WHERE u.id = ?";

    $stmt_user = $conn->prepare($sql_user);

    if ($stmt_user) {
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();

        // Fetch the user details
        if ($result_user->num_rows === 1) {
            $user = $result_user->fetch_assoc();
            error_log("Fetched user details for ID: $user_id"); // Debugging: Log the fetched user details

            // Check if the user's role is Faculty
            if ($user['role'] === 'Faculty') {
                // Fetch additional details from the users_faculty table
                $sql_faculty = "SELECT department FROM users_faculty WHERE userId = ?";
                $stmt_faculty = $conn->prepare($sql_faculty);

                if ($stmt_faculty) {
                    $stmt_faculty->bind_param("i", $user_id);
                    $stmt_faculty->execute();
                    $result_faculty = $stmt_faculty->get_result();

                    // Fetch the faculty details
                    if ($result_faculty->num_rows === 1) {
                        $faculty = $result_faculty->fetch_assoc();
                        $user['faculty_department'] = $faculty['department'];
                        error_log("Fetched faculty details for ID: $user_id"); // Debugging: Log the fetched faculty details
                    } else {
                        error_log("Faculty information for user ID $user_id not found.");
                        $user['faculty_department'] = 'N/A';
                    }

                    $stmt_faculty->close();
                } else {
                    error_log("Failed to prepare SQL statement for faculty: " . $conn->error);
                    echo "Error preparing the SQL statement for faculty.";
                    exit();
                }
            }

            // Fetch pinned documents for the user
            $sql_pinned_docs = "SELECT d.id, d.title, d.description, d.format, dv.version_number AS version
                                FROM pin_documents pd
                                JOIN documents d ON pd.documentId = d.id
                                LEFT JOIN (
                                    SELECT document_id, MAX(version_number) AS version_number
                                    FROM document_versions
                                    GROUP BY document_id
                                ) dv ON d.id = dv.document_id
                                WHERE pd.userId = ?";
            $stmt_pinned_docs = $conn->prepare($sql_pinned_docs);

            if ($stmt_pinned_docs) {
                $stmt_pinned_docs->bind_param("i", $user_id);
                $stmt_pinned_docs->execute();
                $result_pinned_docs = $stmt_pinned_docs->get_result();

                $pinned_documents = [];
                if ($result_pinned_docs->num_rows > 0) {
                    while ($row = $result_pinned_docs->fetch_assoc()) {
                        $pinned_documents[] = $row;
                    }
                }
                $stmt_pinned_docs->close();
            } else {
                error_log("Failed to prepare SQL statement for pinned documents: " . $conn->error);
                echo "Error preparing the SQL statement for pinned documents.";
                exit();
            }

        } else {
            error_log("User with ID $user_id not found.");
            header("Location: login.php");
            exit();
        }

        $stmt_user->close();
    } else {
        error_log("Failed to prepare SQL statement: " . $conn->error);
        echo "Error preparing the SQL statement.";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-photo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .profile-photo-img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 2px solid #4CAF50;
        }

        h2 {
            color: #333;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        strong {
            color: #4CAF50;
        }

        .pinned-documents {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .document-item {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .document-item h4 {
            margin: 0;
            color: #333;
        }

        .result-buttons {
            margin-top: 10px;
        }

        .result-buttons button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .result-buttons button:hover {
            background-color: #45a049;
        }

        .unpin-btn {
            background-color: #f44336;
        }

        .unpin-btn:hover {
            background-color: #d32f2f;
        }

        .document-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="logo">
            <img src="logo.png" alt="Logo">
            <span>CEIT e-Guidelines</span>
        </div>
        <ul class="navbar-list">
            <li class="navbar-item active"><a href="index.php">Home</a></li>
            <li class="navbar-item"><a href="#">Forms</a></li>
            <li class="navbar-item"><a href="profile.php">Profile</a></li>
            <li class="navbar-item"><a href="#">About</a></li>
            <li class="navbar-item"><a href="logout.php" id="logoutBtn">Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2>Profile</h2>
        <?php if (isset($user)): ?>
            <div class="profile-photo">
                <?php
                $profilePhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.jpg';
                ?>
                <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Profile Photo" class="profile-photo-img">
            </div>
            <p><strong>Full Name:</strong>
                <?php echo htmlspecialchars($user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
            <?php if ($user['role'] === 'Faculty'): ?>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($user['faculty_department']); ?></p>
            <?php elseif (!empty($user['student_number'])): ?>
                <p><strong>Student Number:</strong> <?php echo htmlspecialchars($user['student_number']); ?></p>
                <p><strong>Program:</strong> <?php echo htmlspecialchars($user['program']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department']); ?></p>
            <?php else: ?>
                <p>No student information available.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Profile information not available.</p>
        <?php endif; ?>
    </div>

    <div class="container">
        <h3>Pinned Documents</h3>
        <?php if (!empty($pinned_documents)): ?>
            <div class="pinned-documents">
                <?php foreach ($pinned_documents as $doc): ?>
                    <div class="document-item">
                        <h4><?php echo htmlspecialchars($doc['title']); ?></h4>
                        <p><?php echo htmlspecialchars($doc['description']); ?></p>
                        <p><small>Version: <?php echo htmlspecialchars($doc['version']); ?></small></p>
                        <p><small>Format: <?php echo htmlspecialchars($doc['format']); ?></small></p>
                        <div class="result-buttons">
                            <button class="unpin-btn" data-id="<?php echo htmlspecialchars($doc['id']); ?>">Unpin from
                                Profile</button>
                            <button class="view-btn" data-id="<?php echo htmlspecialchars($doc['id']); ?>">View</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No pinned documents available.</p>
        <?php endif; ?>
    </div>


    <script src="profile_script.js"></script>
</body>

</html>