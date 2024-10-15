<?php
$host = 'localhost';
$db   = 'autoverhuur';
$user = 'root';
$pass = "";
$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query("SELECT data FROM images WHERE id = 1 LIMIT 1");
    $image = $stmt->fetch();

    if ($image) {
        header("Content-Type: image/jpg");
        echo $image['data'];
    } else {
        echo "No image found.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
