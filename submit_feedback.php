<?php
// Prevent any output before headers
ob_start();

// Turn off error display to prevent HTML in JSON response
ini_set('display_errors', 0);
error_reporting(0);

try {
    // submit_feedback.php - Handles form submission and stores data in MySQL

    // Include database configuration
    if (!file_exists('config.php')) {
        throw new Exception('Configuration file not found');
    }
    
    require_once 'config.php';

    // Clean any output buffer and set headers
    ob_clean();
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');

    // Check database connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if the request is a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
        exit;
    }

    // Get the raw POST data and decode JSON
    $json_data = file_get_contents('php://input');
    if (!$json_data) {
        throw new Exception('No data received');
    }

    $data = json_decode($json_data, true);

    // Check if JSON decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields (updated with new fields)
    $required_fields = [
        'recruiterInfo.employeeId',
        'recruiterInfo.name', 
        'recruiterInfo.email',
        'recruiterInfo.businessUnit',
        'interviewDetails.date',
        'interviewDetails.position',
        'interviewDetails.systemIntegrator',
        'interviewDetails.clientName',
        'interviewDetails.interviewerName',
        'interviewDetails.geo',
        'candidateInfo.name',
        'candidateInfo.email',
        'candidateInfo.applicantId',
        'candidateReflections.askedAvailability',
        'candidateReflections.answeredConfidently',
        'candidateReflections.hadChallengingQuestions'
    ];

    $validation_errors = [];
    foreach ($required_fields as $field) {
        $path = explode('.', $field);
        $temp = $data;
        foreach ($path as $key) {
            if (!isset($temp[$key]) || empty($temp[$key])) {
                $validation_errors[] = "Field '$field' is required";
                break;
            }
            $temp = $temp[$key];
        }
    }

    if (!empty($validation_errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validation errors', 'errors' => $validation_errors]);
        exit;
    }

    // Extract data from the request (updated with new fields)
    $employee_id = mysqli_real_escape_string($conn, $data['recruiterInfo']['employeeId']);
    $recruiter_name = mysqli_real_escape_string($conn, $data['recruiterInfo']['name']);
    $recruiter_email = mysqli_real_escape_string($conn, $data['recruiterInfo']['email']);
    $business_unit = mysqli_real_escape_string($conn, $data['recruiterInfo']['businessUnit']);

    $interview_date = mysqli_real_escape_string($conn, $data['interviewDetails']['date']);
    $position = mysqli_real_escape_string($conn, $data['interviewDetails']['position']);
    $system_integrator = mysqli_real_escape_string($conn, $data['interviewDetails']['systemIntegrator']);
    $client_name = mysqli_real_escape_string($conn, $data['interviewDetails']['clientName']);
    $interviewer_name = mysqli_real_escape_string($conn, $data['interviewDetails']['interviewerName']);
    
    // NEW: Extract client manager fields (optional)
    $client_manager_name = isset($data['interviewDetails']['clientManagerName']) ? 
        mysqli_real_escape_string($conn, $data['interviewDetails']['clientManagerName']) : '';
    $client_manager_email = isset($data['interviewDetails']['clientManagerEmail']) ? 
        mysqli_real_escape_string($conn, $data['interviewDetails']['clientManagerEmail']) : '';
    
    $geo = mysqli_real_escape_string($conn, $data['interviewDetails']['geo']);

    $candidate_name = mysqli_real_escape_string($conn, $data['candidateInfo']['name']);
    $candidate_email = mysqli_real_escape_string($conn, $data['candidateInfo']['email']);
    $applicant_id = mysqli_real_escape_string($conn, $data['candidateInfo']['applicantId']);

    $questions = isset($data['clientQuestions']) ? $data['clientQuestions'] : [];
    $questions_json = mysqli_real_escape_string($conn, json_encode($questions));

    $asked_availability = mysqli_real_escape_string($conn, $data['candidateReflections']['askedAvailability']);
    $answered_confidently = mysqli_real_escape_string($conn, $data['candidateReflections']['answeredConfidently']);
    $had_challenging_questions = mysqli_real_escape_string($conn, $data['candidateReflections']['hadChallengingQuestions']);

    $challenging_explanation = '';
    if (isset($data['candidateReflections']['challengingExplanation'])) {
        $challenging_explanation = mysqli_real_escape_string($conn, $data['candidateReflections']['challengingExplanation']);
    }

    $additional_comments = '';
    if (isset($data['additionalComments'])) {
        $additional_comments = mysqli_real_escape_string($conn, $data['additionalComments']);
    }

    // Check if required columns exist in the table
    $check_columns = "SHOW COLUMNS FROM feedback LIKE 'geo'";
    $result = mysqli_query($conn, $check_columns);
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Database schema not updated. Please run the ALTER TABLE commands to add missing columns.');
    }

    // Create SQL INSERT statement (updated with new client manager fields)
    $sql = "INSERT INTO feedback (
                employee_id, recruiter_name, recruiter_email, business_unit,
                interview_date, position, system_integrator, client_name, interviewer_name, 
                client_manager_name, client_manager_email, geo,
                candidate_name, candidate_email, applicant_id,
                client_questions,
                asked_availability, answered_confidently, had_challenging_questions, challenging_explanation,
                additional_comments
            ) VALUES (
                '$employee_id', '$recruiter_name', '$recruiter_email', '$business_unit',
                '$interview_date', '$position', '$system_integrator', '$client_name', '$interviewer_name',
                '$client_manager_name', '$client_manager_email', '$geo',
                '$candidate_name', '$candidate_email', '$applicant_id',
                '$questions_json',
                '$asked_availability', '$answered_confidently', '$had_challenging_questions', '$challenging_explanation',
                '$additional_comments'
            )";

    // Execute the query
    if (mysqli_query($conn, $sql)) {
        $feedback_id = mysqli_insert_id($conn);
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'feedbackId' => $feedback_id
        ]);
    } else {
        throw new Exception('Database insert failed: ' . mysqli_error($conn));
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
} finally {
    // Close the database connection if it exists
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
    
    // End output buffering and send response
    ob_end_flush();
}
?>