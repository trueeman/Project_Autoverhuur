<?php
$host = 'localhost';
$db   = 'autoverhuur';
$user = 'root';
$pass = "";

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = intval($_POST['id']);
        
        // De daadwerkelijke delete query
        $stmt = $pdo->prepare("DELETE FROM cars WHERE car_id = ?");
        $stmt->execute([$id]);
        header("Location: Adminweergave.php");
        exit;
    } else {
        echo "<script>alert('Ongeldig auto ID.');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Auto Weergave</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-gray-100">
<nav class="nav-bar">
        <div class="container">
            <h1>Admin Weergave Auto's</h1>
            <div class="nav-links">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="Adminweergave.php" class="nav-link">Admin</a>
                <a href="Test.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <a href="Login.php" class="nav-link login">Login</a>
                <a href="register.php" class="nav-link register">Register</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Auto Weergave</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Automerk</th>
                            <th>Model</th>
                            <th>Bedrag</th>
                            <th>Kilometerstand</th>
                            <th>Bouwjaar</th>
                            <th>Beschikbaar</th>
                            <th>Autotype</th>
                            <th>APK Keuring datum</th>
                            <th>Acties</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            echo "<td>
                                    <a href='edit.php?id=" . $row['car_id'] . "' class='crud-btn edit-btn'>Bewerken</a>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='id' value='" . $row['car_id'] . "'>
                                        <button type='submit' name='delete' class='crud-btn delete-btn' onclick=\"return confirm('Weet je zeker dat je deze auto wilt verwijderen?');\">Verwijderen</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="crud-button-container">
                <a href="Crud.php" class="crud-btn">Nieuwe Auto Toevoegen</a>
            </div>
        </div>
    </main>
</body>
</html>