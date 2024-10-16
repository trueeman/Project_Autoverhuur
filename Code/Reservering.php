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

// Function to map status to display values
function getStatusDisplay($status) {
    $statusInfo = [
        'Pending' => ['class' => 'badge-pending', 'text' => 'In afwachting'],
        'Confirmed' => ['class' => 'badge-confirmed', 'text' => 'Bevestigd'],
        'Cancelled' => ['class' => 'badge-cancelled', 'text' => 'Geannuleerd'],
        'Completed' => ['class' => 'badge-completed', 'text' => 'Voltooid']
    ];

    return $statusInfo[$status] ?? ['class' => 'badge-unknown', 'text' => 'Onbekend'];
}

// Function to insert an image into the database, if it doesn't already exist
function insertImage($pdo, $imagePath) {
    if (!file_exists($imagePath)) {
        throw new Exception("Bestand niet gevonden: $imagePath");
    }

    $imageData = file_get_contents($imagePath);
    $imageName = basename($imagePath);

    // Check if the image already exists in the database by its name or data
    $stmt = $pdo->prepare("SELECT id FROM images WHERE name = :name OR data = :data LIMIT 1");
    $stmt->bindParam(':name', $imageName);
    $stmt->bindParam(':data', $imageData, PDO::PARAM_LOB);
    $stmt->execute();
    $existingImage = $stmt->fetch();

    // If the image already exists, skip the insertion
    if ($existingImage) {
        echo "Afbeelding al in de database: " . $imageName . "<br>";
        return;
    }

    // Insert the image into the database if it doesn't exist
    $stmt = $pdo->prepare("INSERT INTO images (name, data) VALUES (:name, :data)");
    $stmt->bindParam(':name', $imageName);
    $stmt->bindParam(':data', $imageData, PDO::PARAM_LOB);

    if ($stmt->execute()) {
        echo "Afbeelding succesvol ingevoegd: " . $imageName . "<br>";
    } else {
        echo "Fout bij het invoegen van de afbeelding: " . $imageName . "<br>";
    }
}

// Function to retrieve an image by ID
function getImage($pdo, $id) {
    $stmt = $pdo->prepare("SELECT data FROM images WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);  // Fetch as associative array to access 'data'
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Insert image (this can be moved to a separate script for upload functionality)
    $imagePath = $_SERVER["DOCUMENT_ROOT"] . "/Project_Autoverhuur/Img/Demon Dodge 170.jpg";
    insertImage($pdo, $imagePath);

    // Query for reservations and images
    $stmt = $pdo->prepare("
        SELECT 
            CONCAT(u.first_name, ' ', u.last_name) as klantnaam,
            c.make as automerk,
            c.model as automodel,
            i.id as image_id,  -- Get the image ID for retrieval
            r.start_date as startdatum,
            r.end_date as einddatum,
            r.total_price as totaalprijs,
            rs.status_name as status
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN cars c ON r.car_id = c.car_id
        JOIN rental_statuses rs ON r.status_id = rs.status_id
        LEFT JOIN images i ON i.id = c.images_id  -- Assuming images are linked to cars
        ORDER BY u.last_name, r.start_date
    ");

    $stmt->execute();

} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Reserveringen</title>
</head>
<body class="bg-gray-100">
    <nav class="nav-container">
        <div class="nav-content">
            <div class="logo-container">
                <svg class="car-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 16H9m10 0h3v-3.15a1 1 0 00-.84-.99L16 11l-2.7-3.6a1 1 0 00-.8-.4H5.24a2 2 0 00-1.8 1.1l-.8 1.63A6 6 0 002 12.42V16h2"></path>
                    <circle cx="6.5" cy="16.5" r="2.5"></circle>
                    <circle cx="16.5" cy="16.5" r="2.5"></circle>
                </svg>
                <h1>Reserveringen</h1>
            </div>
            <div class="nav-buttons">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="#" class="nav-link">Klanten</a>
                <a href="#" class="nav-link">Dashboard</a>
                <a href="Home.html" class="nav-link">Home</a>
                <button class="nav-link login">Login</button>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Reserveringsoverzicht</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Klantnaam</th>
                            <th>Automerk</th>
                            <th>Model</th>
                            <th>Afbeelding</th>  
                            <th>Startdatum</th>
                            <th>Einddatum</th>
                            <th>Totaalprijs</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    while ($reservation = $stmt->fetch()) {
        $statusInfo = getStatusDisplay($reservation['status']);

        // Prepare image data for the current reservation
        $imageSrc = '';
        if ($reservation['image_id']) {
            // Get image data for the current reservation
            $image = getImage($pdo, $reservation['image_id']);
            if ($image && isset($image['data'])) {
                $imageData = base64_encode($image['data']);  // Encode image data to base64
                $imageSrc = 'data:image/jpeg;base64,' . $imageData;  // Set up the src attribute for the image
            } else {
                $imageSrc = 'path/to/placeholder.jpg';  // Fallback image path if the image is not found
            }
        }

        // Output reservation details along with the image
        echo "<tr>";
        echo "<td>" . htmlspecialchars($reservation['klantnaam']) . "</td>";
        echo "<td>" . htmlspecialchars($reservation['automerk']) . "</td>";
        echo "<td>" . htmlspecialchars($reservation['automodel']) . "</td>";
        
        // Display the image in a table cell
        echo "<td><img src='" . htmlspecialchars($imageSrc) . "' alt='Car Image' width='100'/></td>";
        
        echo "<td>" . date('d-m-Y', strtotime($reservation['startdatum'])) . "</td>";
        echo "<td>" . date('d-m-Y', strtotime($reservation['einddatum'])) . "</td>";
        echo "<td>€" . number_format($reservation['totaalprijs'], 2) . "</td>";
        echo "<td><span class='status-badge " . $statusInfo['class'] . "'>" 
            . htmlspecialchars($statusInfo['text']) . "</span></td>";
        echo "</tr>";
    }
    ?>
                    </tbody>
                </table>
            </div>
            <div class="crud-button-container">
                <button class="crud-btn">Nieuwe Reservering</button>
                <button class="crud-btn">Exporteer Overzicht</button>
            </div>
        </div>
    </main>
</body>
</html>
