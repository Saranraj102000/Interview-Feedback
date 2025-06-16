<?php
// export_data.php - Handle data export requests

// Database connection parameters (same as dashboard)
$host = "localhost";
$username = "root";
$password = "";
$database = "vdart_feedback";

header('Content-Type: text/csv; charset=utf-8');

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get export type
    $exportType = $_GET['type'] ?? 'all';
    
    // Get all filter parameters (same logic as dashboard.php)
    $selectedPeriod = $_GET['period'] ?? 'all';
    $filterGeo = $_GET['geo'] ?? '';
    $filterCandidateName = $_GET['candidate_name'] ?? '';
    $filterApplicantId = $_GET['applicant_id'] ?? '';
    $filterSystemIntegrator = $_GET['system_integrator'] ?? '';
    $filterRecruiter = $_GET['recruiter_name'] ?? '';
    $filterClient = $_GET['client'] ?? '';
    $filterPosition = $_GET['position'] ?? '';
    $filterBusinessUnit = $_GET['business_unit'] ?? '';
    $filterStartDate = $_GET['start_date'] ?? '';
    $filterEndDate = $_GET['end_date'] ?? '';
    $filterAvailabilityAsked = $_GET['availability_asked'] ?? '';
    $filterConfidentResponses = $_GET['confident_responses'] ?? '';
    $filterChallengingQuestions = $_GET['challenging_questions'] ?? '';
    $filterExcludeIncomplete = $_GET['exclude_incomplete'] ?? '';
    
    // Build the same filter SQL as dashboard
    $dateFilterSQL = "";
    $whereClauseAdded = false;
    $params = [];
    
    // Apply period filters
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
    }
    
    // Apply all other filters (same logic as dashboard)
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
    
    // Set filename based on filters
    $filename = 'VDart_Interview_Export_' . date('Y-m-d');
    if ($filterClient) $filename .= '_' . preg_replace('/[^a-zA-Z0-9]/', '', $filterClient);
    if ($filterPosition) $filename .= '_' . preg_replace('/[^a-zA-Z0-9]/', '', $filterPosition);
    if ($filterBusinessUnit) $filename .= '_' . preg_replace('/[^a-zA-Z0-9]/', '', $filterBusinessUnit);
    $filename .= '.csv';
    
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    if ($exportType === 'all') {
        // Export complete analytics report
        
        // Get summary data first
        $metricsSQL = "
            SELECT 
                COUNT(*) as total_interviews,
                SUM(CASE WHEN asked_availability = 'yes' THEN 1 ELSE 0 END) as availability_asked,
                SUM(CASE WHEN answered_confidently = 'yes' THEN 1 ELSE 0 END) as answered_confidently,
                SUM(CASE WHEN had_challenging_questions = 'yes' THEN 1 ELSE 0 END) as challenging_questions,
                SUM(LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1) as total_questions
            FROM feedback
            $dateFilterSQL
        ";
        
        $stmt = $conn->prepare($metricsSQL);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Write summary section
        fputcsv($output, ['VDART INTERVIEW ANALYTICS EXPORT']);
        fputcsv($output, ['Export Date', date('Y-m-d H:i:s')]);
        fputcsv($output, ['Filter Applied', $selectedPeriod]);
        if ($filterClient) fputcsv($output, ['Client Filter', $filterClient]);
        if ($filterPosition) fputcsv($output, ['Position Filter', $filterPosition]);
        if ($filterBusinessUnit) fputcsv($output, ['Business Unit Filter', $filterBusinessUnit]);
        if ($filterGeo) fputcsv($output, ['GEO Filter', $filterGeo]);
        if ($filterCandidateName) fputcsv($output, ['Candidate Name Filter', $filterCandidateName]);
        if ($filterApplicantId) fputcsv($output, ['Applicant ID Filter', $filterApplicantId]);
        fputcsv($output, []);
        
        // Write metrics
        fputcsv($output, ['KEY METRICS']);
        fputcsv($output, ['Total Interviews', $metrics['total_interviews']]);
        fputcsv($output, ['Total Questions', $metrics['total_questions'] ?? 0]);
        fputcsv($output, ['Availability Asked', $metrics['availability_asked']]);
        fputcsv($output, ['Confident Responses', $metrics['answered_confidently']]);
        fputcsv($output, ['Challenging Questions', $metrics['challenging_questions']]);
        fputcsv($output, ['Avg Questions per Interview', $metrics['total_interviews'] > 0 ? round(($metrics['total_questions'] ?? 0) / $metrics['total_interviews'], 1) : 0]);
        fputcsv($output, []);
        
        // Write all interview records header
        fputcsv($output, ['ALL INTERVIEW RECORDS (FILTERED)']);
        fputcsv($output, [
            'ID', 'Interview Date', 'Candidate Name', 'Candidate Email', 'Applicant ID',
            'Position', 'Client Name', 'System Integrator', 'Interviewer Name', 
            'GEO', 'Recruiter Name', 'Business Unit', 'Employee ID',
            'Availability Asked', 'Confident Responses', 'Challenging Questions', 
            'Challenging Explanation', 'Additional Comments', 'Client Questions Count', 'Created At'
        ]);
        
    } elseif ($exportType === 'interviews') {
        // Export only interview records
        fputcsv($output, [
            'ID', 'Interview Date', 'Candidate Name', 'Candidate Email', 'Applicant ID',
            'Position', 'Client Name', 'System Integrator', 'Interviewer Name', 
            'GEO', 'Recruiter Name', 'Business Unit', 'Employee ID',
            'Availability Asked', 'Confident Responses', 'Challenging Questions', 
            'Challenging Explanation', 'Additional Comments', 'Client Questions Count', 'Created At'
        ]);
    }
    
    // Get ALL filtered interview records (not limited to 20)
    $allInterviewsSQL = "
        SELECT 
            id, interview_date, candidate_name, candidate_email, applicant_id,
            position, client_name, system_integrator, interviewer_name,
            geo, recruiter_name, business_unit, employee_id,
            asked_availability, answered_confidently, had_challenging_questions,
            challenging_explanation, additional_comments,
            CASE 
                WHEN client_questions IS NOT NULL AND client_questions != '' 
                THEN LENGTH(client_questions) - LENGTH(REPLACE(client_questions, ',', '')) + 1 
                ELSE 0 
            END as question_count,
            created_at
        FROM feedback
        $dateFilterSQL
        ORDER BY interview_date DESC
    ";
    
    $stmt = $conn->prepare($allInterviewsSQL);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    
    // Write all interview records
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['interview_date'],
            $row['candidate_name'] ?? '',
            $row['candidate_email'] ?? '',
            $row['applicant_id'] ?? '',
            $row['position'] ?? '',
            $row['client_name'] ?? '',
            $row['system_integrator'] ?? '',
            $row['interviewer_name'] ?? '',
            $row['geo'] ?? '',
            $row['recruiter_name'] ?? '',
            $row['business_unit'] ?? '',
            $row['employee_id'] ?? '',
            $row['asked_availability'] === 'yes' ? 'Yes' : 'No',
            $row['answered_confidently'] === 'yes' ? 'Yes' : 'No',
            $row['had_challenging_questions'] === 'yes' ? 'Yes' : 'No',
            $row['challenging_explanation'] ?? '',
            $row['additional_comments'] ?? '',
            $row['question_count'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    
} catch(PDOException $e) {
    header('Content-Type: text/plain');
    echo "Error: Unable to export data. Please try again later.";
    error_log("Export Error: " . $e->getMessage());
}
?>