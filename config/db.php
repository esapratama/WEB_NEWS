<?php
require __DIR__ . '/../vendor/autoload.php'; 

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->basisdata_news; 
} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
}
?>
