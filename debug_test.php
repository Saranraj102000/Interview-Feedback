<?php
header('Content-Type: application/json');

try {
    // Test 1: Check if config.php exists
    if (!file_exists('config.php')) {
        throw new Exception('config.php file not found');
    }
    
    // Test 2: Include config
    require_once 'config.php';
    
    // Test 3: Check connection variable
    if (!isset($conn)) {
        throw new Exception('Database connection variable $conn not found');
    }
    
    // Test 4: Test connection
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test 5: Check table exists
    $result = mysqli_query($conn, "SHOW TABLES LIKE 'feedback'");
    if (mysqli_num_rows($result) == 0) {
        throw new Exception('Table "feedback" does not exist');
    }
    
    // Test 6: Check table structure
    $result = mysqli_query($conn, "DESCRIBE feedback");
    $columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $columns[] = $row['Field'];
    }
    
    // Test 7: Check for required new columns
    $required_columns = ['geo', 'candidate_name', 'candidate_email', 'applicant_id'];
    $missing_columns = [];
    
    foreach ($required_columns as $col) {
        if (!in_array($col, $columns)) {
            $missing_columns[] = $col;
        }
    }
    
    // Return results
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'table_columns' => $columns,
        'missing_columns' => $missing_columns,
        'database_ready' => empty($missing_columns)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>