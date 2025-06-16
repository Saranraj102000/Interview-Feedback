<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "vdart_feedback";

// Initialize variables
$interviewData = null;
$dbError = false;
$notFound = false;

// Function to format date
function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('M d, Y');
}

// Function to generate class for availability and confidence indicators
function getStatusClass($value) {
    return $value == 'yes' ? 'bg-success' : 'bg-danger';
}

// Function to parse and count questions properly
function parseQuestions($questionsStr) {
    if (empty($questionsStr)) return [];
    
    // Check if it's JSON array format like ["abc","def"]
    if (substr($questionsStr, 0, 1) === '[' && substr($questionsStr, -1) === ']') {
        try {
            // Try to parse as JSON array
            $parsed = json_decode($questionsStr, true);
            if (is_array($parsed)) {
                // Filter out empty questions
                return array_filter($parsed, function($q) {
                    return !empty(trim($q));
                });
            }
        } catch (Exception $e) {
            // If JSON parsing fails, fall back to comma-separated
        }
        
        // Manual parsing if JSON decode fails
        $questionsStr = trim($questionsStr, '[]');
        $questions = explode(',', $questionsStr);
        $cleanQuestions = [];
        foreach ($questions as $question) {
            $clean = trim($question, ' "\'');
            if (!empty($clean)) {
                $cleanQuestions[] = $clean;
            }
        }
        return $cleanQuestions;
    } else {
        // Handle comma-separated format
        $questions = explode(',', $questionsStr);
        $cleanQuestions = [];
        foreach ($questions as $question) {
            $clean = trim($question);
            if (!empty($clean)) {
                $cleanQuestions[] = $clean;
            }
        }
        return $cleanQuestions;
    }
}

// Function to count questions
function countQuestions($questionsStr) {
    $questions = parseQuestions($questionsStr);
    return count($questions);
}

// Check if ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $notFound = true;
} else {
    $interviewId = (int)$_GET['id'];
    
    // Connect to the database
    try {
        $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Query to get interview details
        $sql = "
    SELECT 
        *,
        TIMESTAMPDIFF(MINUTE, created_at, updated_at) as interview_duration
    FROM feedback
    WHERE id = :id
";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $interviewId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $interviewData = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $notFound = true;
        }
        
    } catch(PDOException $e) {
        // For debugging, display error message temporarily
        $dbError = true;
        $errorMessage = $e->getMessage();
        
        // Log the error
        error_log("Database Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Details - VDart Interview Analytics</title>
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

        .detail-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .detail-card:hover {
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 1px solid rgba(236, 240, 241, 0.6);
            padding-bottom: 12px;
        }

        /* Status indicators */
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
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
            font-size: 0.95rem;
        }

        .status-text.yes {
            color: var(--success);
        }

        .status-text.no {
            color: var(--danger);
        }

        /* Question list */
        .question-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .question-list li {
            padding: 12px 15px;
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

        .badge {
    font-weight: 500;
    border-radius: 5px;
    padding: 0.4em 0.8em;
}

        code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.875em;
    color: #d63384;
}

/* Enhanced info box */
.info-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-left: 4px solid var(--accent);
}

/* Enhanced table styling */
.table th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: var(--primary);
}

.table td {
    border-bottom: 1px solid #f1f1f1;
}

/* Email link styling */
a.text-decoration-none:hover {
    text-decoration: underline !important;
    color: var(--accent) !important;
}

/* Responsive table improvements */
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .table th {
        width: 35% !important;
    }
}


        .info-box-title {
            font-weight: 600;
            margin-bottom: 10px;
            font-family: 'Montserrat', sans-serif;
        }

        /* Status cards */
        .status-card {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .status-card .icon {
            font-size: 1.7rem;
            margin-right: 15px;
        }

        .status-card .text {
            flex: 1;
        }

        .status-card .title {
            font-weight: 600;
            margin-bottom: 5px;
            font-family: 'Montserrat', sans-serif;
        }

        .status-card .description {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .status-card.success {
            border-left: 4px solid var(--success);
        }

        .status-card.success .icon {
            color: var(--success);
        }

        .status-card.danger {
            border-left: 4px solid var(--danger);
        }

        .status-card.danger .icon {
            color: var(--danger);
        }

        .status-card.warning {
            border-left: 4px solid var(--warning);
        }

        .status-card.warning .icon {
            color: var(--warning);
        }

        /* Not found page */
        .not-found-container {
            text-align: center;
            padding: 50px 20px;
        }

        .not-found-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 25px;
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

        /* Error message */
        .db-error-message {
            background-color: var(--danger);
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--box-shadow);
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .row-cols-lg-3 > * {
                flex: 0 0 100%;
                max-width: 100%;
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
            
            .page-header .btn-group {
                margin-top: 15px;
                align-self: flex-start;
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
                        <!-- <li><a href="reports.php">Reports</a></li> -->
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="main-container">
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

        <?php if ($notFound): ?>
            <!-- Not found message -->
            <div class="not-found-container">
                <div class="not-found-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2>Interview Not Found</h2>
                <p class="text-muted mb-4">The interview record you're looking for doesn't exist or may have been removed.</p>
                <div>
                    <a href="all_interviews.php" class="btn btn-primary">
                        <i class="fas fa-list me-2"></i> View All Interviews
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-chart-bar me-2"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        <?php elseif ($interviewData): ?>
            <!-- Interview details -->
            <div class="page-header">
                <div>
                    <h2 class="page-title">Interview Details</h2>
                    <p class="text-muted">Detailed view of interview feedback</p>
                </div>
                <div class="btn-group">
                    <a href="all_interviews.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to All Interviews
                    </a>
                    <a href="edit_interview.php?id=<?= $interviewId ?>" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit me-1"></i> Edit Interview
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-chart-bar me-1"></i> Dashboard
                    </a>
                </div>
            </div>

            <!-- Basic information card -->
            <div class="detail-card">
                <h3 class="section-title">
                    <i class="fas fa-info-circle me-2"></i> Basic Information
                </h3>
                <div class="row">
                    <div class="col-md-4">
                        <table class="table">
                            <tr>
                                <th width="40%">Position</th>
                                <td>
                                    <strong><?= htmlspecialchars($interviewData['position']) ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Client</th>
                                <td><?= htmlspecialchars($interviewData['client_name']) ?></td>
                            </tr>
                            <tr>
                                <th>System Integrator</th>
                                <td><?= htmlspecialchars($interviewData['system_integrator'] ?? 'Not specified') ?></td>
                            </tr>
                            <tr>
                                <th>GEO</th>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($interviewData['geo'] ?? 'Not specified') ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table class="table">
                            <tr>
                                <th width="40%">Candidate Name</th>
                                <td>
                                    <strong><?= htmlspecialchars($interviewData['candidate_name']) ?></strong>
                                </td>
                            </tr>
                            <tr>
                                <th>Candidate Email</th>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($interviewData['candidate_email']) ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($interviewData['candidate_email']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>Applicant ID</th>
                                <td>
                                    <code><?= htmlspecialchars($interviewData['applicant_id']) ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th>Business Unit</th>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($interviewData['business_unit']) ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <table class="table">
                            <tr>
                                <th width="40%">Recruiter</th>
                                <td><?= htmlspecialchars($interviewData['recruiter_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Employee ID</th>
                                <td><?= htmlspecialchars($interviewData['employee_id'] ?? 'Not specified') ?></td>
                            </tr>
                            <tr>
                                <th>Interviewer</th>
                                <td><?= htmlspecialchars($interviewData['interviewer_name'] ?? 'Not specified') ?></td>
                            </tr>
                            <tr>
                                <th>Interview Date</th>
                                <td><?= formatDate($interviewData['interview_date']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Additional metadata row -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="info-box">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Duration:</strong>
                                    <?php if ($interviewData['interview_duration']): ?>
                                        <i class="far fa-clock me-1"></i> <?= $interviewData['interview_duration'] ?> minutes
                                    <?php else: ?>
                                        <span class="text-muted">Not available</span>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Created:</strong>
                                    <?php 
                                    $createdDate = new DateTime($interviewData['created_at']); 
                                    echo $createdDate->format('M d, Y - h:i A');
                                    ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Last Updated:</strong>
                                    <?php 
                                    $updatedDate = new DateTime($interviewData['updated_at']);
                                    echo $updatedDate->format('M d, Y - h:i A');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interview outcomes card -->
            <div class="detail-card">
                <h3 class="section-title">
                    <i class="fas fa-clipboard-check me-2"></i> Interview Outcomes
                </h3>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
                    <!-- Availability Card -->
                    <div class="col">
                        <div class="status-card <?= $interviewData['asked_availability'] === 'yes' ? 'success' : 'danger' ?>">
                            <div class="icon">
                                <i class="fas <?= $interviewData['asked_availability'] === 'yes' ? 'fa-calendar-check' : 'fa-calendar-times' ?>"></i>
                            </div>
                            <div class="text">
                                <div class="title">Availability Asked</div>
                                <div class="description">
                                    <?= $interviewData['asked_availability'] === 'yes' 
                                        ? 'Candidate was asked about availability' 
                                        : 'Candidate was not asked about availability' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confidence Card -->
                    <div class="col">
                        <div class="status-card <?= $interviewData['answered_confidently'] === 'yes' ? 'success' : 'danger' ?>">
                            <div class="icon">
                                <i class="fas <?= $interviewData['answered_confidently'] === 'yes' ? 'fa-shield-alt' : 'fa-shield' ?>"></i>
                            </div>
                            <div class="text">
                                <div class="title">Confident Responses</div>
                                <div class="description">
                                    <?= $interviewData['answered_confidently'] === 'yes' 
                                        ? 'Candidate answered questions confidently' 
                                        : 'Candidate did not show confidence in answers' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Challenging Questions Card -->
                    <div class="col">
                        <div class="status-card <?= $interviewData['had_challenging_questions'] === 'yes' ? 'warning' : 'success' ?>">
                            <div class="icon">
                                <i class="fas <?= $interviewData['had_challenging_questions'] === 'yes' ? 'fa-exclamation-triangle' : 'fa-check-circle' ?>"></i>
                            </div>
                            <div class="text">
                                <div class="title">Challenging Questions</div>
                                <div class="description">
                                    <?= $interviewData['had_challenging_questions'] === 'yes' 
                                        ? 'Interviewer asked challenging questions' 
                                        : 'No challenging questions were asked' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional interview details -->
                <?php if (!empty($interviewData['candidate_prepared'])): ?>
                <div class="info-box">
                    <div class="info-box-title">
                        <i class="fas fa-user-check me-1"></i> Candidate Preparation
                    </div>
                    <p class="mb-0"><?= htmlspecialchars($interviewData['candidate_prepared']) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($interviewData['notes'])): ?>
                <div class="info-box">
                    <div class="info-box-title">
                        <i class="fas fa-sticky-note me-1"></i> Additional Notes
                    </div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($interviewData['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Candidate Reflections card -->
            <div class="detail-card">
                <h3 class="section-title">
                    <i class="fas fa-user-thoughts me-2"></i> Candidate Reflections
                </h3>
                
                <?php if (!empty($interviewData['challenging_explanation'])): ?>
                <div class="info-box mb-3">
                    <div class="info-box-title">
                        <i class="fas fa-comment-dots me-1"></i> Challenging Questions Explanation
                    </div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($interviewData['challenging_explanation'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($interviewData['additional_comments'])): ?>
                <div class="info-box">
                    <div class="info-box-title">
                        <i class="fas fa-comments me-1"></i> Additional Comments
                    </div>
                    <p class="mb-0"><?= nl2br(htmlspecialchars($interviewData['additional_comments'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (empty($interviewData['challenging_explanation']) && empty($interviewData['additional_comments'])): ?>
                <div class="alert alert-light">
                    <i class="fas fa-info-circle me-2"></i> No additional reflections were provided for this interview.
                </div>
                <?php endif; ?>
            </div>

            <!-- Enhanced Questions card -->
            <div class="detail-card">
                <h3 class="section-title">
                    <i class="fas fa-question-circle me-2"></i> Interview Questions
                </h3>
                
                <?php if (!empty($interviewData['client_questions'])): ?>
                    <?php $questions = parseQuestions($interviewData['client_questions']); ?>
                    <?php if (!empty($questions)): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-list-ol me-2"></i>Client Questions
                                </h5>
                                <div>
                                    <span class="badge bg-primary"><?= count($questions) ?> questions</span>
                                    <span class="badge bg-secondary ms-1">
                                        Avg: <?= round(count($questions) / max(1, $interviewData['interview_duration'] ?? 30), 1) ?> per <?= $interviewData['interview_duration'] ? 'minute' : '30 min' ?>
                                    </span>
                                </div>
                            </div>
                            <ul class="question-list">
                                <?php foreach ($questions as $index => $question): ?>
                                    <li>
                                        <strong>Q<?= $index + 1 ?>:</strong> 
                                        <?= htmlspecialchars($question) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light">
                            <i class="fas fa-info-circle me-2"></i> No valid questions were found in the recorded data.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-light">
                        <i class="fas fa-info-circle me-2"></i> No specific questions were recorded for this interview.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Quick Actions card -->
        <?php if ($interviewData): ?>
        <div class="detail-card">
            <h3 class="section-title">
                <i class="fas fa-cogs me-2"></i> Quick Actions
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <a href="all_interviews.php?candidate_name=<?= urlencode($interviewData['candidate_name']) ?>" 
                        class="btn btn-outline-primary">
                            <i class="fas fa-user me-2"></i> View All Interviews by <?= htmlspecialchars($interviewData['candidate_name']) ?>
                        </a>
                        <a href="all_interviews.php?client=<?= urlencode($interviewData['client_name']) ?>" 
                        class="btn btn-outline-info">
                            <i class="fas fa-building me-2"></i> View All Interviews for <?= htmlspecialchars($interviewData['client_name']) ?>
                        </a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-grid gap-2">
                        <a href="all_interviews.php?position=<?= urlencode($interviewData['position']) ?>" 
                        class="btn btn-outline-success">
                            <i class="fas fa-briefcase me-2"></i> View Similar Positions
                        </a>
                        <a href="all_interviews.php?business_unit=<?= urlencode($interviewData['business_unit']) ?>" 
                        class="btn btn-outline-warning">
                            <i class="fas fa-users me-2"></i> View <?= htmlspecialchars($interviewData['business_unit']) ?> Unit Interviews
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
        });
    </script>
</body>
</html>