<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "vdart_feedback";

// Initialize variables
$interview = null;
$successMessage = '';
$errorMessage = '';
$dbError = false;

// Get interview ID from URL
$interviewId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$interviewId) {
    header('Location: all_interviews.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_interview'])) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Prepare the client_questions data in the correct format
        $clientQuestions = $_POST['client_questions'] ?? '';
        
        // Handle different possible constraint requirements
        if (!empty($clientQuestions)) {
            // Split comma-separated questions into array
            $questionsArray = array_map('trim', explode(',', $clientQuestions));
            // Remove empty questions
            $questionsArray = array_filter($questionsArray, function($q) { return !empty($q); });
            
            if (!empty($questionsArray)) {
                // Try different formats that might satisfy the constraint
                // Option 1: JSON array format
                $clientQuestions = json_encode(array_values($questionsArray));
                
                // If that doesn't work, we'll try other formats in the catch block
            } else {
                $clientQuestions = null; // Try NULL for empty
            }
        } else {
            $clientQuestions = null; // Try NULL for empty
        }
        
        // Prepare update query with correct column names from your database
        $updateSQL = "UPDATE feedback SET 
            interview_date = :interview_date,
            position = :position,
            client_name = :client_name,
            business_unit = :business_unit,
            geo = :geo,
            candidate_name = :candidate_name,
            candidate_email = :candidate_email,
            applicant_id = :applicant_id,
            system_integrator = :system_integrator,
            recruiter_name = :recruiter_name,
            recruiter_email = :recruiter_email,
            interviewer_name = :interviewer_name,
            employee_id = :employee_id,
            asked_availability = :asked_availability,
            answered_confidently = :answered_confidently,
            had_challenging_questions = :had_challenging_questions,
            client_questions = :client_questions,
            updated_at = NOW()
            WHERE id = :id";
        
        // Debug: Log what we're trying to save
        error_log("Attempting to save client_questions: " . $clientQuestions);
        
        $stmt = $conn->prepare($updateSQL);
        
        // Bind parameters with correct values
        $stmt->bindValue(':interview_date', $_POST['interview_date'] ?? '');
        $stmt->bindValue(':position', $_POST['position'] ?? '');
        $stmt->bindValue(':client_name', $_POST['client_name'] ?? '');
        $stmt->bindValue(':business_unit', $_POST['business_unit'] ?? '');
        $stmt->bindValue(':geo', $_POST['geo'] ?? '');
        $stmt->bindValue(':candidate_name', $_POST['candidate_name'] ?? '');
        $stmt->bindValue(':candidate_email', $_POST['candidate_email'] ?? '');
        $stmt->bindValue(':applicant_id', $_POST['applicant_id'] ?? '');
        $stmt->bindValue(':system_integrator', $_POST['system_integrator'] ?? '');
        $stmt->bindValue(':recruiter_name', $_POST['recruiter_name'] ?? '');
        $stmt->bindValue(':recruiter_email', $_POST['recruiter_email'] ?? '');
        $stmt->bindValue(':interviewer_name', $_POST['interviewer_name'] ?? '');
        $stmt->bindValue(':employee_id', $_POST['employee_id'] ?? '');
        $stmt->bindValue(':asked_availability', $_POST['asked_availability'] ?? '');
        $stmt->bindValue(':answered_confidently', $_POST['answered_confidently'] ?? '');
        $stmt->bindValue(':had_challenging_questions', $_POST['had_challenging_questions'] ?? '');
        $stmt->bindValue(':client_questions', $clientQuestions);
        $stmt->bindValue(':id', $interviewId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $successMessage = "Interview record has been successfully updated.";
            // Log the update
            error_log("Interview updated: ID {$interviewId}, Position: {$_POST['position']}, Client: {$_POST['client_name']}");
        } else {
            $errorMessage = "Failed to update the interview record. Please try again.";
            error_log("SQL execute failed: " . print_r($stmt->errorInfo(), true));
        }
        
    } catch(PDOException $e) {
        // If it's a constraint violation on client_questions, try alternative formats
        if (strpos($e->getMessage(), 'client_questions') !== false && strpos($e->getMessage(), '4025') !== false) {
            try {
                // Try format 2: Simple comma-separated string
                $clientQuestions = $_POST['client_questions'] ?? '';
                if (empty($clientQuestions)) {
                    $clientQuestions = null;
                }
                
                $stmt = $conn->prepare($updateSQL);
                // Bind all parameters again
                $stmt->bindValue(':interview_date', $_POST['interview_date'] ?? '');
                $stmt->bindValue(':position', $_POST['position'] ?? '');
                $stmt->bindValue(':client_name', $_POST['client_name'] ?? '');
                $stmt->bindValue(':business_unit', $_POST['business_unit'] ?? '');
                $stmt->bindValue(':geo', $_POST['geo'] ?? '');
                $stmt->bindValue(':candidate_name', $_POST['candidate_name'] ?? '');
                $stmt->bindValue(':candidate_email', $_POST['candidate_email'] ?? '');
                $stmt->bindValue(':applicant_id', $_POST['applicant_id'] ?? '');
                $stmt->bindValue(':system_integrator', $_POST['system_integrator'] ?? '');
                $stmt->bindValue(':recruiter_name', $_POST['recruiter_name'] ?? '');
                $stmt->bindValue(':recruiter_email', $_POST['recruiter_email'] ?? '');
                $stmt->bindValue(':interviewer_name', $_POST['interviewer_name'] ?? '');
                $stmt->bindValue(':client_manager_name', $_POST['client_manager_name'] ?? '');
                $stmt->bindValue(':client_manager_email', $_POST['client_manager_email'] ?? '');
                $stmt->bindValue(':employee_id', $_POST['employee_id'] ?? '');
                $stmt->bindValue(':asked_availability', $_POST['asked_availability'] ?? '');
                $stmt->bindValue(':answered_confidently', $_POST['answered_confidently'] ?? '');
                $stmt->bindValue(':had_challenging_questions', $_POST['had_challenging_questions'] ?? '');
                $stmt->bindValue(':client_questions', $clientQuestions);
                $stmt->bindValue(':id', $interviewId, PDO::PARAM_INT);
                
                error_log("Retrying with format 2 - client_questions: " . $clientQuestions);
                
                if ($stmt->execute()) {
                    $successMessage = "Interview record has been successfully updated.";
                } else {
                    $errorMessage = "Failed to update the interview record. Constraint issue with client_questions format.";
                    error_log("Second attempt failed: " . print_r($stmt->errorInfo(), true));
                }
                
            } catch(PDOException $e2) {
                $errorMessage = "Database constraint error on client_questions field. The field may require a specific format. Error: " . $e2->getMessage();
                error_log("Second attempt error: " . $e2->getMessage());
            }
        } else {
            $errorMessage = "Database error occurred while updating the record: " . $e->getMessage();
            error_log("Update Error Details: " . $e->getMessage());
            error_log("Update Error Code: " . $e->getCode());
        }
    }
}

// Fetch interview data
try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT * FROM feedback WHERE id = :id");
    $stmt->bindValue(':id', $interviewId, PDO::PARAM_INT);
    $stmt->execute();
    $interview = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$interview) {
        header('Location: all_interviews.php?error=Interview not found');
        exit;
    }
    
    // Get dropdown options
    $clientSQL = "SELECT DISTINCT client_name FROM feedback WHERE client_name IS NOT NULL AND client_name != '' ORDER BY client_name";
    $stmt = $conn->prepare($clientSQL);
    $stmt->execute();
    $clientOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $positionSQL = "SELECT DISTINCT position FROM feedback WHERE position IS NOT NULL AND position != '' ORDER BY position";
    $stmt = $conn->prepare($positionSQL);
    $stmt->execute();
    $positionOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $businessUnitSQL = "SELECT DISTINCT business_unit FROM feedback WHERE business_unit IS NOT NULL AND business_unit != '' ORDER BY business_unit";
    $stmt = $conn->prepare($businessUnitSQL);
    $stmt->execute();
    $businessUnitOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $geoSQL = "SELECT DISTINCT geo FROM feedback WHERE geo IS NOT NULL AND geo != '' ORDER BY geo";
    $stmt = $conn->prepare($geoSQL);
    $stmt->execute();
    $geoOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $siSQL = "SELECT DISTINCT system_integrator FROM feedback WHERE system_integrator IS NOT NULL AND system_integrator != '' ORDER BY system_integrator";
    $stmt = $conn->prepare($siSQL);
    $stmt->execute();
    $systemIntegratorOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $recruiterSQL = "SELECT DISTINCT recruiter_name FROM feedback WHERE recruiter_name IS NOT NULL AND recruiter_name != '' ORDER BY recruiter_name";
    $stmt = $conn->prepare($recruiterSQL);
    $stmt->execute();
    $recruiterOptions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    $dbError = true;
    $errorMessage = $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
}

// Function to format date for input
function formatDateForInput($dateString) {
    if (empty($dateString)) return '';
    $date = new DateTime($dateString);
    return $date->format('Y-m-d');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Interview - VDart Interview Analytics</title>
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
            max-width: 1200px;
            margin: 0 auto;
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

        .form-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h5 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            border-bottom: 2px solid var(--accent);
            padding-bottom: 10px;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-control, .form-select {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
        }

        .form-check-input:checked {
            background-color: var(--accent);
            border-color: var(--accent);
        }

        .form-check-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(26, 188, 156, 0.25);
        }

        /* Button styles */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
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
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        .btn-outline-secondary {
            border-color: #dee2e6;
            color: #6c757d;
        }

        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background-color: #f8f9fa;
            color: var(--dark);
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

        /* Questions editing interface */
        .question-item {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 15px;
            position: relative;
            transition: all 0.3s ease;
        }

        .question-item:hover {
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(26, 188, 156, 0.1);
        }

        .question-item.dragging {
            opacity: 0.7;
            transform: rotate(2deg);
        }

        .question-input {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 12px;
            width: 100%;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 80px;
        }

        .question-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(26, 188, 156, 0.25);
            outline: none;
        }

        .question-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 10px;
        }

        .drag-handle {
            cursor: grab;
            color: #6c757d;
            font-size: 18px;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .drag-handle:hover {
            color: var(--accent);
            background-color: rgba(26, 188, 156, 0.1);
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .question-number {
            font-weight: 600;
            color: var(--accent);
            min-width: 25px;
            font-size: 0.9rem;
        }

        .remove-question-btn {
            padding: 4px 8px;
            font-size: 0.8rem;
            border-radius: 4px;
        }

        .empty-questions {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .empty-questions i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #dee2e6;
        }

        /* Auto-resize textarea */
        .questions-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .questions-help {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }

        /* Status indicators in form */
        .status-options {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .form-check {
            margin-bottom: 0;
        }

        /* Responsive styles */
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
            
            .status-options {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
                        <li><a href="all_interviews.php">All Interviews</a></li>
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
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; color: #fff; text-decoration: underline;">Click to show error details</summary>
                        <pre style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 4px; margin-top: 10px; text-align: left; font-size: 12px;"><?= htmlspecialchars($errorMessage) ?></pre>
                    </details>
                <?php else: ?>
                    Please check your connection settings or contact support.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <div>
                <h2 class="page-title">Edit Interview</h2>
                <p class="text-muted">Update interview feedback and details</p>
            </div>
            <div>
                <a href="all_interviews.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to All Interviews
                </a>
                <a href="interview_details.php?id=<?= $interviewId ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-eye me-1"></i> View Details
                </a>
            </div>
        </div>

        <?php if ($interview && !$dbError): ?>
        <form method="POST" class="needs-validation" novalidate>
            <div class="form-card">
                <!-- Basic Information Section -->
                <div class="form-section">
                    <h5><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="interview_date" class="form-label">Interview Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="interview_date" name="interview_date" 
                                   value="<?= formatDateForInput($interview['interview_date']) ?>" required>
                            <div class="invalid-feedback">Please provide a valid interview date.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="position" name="position" 
                                   value="<?= htmlspecialchars($interview['position'] ?? '') ?>" 
                                   list="positionList" required>
                            <datalist id="positionList">
                                <?php foreach ($positionOptions as $pos): ?>
                                    <option value="<?= htmlspecialchars($pos) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback">Please provide the job position.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_name" class="form-label">Client Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="client_name" name="client_name" 
                                   value="<?= htmlspecialchars($interview['client_name'] ?? '') ?>" 
                                   list="clientList" required>
                            <datalist id="clientList">
                                <?php foreach ($clientOptions as $client): ?>
                                    <option value="<?= htmlspecialchars($client) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback">Please provide the client name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="business_unit" class="form-label">Business Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="business_unit" name="business_unit" required>
                                <option value="">Select Business Unit</option>
                                <option value="sidd" <?= ($interview['business_unit'] ?? '') === 'sidd' ? 'selected' : '' ?>>Sidd</option>
                                <option value="oliver" <?= ($interview['business_unit'] ?? '') === 'oliver' ? 'selected' : '' ?>>Oliver</option>
                                <option value="nambu" <?= ($interview['business_unit'] ?? '') === 'nambu' ? 'selected' : '' ?>>Nambu</option>
                                <option value="rohit" <?= ($interview['business_unit'] ?? '') === 'rohit' ? 'selected' : '' ?>>Rohit</option>
                                <option value="vinay" <?= ($interview['business_unit'] ?? '') === 'vinay' ? 'selected' : '' ?>>Vinay</option>
                            </select>
                            <div class="invalid-feedback">Please select the business unit.</div>
                        </div>
                    </div>
                </div>

                <!-- Geographic and System Information -->
                <div class="form-section">
                    <h5><i class="fas fa-globe me-2"></i>Geographic & System Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="geo" class="form-label">Geographic Region (GEO)</label>
                            <select class="form-select" id="geo" name="geo">
                                <option value="">Select GEO</option>
                                <option value="US" <?= ($interview['geo'] ?? '') === 'US' ? 'selected' : '' ?>>US</option>
                                <option value="others" <?= ($interview['geo'] ?? '') === 'others' ? 'selected' : '' ?>>Others</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="system_integrator" class="form-label">System Integrator</label>
                            <input type="text" class="form-control" id="system_integrator" name="system_integrator" 
                                   value="<?= htmlspecialchars($interview['system_integrator'] ?? '') ?>" 
                                   list="systemIntegratorList">
                            <datalist id="systemIntegratorList">
                                <?php foreach ($systemIntegratorOptions as $si): ?>
                                    <option value="<?= htmlspecialchars($si) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- Candidate Information -->
                <div class="form-section">
                    <h5><i class="fas fa-user me-2"></i>Candidate Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="candidate_name" class="form-label">Candidate Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="candidate_name" name="candidate_name" 
                                   value="<?= htmlspecialchars($interview['candidate_name']) ?>" required>
                            <div class="invalid-feedback">Please provide the candidate name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="candidate_email" class="form-label">Candidate Email</label>
                            <input type="email" class="form-control" id="candidate_email" name="candidate_email" 
                                   value="<?= htmlspecialchars($interview['candidate_email']) ?>">
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="applicant_id" class="form-label">Applicant ID</label>
                            <input type="text" class="form-control" id="applicant_id" name="applicant_id" 
                                   value="<?= htmlspecialchars($interview['applicant_id']) ?>">
                        </div>
                    </div>
                </div>

                <!-- Recruiter Information -->
                <div class="form-section">
                    <h5><i class="fas fa-user-tie me-2"></i>Recruiter Information</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recruiter_name" class="form-label">Recruiter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="recruiter_name" name="recruiter_name" 
                                   value="<?= htmlspecialchars($interview['recruiter_name'] ?? '') ?>" 
                                   list="recruiterList" required>
                            <datalist id="recruiterList">
                                <?php foreach ($recruiterOptions as $recruiter): ?>
                                    <option value="<?= htmlspecialchars($recruiter) ?>">
                                <?php endforeach; ?>
                            </datalist>
                            <div class="invalid-feedback">Please provide the recruiter name.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employee_id" class="form-label">Employee ID</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                   value="<?= htmlspecialchars($interview['employee_id']) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="recruiter_email" class="form-label">Recruiter Email</label>
                            <input type="email" class="form-control" id="recruiter_email" name="recruiter_email" 
                                   value="<?= htmlspecialchars($interview['recruiter_email'] ?? '') ?>">
                            <div class="invalid-feedback">Please provide a valid email address.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="interviewer_name" class="form-label">Interviewer Name</label>
                            <input type="text" class="form-control" id="interviewer_name" name="interviewer_name" 
                                   value="<?= htmlspecialchars($interview['interviewer_name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="client_manager_name" class="form-label">Client Manager Name</label>
                            <input type="text" class="form-control" id="client_manager_name" name="client_manager_name" 
                                   value="<?= htmlspecialchars($interview['client_manager_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="client_manager_email" class="form-label">Client Manager Email ID/LinkedIn</label>
                            <input type="text" class="form-control" id="client_manager_email" name="client_manager_email" 
                                   value="<?= htmlspecialchars($interview['client_manager_email'] ?? '') ?>"
                                   placeholder="Email or LinkedIn profile">
                        </div>
                    </div>
                </div>

                <!-- Interview Assessment -->
                <div class="form-section">
                    <h5><i class="fas fa-clipboard-check me-2"></i>Interview Assessment</h5>
                    
                    <div class="mb-4">
                        <label class="form-label">Was availability asked? <span class="text-danger">*</span></label>
                        <div class="status-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="asked_availability" 
                                       id="availability_yes" value="yes" 
                                       <?= $interview['asked_availability'] == 'yes' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="availability_yes">
                                    <i class="fas fa-check-circle text-success me-1"></i> Yes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="asked_availability" 
                                       id="availability_no" value="no" 
                                       <?= $interview['asked_availability'] == 'no' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="availability_no">
                                    <i class="fas fa-times-circle text-danger me-1"></i> No
                                </label>
                            </div>
                        </div>
                        <div class="invalid-feedback">Please select whether availability was asked.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Did the candidate answer confidently? <span class="text-danger">*</span></label>
                        <div class="status-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answered_confidently" 
                                       id="confident_yes" value="yes" 
                                       <?= $interview['answered_confidently'] == 'yes' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="confident_yes">
                                    <i class="fas fa-check-circle text-success me-1"></i> Yes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="answered_confidently" 
                                       id="confident_no" value="no" 
                                       <?= $interview['answered_confidently'] == 'no' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="confident_no">
                                    <i class="fas fa-times-circle text-danger me-1"></i> No
                                </label>
                            </div>
                        </div>
                        <div class="invalid-feedback">Please select whether the candidate answered confidently.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Were there challenging questions? <span class="text-danger">*</span></label>
                        <div class="status-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="had_challenging_questions" 
                                       id="challenging_yes" value="yes" 
                                       <?= $interview['had_challenging_questions'] == 'yes' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="challenging_yes">
                                    <i class="fas fa-exclamation-triangle text-warning me-1"></i> Yes
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="had_challenging_questions" 
                                       id="challenging_no" value="no" 
                                       <?= $interview['had_challenging_questions'] == 'no' ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="challenging_no">
                                    <i class="fas fa-check-circle text-success me-1"></i> No
                                </label>
                            </div>
                        </div>
                        <div class="invalid-feedback">Please select whether there were challenging questions.</div>
                    </div>
                </div>

                <!-- Questions Section -->
                <div class="form-section">
                    <h5><i class="fas fa-question-circle me-2"></i>Interview Questions</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label mb-0">Client Questions</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addQuestionBtn">
                                <i class="fas fa-plus me-1"></i> Add Question
                            </button>
                        </div>
                        
                        <div id="questionsContainer">
                            <!-- Questions will be populated here by JavaScript -->
                        </div>
                        
                        <div class="questions-help mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Click "Add Question" to add new questions. Use the drag handle to reorder questions.
                        </div>
                        
                        <!-- Hidden textarea to store the final comma-separated value -->
                        <textarea class="d-none" id="client_questions" name="client_questions"><?= htmlspecialchars($interview['client_questions']) ?></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Fields marked with <span class="text-danger">*</span> are required
                        </small>
                    </div>
                    <div>
                        <a href="all_interviews.php" class="btn btn-secondary me-3">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" name="update_interview" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update Interview
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

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
            
            // Auto-hide success/error messages
            const alerts = document.querySelectorAll('.alert-dismissible');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
            
            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    // Update hidden textarea before validation
                    updateHiddenTextarea();
                    
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });
            
            // Questions Management
            let questionCount = 0;
            const questionsContainer = document.getElementById('questionsContainer');
            const addQuestionBtn = document.getElementById('addQuestionBtn');
            const hiddenTextarea = document.getElementById('client_questions');
            
            // Debug logs
            console.log('Questions container:', questionsContainer);
            console.log('Add button:', addQuestionBtn);
            console.log('Hidden textarea:', hiddenTextarea);
            console.log('Initial textarea value:', hiddenTextarea ? hiddenTextarea.value : 'null');
            
            if (!questionsContainer || !addQuestionBtn || !hiddenTextarea) {
                console.error('Required elements not found!');
                return;
            }
            
            // Initialize questions from existing data
            function initializeQuestions() {
                const existingQuestions = hiddenTextarea.value.trim();
                console.log('Existing questions raw:', existingQuestions); // Debug log
                
                if (existingQuestions && existingQuestions !== '' && existingQuestions !== '[""]') {
                    let questions = [];
                    
                    // Check if it's JSON array format like ["abc","def"]
                    if (existingQuestions.startsWith('[') && existingQuestions.endsWith(']')) {
                        try {
                            // Parse as JSON array
                            const parsedArray = JSON.parse(existingQuestions);
                            if (Array.isArray(parsedArray)) {
                                questions = parsedArray.filter(q => q && typeof q === 'string' && q.trim().length > 0);
                            }
                        } catch (e) {
                            console.log('Failed to parse as JSON, trying comma-separated');
                            // If JSON parsing fails, fall back to comma-separated
                            questions = existingQuestions
                                .replace(/^\[|\]$/g, '') // Remove brackets
                                .split(',')
                                .map(q => q.trim().replace(/^"?'?|"?'?$/g, '')) // Remove quotes
                                .filter(q => q && q.length > 0);
                        }
                    } else {
                        // Handle comma-separated format
                        questions = existingQuestions.split(',')
                            .map(q => q.trim())
                            .filter(q => q && q.length > 0 && q !== '""' && q !== "''");
                    }
                    
                    console.log('Parsed questions:', questions); // Debug log
                    
                    if (questions.length > 0) {
                        questions.forEach(question => {
                            const cleanQuestion = question.trim();
                            if (cleanQuestion && cleanQuestion.length > 0) {
                                addQuestionItem(cleanQuestion);
                            }
                        });
                    }
                }
                
                if (questionCount === 0) {
                    showEmptyState();
                }
                
                updateQuestionNumbers();
            }
            
            // Show empty state
            function showEmptyState() {
                questionsContainer.innerHTML = `
                    <div class="empty-questions">
                        <i class="fas fa-question-circle"></i>
                        <h6>No questions added yet</h6>
                        <p>Click "Add Question" to start adding interview questions.</p>
                    </div>
                `;
            }
            
            // Add new question item
            function addQuestionItem(text = '') {
                // Clean and validate the text
                const cleanText = typeof text === 'string' ? text.trim() : '';
                
                // Skip completely empty questions when loading existing data
                if (arguments.length > 0 && (!cleanText || cleanText.length === 0 || cleanText === '""' || cleanText === "''" || cleanText === '[""]')) {
                    console.log('Skipping invalid question:', text);
                    return;
                }
                
                console.log('Adding question:', cleanText); // Debug log
                questionCount++;
                
                // Remove empty state if it exists
                const emptyState = questionsContainer.querySelector('.empty-questions');
                if (emptyState) {
                    emptyState.remove();
                }
                
                const questionDiv = document.createElement('div');
                questionDiv.className = 'question-item';
                questionDiv.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="drag-handle me-2" draggable="true">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="question-number me-2">#<span class="number">1</span></div>
                        <div class="flex-grow-1">
                            <textarea class="question-input" placeholder="Enter your interview question here..." 
                                      rows="2"></textarea>
                            <div class="question-controls">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-question-btn">
                                    <i class="fas fa-trash-alt me-1"></i> Remove
                                </button>
                                <small class="text-muted ms-auto">
                                    <span class="char-count">0</span> characters
                                </small>
                            </div>
                        </div>
                    </div>
                `;
                
                questionsContainer.appendChild(questionDiv);
                
                // Add event listeners
                const textarea = questionDiv.querySelector('.question-input');
                
                // Set the text content safely after creating the element
                if (cleanText && cleanText.length > 0) {
                    textarea.value = cleanText;
                }
                const removeBtn = questionDiv.querySelector('.remove-question-btn');
                const charCount = questionDiv.querySelector('.char-count');
                const dragHandle = questionDiv.querySelector('.drag-handle');
                
                console.log('Question div created, textarea:', textarea); // Debug log
                
                // Character count
                function updateCharCount() {
                    charCount.textContent = textarea.value.length;
                }
                
                textarea.addEventListener('input', function() {
                    updateCharCount();
                    updateHiddenTextarea();
                    autoResize(this);
                });
                
                // Auto-resize textarea
                function autoResize(element) {
                    element.style.height = 'auto';
                    element.style.height = element.scrollHeight + 'px';
                }
                
                // Remove question
                removeBtn.addEventListener('click', function() {
                    questionDiv.remove();
                    questionCount--;
                    
                    if (questionCount === 0) {
                        showEmptyState();
                    } else {
                        updateQuestionNumbers();
                    }
                    
                    updateHiddenTextarea();
                });
                
                // Drag and drop
                dragHandle.addEventListener('dragstart', function(e) {
                    questionDiv.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', questionDiv.outerHTML);
                });
                
                dragHandle.addEventListener('dragend', function() {
                    questionDiv.classList.remove('dragging');
                });
                
                // Focus on new question (only if it's empty)
                if (!cleanText || cleanText.length === 0) {
                    setTimeout(() => textarea.focus(), 100);
                }
                
                // Initial character count and resize
                updateCharCount();
                setTimeout(() => autoResize(textarea), 50);
                
                updateQuestionNumbers();
                updateHiddenTextarea();
                
                console.log('Question added successfully, count:', questionCount); // Debug log
            }
            
            // Update question numbers
            function updateQuestionNumbers() {
                const questionItems = questionsContainer.querySelectorAll('.question-item');
                questionItems.forEach((item, index) => {
                    const numberSpan = item.querySelector('.number');
                    if (numberSpan) {
                        numberSpan.textContent = index + 1;
                    }
                });
            }
            
            // Update hidden textarea with all questions
            function updateHiddenTextarea() {
                const questionItems = questionsContainer.querySelectorAll('.question-item');
                const questions = [];
                
                questionItems.forEach(item => {
                    const textarea = item.querySelector('.question-input');
                    const text = textarea.value.trim();
                    // Only include non-empty questions
                    if (text && text.length > 0) {
                        questions.push(text);
                    }
                });
                
                // Save as simple comma-separated format (not JSON array)
                hiddenTextarea.value = questions.length > 0 ? questions.join(', ') : '';
                console.log('Updated hidden textarea:', hiddenTextarea.value); // Debug log
            }
            
            // Add question button
            addQuestionBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                console.log('Add question button clicked'); // Debug log
                addQuestionItem();
            });
            
            // Drag and drop for reordering
            questionsContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                const dragging = questionsContainer.querySelector('.dragging');
                const siblings = [...questionsContainer.querySelectorAll('.question-item:not(.dragging)')];
                
                const nextSibling = siblings.find(sibling => {
                    return e.clientY <= sibling.getBoundingClientRect().top + sibling.offsetHeight / 2;
                });
                
                questionsContainer.insertBefore(dragging, nextSibling);
            });
            
            questionsContainer.addEventListener('drop', function(e) {
                e.preventDefault();
                updateQuestionNumbers();
                updateHiddenTextarea();
            });
            
            // Initialize
            initializeQuestions();
        });
    </script>
</body>
</html>