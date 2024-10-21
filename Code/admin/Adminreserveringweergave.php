<?php
$host = 'localhost';
$db   = 'autoverhuur';
$user = 'root';
$pass = "";
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
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
    
    $stmt = $pdo->prepare("
        SELECT 
            r.rental_id,
            CONCAT(u.first_name, ' ', u.last_name) as klantnaam,
            c.make as automerk,
            c.model as automodel,
            r.start_date as startdatum,
            r.end_date as einddatum,
            r.total_price as totaalprijs,
            rs.status_name as status
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN cars c ON r.car_id = c.car_id
        JOIN rental_statuses rs ON r.status_id = rs.status_id
        ORDER BY u.last_name, r.start_date
    ");
    $stmt->execute();

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles.css">
    <title>Admin Reserveringen</title>
</head>
<body class="bg-gray-100">
<nav class="nav-bar">
        <div class="container">
            <h1>Admin Weergave Reserveringen</h1>
            <div class="nav-links">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="Contact.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <a href="Login.php" class="nav-link login">Login</a>
                <a href="register.php" class="nav-link register">Register</a>
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
                            
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($reservation['klantnaam']) . "</td>";
                            echo "<td>" . htmlspecialchars($reservation['automerk']) . "</td>";
                            echo "<td>" . htmlspecialchars($reservation['automodel']) . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($reservation['startdatum'])) . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($reservation['einddatum'])) . "</td>";
                            echo "<td>€" . number_format($reservation['totaalprijs'], 2) . "</td>";
                            echo "<td><span class='status-badge " . $statusInfo['class'] . "'>" 
                                 . htmlspecialchars($statusInfo['text']) . "</span></td>";
                            echo "<td><a href='reservering_edit.php?id=" . $reservation['rental_id'] . "' class='crud-btn'>Bewerken</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>