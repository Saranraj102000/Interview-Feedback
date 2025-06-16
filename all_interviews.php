<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "vdart_feedback";

// Initialize variables
$interviewsData = [];
$totalRows = 0;
$dbError = false;
$successMessage = '';
$errorMessage = '';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_interview'])) {
    $interviewId = (int)$_POST['interview_id'];
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // First, get interview details for confirmation
        $checkStmt = $conn->prepare("SELECT position, client_name, interview_date FROM feedback WHERE id = :id");
        $checkStmt->bindValue(':id', $interviewId, PDO::PARAM_INT);
        $checkStmt->execute();
        $interviewDetails = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($interviewDetails) {
            // Delete the interview
            $deleteStmt = $conn->prepare("DELETE FROM feedback WHERE id = :id");
            $deleteStmt->bindValue(':id', $interviewId, PDO::PARAM_INT);
            
            if ($deleteStmt->execute()) {
                $successMessage = "Interview record for {$interviewDetails['position']} at {$interviewDetails['client_name']} has been successfully deleted.";
                
                // Log the deletion (optional - you can add this to a separate audit log table)
                error_log("Interview deleted: ID {$interviewId}, Position: {$interviewDetails['position']}, Client: {$interviewDetails['client_name']}, Date: {$interviewDetails['interview_date']}");
            } else {
                $errorMessage = "Failed to delete the interview record. Please try again.";
            }
        } else {
            $errorMessage = "Interview record not found.";
        }
        
    } catch(PDOException $e) {
        $errorMessage = "Database error occurred while deleting the record.";
        error_log("Delete Error: " . $e->getMessage());
    }
}

// Pagination settings
$recordsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $recordsPerPage;

// Initialize filter values (copied from dashboard.php)
$filterPeriods = [
    'all' => 'All Time',
    'current_month' => 'Current Month',
    'last_month' => 'Last Month',
    'last_3months' => 'Last 3 Months',
    'last_6months' => 'Last 6 Months',
    'last_year' => 'Last Year'
];

// Default selected period
$selectedPeriod = isset($_GET['period']) && array_key_exists($_GET['period'], $filterPeriods) ? $_GET['period'] : 'all';

// Initialize advanced filter values
$filterGeo = isset($_GET['geo']) ? $_GET['geo'] : '';
$filterCandidateName = isset($_GET['candidate_name']) ? $_GET['candidate_name'] : '';
$filterApplicantId = isset($_GET['applicant_id']) ? $_GET['applicant_id'] : '';
$filterSystemIntegrator = isset($_GET['system_integrator']) ? $_GET['system_integrator'] : '';
$filterRecruiter = isset($_GET['recruiter_name']) ? $_GET['recruiter_name'] : '';
$filterClient = isset($_GET['client']) ? $_GET['client'] : '';
$filterPosition = isset($_GET['position']) ? $_GET['position'] : '';
$filterBusinessUnit = isset($_GET['business_unit']) ? $_GET['business_unit'] : '';
$filterStartDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$filterEndDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filterAvailabilityAsked = isset($_GET['availability_asked']) ? $_GET['availability_asked'] : '';
$filterConfidentResponses = isset($_GET['confident_responses']) ? $_GET['confident_responses'] : '';
$filterChallengingQuestions = isset($_GET['challenging_questions']) ? $_GET['challenging_questions'] : '';
$filterExcludeIncomplete = isset($_GET['exclude_incomplete']) ? $_GET['exclude_incomplete'] : '';

// Sort options
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'interview_date';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Prepare date filtering SQL based on selected period
$dateFilterSQL = "";
$whereClauseAdded = false;
$params = []; // To store all binding parameters

switch ($selectedPeriod) {
    case 'current_month':
        $dateFilterSQL = "WHERE interview_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        $whereClauseAdded = true;
        break;
    case 'last_month':
        $dateFilterSQL = "WHERE interview_date >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01') 
                        AND interview_date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
        $whereClauseAdded = true;
        break;
    case 'last_3months':
        $dateFilterSQL = "WHERE interview_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
        $whereClauseAdded = true;
        break;
    case 'last_6months':
        $dateFilterSQL = "WHERE interview_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
        $whereClauseAdded = true;
        break;
    case 'last_year':
        $dateFilterSQL = "WHERE interview_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
        $whereClauseAdded = true;
        break;
    default:
        $dateFilterSQL = ""; // All time
}

// Add custom date range if provided
if ($filterGeo) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND geo = :geo";
    } else {
        $dateFilterSQL = "WHERE geo = :geo";
        $whereClauseAdded = true;
    }
    $params[':geo'] = $filterGeo;
}

if ($filterCandidateName) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND candidate_name LIKE :candidate_name";
    } else {
        $dateFilterSQL = "WHERE candidate_name LIKE :candidate_name";
        $whereClauseAdded = true;
    }
    $params[':candidate_name'] = '%' . $filterCandidateName . '%';
}

if ($filterApplicantId) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND applicant_id = :applicant_id";
    } else {
        $dateFilterSQL = "WHERE applicant_id = :applicant_id";
        $whereClauseAdded = true;
    }
    $params[':applicant_id'] = $filterApplicantId;
}

if ($filterSystemIntegrator) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND system_integrator LIKE :system_integrator";
    } else {
        $dateFilterSQL = "WHERE system_integrator LIKE :system_integrator";
        $whereClauseAdded = true;
    }
    $params[':system_integrator'] = '%' . $filterSystemIntegrator . '%';
}

if ($filterRecruiter) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND recruiter_name LIKE :recruiter_name";
    } else {
        $dateFilterSQL = "WHERE recruiter_name LIKE :recruiter_name";
        $whereClauseAdded = true;
    }
    $params[':recruiter_name'] = '%' . $filterRecruiter . '%';
}

if ($filterStartDate && $filterEndDate) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND interview_date BETWEEN :start_date AND :end_date";
    } else {
        $dateFilterSQL = "WHERE interview_date BETWEEN :start_date AND :end_date";
        $whereClauseAdded = true;
    }
    $params[':start_date'] = $filterStartDate;
    $params[':end_date'] = $filterEndDate;
}

// Add additional advanced filters
if ($filterClient) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND client_name = :client";
    } else {
        $dateFilterSQL = "WHERE client_name = :client";
        $whereClauseAdded = true;
    }
    $params[':client'] = $filterClient;
}

if ($filterPosition) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND position = :position";
    } else {
        $dateFilterSQL = "WHERE position = :position";
        $whereClauseAdded = true;
    }
    $params[':position'] = $filterPosition;
}

if ($filterBusinessUnit) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND business_unit = :business_unit";
    } else {
        $dateFilterSQL = "WHERE business_unit = :business_unit";
        $whereClauseAdded = true;
    }
    $params[':business_unit'] = $filterBusinessUnit;
}

if ($filterAvailabilityAsked === 'yes') {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND asked_availability = 'yes'";
    } else {
        $dateFilterSQL = "WHERE asked_availability = 'yes'";
        $whereClauseAdded = true;
    }
}

if ($filterConfidentResponses === 'yes') {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND answered_confidently = 'yes'";
    } else {
        $dateFilterSQL = "WHERE answered_confidently = 'yes'";
        $whereClauseAdded = true;
    }
}

if ($filterChallengingQuestions === 'yes') {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND had_challenging_questions = 'yes'";
    } else {
        $dateFilterSQL = "WHERE had_challenging_questions = 'yes'";
        $whereClauseAdded = true;
    }
}

if ($filterExcludeIncomplete == '1') {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND client_questions IS NOT NULL AND client_questions != ''";
    } else {
        $dateFilterSQL = "WHERE client_questions IS NOT NULL AND client_questions != ''";
        $whereClauseAdded = true;
    }
}

// Function to generate class for availability and confidence indicators
function getStatusClass($value) {
    return $value == 'yes' ? 'bg-success' : 'bg-danger';
}

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M d, Y');
}

// Connect to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total number of records for pagination
    $countSQL = "
        SELECT COUNT(*) as total
        FROM feedback
        $dateFilterSQL
    ";
    
    $stmt = $conn->prepare($countSQL);
    
    // Bind all parameters for count query
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $totalRows = $stmt->fetchColumn();
    
    // Calculate total pages
    $totalPages = ceil($totalRows / $recordsPerPage);
    
    // Ensure page is within valid range
    if ($page < 1) $page = 1;
    if ($page > $totalPages && $totalPages > 0) $page = $totalPages;
    
    // Query to get interviews with pagination
    $interviewsSQL = "
    SELECT 
        id,
        interview_date,
        position,
        client_name,
        business_unit,
        geo,
        candidate_name,
        candidate_email,
        applicant_id,
        system_integrator,
        asked_availability,
        answered_confidently,
        had_challenging_questions,
        recruiter_name,
        employee_id,
        client_questions,
        TIMESTAMPDIFF(MINUTE, created_at, updated_at) as interview_duration
    FROM feedback
    $dateFilterSQL
    ORDER BY $sortField $sortOrder
    LIMIT :offset, :limit
";
    
    $stmt = $conn->prepare($interviewsSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    
    $stmt->execute();
    $interviewsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get client options for filter dropdown
    $clientSQL = "SELECT DISTINCT client_name FROM feedback WHERE client_name IS NOT NULL AND client_name != '' ORDER BY client_name";
    $stmt = $conn->prepare($clientSQL);
    $stmt->execute();
    $clientOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get position options for filter dropdown
    $positionSQL = "SELECT DISTINCT position FROM feedback WHERE position IS NOT NULL AND position != '' ORDER BY position";
    $stmt = $conn->prepare($positionSQL);
    $stmt->execute();
    $positionOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get business unit options for filter dropdown
    $businessUnitSQL = "SELECT DISTINCT business_unit FROM feedback WHERE business_unit IS NOT NULL AND business_unit != '' ORDER BY business_unit";
    $stmt = $conn->prepare($businessUnitSQL);
    $stmt->execute();
    $businessUnitOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get GEO options for filter dropdown
    $geoSQL = "SELECT DISTINCT geo FROM feedback WHERE geo IS NOT NULL AND geo != '' ORDER BY geo";
    $stmt = $conn->prepare($geoSQL);
    $stmt->execute();
    $geoOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get System Integrator options for filter dropdown
    $siSQL = "SELECT DISTINCT system_integrator FROM feedback WHERE system_integrator IS NOT NULL AND system_integrator != '' ORDER BY system_integrator";
    $stmt = $conn->prepare($siSQL);
    $stmt->execute();
    $systemIntegratorOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get Recruiter options for filter dropdown
    $recruiterSQL = "SELECT DISTINCT recruiter_name FROM feedback WHERE recruiter_name IS NOT NULL AND recruiter_name != '' ORDER BY recruiter_name";
    $stmt = $conn->prepare($recruiterSQL);
    $stmt->execute();
    $recruiterOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    // For debugging, display error message temporarily
    $dbError = true;
    $errorMessage = $e->getMessage();
    
    // Log the error
    error_log("Database Error: " . $e->getMessage());
    
    // Initialize empty arrays
    $interviewsData = [];
    $totalRows = 0;
    $totalPages = 0;
    $clientOptions = [];
    $positionOptions = [];
    $businessUnitOptions = [];
}

// Function to generate pagination URL
function getPaginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

// Function to generate sort URL
function getSortUrl($field) {
    $params = $_GET;
    $currentSortField = isset($_GET['sort']) ? $_GET['sort'] : 'interview_date';
    $currentSortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    
    if ($field === $currentSortField) {
        // Toggle sort order if clicking the same field
        $params['order'] = ($currentSortOrder === 'ASC') ? 'DESC' : 'ASC';
    } else {
        // Default to DESC for new sort field
        $params['order'] = 'DESC';
    }
    
    $params['sort'] = $field;
    
    return '?' . http_build_query($params);
}

// Function to get sort icon
function getSortIcon($field) {
    $currentSortField = isset($_GET['sort']) ? $_GET['sort'] : 'interview_date';
    $currentSortOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';
    
    if ($field === $currentSortField) {
        return ($currentSortOrder === 'ASC') ? 'fa-sort-up' : 'fa-sort-down';
    }
    
    return 'fa-sort';
}

// Function to count questions in comma-separated list
function countQuestions($questionsStr) {
    if (empty($questionsStr)) return 0;
    return substr_count($questionsStr, ',') + 1;
}

// Function to truncate text
function truncateText($text, $length = 50) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Interviews - VDart Interview Analytics</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #1abc9c;
            --light: #ecf0f1;
            --dark: #34495e;
            --danger: #e74c3c;
            --success: #2ecc71;
            --warning: #f39c12;
            --info: #3498db;
            --border-radius: 8px;
            --box-shadow: 0 8px 15px rgba(0, 0, 0, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header styling */
        header {
            background: linear-gradient(135deg, #2c3e50, #1abc9c);
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-full-container {
            width: 100%;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .logo-container {
            display: flex;
            align-items: center;
            padding-left: 20px;
        }

        #company-logo {
            height: 40px;
            margin-left: 0;
            padding-left: 0;
            filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));
        }

        .right-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .header-title {
            font-size: 26px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-family: 'Montserrat', sans-serif;
            text-align: center;
            width: 100%;
            margin: 0 auto;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        /* Navigation styling */
        nav ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        nav ul li {
            margin-left: 25px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 0;
            transition: all 0.3s ease;
            position: relative;
            opacity: 0.9;
        }

        nav ul li a:hover {
            color: #fff;
            opacity: 1;
        }

        nav ul li a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #fff;
            transition: width 0.3s ease;
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .active {
            opacity: 1;
        }

        .active::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #fff;
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }

        /* Main content */
        .main-container {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 0;
        }

        .table-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .table-card:hover {
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .filter-section {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 25px;
        }

        .filter-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            font-family: 'Montserrat', sans-serif;
        }

        /* Table styles */
        .table {
            margin-bottom: 0;
        }

        .table > :not(:first-child) {
            border-top: none;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
            padding: 1rem 0.75rem;
            position: relative;
            cursor: pointer;
        }

        .table thead th:hover {
            background-color: #f1f3f5;
        }

        .sort-icon {
            margin-left: 5px;
        }

        .table tbody td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: var(--dark);
            font-size: 0.9rem;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .bg-success {
            background-color: var(--success);
        }

        .bg-danger {
            background-color: var(--danger);
        }

        .status-text {
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-text.yes {
            color: var(--success);
        }

        .status-text.no {
            color: var(--danger);
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }

        .pagination-info {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            color: var(--primary);
            border-radius: 4px;
            margin: 0 2px;
        }

        .page-link:hover {
            color: var(--accent);
            background-color: #f8f9fa;
        }

        .page-item.active .page-link {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        /* Button styles */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 4px 10px rgba(26, 188, 156, 0.2);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: #16a085;
            border-color: #16a085;
            box-shadow: 0 6px 15px rgba(26, 188, 156, 0.3);
        }

        .btn-outline-secondary {
            border-color: #dee2e6;
            color: #6c757d;
        }

        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: #f8f9fa;
            color: var(--dark);
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .btn-danger {
            background-color: var(--danger);
            border-color: var(--danger);
            box-shadow: 0 4px 10px rgba(231, 76, 60, 0.2);
        }

        .btn-danger:hover, .btn-danger:focus {
            background-color: #c0392b;
            border-color: #c0392b;
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
        }

        .btn-outline-danger {
            color: var(--danger);
            border-color: var(--danger);
        }

        .btn-outline-danger:hover {
            background-color: var(--danger);
            border-color: var(--danger);
            color: white;
        }

        /* Preview badge */
        .badge-questions {
            background-color: #e9f7fe;
            color: #3498db;
            font-weight: 500;
            font-size: 0.8rem;
            padding: 0.3em 0.6em;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .badge-questions:hover {
            background-color: #d0ebfc;
        }

        /* Modal styles */
        .modal-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .question-list {
            list-style: none;
            padding-left: 0;
        }

        .question-list li {
            padding: 10px 15px;
            border-bottom: 1px solid #f1f1f1;
            position: relative;
        }

        .question-list li:last-child {
            border-bottom: none;
        }

        .question-list li::before {
            content: '\f059';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--accent);
            margin-right: 10px;
        }

        /* Alert messages */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .db-error-message {
            background-color: var(--danger);
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--box-shadow);
        }

        /* No results */
        .no-results {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }

        .no-results i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }

        /* Action buttons group */
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
        }

        .action-buttons .btn {
            padding: 0.375rem 0.5rem;
            min-width: auto;
        }

        /* Delete confirmation modal */
        .delete-warning {
            color: var(--danger);
            font-weight: 500;
        }

        .interview-details-summary {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            margin: 15px 0;
        }

        .interview-details-summary h6 {
            color: var(--dark);
            margin-bottom: 10px;
        }

        .interview-details-summary .detail-item {
            margin-bottom: 5px;
        }

        .interview-details-summary .detail-label {
            font-weight: 600;
            color: #6c757d;
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .filter-row {
                flex-direction: column;
            }
            
            .filter-col {
                margin-bottom: 15px;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .page-header .btn {
                margin-top: 15px;
            }
            
            .table-responsive {
                font-size: 0.85rem;
            }

            .action-buttons {
                flex-direction: column;
                gap: 3px;
            }

            .action-buttons .btn {
                font-size: 0.75rem;
                padding: 0.25rem 0.4rem;
            }
            
            .header-full-container {
                flex-direction: row;
                padding: 10px 0;
                position: relative;
            }
            
            .logo-container {
                position: absolute;
                top: 10px;
                left: 10px;
                z-index: 10;
            }
            
            #company-logo {
                height: 35px;
            }
            
            .right-header {
                width: 100%;
                margin-top: 45px;
                padding: 0 15px;
            }
            
            .header-title {
                text-align: center;
                width: 100%;
                font-size: 22px;
                margin: 0 0 10px 0;
            }
            
            .mobile-menu-btn {
                display: block;
                position: absolute;
                top: 10px;
                right: 15px;
                z-index: 10;
            }
            
            nav {
                width: 100%;
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease;
            }
            
            nav.open {
                max-height: 200px;
            }
            
            nav ul {
                flex-direction: column;
                width: 100%;
                align-items: center;
            }
            
            nav ul li {
                margin: 10px 0;
                margin-left: 0;
            }
            
            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-full-container">
            <!-- Logo container -->
            <div class="logo-container">
                <img id="company-logo" src="vdartwhitelogo (1).png" alt="VDart Logo">
            </div>
            
            <!-- Right section with title and nav -->
            <div class="right-header">
                <h1 class="header-title">VDart Interview Analytics</h1>
                
                <!-- Mobile menu button -->
                <button class="mobile-menu-btn" id="mobile-menu-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                
                <nav id="main-nav">
                    <ul>
                        <li><a href="index.php">Feedback Form</a></li>
                        <li><a href="dashboard.php">Analytics</a></li>
                        <li><a href="all_interviews.php" class="active">All Interviews</a></li>
                        <!-- <li><a href="reports.php">Reports</a></li> -->
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Success/Error Messages -->
        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($successMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage && !$dbError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($errorMessage) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div class="db-error-message">
                <i class="fas fa-exclamation-triangle me-2"></i> Database connection error. 
                <?php if (isset($errorMessage)): ?>
                    <details>
                        <summary>Error details (click to show)</summary>
                        <pre><?= htmlspecialchars($errorMessage) ?></pre>
                    </details>
                <?php else: ?>
                    Please check your connection settings or contact support.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-title">All Interviews</h2>
                <p class="text-muted">Showing all interview records with detailed information</p>
            </div>
            <div>
                <a href="dashboard.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-chart-bar me-1"></i> Back to Dashboard
                </a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fas fa-filter me-1"></i> Filters
                </button>
            </div>
        </div>

        <!-- Current filters display -->
        <?php if ($filterClient || $filterPosition || $filterBusinessUnit || $filterGeo || $filterCandidateName || $filterApplicantId || $filterSystemIntegrator || $filterRecruiter || $filterStartDate || $filterEndDate || $filterAvailabilityAsked || $filterConfidentResponses || $filterChallengingQuestions || $selectedPeriod != 'all'): ?>
            <div class="filter-section mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h3 class="filter-title mb-0">Active Filters</h3>
                    <a href="all_interviews.php" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear All Filters
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($selectedPeriod != 'all'): ?>
                        <span class="badge bg-light text-dark p-2">
                            Period: <?= htmlspecialchars($filterPeriods[$selectedPeriod]) ?>
                            <a href="<?= str_replace("period=$selectedPeriod", "period=all", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterStartDate && $filterEndDate): ?>
                        <span class="badge bg-light text-dark p-2">
                            Date Range: <?= htmlspecialchars($filterStartDate) ?> to <?= htmlspecialchars($filterEndDate) ?>
                            <a href="<?= str_replace(["start_date=$filterStartDate", "end_date=$filterEndDate"], ["start_date=", "end_date="], $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterClient): ?>
                        <span class="badge bg-light text-dark p-2">
                            Client: <?= htmlspecialchars($filterClient) ?>
                            <a href="<?= str_replace("client=" . urlencode($filterClient), "client=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterPosition): ?>
                        <span class="badge bg-light text-dark p-2">
                            Position: <?= htmlspecialchars($filterPosition) ?>
                            <a href="<?= str_replace("position=" . urlencode($filterPosition), "position=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterBusinessUnit): ?>
                        <span class="badge bg-light text-dark p-2">
                            Business Unit: <?= htmlspecialchars($filterBusinessUnit) ?>
                            <a href="<?= str_replace("business_unit=" . urlencode($filterBusinessUnit), "business_unit=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterAvailabilityAsked): ?>
                        <span class="badge bg-light text-dark p-2">
                            Availability Asked: Yes
                            <a href="<?= str_replace("availability_asked=yes", "availability_asked=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterConfidentResponses): ?>
                        <span class="badge bg-light text-dark p-2">
                            Confident Responses: Yes
                            <a href="<?= str_replace("confident_responses=yes", "confident_responses=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterChallengingQuestions): ?>
                        <span class="badge bg-light text-dark p-2">
                            Challenging Questions: Yes
                            <a href="<?= str_replace("challenging_questions=yes", "challenging_questions=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($filterExcludeIncomplete): ?>
                        <span class="badge bg-light text-dark p-2">
                            Exclude Incomplete Feedback
                            <a href="<?= str_replace("exclude_incomplete=1", "exclude_incomplete=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if ($filterGeo): ?>
                        <span class="badge bg-light text-dark p-2">
                            GEO: <?= htmlspecialchars($filterGeo) ?>
                            <a href="<?= str_replace("geo=" . urlencode($filterGeo), "geo=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if ($filterCandidateName): ?>
                        <span class="badge bg-light text-dark p-2">
                            Candidate: <?= htmlspecialchars($filterCandidateName) ?>
                            <a href="<?= str_replace("candidate_name=" . urlencode($filterCandidateName), "candidate_name=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if ($filterApplicantId): ?>
                        <span class="badge bg-light text-dark p-2">
                            Applicant ID: <?= htmlspecialchars($filterApplicantId) ?>
                            <a href="<?= str_replace("applicant_id=" . urlencode($filterApplicantId), "applicant_id=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if ($filterSystemIntegrator): ?>
                        <span class="badge bg-light text-dark p-2">
                            System Integrator: <?= htmlspecialchars($filterSystemIntegrator) ?>
                            <a href="<?= str_replace("system_integrator=" . urlencode($filterSystemIntegrator), "system_integrator=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                    <?php if ($filterRecruiter): ?>
                        <span class="badge bg-light text-dark p-2">
                            Recruiter: <?= htmlspecialchars($filterRecruiter) ?>
                            <a href="<?= str_replace("recruiter_name=" . urlencode($filterRecruiter), "recruiter_name=", $_SERVER['REQUEST_URI']) ?>" class="text-dark ms-1">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </span>
                    <?php endif; ?>

                </div>
            </div>
        <?php endif; ?>

        <!-- Table card -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th onclick="window.location='<?= getSortUrl('interview_date') ?>'">
                                Date <i class="fas <?= getSortIcon('interview_date') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('position') ?>'">
                                Position <i class="fas <?= getSortIcon('position') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('client_name') ?>'">
                                Client <i class="fas <?= getSortIcon('client_name') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('geo') ?>'">
                                GEO <i class="fas <?= getSortIcon('geo') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('candidate_name') ?>'">
                                Candidate <i class="fas <?= getSortIcon('candidate_name') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('business_unit') ?>'">
                                Business Unit <i class="fas <?= getSortIcon('business_unit') ?> sort-icon"></i>
                            </th>
                            <th>Questions</th>
                            <th onclick="window.location='<?= getSortUrl('asked_availability') ?>'">
                                Availability <i class="fas <?= getSortIcon('asked_availability') ?> sort-icon"></i>
                            </th>
                            <th onclick="window.location='<?= getSortUrl('answered_confidently') ?>'">
                                Confidence <i class="fas <?= getSortIcon('answered_confidently') ?> sort-icon"></i>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($interviewsData) > 0): ?>
                            <?php foreach ($interviewsData as $interview): ?>
                                <tr>
                                    <td><?= formatDate($interview['interview_date']) ?></td>
                                    <td><strong><?= htmlspecialchars($interview['position']) ?></strong></td>
                                    <td><?= htmlspecialchars($interview['client_name']) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($interview['geo'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?= htmlspecialchars($interview['candidate_name']) ?></strong>
                                            <?php if (!empty($interview['applicant_id'])): ?>
                                                <br><small class="text-muted">ID: <?= htmlspecialchars($interview['applicant_id']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($interview['business_unit']) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $questionCount = countQuestions($interview['client_questions']); 
                                        if ($questionCount > 0):
                                        ?>
                                            <span class="badge-questions" data-bs-toggle="modal" data-bs-target="#questionsModal-<?= $interview['id'] ?>">
                                                <i class="fas fa-question-circle me-1"></i> <?= $questionCount ?> questions
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">No questions recorded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-indicator <?= getStatusClass($interview['asked_availability']) ?>"></span>
                                        <span class="status-text <?= $interview['asked_availability'] ?>">
                                            <?= $interview['asked_availability'] == 'yes' ? 'Yes' : 'No' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-indicator <?= getStatusClass($interview['answered_confidently']) ?>"></span>
                                        <span class="status-text <?= $interview['answered_confidently'] ?>">
                                            <?= $interview['answered_confidently'] == 'yes' ? 'Yes' : 'No' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="interview_details.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-outline-secondary" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_interview.php?id=<?= $interview['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Interview">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#previewModal-<?= $interview['id'] ?>" title="Quick Preview">
                                                <i class="fas fa-expand-alt"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal-<?= $interview['id'] ?>" 
                                                    title="Delete Interview">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">
                                    <div class="no-results">
                                        <i class="fas fa-search mb-3"></i>
                                        <h4>No interviews found</h4>
                                        <p>Try adjusting your search filters or create new interview feedback.</p>
                                        <a href="all_interviews.php" class="btn btn-outline-secondary mt-3">Clear Filters</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 0): ?>
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing <?= ($offset + 1) ?>-<?= min($offset + $recordsPerPage, $totalRows) ?> of <?= $totalRows ?> interviews
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= getPaginationUrl(1) ?>" aria-label="First">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= getPaginationUrl($page - 1) ?>" aria-label="Previous">
                                        <i class="fas fa-angle-left"></i>
                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Show limited page numbers with ellipsis
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            // Always show first page
                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl(1) . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            // Page numbers
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $activeClass = ($i == $page) ? 'active' : '';
                                echo '<li class="page-item ' . $activeClass . '"><a class="page-link" href="' . getPaginationUrl($i) . '">' . $i . '</a></li>';
                            }
                            
                            // Always show last page
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . getPaginationUrl($totalPages) . '">' . $totalPages . '</a></li>';
                            }
                            ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= getPaginationUrl($page + 1) ?>" aria-label="Next">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="<?= getPaginationUrl($totalPages) ?>" aria-label="Last">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterModalLabel">Filter Interviews</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="GET" id="filterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="geoFilter" class="form-label">Geographic Region (GEO)</label>
                                <select class="form-select" id="geoFilter" name="geo">
                                    <option value="">All Regions</option>
                                    <?php foreach ($geoOptions as $geo): ?>
                                        <option value="<?= htmlspecialchars($geo) ?>" <?= $filterGeo === $geo ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($geo) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="systemIntegratorFilter" class="form-label">System Integrator</label>
                                <select class="form-select" id="systemIntegratorFilter" name="system_integrator">
                                    <option value="">All System Integrators</option>
                                    <?php foreach ($systemIntegratorOptions as $si): ?>
                                        <option value="<?= htmlspecialchars($si) ?>" <?= $filterSystemIntegrator === $si ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($si) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="candidateNameFilter" class="form-label">Candidate Name</label>
                                <input type="text" class="form-control" id="candidateNameFilter" name="candidate_name" 
                                    value="<?= htmlspecialchars($filterCandidateName) ?>" placeholder="Search candidate name...">
                            </div>
                            <div class="col-md-4">
                                <label for="applicantIdFilter" class="form-label">Applicant ID</label>
                                <input type="text" class="form-control" id="applicantIdFilter" name="applicant_id" 
                                    value="<?= htmlspecialchars($filterApplicantId) ?>" placeholder="Enter Applicant ID...">
                            </div>
                            <div class="col-md-4">
                                <label for="recruiterFilter" class="form-label">Recruiter</label>
                                <select class="form-select" id="recruiterFilter" name="recruiter_name">
                                    <option value="">All Recruiters</option>
                                    <?php foreach ($recruiterOptions as $recruiter): ?>
                                        <option value="<?= htmlspecialchars($recruiter) ?>" <?= $filterRecruiter === $recruiter ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($recruiter) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="period" class="form-label">Time Period</label>
                                <select name="period" id="period" class="form-select">
                                    <?php foreach ($filterPeriods as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $selectedPeriod === $value ? 'selected' : '' ?>>
                                            <?= $label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Custom Date Range</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="startDate" name="start_date" value="<?= htmlspecialchars($filterStartDate) ?>">
                                    <span class="input-group-text">to</span>
                                    <input type="date" class="form-control" id="endDate" name="end_date" value="<?= htmlspecialchars($filterEndDate) ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="clientFilter" class="form-label">Client</label>
                                <select class="form-select" id="clientFilter" name="client">
                                    <option value="">All Clients</option>
                                    <?php foreach ($clientOptions as $client): ?>
                                        <option value="<?= htmlspecialchars($client) ?>" <?= $filterClient === $client ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="positionFilter" class="form-label">Position</label>
                                <select class="form-select" id="positionFilter" name="position">
                                    <option value="">All Positions</option>
                                    <?php foreach ($positionOptions as $position): ?>
                                        <option value="<?= htmlspecialchars($position) ?>" <?= $filterPosition === $position ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($position) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="businessUnitFilter" class="form-label">Business Unit</label>
                                <select class="form-select" id="businessUnitFilter" name="business_unit">
                                    <option value="">All Business Units</option>
                                    <?php foreach ($businessUnitOptions as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit) ?>" <?= $filterBusinessUnit === $unit ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($unit) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Interview Outcomes</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="yes" id="availabilityAsked" name="availability_asked" <?= $filterAvailabilityAsked === 'yes' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="availabilityAsked">
                                        Availability Asked
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="yes" id="confidentResponses" name="confident_responses" <?= $filterConfidentResponses === 'yes' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="confidentResponses">
                                        Confident Responses
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="yes" id="challengingQuestions" name="challenging_questions" <?= $filterChallengingQuestions === 'yes' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="challengingQuestions">
                                        Challenging Questions
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Additional Filters</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="excludeIncomplete" name="exclude_incomplete" <?= $filterExcludeIncomplete == '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="excludeIncomplete">
                                        Exclude Incomplete Feedback
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort field (hidden) -->
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sortField) ?>">
                        <input type="hidden" name="order" value="<?= htmlspecialchars($sortOrder) ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="all_interviews.php" class="btn btn-secondary me-auto">Reset Filters</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('filterForm').submit();">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal for each interview -->
    <?php foreach ($interviewsData as $interview): ?>
        <div class="modal fade" id="deleteModal-<?= $interview['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel-<?= $interview['id'] ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title delete-warning" id="deleteModalLabel-<?= $interview['id'] ?>">
                            <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>Warning:</strong> This action cannot be undone. The interview record will be permanently deleted from the database.
                        </div>
                        
                        <p>Are you sure you want to delete this interview record?</p>
                        
                        <div class="interview-details-summary">
                            <h6>Interview Details:</h6>
                            <div class="detail-item">
                                <span class="detail-label">Position:</span> <?= htmlspecialchars($interview['position']) ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Client:</span> <?= htmlspecialchars($interview['client_name']) ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Business Unit:</span> <?= htmlspecialchars($interview['business_unit']) ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Interview Date:</span> <?= formatDate($interview['interview_date']) ?>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Recruiter:</span> <?= htmlspecialchars($interview['recruiter_name']) ?>
                            </div>
                            <?php if (!empty($interview['client_questions'])): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Questions:</span> <?= countQuestions($interview['client_questions']) ?> recorded
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="interview_id" value="<?= $interview['id'] ?>">
                            <button type="submit" name="delete_interview" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Delete Interview
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Questions Modal for each interview -->
    <?php foreach ($interviewsData as $interview): ?>
        <?php if (!empty($interview['client_questions'])): ?>
            <div class="modal fade" id="questionsModal-<?= $interview['id'] ?>" tabindex="-1" aria-labelledby="questionsModalLabel-<?= $interview['id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="questionsModalLabel-<?= $interview['id'] ?>">Interview Questions</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Position:</strong> <?= htmlspecialchars($interview['position']) ?><br>
                                <strong>Client:</strong> <?= htmlspecialchars($interview['client_name']) ?><br>
                                <strong>Date:</strong> <?= formatDate($interview['interview_date']) ?>
                            </div>
                            <h6>Questions Asked:</h6>
                            <ul class="question-list">
                                <?php 
                                $questions = explode(',', $interview['client_questions']);
                                foreach ($questions as $question): 
                                    if (trim($question)):
                                ?>
                                    <li><?= htmlspecialchars(trim($question)) ?></li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                            
                            <?php if ($interview['had_challenging_questions'] == 'yes'): ?>
                                <div class="mt-4">
                                    <h6>Challenging Questions:</h6>
                                    <div class="alert alert-light">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        This interview contained challenging questions that the candidate had difficulty with.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="interview_details.php?id=<?= $interview['id'] ?>" class="btn btn-primary">View Full Details</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Preview Modal for each interview -->
        <div class="modal fade" id="previewModal-<?= $interview['id'] ?>" tabindex="-1" aria-labelledby="previewModalLabel-<?= $interview['id'] ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="previewModalLabel-<?= $interview['id'] ?>">Interview Quick View</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Basic Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Position</th>
                                        <td><?= htmlspecialchars($interview['position']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Client</th>
                                        <td><?= htmlspecialchars($interview['client_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Business Unit</th>
                                        <td><?= htmlspecialchars($interview['business_unit']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Interview Date</th>
                                        <td><?= formatDate($interview['interview_date']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Recruiter</th>
                                        <td><?= htmlspecialchars($interview['recruiter_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Duration</th>
                                        <td><?= $interview['interview_duration'] ? $interview['interview_duration'] . ' min' : 'N/A' ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6>Interview Outcomes</h6>
                                <div class="p-3 mb-3 rounded" style="background-color: #f8f9fa;">
                                    <div class="mb-3">
                                        <span class="d-block fw-bold mb-2">Availability Asked</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar <?= $interview['asked_availability'] == 'yes' ? 'bg-success' : 'bg-danger' ?>" 
                                                role="progressbar" 
                                                style="width: 100%;" 
                                                aria-valuenow="100" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="d-block mt-1 <?= $interview['asked_availability'] == 'yes' ? 'text-success' : 'text-danger' ?>">
                                            <?= $interview['asked_availability'] == 'yes' ? 'Yes - Availability was asked' : 'No - Availability was not asked' ?>
                                        </small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <span class="d-block fw-bold mb-2">Confident Responses</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar <?= $interview['answered_confidently'] == 'yes' ? 'bg-success' : 'bg-danger' ?>" 
                                                role="progressbar" 
                                                style="width: 100%;" 
                                                aria-valuenow="100" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="d-block mt-1 <?= $interview['answered_confidently'] == 'yes' ? 'text-success' : 'text-danger' ?>">
                                            <?= $interview['answered_confidently'] == 'yes' ? 'Yes - Candidate answered confidently' : 'No - Candidate did not answer confidently' ?>
                                        </small>
                                    </div>
                                    
                                    <div>
                                        <span class="d-block fw-bold mb-2">Challenging Questions</span>
                                        <div class="progress" style="height: 10px;">
                                            <div class="progress-bar <?= $interview['had_challenging_questions'] == 'yes' ? 'bg-danger' : 'bg-success' ?>" 
                                                role="progressbar" 
                                                style="width: 100%;" 
                                                aria-valuenow="100" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                        <small class="d-block mt-1 <?= $interview['had_challenging_questions'] == 'yes' ? 'text-danger' : 'text-success' ?>">
                                            <?= $interview['had_challenging_questions'] == 'yes' ? 'Yes - Interview had challenging questions' : 'No - No challenging questions' ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Questions section -->
                        <?php if (!empty($interview['client_questions'])): ?>
                            <div class="mt-3">
                                <h6>Questions Asked (<?= countQuestions($interview['client_questions']) ?>)</h6>
                                <ul class="question-list">
                                    <?php 
                                    $questions = explode(',', $interview['client_questions']);
                                    foreach ($questions as $question): 
                                        if (trim($question)):
                                    ?>
                                        <li><?= htmlspecialchars(trim($question)) ?></li>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light mt-3">
                                <i class="fas fa-info-circle me-2"></i> No specific questions were recorded for this interview.
                            </div>
                        <?php endif; ?>
                        
                        <!-- Challenging questions details -->
                        <?php if ($interview['had_challenging_questions'] == 'yes'): ?>
                            <div class="mt-3">
                                <h6>Challenging Questions Details</h6>
                                <div class="alert alert-light">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    This interview contained challenging questions that the candidate had difficulty with.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="interview_details.php?id=<?= $interview['id'] ?>" class="btn btn-primary">View Full Details</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mainNav = document.getElementById('main-nav');
            
            if (mobileMenuToggle && mainNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNav.classList.toggle('open');
                    
                    // Change icon between bars and times
                    const icon = mobileMenuToggle.querySelector('i');
                    if (icon.classList.contains('fa-bars')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                });
            }
            
            // Auto-hide success/error messages after 5 seconds
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Confirmation for delete actions (double-check)
            const deleteButtons = document.querySelectorAll('[data-bs-target*="deleteModal"]');
            deleteButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Add a slight delay to emphasize the importance of the action
                    setTimeout(function() {
                        const modal = document.querySelector(button.getAttribute('data-bs-target'));
                        if (modal) {
                            const deleteForm = modal.querySelector('form[method="POST"]');
                            if (deleteForm) {
                                deleteForm.addEventListener('submit', function(e) {
                                    // Final confirmation before actual deletion
                                    if (!confirm('Are you absolutely sure? This action cannot be undone.')) {
                                        e.preventDefault();
                                    }
                                });
                            }
                        }
                    }, 100);
                });
            });
            
            // Initialize any tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>