<?php
$host = 'localhost';
$db   = 'autoverhuur';  // Your database name
$user = 'root';  // Your database username
$pass = "";  // Your database password

$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error: Could not connect to the database. " . $e->getMessage());
}

// Now prepare and execute the SQL query
try {
    // Prepare the SQL query to fetch car details
    $stmt = $pdo->prepare("
        SELECT 
            c.make AS automerk,
            c.model AS automodel,
            c.price_per_day AS prijs_per_dag
        FROM cars c
        ORDER BY c.make, c.model
    ");
    
    // Execute the query
    $stmt->execute();

} catch (PDOException $e) {
    die("Error: Could not execute the SQL query. " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Beschikbare Huurauto's</title>
</head>
<body class="bg-gray-100">
<nav class="nav-bar">
        <div class="container">
            <h1>Huurauto's</h1>
            <div class="nav-links">
                <a href="Huurauto's.php" class="nav-link">Huurauto's</a>
                <a href="./admin/Adminreserveringweergave.php" class="nav-link">Admin</a>
                <a href="Contact.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <a href="login.php" class="nav-link login">Login</a>
                <a href="register.php" class="nav-link register">Register</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Beschikbare Huurauto's</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Automerk</th>
                            <th>Model</th>
                            <th>Afbeelding</th>  
                            <th>Prijs per dag</th>
                        </tr>
                    </thead>
                    <tbody>
    <?php
    // Check if the statement executed successfully and contains data
    if ($stmt) {
        // Fetch car data and display in table rows
        while ($car = $stmt->fetch()) {
            // Kies het juiste afbeelding-pad op basis van automerk
            if ($car['automerk'] == 'Dodge') {
                $imageSrc = '/Project_Autoverhuur/Img/Dodge Demon 170.jpg'; 
            }
            elseif ($car['automerk'] == 'Audi') {
                $imageSrc = '/Project_Autoverhuur/Img/audi a3.jpg'; 
            }  elseif ($car['automerk'] == 'Mercedes') {
                $imageSrc = '/Project_Autoverhuur/Img/merrie.jpg'; 
            } elseif ($car['automerk'] == 'BMW') {
                $imageSrc = '/Project_Autoverhuur/Img/bmw.jpg'; 
            } else {
                $imageSrc = '/Project_Autoverhuur/Img/placeholder.jpg';  // Voor alle andere merken
            }

            // Output car details along with the image
            echo "<tr>";
            echo "<td>" . htmlspecialchars($car['automerk']) . "</td>";
            echo "<td>" . htmlspecialchars($car['automodel']) . "</td>";
            
            // Display car image
            echo "<td><img src='" . htmlspecialchars($imageSrc) . "' alt='Car Image' width='100'/></td>";
            echo "<td>€" . number_format($car['prijs_per_dag'], 2) . "</td>";
            echo "</tr>";
        }
    } else {
        // If no data was found or query failed, display a message
        echo "<tr><td colspan='4'>Geen auto's beschikbaar.</td></tr>";
    }
    ?>

                    </tbody>
                </table>
            </div>
          <form onsubmit="window.location.href='Reservering_new.php'; return false;">
            <input type="submit" value="Auto Reserveren" name="Submit" class="crud-btn" />
          </form>
        </div>
    </main>
</body>
</html>
