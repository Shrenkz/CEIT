<?php
session_start();
include './actions/db_config.php';

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

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="./css/styles.css">
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
            margin: 0;
            margin-bottom: 10px;
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
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        }

        .unpin-btn {
            background-color: #fe6f6f;
        }

        .unpin-btn:hover {
            background-color: #f10c0c;
        }

        .document-item:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .custom-alert {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .custom-alert.hidden {
            display: none;
        }

        .custom-alert-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .custom-alert .button-container {
            display: flex;
            justify-content: flex-end;
        }

        .custom-alert button {
            margin-left: 10px;
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
            <li class="navbar-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><a
                    href="index.php">Home</a></li>
            <li class="navbar-item <?php echo $current_page == 'forms.php' ? 'active' : ''; ?>"><a
                    href="forms.php">Forms</a></li>
            <li class="navbar-item <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>"><a
                    href="profile.php">Profile</a></li>
            <li class="navbar-item <?php echo $current_page == 'about.php' ? 'active' : ''; ?>"><a
                    href="about.php">About</a></li>
            <li class="navbar-item"><a href="./actions/logout.php" id="logoutBtn">Logout</a></li>
        </ul>
    </div>
    <div class="container">
        <h2>Profile</h2>
        <?php if (isset($user)): ?>
            <?php
            $profilePhotoPath = 'uploads/profile_photos/' . (!empty($user['profile_photo']) ? $user['profile_photo'] : 'default.png');
            ?>
            <div class="profile-photo">
                <a href="javascript:void(0);" onclick="document.getElementById('photoUpload').click();">
                    <img src="<?php echo htmlspecialchars($profilePhotoPath); ?>" alt="Profile Photo"
                        class="profile-photo-img">
                </a>
                <form id="photoForm" action="./actions/upload_photo.php" method="post" enctype="multipart/form-data"
                    style="display: none;">
                    <input type="file" name="profile_photo" id="photoUpload" onchange="this.form.submit();"
                        accept="image/*">
                </form>
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

    <div id="customAlert" class="custom-alert hidden">
        <div class="custom-alert-content">
            <h2 id="alertMessage"></h2>
            <div class="button-container">
                <button id="alertYesBtn">Yes</button>
                <button id="alertNoBtn" class="btn-secondary">No</button>
            </div>
        </div>
    </div>

    <script src="./scripts/profile_script.js"></script>

</body>

</html>