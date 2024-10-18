<?php
// Database connection setup
$host = 'localhost';
$db   = 'autoverhuur';
$db = 'autoverhuur';
$user = 'root';
$pass = '';
$pass = "";
$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Get the image ID from the URL parameter
    $image_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    // Retrieve image data from the database
    $stmt = $pdo->prepare("SELECT name, data FROM images WHERE id = :id");
    $stmt->execute(['id' => $image_id]);
    if ($stmt->rowCount() > 0) {
        $image = $stmt->fetch();
        // Set the content type header based on image type
        header("Content-Type: image/jpeg"); // Adjust this if your images are not JPEG
        echo $image['data']; // Output the image data directly
    
    // Get the image ID from the query string
    $imageId = isset($_GET['id']) ? intval($_GET['id']) : 1;
    
    $stmt = $pdo->prepare("SELECT data FROM images WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $imageId]);
    $image = $stmt->fetch();
    
    if ($image) {
        header("Content-Type: image/jpeg");
        echo $image['data'];
    } else {
        echo "Afbeelding niet gevonden.";
        header("HTTP/1.0 404 Not Found");
        echo "No image found.";
    }
} catch (PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
    header("HTTP/1.0 500 Internal Server Error");
    error_log("Database Error: " . $e->getMessage());
    echo "An error occurred while retrieving the image.";
}
?>
