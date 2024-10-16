<?php
$host = 'localhost';   // Your MySQL host
$db   = 'autoverhuur'; // Your database name
$user = 'root';        // Your MySQL username
$pass = '';            // Your MySQL password

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Check if a file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = $_FILES['image']['name'];
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $imageType = $_FILES['image']['type'];

        // Prepare the SQL statement
        $stmt = $pdo->prepare("INSERT INTO images (name, image_data) VALUES (:name, :image_data)");
        $stmt->bindParam(':name', $imageName);
        $stmt->bindParam(':image_data', $imageData, PDO::PARAM_LOB);

        // Execute the statement
        if ($stmt->execute()) {
            echo "Image uploaded successfully!";
        } else {
            echo "Error uploading the image.";
        }
    } else {
        echo "No file uploaded or an error occurred during upload.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!-- HTML form for uploading the image -->
<form method="POST" enctype="multipart/form-data">
    <label for="image">Choose an image:</label>
    <input type="file" name="image" id="image" required>
    <input type="submit" value="Upload Image">
</form>