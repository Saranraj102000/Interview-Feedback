<?php
// get_feedback.php - Retrieves feedback records from the database

// Include database configuration
require_once 'config.php';

// Set response headers
header('Content-Type: application/json');

// Get query parameters
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$client = isset($_GET['client']) ? $_GET['client'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100; // Default limit of 100 records
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

// Build the SQL query based on parameters
if ($id !== null) {
    // Get a specific feedback record by ID
    $sql = "SELECT * FROM feedback WHERE id = $id";
} else if ($client !== null) {
    // Get feedback records for a specific client
    $client = mysqli_real_escape_string($conn, $client);
    $sql = "SELECT * FROM feedback WHERE client_name LIKE '%$client%' ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
} else if ($start_date !== null && $end_date !== null) {
    // Get feedback records within a date range
    $start_date = mysqli_real_escape_string($conn, $start_date);
    $end_date = mysqli_real_escape_string($conn, $end_date);
    $sql = "SELECT * FROM feedback WHERE interview_date BETWEEN '$start_date' AND '$end_date' ORDER BY interview_date DESC LIMIT $limit OFFSET $offset";
} else {
    // Get all feedback records
    $sql = "SELECT * FROM feedback ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
}

// Execute the query
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving feedback records',
        'error' => mysqli_error($conn)
    ]);
    exit;
}

// Check if any records were found
if (mysqli_num_rows($result) > 0) {
    $records = [];
    
    // Fetch all records as associative arrays
    while ($row = mysqli_fetch_assoc($result)) {
        // Transform the client_questions JSON string back to an array
        if (isset($row['client_questions'])) {
            $row['client_questions'] = json_decode($row['client_questions'], true);
        }
        
        // Format the record into a nested structure
        $record = [
            'id' => $row['id'],
            'recruiterInfo' => [
                'employeeId' => $row['employee_id'],
                'name' => $row['recruiter_name'],
                'email' => $row['recruiter_email'],
                'businessUnit' => $row['business_unit']
            ],
            'interviewDetails' => [
                'date' => $row['interview_date'],
                'position' => $row['position'],
                'systemIntegrator' => $row['system_integrator'],
                'clientName' => $row['client_name'],
                'interviewerName' => $row['interviewer_name']
            ],
            'clientQuestions' => $row['client_questions'],
            'candidateReflections' => [
                'askedAvailability' => $row['asked_availability'],
                'answeredConfidently' => $row['answered_confidently'],
                'hadChallengingQuestions' => $row['had_challenging_questions'],
                'challengingExplanation' => $row['challenging_explanation']
            ],
            'additionalComments' => $row['additional_comments'],
            'submittedAt' => $row['created_at'],
            'updatedAt' => $row['updated_at']
        ];
        
        $records[] = $record;
    }
    
    // Return single record or array of records
    if ($id !== null && count($records) === 1) {
        echo json_encode([
            'success' => true,
            'feedback' => $records[0]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'count' => count($records),
            'feedback' => $records
        ]);
    }
} else {
    // No records found
    if ($id !== null) {
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false,
            'message' => 'Feedback record not found'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'count' => 0,
            'feedback' => []
        ]);
    }
}

// Close the database connection
mysqli_close($conn);
?>