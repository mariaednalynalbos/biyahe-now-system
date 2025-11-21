<?php
require_once 'db.php';

try {
    // Check what tables exist
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Available Tables:</h3>";
    echo "<pre>";
    print_r($tables);
    echo "</pre>";
    
    // Check accounts table structure if it exists
    if (in_array('accounts', $tables)) {
        echo "<h3>Accounts Table Structure:</h3>";
        $columns = $pdo->query("DESCRIBE accounts")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        echo "<h3>Sample Accounts Data:</h3>";
        $sample = $pdo->query("SELECT * FROM accounts LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($sample);
        echo "</pre>";
    }
    
    // Check passengers table structure if it exists
    if (in_array('passengers', $tables)) {
        echo "<h3>Passengers Table Structure:</h3>";
        $columns = $pdo->query("DESCRIBE passengers")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        echo "<h3>Sample Passengers Data:</h3>";
        $sample = $pdo->query("SELECT * FROM passengers LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($sample);
        echo "</pre>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>