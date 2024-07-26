<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_config.php';

function sanitize($input)
{
    global $conn;
    return $conn->real_escape_string($input);
}

if (isset($_GET['filters'])) {
    $filters = json_decode($_GET['filters'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Invalid JSON input', 'details' => json_last_error_msg()]);
        exit();
    }

    $sql = "SELECT d.*, dv.*
            FROM document_versions dv
            JOIN documents d ON dv.document_id = d.id
            LEFT JOIN keywords k ON d.id = k.documentId
            WHERE dv.version_number = (
                SELECT MAX(version_number)
                FROM document_versions
                WHERE document_id = dv.document_id
            )";

    if (!empty($filters['keywords'])) {
        $keywordConditions = [];
        foreach ($filters['keywords'] as $keyword) {
            $keyword = sanitize($keyword);
            $keywordConditions[] = "k.keyword LIKE ?";
        }
        $sql .= " AND (" . implode(" OR ", $keywordConditions) . ")";
    }

    if (!empty($filters['format'])) {
        $format = sanitize($filters['format']);
        $sql .= " AND d.format = ?";
    }

    if (!empty($filters['version']) && $filters['version'] !== 'all') {
        if ($filters['version'] !== 'latest') {
            $version = sanitize($filters['version']);
            $sql .= " AND dv.version_number = ?";
        }
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['error' => 'SQL Prepare Error', 'details' => $conn->error]);
        exit();
    }

    $params = [];
    $types = '';

    if (!empty($filters['keywords'])) {
        foreach ($filters['keywords'] as $keyword) {
            $params[] = "%$keyword%";
            $types .= 's';
        }
    }

    if (!empty($filters['format'])) {
        $params[] = $filters['format'];
        $types .= 's';
    }

    if (!empty($filters['version']) && $filters['version'] !== 'all' && $filters['version'] !== 'latest') {
        $params[] = $filters['version'];
        $types .= 's';
    }

    if ($types) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    $stmt->close();

    header('Content-Type: application/json');
    echo json_encode($results);
} else {
    echo json_encode(['error' => 'No filters provided']);
}
?>