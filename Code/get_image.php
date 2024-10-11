<?php
// Database connection setup
$host = 'localhost';
$db   = 'autoverhuur';
$user = 'root';
$pass = '';

$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
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
    } else {
        echo "Afbeelding niet gevonden.";
    }

} catch (PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
}
?>
