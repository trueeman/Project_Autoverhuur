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

    // Debugging: Check if $_POST is populated
    var_dump($_POST); // Output the $_POST array to see what data is being sent

    // Check if a file was uploaded
    if (isset($_FILES['car_image'])) {
        // Check for any errors during upload
        if ($_FILES['car_image']['error'] !== UPLOAD_ERR_OK) {
            die("File upload error: " . $_FILES['car_image']['error']);
        }

        // Define image path
        $imagePath = '/Project_Autoverhuur/Img/' . basename($_FILES['car_image']['name']);

        // Move the uploaded file to the specified directory
        if (move_uploaded_file($_FILES['car_image']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $imagePath)) {
            // Check if car_id is set in the POST request
            if (isset($_POST['car_id'])) {
                $car_id = $_POST['car_id']; // Get car_id from the POST request
                
                // Save to database
                $stmt = $pdo->prepare("INSERT INTO images (car_id, image_path) VALUES (:car_id, :image_path)");
                $stmt->execute(['car_id' => $car_id, 'image_path' => $imagePath]);

                // Check if the image was uploaded successfully
                if ($stmt->rowCount()) {
                    echo "Image uploaded successfully!";
                } else {
                    echo "Error uploading the image.";
                }
            } else {
                echo "Car ID is not set."; // This indicates the issue
            }
        } else {
            echo "Failed to move uploaded file.";
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
    <label for="car_id">Car ID:</label>
    <input type="hidden" name="car_id" id="car_id" value="60"> 

    <label for="car_image">Choose an image:</label>
    <input type="file" name="car_image" id="car_image" required>
    <input type="submit" value="Upload Image">
</form>
