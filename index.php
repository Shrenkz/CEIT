<?php
session_start();
include 'db_config.php';

// Initialize variables
$searchQuery = '';
$results = [];
$resultCount = 0;

// Function to sanitize input to prevent SQL injection
function sanitize($input)
{
    global $conn;
    return $conn->real_escape_string($input);
}

// Capture advanced search inputs
$format = isset($_GET['format']) ? $_GET['format'] : '';
$version = isset($_GET['version']) ? $_GET['version'] : 'all';
$keywords = isset($_GET['keywords']) ? (array) $_GET['keywords'] : [];

// Check if the search form is submitted
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = sanitize(trim($_GET['search']));

    // Get the logged-in user's role
    $user_role = '';
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql_user = "SELECT role FROM users WHERE id = ?";
        $stmt_user = $conn->prepare($sql_user);
        if ($stmt_user) {
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();
            if ($result_user->num_rows === 1) {
                $user_row = $result_user->fetch_assoc();
                $user_role = $user_row['role'];
            }
            $stmt_user->close();
        }
    }

    // Base query
    $sql_base = "SELECT d.*, dv.version_number as version FROM documents d
    JOIN level_of_access l ON d.id = l.documentId
    LEFT JOIN (
        SELECT document_id, MAX(version_number) as version_number
        FROM document_versions
        GROUP BY document_Id
    ) dv ON d.id = dv.document_Id
    WHERE d.title LIKE ?";

    // Add advanced search filters
    if (!empty($format)) {
        $sql_base .= " AND d.format = ?";
    }

    if ($version === 'latest') {
        $sql_base .= " AND dv.version_number = (SELECT MAX(version_number) FROM document_versions WHERE document_id = d.id)";
    } else {
        $sql_base = str_replace(" AND dv.version_number = (SELECT MAX(version_number) FROM document_versions WHERE document_id = d.id)", "", $sql_base);
    }

    if ($user_role !== 'Both') {
        $sql_base .= " AND l.access = ?";
    } else {
        $sql_base .= " AND (l.access = 'Both' OR l.access = ?)";
    }

    if (!empty($keywords)) {
        foreach ($keywords as $keyword) {
            $sql_base .= " AND d.title LIKE ?";
        }
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql_base);

    if ($stmt) {
        $searchPattern = "%$searchQuery%";
        $params = [$searchPattern];
        $types = 's';

        if (!empty($format)) {
            $params[] = $format;
            $types .= 's';
        }

        if ($user_role !== 'Both') {
            $params[] = $user_role;
            $types .= 's';
        } else {
            $params[] = 'Both';
            $types .= 's';
        }

        if (!empty($keywords)) {
            foreach ($keywords as $keyword) {
                $params[] = "%$keyword%";
                $types .= 's';
            }
        }

        // Bind the parameters dynamically
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            $resultCount = count($results);
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
    <title>Search Results</title>
    <link rel="stylesheet" href="styles.css">
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
    <div class="main-content">
        <div class="container">
            <form method="GET" action="index.php" class="search-bar">
                <h4>Search</h4>
                <input type="text" name="search" placeholder="Type something..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button type="submit" id="searchBtn">Submit</button>
                <a href="#" id="advancedSearchBtn">Advanced Search</a>
                <div id="advancedSearchModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h3>Advanced Search</h3>
                        <div id="keywordContainer">
                            <label for="keyword1">Keyword</label>
                            <input type="text" id="keyword1" name="keywords[]" placeholder="Keyword"
                                style="max-width: 96.4%;">
                        </div>
                        <button type="button" id="addKeywordBtn">Add Keyword</button>
                        <label for="format">Format</label>
                        <select id="format" name="format">
                            <option value="">All Formats</option>
                            <option value="pdf">PDF</option>
                            <option value="image">Image</option>
                            <option value="text">Text</option>
                        </select>
                        <label for="version">Version</label>
                        <select id="version" name="version">
                            <option value="all">All Versions</option>
                            <option value="latest">Latest</option>
                        </select>
                        <div class="button-container">
                            <button type="button" id="applyFilters">Apply Filters</button>
                        </div>
                    </div>
                </div>
            </form>
            <div id="resultsContainer">
                <div class="result-count">
                    <?php
                    if (!empty($searchQuery) || !empty($keywords) || !empty($format) || $version !== 'all') {
                        $queryString = htmlspecialchars($searchQuery);
                        if (!empty($keywords)) {
                            $queryString .= " " . implode(" ", array_map('htmlspecialchars', $keywords));
                        }
                        if (!empty($format)) {
                            $queryString .= " Format: " . htmlspecialchars($format);
                        }
                        if ($version !== 'all') {
                            $queryString .= " Version: " . htmlspecialchars($version);
                        }
                        echo $resultCount > 0 ? "Found $resultCount result(s) for \"$queryString\"" : "No results found for \"$queryString\".";
                    } else {
                        echo "Please enter a search query.";
                    }
                    ?>
                </div>
                <div id="results">
                    <?php if (!empty($results)): ?>
                        <div class="sort-results">Sort Results</div>
                        <?php foreach ($results as $result): ?>
                            <div class="result-item">
                                <h4><?php echo htmlspecialchars($result['title']); ?></h4>
                                <p><?php echo htmlspecialchars($result['description']); ?></p>
                                <p><small>Version: <?php echo htmlspecialchars($result['version']); ?></small></p>
                                <p><small>Format: <?php echo htmlspecialchars($result['format']); ?></small></p>
                                <div class="result-buttons">
                                    <button class="pin-btn"
                                        data-document-id="<?php echo htmlspecialchars($result['id']); ?>">Pin to
                                        Profile</button>
                                    <button class="view-btn"
                                        data-id="<?php echo htmlspecialchars($result['id']); ?>">View</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="frequently-accessed">
            <h4>Frequently Accessed Documents</h4>
            <ul>
                <li><a href="#">Document 1</a></li>
                <li><a href="#">Document 2</a></li>
                <li><a href="#">Document 3</a></li>
                <li><a href="#">Document 4</a></li>
                <li><a href="#">Document 5</a></li>
            </ul>
        </div>
    </div>

    <div id="customAlert" class="custom-alert hidden">
        <div class="custom-alert-content">
            <span id="alertMessage"></span>
            <button id="alertOkBtn">OK</button>
        </div>
    </div>

    <script src="scripts.js"></script>

    <input type="hidden" id="user-id" value="<?php echo $_SESSION['user_id']; ?>">

</body>

</html>