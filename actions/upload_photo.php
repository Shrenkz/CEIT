<?php
session_start();
include 'db_config.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
        $fileName = $_FILES['profile_photo']['name'];
        $fileSize = $_FILES['profile_photo']['size'];
        $fileType = $_FILES['profile_photo']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedExts = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($fileExtension, $allowedExts)) {
            $uploadDir = dirname(__DIR__) . '/uploads/profile_photos/';
            $newFileName = $user_id . '.' . $fileExtension;
            $uploadFileDir = $uploadDir . $newFileName;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move the file
            if (move_uploaded_file($fileTmpPath, $uploadFileDir)) {
                $sql = "UPDATE users SET profile_photo = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $newFileName, $user_id);

                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'newPhotoUrl' => 'uploads/profile_photos/' . $newFileName]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating profile photo.']);
                }

                $stmt->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error uploading the file.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
}

$conn->close();
?>
