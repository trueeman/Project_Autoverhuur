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

// Function to retrieve an image by car ID
function getImage($pdo, $imageId) {
    $stmt = $pdo->prepare("SELECT data FROM images WHERE id = :id LIMIT 1");
    $stmt->bindParam(':id', $imageId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

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
    echo "Error: " . $e->getMessage();
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
                            <th>Afbeelding</th>  <!-- New column for image -->
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

                            // Initialize the image source with a default placeholder
                            $imageSrc = 'placeholder.jpg';  // Default placeholder image (you need to have this image in your project)

                            if ($reservation['image_id']) {
                                // Get image data for the current reservation
                                $image = getImage($pdo, $reservation['image_id']);
                                if ($image) {
                                    // If image found, convert it to base64 and display it
                                    $imageData = base64_encode($image['data']);
                                    $imageSrc = 'data:image/jpg;base64,' . $imageData;
                                }
                            }

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($reservation['klantnaam']) . "</td>";
                            echo "<td>" . htmlspecialchars($reservation['automerk']) . "</td>";
                            echo "<td>" . htmlspecialchars($reservation['automodel']) . "</td>";
                            echo "<td><img src='" . htmlspecialchars($imageSrc) . "' alt='Car Image' width='100'/></td>";  // Display the image
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
