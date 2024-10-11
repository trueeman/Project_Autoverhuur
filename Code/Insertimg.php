<?php
$host = 'localhost';
$dbname = 'autoverhuur';
$username = 'root';
$password = '';

try {
    // Maak een PDO-verbinding
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Stel de PDO-foutmodus in op exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL om de tabel te maken
    $sql_create_table = "CREATE TABLE IF NOT EXISTS images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        data LONGBLOB NOT NULL,
        upload_date DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql_create_table);
    echo "Tabel 'images' succesvol aangemaakt <br>";

    // Pad naar je afbeelding
    $image_path =  $_SERVER["DOCUMENT_ROOT"] . "/Project_Autoverhuur/Img/Demon Dodge 170.jpg";

    // Controleer of het bestand bestaat
    if (!file_exists($image_path)) {
        throw new Exception("Bestand niet gevonden: $image_path");
    }

    // Lees de afbeeldingsdata
    $image_data = file_get_contents($image_path);
    $image_name = basename($image_path);

    // Bereid de SQL-statement voor
    $stmt = $pdo->prepare("INSERT INTO images (name, data) VALUES (:name, :data)");

    // Bind de parameters en voer de statement uit
    $stmt->bindParam(':name', $image_name);
    $stmt->bindParam(':data', $image_data, PDO::PARAM_LOB);

    if ($stmt->execute()) {
        echo "Afbeelding succesvol ingevoegd";
    } else {
        echo "Fout bij het invoegen van de afbeelding";
    }

} catch(PDOException $e) {
    echo "Databasefout: " . $e->getMessage();
} catch(Exception $e) {
    echo "Fout: " . $e->getMessage();
}
?>