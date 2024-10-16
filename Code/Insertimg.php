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
    // Upload logica voor afbeelding
if(isset($_FILES['car_image'])) {
    $imagePath = '/Project_Autoverhuur/Img/' . basename($_FILES['car_image']['name']);
    move_uploaded_file($_FILES['car_image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath);
    
    // Opslaan in database
    $stmt = $pdo->prepare("INSERT INTO images (car_id, image_path) VALUES (:car_id, :image_path)");
    $stmt->execute(['car_id' => $car_id, 'image_path' => $imagePath]);
}
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