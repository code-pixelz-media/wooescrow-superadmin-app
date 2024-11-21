<?php
try {
    $dbConnection = new PDO('mysql:host=localhost;dbname=local', 'root', 'root');
    $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>