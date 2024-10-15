<?php
$host = 'localhost';
$db = 'autoverhuur';
$user = 'root';
$pass = "";
$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Get the image ID from the query string
    $imageId = isset($_GET['id']) ? intval($_GET['id']) : 1;
    
    $stmt = $pdo->prepare("SELECT data FROM images WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $imageId]);
    $image = $stmt->fetch();
    
    if ($image) {
        header("Content-Type: image/jpeg");
        echo $image['data'];
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "No image found.";
    }
} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    error_log("Database Error: " . $e->getMessage());
    echo "An error occurred while retrieving the image.";
}
?>