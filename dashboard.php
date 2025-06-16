<?php
// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "vdart_feedback";

// Initialize variables for chart data
$totalInterviews = 0;
$totalQuestions = 0;
$availabilityAskedCount = 0;
$answeredConfidentlyCount = 0;
$challengingQuestionsCount = 0;
$positionData = [];
$clientData = [];
$monthlyTrendData = [];
$businessUnitData = [];
$recentInterviews = [];
$questionsPerPositionData = [];
$questionDistributionData = [];
$dbError = false;

// Initialize calculation variables with defaults
$interviewGrowth = 0;
$questionGrowth = 0;
$confidenceSuccessRate = 0;
$availabilitySuccessRate = 0;
$avgQuestionsPerInterview = 0;

// Filter period options
$filterPeriods = [
    'all' => 'All Time',
    'current_month' => 'Current Month',
    'last_month' => 'Last Month',
    'last_3months' => 'Last 3 Months',
    'last_6months' => 'Last 6 Months',
    'last_year' => 'Last Year'
];

// Default selected period
$selectedPeriod = 'all';

// Handle filter period change if submitted
if (isset($_GET['period']) && array_key_exists($_GET['period'], $filterPeriods)) {
    $selectedPeriod = $_GET['period'];
}

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
        $dateFilterSQL .= " AND system_integrator = :system_integrator";
    } else {
        $dateFilterSQL = "WHERE system_integrator = :system_integrator";
        $whereClauseAdded = true;
    }
    $params[':system_integrator'] = $filterSystemIntegrator;
}

if ($filterRecruiter) {
    if ($whereClauseAdded) {
        $dateFilterSQL .= " AND recruiter_name = :recruiter_name";
    } else {
        $dateFilterSQL = "WHERE recruiter_name = :recruiter_name";
        $whereClauseAdded = true;
    }
    $params[':recruiter_name'] = $filterRecruiter;
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
    
    // Debug information
    if (isset($_GET['debug']) && $_GET['debug'] == 1) {
        echo "<div class='alert alert-info'>";
        echo "<p><strong>Connection:</strong> Successfully connected to database: $database</p>";
        echo "<p><strong>Filter SQL:</strong> $dateFilterSQL</p>";
        if ($filterStartDate && $filterEndDate) echo "<p><strong>Date Range:</strong> $filterStartDate to $filterEndDate</p>";
        if ($filterClient) echo "<p><strong>Client Filter:</strong> $filterClient</p>";
        if ($filterPosition) echo "<p><strong>Position Filter:</strong> $filterPosition</p>";
        if ($filterBusinessUnit) echo "<p><strong>Business Unit Filter:</strong> $filterBusinessUnit</p>";
        echo "</div>";
    }

    // Query 1: Total interviews and other key metrics
    $metricsSQL = "
        SELECT 
            COUNT(*) as total_interviews,
            SUM(CASE WHEN asked_availability = 'yes' THEN 1 ELSE 0 END) as availability_asked,
            SUM(CASE WHEN answered_confidently = 'yes' THEN 1 ELSE 0 END) as answered_confidently,
            SUM(CASE WHEN had_challenging_questions = 'yes' THEN 1 ELSE 0 END) as challenging_questions
        FROM feedback
        $dateFilterSQL
    ";
    
    $stmt = $conn->prepare($metricsSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $metricsData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $totalInterviews = $metricsData['total_interviews'] ?? 0;
    $availabilityAskedCount = $metricsData['availability_asked'] ?? 0;
    $answeredConfidentlyCount = $metricsData['answered_confidently'] ?? 0;
    $challengingQuestionsCount = $metricsData['challenging_questions'] ?? 0;
    
    // Query to get total number of questions
    $questionSQL = "
    SELECT 
        SUM(LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1) as total_questions
    FROM feedback
    $dateFilterSQL
    " . ($whereClauseAdded ? "AND" : "WHERE") . " client_questions IS NOT NULL AND client_questions != ''
";
    
    $stmt = $conn->prepare($questionSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $questionsData = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalQuestions = $questionsData['total_questions'] ? $questionsData['total_questions'] : 0;
    
    // Query 2: Position data
    $positionSQL = "
        SELECT position, COUNT(*) as count
        FROM feedback
        $dateFilterSQL
        GROUP BY position
        ORDER BY count DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($positionSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $positionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 3: Client data
    $clientSQL = "
        SELECT 
            client_name, 
            COUNT(*) as total_interviews,
            SUM(CASE WHEN asked_availability = 'yes' THEN 1 ELSE 0 END) as availability_asked
        FROM feedback
        $dateFilterSQL
        GROUP BY client_name
        ORDER BY total_interviews DESC
        LIMIT 10
    ";
    
    $stmt = $conn->prepare($clientSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $clientData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 4: Monthly trend data
    $trendSQL = "
        SELECT 
            DATE_FORMAT(interview_date, '%Y-%m') as month,
            COUNT(*) as interviews,
            SUM(CASE WHEN asked_availability = 'yes' THEN 1 ELSE 0 END) as potential_offers
        FROM feedback
        WHERE interview_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    ";
    
    // Add additional filter conditions to trend SQL if applied
    $trendParams = []; // Separate params array for trend query
    
    if ($filterClient) {
        $trendSQL .= " AND client_name = :client";
        $trendParams[':client'] = $filterClient;
    }
    
    if ($filterPosition) {
        $trendSQL .= " AND position = :position";
        $trendParams[':position'] = $filterPosition;
    }
    
    if ($filterBusinessUnit) {
        $trendSQL .= " AND business_unit = :business_unit";
        $trendParams[':business_unit'] = $filterBusinessUnit;
    }
    
    $trendSQL .= " GROUP BY DATE_FORMAT(interview_date, '%Y-%m') ORDER BY month ASC";
    
    $stmt = $conn->prepare($trendSQL);
    
    // Bind trend parameters
    foreach ($trendParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $monthlyTrendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 5: Business Unit distribution
    $businessUnitSQL = "
        SELECT 
            business_unit, 
            COUNT(*) as count,
            SUM(CASE WHEN asked_availability = 'yes' THEN 1 ELSE 0 END) as availability_asked
        FROM feedback
        $dateFilterSQL
        GROUP BY business_unit
        ORDER BY count DESC
    ";
    
    $stmt = $conn->prepare($businessUnitSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $businessUnitData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 6: Recent interviews
    $recentSQL = "
    SELECT 
        id,
        interview_date,
        position,
        client_name,
        geo,
        candidate_name,
        applicant_id,
        asked_availability,
        answered_confidently,
        had_challenging_questions,
        recruiter_name,
        TIMESTAMPDIFF(MINUTE, created_at, updated_at) as interview_duration
    FROM feedback
    $dateFilterSQL
    ORDER BY interview_date DESC
    LIMIT 20
";
    
    $stmt = $conn->prepare($recentSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $recentInterviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 7: Questions per position (estimate from client_questions field)
    $questionsPerPositionSQL = "
    SELECT 
        position,
        AVG(LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1) as avgQuestions
    FROM feedback
    $dateFilterSQL
    " . ($whereClauseAdded ? "AND" : "WHERE") . " client_questions IS NOT NULL AND client_questions != ''
    GROUP BY position
    ORDER BY avgQuestions DESC
    LIMIT 7
";
    
    $stmt = $conn->prepare($questionsPerPositionSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $questionsPerPositionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query 8: Questions distribution
    $questionDistributionSQL = "
    SELECT 
        CASE 
            WHEN question_count BETWEEN 1 AND 2 THEN '1-2'
            WHEN question_count BETWEEN 3 AND 4 THEN '3-4'
            WHEN question_count BETWEEN 5 AND 6 THEN '5-6'
            WHEN question_count BETWEEN 7 AND 8 THEN '7-8'
            ELSE '9+'
        END as questions,
        COUNT(*) as count
    FROM (
        SELECT 
            id,
            LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1 as question_count
        FROM feedback
        $dateFilterSQL
        " . ($whereClauseAdded ? "AND" : "WHERE") . " client_questions IS NOT NULL AND client_questions != ''
    ) as question_counts
    GROUP BY questions
    ORDER BY FIELD(questions, '1-2', '3-4', '5-6', '7-8', '9+')
";
    
    $stmt = $conn->prepare($questionDistributionSQL);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $questionDistributionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate month-over-month growth for interviews
    $interviewGrowth = 0;
    if (count($monthlyTrendData) >= 2) {
        $currentMonth = isset($monthlyTrendData[count($monthlyTrendData) - 1]['interviews']) ? 
            (int)$monthlyTrendData[count($monthlyTrendData) - 1]['interviews'] : 0;
        $previousMonth = isset($monthlyTrendData[count($monthlyTrendData) - 2]['interviews']) ? 
            (int)$monthlyTrendData[count($monthlyTrendData) - 2]['interviews'] : 0;
        
        if ($previousMonth > 0) {
            $interviewGrowth = round((($currentMonth - $previousMonth) / $previousMonth) * 100);
        }
    }
    
    // Calculate month-over-month growth for questions
    // Query for question trends
    $questionTrendSQL = "
        SELECT 
            DATE_FORMAT(interview_date, '%Y-%m') as month,
            SUM(LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1) as question_count
        FROM feedback
        WHERE interview_date >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
        AND client_questions IS NOT NULL AND client_questions != ''
    ";
    
    $questionTrendParams = []; // Separate params array for question trend query
    
    if ($filterClient) {
        $questionTrendSQL .= " AND client_name = :client";
        $questionTrendParams[':client'] = $filterClient;
    }
    
    if ($filterPosition) {
        $questionTrendSQL .= " AND position = :position";
        $questionTrendParams[':position'] = $filterPosition;
    }
    
    if ($filterBusinessUnit) {
        $questionTrendSQL .= " AND business_unit = :business_unit";
        $questionTrendParams[':business_unit'] = $filterBusinessUnit;
    }
    
    $questionTrendSQL .= " GROUP BY DATE_FORMAT(interview_date, '%Y-%m') ORDER BY month ASC";
    
    $stmt = $conn->prepare($questionTrendSQL);
    
    // Bind question trend parameters
    foreach ($questionTrendParams as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    $stmt->execute();
    $questionTrendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize question growth
    $questionGrowth = 0;
    
    if (count($questionTrendData) >= 2) {
        $currentMonth = isset($questionTrendData[count($questionTrendData) - 1]['question_count']) ? 
            (int)$questionTrendData[count($questionTrendData) - 1]['question_count'] : 0;
        $previousMonth = isset($questionTrendData[count($questionTrendData) - 2]['question_count']) ? 
            (int)$questionTrendData[count($questionTrendData) - 2]['question_count'] : 0;
        
        if ($previousMonth > 0) {
            $questionGrowth = round((($currentMonth - $previousMonth) / $previousMonth) * 100);
        }
    }
    
    // Success rate calculations
    $availabilitySuccessRate = $totalInterviews > 0 ? round(($availabilityAskedCount / $totalInterviews) * 100) : 0;
    $confidenceSuccessRate = $totalInterviews > 0 ? round(($answeredConfidentlyCount / $totalInterviews) * 100) : 0;
    
} catch(PDOException $e) {
    // For debugging, display error message temporarily
    $dbError = true;
    $errorMessage = $e->getMessage();
    
    // Log the error
    error_log("Database Error: " . $e->getMessage());
    
    // Initialize empty arrays for all data structures in case of error
    $totalInterviews = 0;
    $totalQuestions = 0;
    $availabilityAskedCount = 0;
    $answeredConfidentlyCount = 0;
    $challengingQuestionsCount = 0;
    $positionData = [];
    $clientData = [];
    $monthlyTrendData = [];
    $businessUnitData = [];
    $recentInterviews = [];
    $questionsPerPositionData = [];
    $questionDistributionData = [];
}

// Calculate average questions per interview
$avgQuestionsPerInterview = ($totalInterviews > 0) ? round($totalQuestions / $totalInterviews, 1) : 0;


$businessUnitPercentages = [];
$totalBusinessUnitCount = 0;

// Calculate total for percentage computation
foreach ($businessUnitData as $unit) {
    $totalBusinessUnitCount += $unit['count'];
}

// Convert counts to percentages
foreach ($businessUnitData as $unit) {
    $unitName = $unit['business_unit'] ? $unit['business_unit'] : 'Unknown';
    $unitCount = $unit['count'];
    $unitPercentage = $totalBusinessUnitCount > 0 ? round(($unitCount / $totalBusinessUnitCount) * 100) : 0;
    
    $businessUnitPercentages[] = [
        'unit' => $unitName,
        'count' => $unitCount,
        'percentage' => $unitPercentage,
        'availabilityAsked' => $unit['availability_asked']
    ];
}

// Limit to top 5 for display
$topBusinessUnits = array_slice($businessUnitPercentages, 0, 5);

// Format data for the doughnut chart
$businessUnitChartData = json_encode(array_map(function($item) {
    return [
        'label' => $item['unit'],
        'count' => $item['count'],
        'percentage' => $item['percentage']
    ];
}, $topBusinessUnits));

// Format data for charts (convert to JSON)
$positionChartData = json_encode(array_map(function($item) {
    return ['name' => $item['position'], 'value' => (int)$item['count']];
}, $positionData));

$clientChartData = json_encode(array_map(function($item) {
    return [
        'client' => $item['client_name'], 
        'interviews' => (int)$item['total_interviews'],
        'availabilityAsked' => (int)$item['availability_asked']
    ];
}, $clientData));

$trendChartData = json_encode(array_map(function($item) {
    // Extract month name from date
    $date = new DateTime($item['month'] . '-01');
    return [
        'month' => $date->format('M'),
        'interviews' => (int)$item['interviews'],
        'potentialOffers' => (int)$item['potential_offers']
    ];
}, $monthlyTrendData));

$businessUnitChartData = json_encode(array_map(function($item) {
    return [
        'unit' => $item['business_unit'],
        'count' => (int)$item['count'],
        'availabilityAsked' => (int)$item['availability_asked']
    ];
}, $businessUnitData));

$questionsPerPositionChartData = json_encode(array_map(function($item) {
    return [
        'position' => $item['position'],
        'avgQuestions' => round((float)$item['avgQuestions'], 1)
    ];
}, $questionsPerPositionData));

$questionDistributionChartData = json_encode(array_map(function($item) {
    return [
        'questions' => $item['questions'],
        'count' => (int)$item['count']
    ];
}, $questionDistributionData));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VDart Interview Analytics Dashboard</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- ApexCharts CSS -->
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.css" rel="stylesheet">
    <style>
        :root {
    --primary: #2c3e50;
    --secondary: #3498db;
    --accent: #1abc9c; /* Changed from #3498db to #1abc9c to match dashboard */
    --light: #ecf0f1;
    --dark: #34495e;
    --danger: #e74c3c;
    --success: #2ecc71;
    --warning: #f39c12;
    --info: #3498db;
    --border-radius: 8px; /* Changed from 0 to 8px to match dashboard */
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
    height: 40px; /* Changed from 35px to 40px */
    margin-left: 0;
    padding-left: 0;
    filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2)); /* Added drop shadow */
}

.right-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.header-title {
    font-size: 26px; /* Changed from 24px to 26px */
    font-weight: 600;
    letter-spacing: 0.5px;
    font-family: 'Montserrat', sans-serif;
    text-align: center;
    width: 100%;
    margin: 0 auto;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2); /* Added text shadow */
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
    background-color: #fff; /* Changed from #3498db to #fff */
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
    background-color: #fff; /* Changed from commented out value */
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

        /* Dashboard styling */
        .dashboard-container {
            padding: 30px;
        }

        .metrics-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 25px;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .metrics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .metrics-card .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
            font-family: 'Montserrat', sans-serif;
            position: relative;
            z-index: 2;
        }

        .metrics-card .card-value {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary);
            position: relative;
            z-index: 2;
        }

        .metrics-card .card-trend {
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            font-weight: 500;
            position: relative;
            z-index: 2;
        }

        .metrics-card .trend-up {
            color: var(--success);
        }

        .metrics-card .trend-down {
            color: var(--danger);
        }

        .metrics-card .card-icon {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.8rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(52, 152, 219, 0.2);
            z-index: 1;
        }

        .metrics-card .bg-pattern {
            position: absolute;
            bottom: -20px;
            right: -20px;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(52, 152, 219, 0.05) 0%, rgba(255,255,255,0) 70%);
            z-index: 1;
        }

        .chart-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 1px solid rgba(236, 240, 241, 0.6);
            padding-bottom: 12px;
        }

        .dashboard-filters {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .dashboard-filters:hover {
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .filter-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .date-range {
            display: flex;
            gap: 10px;
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
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            font-family: 'Montserrat', sans-serif;
            border-bottom: 1px solid rgba(236, 240, 241, 0.6);
            padding-bottom: 12px;
        }

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

        .bg-warning {
            background-color: var(--warning);
        }

        /* Table Enhancements */
        .table {
            margin-bottom: 0;
        }

        .table > :not(:first-child) {
            border-top: none;
        }

        .table thead th {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
            padding: 1rem 0.75rem;
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

        /* Button Enhancements */
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

        /* Dashboard Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .dashboard-header h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p.text-muted {
            font-size: 0.95rem;
            margin-bottom: 0;
        }

        /* Badge Styles */
        .badge {
            font-weight: 500;
            border-radius: 5px;
            padding: 0.4em 0.8em;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 6px;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dee2e6;
            font-size: 0.9rem;
        }

        .form-select:focus, .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(26, 188, 156, 0.25);
        }

        /* Status indicators in table */
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
            .chart-card {
                margin-bottom: 25px;
            }
            
            .metrics-card {
                margin-bottom: 25px;
            }
        }

        @media (max-width: 768px) {
    .dashboard-container {
        padding: 20px;
    }
    
    .metrics-card .card-value {
        font-size: 2rem;
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

@media print {
    /* Hide interactive elements when printing */
    header, 
    .dashboard-filters, 
    .btn, 
    .modal,
    .dropdown,
    .mobile-menu-btn,
    nav {
        display: none !important;
    }
    
    /* Reset dashboard container padding */
    .dashboard-container {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Optimize cards for printing */
    .chart-card, 
    .metrics-card, 
    .table-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 20px !important;
        padding: 15px !important;
        background: white !important;
    }
    
    /* Ensure white background */
    body {
        background: white !important;
        color: black !important;
    }
    
    /* Optimize table for printing */
    .table {
        font-size: 11px !important;
    }
    
    .table th,
    .table td {
        padding: 0.5rem 0.25rem !important;
        border: 1px solid #ddd !important;
    }
    
    /* Hide action columns in tables */
    .table th:last-child,
    .table td:last-child {
        display: none !important;
    }
    
    /* Optimize metrics cards */
    .metrics-card .card-value {
        font-size: 1.8rem !important;
    }
    
    /* Add page breaks */
    .row {
        page-break-inside: avoid;
    }
    
    /* Chart containers */
    .chart-card {
        height: auto !important;
    }
    
    /* Ensure proper spacing */
    .col-md-3,
    .col-md-4,
    .col-md-6,
    .col-md-8,
    .col-lg-4,
    .col-lg-8 {
        width: 100% !important;
        margin-bottom: 15px !important;
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
                        <li><a href="dashboard.php" class="active">Analytics</a></li>
                        <!-- <li><a href="reports.php">Reports</a></li> -->
                        <!-- <li><a href="settings.php">Settings</a></li> -->
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
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

        <!-- Filters Section -->
        <div class="dashboard-filters">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-3">Interview Analytics Dashboard</h2>
                    <p class="text-muted">Comprehensive insights from <?= $totalInterviews ?> candidate interviews</p>
                </div>
                <div class="col-md-6">
                    <form action="" method="GET" class="row g-3 justify-content-md-end">
                        <div class="col-auto">
                            <label for="period" class="filter-title">Time Period:</label>
                        </div>
                        <div class="col-auto">
                            <select name="period" id="period" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($filterPeriods as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $selectedPeriod === $value ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#advancedFiltersModal">
                                <i class="fas fa-sliders-h me-1"></i> Advanced Filters
                            </button>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                    <li><a class="dropdown-item" href="#" onclick="exportAnalyticsData()">
                                        <i class="fas fa-chart-line me-2"></i>Full Analytics Report
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportRecentInterviews()">
                                        <i class="fas fa-table me-2"></i>Recent Interviews
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportBusinessUnitData()">
                                        <i class="fas fa-building me-2"></i>Business Unit Performance
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="window.print()">
                                        <i class="fas fa-print me-2"></i>Print Dashboard
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="metrics-card">
                    <div class="card-icon">
                        <i class="fas fa-user-tie fa-2x"></i>
                    </div>
                    <div class="bg-pattern"></div>
                    <h3 class="card-title">Total Interviews</h3>
                    <div class="card-value"><?= $totalInterviews ?></div>
                    <div class="card-trend <?= $interviewGrowth >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-<?= $interviewGrowth >= 0 ? 'arrow-up' : 'arrow-down' ?> me-1"></i> 
                        <?= abs($interviewGrowth) ?>% from last month
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metrics-card">
                    <div class="card-icon">
                        <i class="fas fa-question-circle fa-2x"></i>
                    </div>
                    <div class="bg-pattern"></div>
                    <h3 class="card-title">Total Questions</h3>
                    <div class="card-value"><?= $totalQuestions ?></div>
                    <div class="card-trend <?= $questionGrowth >= 0 ? 'trend-up' : 'trend-down' ?>">
                        <i class="fas fa-<?= $questionGrowth >= 0 ? 'arrow-up' : 'arrow-down' ?> me-1"></i> 
                        <?= abs($questionGrowth) ?>% from last month
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metrics-card">
                    <div class="card-icon">
                        <i class="fas fa-calendar-check fa-2x"></i>
                    </div>
                    <div class="bg-pattern"></div>
                    <h3 class="card-title">Availability Asked</h3>
                    <div class="card-value"><?= $availabilityAskedCount ?></div>
                    <div class="card-trend">
                        <span class="badge bg-success"><?= round(($availabilityAskedCount / max(1, $totalInterviews)) * 100) ?>% of interviews</span>
                    </div>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="metrics-card">
                    <div class="card-icon">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div class="bg-pattern"></div>
                    <h3 class="card-title">Confidence Rate</h3>
                    <div class="card-value"><?= $confidenceSuccessRate ?>%</div>
                    <div class="card-trend">
                        <span class="badge bg-info">Confidence in Interviews</span>
                    </div>
                </div>
            </div> -->
        </div>

        <!-- Business Unit Distribution Section -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="chart-card">
                    <h3 class="chart-title">Business Unit Distribution</h3>
                    <div class="row">
                        <div class="col-md-7">
                            <div id="businessUnitBarChart" style="height: 280px;"></div>
                        </div>
                        <div class="col-md-5">
                            <div id="businessUnitDonutChart" style="height: 280px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card">
                    <h3 class="chart-title">Business Unit Performance</h3>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Business Unit</th>
                                    <th>Interviews</th>
                                    <th>Availability %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topBusinessUnits as $unit): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($unit['unit']) ?></td>
                                        <td><?= $unit['count'] ?></td>
                                        <td>
                                            <?php 
                                                $availPercent = $unit['count'] > 0 ? round(($unit['availabilityAsked'] / $unit['count']) * 100) : 0;
                                            ?>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                    style="width: <?= $availPercent ?>%;" 
                                                    aria-valuenow="<?= $availPercent ?>" 
                                                    aria-valuemin="0" 
                                                    aria-valuemax="100"></div>
                                            </div>
                                            <span class="small"><?= $availPercent ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Indicators Cards -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-card" style="min-height: 200px;">
                    <h3 class="chart-title">Average Questions Per Interview</h3>
                    <div class="text-center">
                        <div class="display-4 fw-bold text-primary"><?= $avgQuestionsPerInterview ?></div>
                        <p class="text-muted">questions are asked on average per interview</p>
                        <div class="progress mt-3" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar bg-primary" style="width: <?= min(($avgQuestionsPerInterview / 10) * 100, 100) ?>%; border-radius: 5px;"></div>
                        </div>
                        <p class="mt-3 small">The ideal number of questions is between 4-6 for a comprehensive assessment</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-card" style="min-height: 200px;">
                    <h3 class="chart-title">Questions Distribution</h3>
                    <div id="questionDistributionChart" style="height: 180px;"></div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="chart-card">
                    <h3 class="chart-title">Questions Per Interview by Position</h3>
                    <div id="questionsPerPositionChart" style="height: 350px;"></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="chart-card">
                    <h3 class="chart-title">Monthly Trend</h3>
                    <div id="trendChart" style="height: 350px;"></div>
                </div>
            </div>
        </div>
<br>
        <!-- Recent Interviews Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <h3 class="table-title">Recent Interviews</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Position</th>
                                    <th>Client</th>
                                    <th>GEO</th>
                                    <th>Recruiter</th>
                                    <th>Duration</th>
                                    <th>Availability Asked</th>
                                    <th>Confident Responses</th>
                                    <th>Challenging Questions</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recentInterviews) > 0): ?>
                                    <?php foreach ($recentInterviews as $interview): ?>
                                    <tr>
                                        <td><?= formatDate($interview['interview_date']) ?></td>
                                        <td><strong><?= htmlspecialchars($interview['position']) ?></strong></td>
                                        <td><?= htmlspecialchars($interview['client_name']) ?></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($interview['geo'] ?? 'N/A') ?></span></td>
                                        <td><?= htmlspecialchars($interview['recruiter_name']) ?></td>
                                        <td><?= $interview['interview_duration'] ? $interview['interview_duration'] . ' min' : 'N/A' ?></td>
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
                                            <span class="status-indicator <?= $interview['had_challenging_questions'] == 'no' ? 'bg-success' : 'bg-danger' ?>"></span>
                                            <span class="status-text <?= $interview['had_challenging_questions'] == 'yes' ? 'no' : 'yes' ?>">
                                                <?= $interview['had_challenging_questions'] == 'yes' ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="interview_details.php?id=<?= isset($interview['id']) ? (int)$interview['id'] : '0' ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No interview records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-4">
                        <a href="all_interviews.php" class="btn btn-primary">View All Interviews</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Modal -->
    <div class="modal fade" id="advancedFiltersModal" tabindex="-1" aria-labelledby="advancedFiltersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="advancedFiltersModalLabel">Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="GET" id="advancedFiltersForm">
                        <!-- Preserve the period filter when submitting advanced filters -->
                        <input type="hidden" name="period" value="<?= htmlspecialchars($selectedPeriod) ?>">
                        <input type="hidden" name="geo" value="<?= htmlspecialchars($filterGeo) ?>">
<input type="hidden" name="candidate_name" value="<?= htmlspecialchars($filterCandidateName) ?>">
<input type="hidden" name="applicant_id" value="<?= htmlspecialchars($filterApplicantId) ?>">
<input type="hidden" name="system_integrator" value="<?= htmlspecialchars($filterSystemIntegrator) ?>">
<input type="hidden" name="recruiter_name" value="<?= htmlspecialchars($filterRecruiter) ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dateRange" class="form-label">Date Range</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="startDate" name="start_date" value="<?= htmlspecialchars($filterStartDate) ?>">
                                    <span class="input-group-text">to</span>
                                    <input type="date" class="form-control" id="endDate" name="end_date" value="<?= htmlspecialchars($filterEndDate) ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="clientFilter" class="form-label">Client</label>
                                <select class="form-select" id="clientFilter" name="client">
                                    <option value="">All Clients</option>
                                    <?php foreach ($clientData as $client): ?>
                                        <option value="<?= htmlspecialchars($client['client_name']) ?>" <?= $filterClient === $client['client_name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client['client_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
    <div class="col-md-6">
        <label for="candidateNameFilter" class="form-label">Candidate Name</label>
        <input type="text" class="form-control" id="candidateNameFilter" name="candidate_name" 
               value="<?= htmlspecialchars($filterCandidateName) ?>" placeholder="Search candidate name...">
    </div>
    <div class="col-md-6">
        <label for="applicantIdFilter" class="form-label">Applicant ID</label>
        <input type="text" class="form-control" id="applicantIdFilter" name="applicant_id" 
               value="<?= htmlspecialchars($filterApplicantId) ?>" placeholder="Enter Applicant ID...">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-6">
        <label for="systemIntegratorFilter" class="form-label">System Integrator</label>
        <input type="text" class="form-control" id="systemIntegratorFilter" name="system_integrator" 
               value="<?= htmlspecialchars($filterSystemIntegrator) ?>" placeholder="System Integrator...">
    </div>
    <div class="col-md-6">
        <label for="recruiterFilter" class="form-label">Recruiter Name</label>
        <input type="text" class="form-control" id="recruiterFilter" name="recruiter_name" 
               value="<?= htmlspecialchars($filterRecruiter) ?>" placeholder="Recruiter name...">
    </div>
</div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="positionFilter" class="form-label">Position</label>
                                <select class="form-select" id="positionFilter" name="position">
                                    <option value="">All Positions</option>
                                    <?php foreach ($positionData as $position): ?>
                                        <option value="<?= htmlspecialchars($position['position']) ?>" <?= $filterPosition === $position['position'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($position['position']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="businessUnitFilter" class="form-label">Business Unit</label>
                                <select class="form-select" id="businessUnitFilter" name="business_unit">
                                    <option value="">All Business Units</option>
                                    <?php foreach ($businessUnitData as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit['business_unit']) ?>" <?= $filterBusinessUnit === $unit['business_unit'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($unit['business_unit']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="geoFilter" class="form-label">Geographic Region (GEO)</label>
                                <select class="form-select" id="geoFilter" name="geo">
                                    <option value="">All Regions</option>
                                    <option value="us" <?= $filterGeo === 'US' ? 'selected' : '' ?>>US</option>
                                    <option value="others" <?= $filterGeo === 'others' ? 'selected' : '' ?>>Others</option>
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
                    </form>
                </div>
                <div class="modal-footer">
                    <a href="dashboard.php" class="btn btn-secondary me-auto">Reset Filters</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('advancedFiltersForm').submit();">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ApexCharts JS -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>

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
            
            // Initialize all charts
            
            // Questions Per Position Chart
            var questionsPerPositionOptions = {
                series: [{
                    name: 'Avg Questions',
                    data: <?= json_encode(array_map(function($item) { 
                        return round((float)$item['avgQuestions'], 1); 
                    }, $questionsPerPositionData)) ?>
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    fontFamily: 'Poppins, sans-serif'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '65%',
                        distributed: false
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1);
                    },
                    style: {
                        fontSize: '12px',
                        fontWeight: 500
                    },
                    offsetY: -20
                },
                xaxis: {
                    categories: <?= json_encode(array_column($questionsPerPositionData, 'position')) ?>,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Poppins, sans-serif'
                        }
                    },
                    axisBorder: {
                        show: false
                    }
                },
                yaxis: {
                    title: {
                        text: 'Average Questions',
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Montserrat, sans-serif'
                        }



                        },
                    min: 0,
                    forceNiceScale: true,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(1);
                        }
                    }
                },
                colors: ['#1abc9c'],
                grid: {
                    borderColor: '#f1f1f1',
                    row: {
                        colors: ['#f8f9fa', 'transparent']
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function(val) {
                            return val.toFixed(1) + " questions on average";
                        }
                    }
                }
            };

            var questionsPerPositionChart = new ApexCharts(document.querySelector("#questionsPerPositionChart"), questionsPerPositionOptions);
            questionsPerPositionChart.render();
            
            // Monthly Trend Chart
            var trendChartOptions = {
                series: [{
                    name: 'Interviews',
                    type: 'column',
                    data: <?= json_encode(array_map(function($item) { 
                        return (int)$item['interviews']; 
                    }, $monthlyTrendData)) ?>
                }, {
                    name: 'Potential Offers',
                    type: 'line',
                    data: <?= json_encode(array_map(function($item) { 
                        return (int)$item['potential_offers']; 
                    }, $monthlyTrendData)) ?>
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    fontFamily: 'Poppins, sans-serif',
                    stacked: false
                },
                stroke: {
                    width: [0, 3],
                    curve: 'smooth'
                },
                plotOptions: {
                    bar: {
                        borderRadius: 5,
                        columnWidth: '60%'
                    }
                },
                markers: {
                    size: 4,
                    strokeWidth: 0,
                    hover: {
                        size: 6
                    }
                },
                xaxis: {
                    categories: <?= json_encode(array_map(function($item) { 
                        $date = new DateTime($item['month'] . '-01');
                        return $date->format('M'); 
                    }, $monthlyTrendData)) ?>,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Poppins, sans-serif'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Count',
                        style: {
                            fontSize: '13px',
                            fontFamily: 'Montserrat, sans-serif'
                        }
                    },
                    min: 0
                },
                colors: ['#3498db', '#1abc9c'],
                fill: {
                    opacity: [0.85, 1],
                    gradient: {
                        inverseColors: false,
                        shade: 'light',
                        type: "vertical",
                        opacityFrom: 0.85,
                        opacityTo: 0.55
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '13px',
                    fontFamily: 'Poppins, sans-serif',
                    offsetY: -10
                },
                grid: {
                    borderColor: '#f1f1f1'
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function (val) {
                            return val;
                        }
                    }
                }
            };

            var trendChart = new ApexCharts(document.querySelector("#trendChart"), trendChartOptions);
            trendChart.render();
            
            // Question Distribution Chart
            var questionDistOptions = {
                series: [{
                    name: 'Interviews',
                    data: <?= json_encode(array_map(function($item) { 
                        return (int)$item['count']; 
                    }, $questionDistributionData)) ?>
                }],
                chart: {
                    type: 'bar',
                    height: 180,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'Poppins, sans-serif',
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 6,
                        horizontal: false,
                        columnWidth: '70%',
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px',
                        fontWeight: 500,
                        colors: ['#fff']
                    },
                    dropShadow: {
                        enabled: true,
                        top: 1,
                        left: 1,
                        blur: 3,
                        opacity: 0.3
                    }
                },
                legend: {
                    show: false
                },
                xaxis: {
                    categories: <?= json_encode(array_map(function($item) { 
                        return $item['questions']; 
                    }, $questionDistributionData)) ?>,
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Poppins, sans-serif'
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: '',
                    },
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Poppins, sans-serif'
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " interviews";
                        }
                    }
                },
                colors: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6'],
                grid: {
                    borderColor: '#f1f1f1',
                    padding: {
                        bottom: 5
                    }
                }
            };

            var questionDistChart = new ApexCharts(document.querySelector("#questionDistributionChart"), questionDistOptions);
            questionDistChart.render();

            // Check if there are active filters and update the filters button appearance
            const hasActiveFilters = <?= ($filterClient || $filterPosition || $filterBusinessUnit || $filterGeo || $filterCandidateName || $filterApplicantId || $filterSystemIntegrator || $filterRecruiter || $filterStartDate || $filterEndDate || $filterAvailabilityAsked || $filterConfidentResponses || $filterChallengingQuestions || $filterExcludeIncomplete) ? 'true' : 'false' ?>;

            if (hasActiveFilters) {
                const filtersBtn = document.querySelector('[data-bs-toggle="modal"][data-bs-target="#advancedFiltersModal"]');
                if (filtersBtn) {
                    filtersBtn.innerHTML = '<i class="fas fa-filter me-1"></i> Filters Active';
                    filtersBtn.classList.remove('btn-outline-secondary');
                    filtersBtn.classList.add('btn-info');
                }
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>

    <script>
        var businessUnitBarOptions = {
    series: [{
        name: 'Interviews',
        data: <?= json_encode(array_map(function($item) { 
            return (int)$item['count']; 
        }, $topBusinessUnits)) ?>
    }, {
        name: 'Availability Asked',
        data: <?= json_encode(array_map(function($item) { 
            return (int)$item['availabilityAsked']; 
        }, $topBusinessUnits)) ?>
    }],
    chart: {
        type: 'bar',
        height: 280,
        stacked: false,
        toolbar: {
            show: false
        },
        fontFamily: 'Poppins, sans-serif'
    },
    plotOptions: {
        bar: {
            horizontal: true,
            columnWidth: '70%',
            borderRadius: 4,
            dataLabels: {
                position: 'top',
            },
        }
    },
    dataLabels: {
        enabled: false
    },
    xaxis: {
        categories: <?= json_encode(array_map(function($item) { 
            return $item['unit']; 
        }, $topBusinessUnits)) ?>,
        labels: {
            style: {
                fontSize: '12px'
            }
        }
    },
    yaxis: {
        title: {
            text: 'Business Units'
        }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return val + " interviews";
            }
        }
    },
    colors: ['#3498db', '#1abc9c'],
    legend: {
        position: 'top',
        horizontalAlign: 'right'
    }
};

var businessUnitBarChart = new ApexCharts(document.querySelector("#businessUnitBarChart"), businessUnitBarOptions);
businessUnitBarChart.render();

// Business Unit Donut Chart
var businessUnitDonutOptions = {
    series: <?= json_encode(array_map(function($item) { 
        return (int)$item['count']; 
    }, $topBusinessUnits)) ?>,
    chart: {
        type: 'donut',
        height: 280,
        fontFamily: 'Poppins, sans-serif'
    },
    labels: <?= json_encode(array_map(function($item) { 
        return $item['unit']; 
    }, $topBusinessUnits)) ?>,
    plotOptions: {
        pie: {
            donut: {
                size: '65%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '14px',
                        fontFamily: 'Montserrat, sans-serif',
                        color: undefined,
                        offsetY: -10
                    },
                    value: {
                        show: true,
                        fontSize: '20px',
                        fontFamily: 'Montserrat, sans-serif',
                        color: undefined,
                        offsetY: 16,
                        formatter: function (val) {
                            return val;
                        }
                    },
                    total: {
                        show: true,
                        label: 'Total',
                        color: '#373d3f',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => {
                                return a + b;
                            }, 0);
                        }
                    }
                }
            }
        }
    },
    dataLabels: {
        enabled: false
    },
    responsive: [{
        breakpoint: 480,
        options: {
            chart: {
                height: 200
            },
            legend: {
                show: false
            }
        }
    }],
    legend: {
        position: 'bottom',
        offsetY: 0,
        height: 40
    },
    colors: ['#3498db', '#1abc9c', '#f1c40f', '#e74c3c', '#9b59b6']
};

var businessUnitDonutChart = new ApexCharts(document.querySelector("#businessUnitDonutChart"), businessUnitDonutOptions);
businessUnitDonutChart.render();
</script>

<script>
    function exportAnalyticsData() {
    // Get current URL parameters to maintain filters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Build export URL with all current filters
    const exportUrl = new URL('export_data.php', window.location.origin + window.location.pathname.replace('dashboard.php', ''));
    
    // Add all current filter parameters
    urlParams.forEach((value, key) => {
        exportUrl.searchParams.append(key, value);
    });
    
    // Set export type
    exportUrl.searchParams.set('type', 'all');
    
    // Show loading notification
    showNotification('Preparing export... This may take a moment for large datasets.', 'info');
    
    // Create hidden link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl.toString();
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success notification after short delay
    setTimeout(() => {
        const totalInterviews = <?= $totalInterviews ?>;
        showNotification(`Complete analytics export started! Exporting ${totalInterviews} interview records with current filters.`, 'success');
    }, 1000);
}

function exportAllInterviews() {
    // Get current URL parameters to maintain filters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Build export URL with all current filters
    const exportUrl = new URL('export_data.php', window.location.origin + window.location.pathname.replace('dashboard.php', ''));
    
    // Add all current filter parameters
    urlParams.forEach((value, key) => {
        exportUrl.searchParams.append(key, value);
    });
    
    // Set export type to interviews only
    exportUrl.searchParams.set('type', 'interviews');
    
    // Show loading notification
    showNotification('Exporting interview records...', 'info');
    
    // Create hidden link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl.toString();
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Show success notification
    setTimeout(() => {
        const totalInterviews = <?= $totalInterviews ?>;
        showNotification(`Interview records export started! Exporting ${totalInterviews} records with current filters.`, 'success');
    }, 1000);
}

// Function to show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds for longer messages
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function exportWithDateRange() {
    const startDate = prompt('Enter start date (YYYY-MM-DD):');
    const endDate = prompt('Enter end date (YYYY-MM-DD):');
    
    if (startDate && endDate) {
        // Validate date format
        const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (!dateRegex.test(startDate) || !dateRegex.test(endDate)) {
            showNotification('Please enter dates in YYYY-MM-DD format', 'error');
            return;
        }
        
        const exportUrl = new URL('export_data.php', window.location.origin + window.location.pathname.replace('dashboard.php', ''));
        exportUrl.searchParams.set('type', 'all');
        exportUrl.searchParams.set('start_date', startDate);
        exportUrl.searchParams.set('end_date', endDate);
        
        const link = document.createElement('a');
        link.href = exportUrl.toString();
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification(`Exporting data from ${startDate} to ${endDate}...`, 'success');
    }
}

function exportAllData() {
    if (confirm('This will export ALL interview records without any filters. This may be a large file. Continue?')) {
        const exportUrl = new URL('export_data.php', window.location.origin + window.location.pathname.replace('dashboard.php', ''));
        exportUrl.searchParams.set('type', 'all');
        // Don't add any filter parameters to get all data
        
        showNotification('Exporting ALL interview records... This may take several minutes.', 'info');
        
        const link = document.createElement('a');
        link.href = exportUrl.toString();
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Function to export specific data sets
function exportTableData(tableId, filename) {
    const table = document.querySelector(tableId);
    if (!table) {
        showNotification('Table not found!', 'error');
        return;
    }
    
    let csvContent = '';
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const csvRow = [];
        
        cols.forEach(col => {
            // Clean up the cell content
            let cellText = col.textContent.trim();
            // Handle status indicators
            if (col.querySelector('.status-text')) {
                cellText = col.querySelector('.status-text').textContent.trim();
            }
            // Escape quotes and wrap in quotes if contains comma
            if (cellText.includes(',') || cellText.includes('"') || cellText.includes('\n')) {
                cellText = '"' + cellText.replace(/"/g, '""') + '"';
            }
            csvRow.push(cellText);
        });
        
        csvContent += csvRow.join(',') + '\n';
    });
    
    // Create and download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification(`${filename} exported successfully!`, 'success');
}

// Export functions for different data sets
function exportRecentInterviews() {
    exportAllInterviews();
}

function exportBusinessUnitData() {
    // For now, export as part of analytics data since business unit data is aggregated
    exportAnalyticsData();
}
</script>
</body>
</html>