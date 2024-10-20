<?php
session_start();

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

function getStatusDisplay($status) {
    switch(strtolower($status)) {
        case 'voltooid':
            return ['class' => 'completed', 'text' => 'Voltooid'];
        case 'geboekt':
            return ['class' => 'available', 'text' => 'Geboekt'];
        case 'in behandeling':
            return ['class' => 'pending', 'text' => 'In behandeling'];
        default:
            return ['class' => 'unavailable', 'text' => $status];
    }
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['user_id'];

    // Query for user-specific reservations
    $stmt = $pdo->prepare("
        SELECT 
            r.rental_id,
            c.make as automerk,
            c.model as automodel,
            r.start_date as startdatum,
            r.end_date as einddatum,
            r.total_price as totaalprijs,
            rs.status_name as status
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        JOIN rental_statuses rs ON r.status_id = rs.status_id
        WHERE r.user_id = :user_id
        ORDER BY r.start_date
    ");

    $stmt->execute(['user_id' => $userId]);

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
    <title>Mijn Reserveringen</title>
</head>
<body class="bg-gray-100">
<nav class="nav-bar">
        <div class="container">
            <h1>Mijn Reserveringen</h1>
            <div class="nav-links">
                <a href="Home.php" class="nav-link">Home</a>
                <a href="Huurauto's.php" class="nav-link">Huurauto's</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Test.php" class="nav-link">Contact</a>
                <a href="logout.php" class="nav-link login">Uitloggen</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Uw Reserveringsoverzicht</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Automerk</th>
                            <th>Model</th>
                            <th>Afbeelding</th>
                            <th>Startdatum</th>
                            <th>Einddatum</th>
                            <th>Totaalprijs</th>
                            <th>Status</th>
                            <th>Actie</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    while ($reservation = $stmt->fetch()) {
        $statusInfo = getStatusDisplay($reservation['status']);

        if ($reservation['automerk'] == 'Dodge') {
            $imageSrc = '/Project_Autoverhuur/Img/Dodge Demon 170.jpg'; 
        } elseif ($reservation['automerk'] == 'Audi') {
            $imageSrc = '/Project_Autoverhuur/Img/audi a3.jpg'; 
        } elseif ($reservation['automerk'] == 'Mercedes') {
            $imageSrc = '/Project_Autoverhuur/Img/merrie.jpg'; 
        } elseif ($reservation['automerk'] == 'BMW') {
            $imageSrc = '/Project_Autoverhuur/Img/bmw.jpg'; 
        } else {
            $imageSrc = '/Project_Autoverhuur/Img/placeholder.jpg'; 
        }

        echo "<tr>";
        echo "<td>" . htmlspecialchars($reservation['automerk']) . "</td>";
        echo "<td>" . htmlspecialchars($reservation['automodel']) . "</td>";
        echo "<td><img src='" . htmlspecialchars($imageSrc) . "' alt='Car Image' width='100'/></td>";
        echo "<td>" . date('d-m-Y', strtotime($reservation['startdatum'])) . "</td>";
        echo "<td>" . date('d-m-Y', strtotime($reservation['einddatum'])) . "</td>";
        echo "<td>€" . number_format($reservation['totaalprijs'], 2) . "</td>";
        echo "<td><span class='status-badge " . $statusInfo['class'] . "'>" 
            . htmlspecialchars($statusInfo['text']) . "</span></td>";
        echo "<td><a href='Reservering_editklant.php?id=" . $reservation['rental_id'] . "' class='crud-btn'>Bewerken</a></td>";
        echo "</tr>";
    }
    ?>
                    </tbody>
                </table>
            </div>
            <form action="reservering_new.php" method="get">
                <input type="submit" value="Nieuwe Reservering" class="crud-btn" />
            </form>
        </div>
    </main>
</body>
</html>