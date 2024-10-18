<?php
$host = 'localhost';   
$db   = 'autoverhuur'; 
$user = 'root';        
$pass = '';            

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    if (isset($_FILES['car_image'])) {
        if ($_FILES['car_image']['error'] !== UPLOAD_ERR_OK) {
            die("File upload error: " . $_FILES['car_image']['error']);
        }

        $fileData = file_get_contents($_FILES['car_image']['tmp_name']);
        $fileName = $_FILES['car_image']['name'];

        if (isset($_POST['car_id'])) {
            $car_id = $_POST['car_id'];

            $stmt = $pdo->prepare("INSERT INTO images (car_id, name, data, upload_date) VALUES (:car_id, :name, :data, NOW())");
            $stmt->execute([
                'car_id' => $car_id,
                'name' => $fileName,
                'data' => $fileData
            ]);

            if ($stmt->rowCount()) {
                echo "Image uploaded successfully!";
            } else {
                echo "Error uploading the image.";
            }
        } else {
            echo "Car ID is not set.";
        }
    } else {
        echo "No file uploaded.";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!-- HTML form for uploading the image -->
<form method="POST" enctype="multipart/form-data">
    <label for="car_id">Car ID:</label>
    <input type="hidden" name="car_id" id="car_id" value="1"> 

    <label for="car_image">Choose an image:</label>
    <input type="file" name="car_image" id="car_image" required>
    <input type="submit" value="Upload Image">
</form>
