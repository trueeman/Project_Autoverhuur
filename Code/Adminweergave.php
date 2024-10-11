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
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Admin Auto Weergave</title>
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
                <h1>Admin Auto Weergave</h1>
            </div>
            <div class="nav-buttons">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="#" class="nav-link">Contact</a>
                <a href="#" class="nav-link">Mijn boekingen</a>
                <a href="Home.html" class="nav-link">Home</a>
                <button class="nav-link login">Login</button>
                <button class="nav-link register">Register</button>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Auto Weergave</h2>
            <div class="table-container">
                <table>
                    <head>
                        <tr>
                            <th>Automerk</th>
                            <th>Model</th>
                            <th>Bedrag</th>
                            <th>Kilometerstand</th>
                            <th>Bouwjaar</th>
                            <th>Beschikbaar</th>
                            <th>Autotype</th>
                            <th>APK Keuring datum</th>
                        </tr>
                    </head>
                    <body>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM cars");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['make']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['model']) . "</td>";
                            echo "<td>€" . number_format($row['price_per_day'], 2) . "/dag</td>";
                            echo "<td>" . number_format($row['mileage']) . " km</td>";
                            echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                            echo "<td><span class='status-badge " . ($row['availability'] ? "available" : "unavailable") . "'>" 
                                 . ($row['availability'] ? "Beschikbaar" : "Niet beschikbaar") . "</span></td>";
                            echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                            echo "<td>" . date('d-m-Y', strtotime($row['apk_date'])) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </body>
                </table>
            </div>
            <div class="crud-button-container">
                <button class="crud-btn"><a href="Crud.php">CRUD</a></button>
            </div>
        </div>
    </main>
</body>
</html>