<?php
include 'config.php';

// Get all table names
$tablesResult = $conn->query("SHOW TABLES");

if ($tablesResult->num_rows > 0) {
    while ($row = $tablesResult->fetch_array()) {
        $table = $row[0];
        echo "<h3>Table: $table</h3>";

        // Get columns for each table
        $columnsResult = $conn->query("SHOW COLUMNS FROM $table");

        if ($columnsResult->num_rows > 0) {
            echo "<ul>";
            while ($column = $columnsResult->fetch_assoc()) {
                echo "<li><strong>{$column['Field']}</strong> - {$column['Type']}</li>";
            }
            echo "</ul>";
        } else {
            echo "No columns found in $table.";
        }
    }
} else {
    echo "No tables found in the database.";
}

$conn->close();
?>
