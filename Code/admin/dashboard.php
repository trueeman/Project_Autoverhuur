<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Fetch summary statistics
    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM rentals) as total_rentals,
            (SELECT COUNT(*) FROM cars) as total_cars,
            (SELECT COUNT(*) FROM users WHERE role_id != 1) as total_customers,
            (SELECT COUNT(*) FROM rentals WHERE status_id = 
                (SELECT status_id FROM rental_statuses WHERE status_name = 'In behandeling')
            ) as pending_rentals
    ")->fetch();

    // Fetch recent rentals
    $recentRentals = $pdo->query("
        SELECT 
            r.rental_id,
            CONCAT(u.first_name, ' ', u.last_name) as customer_name,
            c.make,
            c.model,
            r.start_date,
            r.total_price,
            rs.status_name
        FROM rentals r
        JOIN users u ON r.user_id = u.user_id
        JOIN cars c ON r.car_id = c.car_id
        JOIN rental_statuses rs ON r.status_id = rs.status_id
        ORDER BY r.rental_id DESC
        LIMIT 5
    ")->fetchAll();

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
    <title>Admin Dashboard</title>
</head>
<body class="bg-gray-100">
    <nav class="nav-bar">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <div class="nav-links">
                <a href="../Adminweergave.php" class="nav-link">Beheer Auto's</a>
                <a href="../Adminreserveringweergave.php" class="nav-link">Beheer Reserveringen</a>
                <a href="../logout.php" class="nav-link login">Uitloggen</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <!-- Statistics Cards -->
        <div class="content-container">
            <h2>Dashboard Overzicht</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 1.25rem; color: #4b5563; margin-bottom: 0.5rem;">Totaal Reserveringen</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #1f2937;"><?php echo $stats['total_rentals']; ?></p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 1.25rem; color: #4b5563; margin-bottom: 0.5rem;">Beschikbare Auto's</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #1f2937;"><?php echo $stats['total_cars']; ?></p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 1.25rem; color: #4b5563; margin-bottom: 0.5rem;">Totaal Klanten</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #1f2937;"><?php echo $stats['total_customers']; ?></p>
                </div>
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 1.25rem; color: #4b5563; margin-bottom: 0.5rem;">In Behandeling</h3>
                    <p style="font-size: 2rem; font-weight: bold; color: #1f2937;"><?php echo $stats['pending_rentals']; ?></p>
                </div>
            </div>

            <!-- Recent Rentals Table -->
            <h2>Recente Reserveringen</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Klantnaam</th>
                            <th>Auto</th>
                            <th>Startdatum</th>
                            <th>Prijs</th>
                            <th>Status</th>
                            <th>Actie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRentals as $rental): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rental['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($rental['make'] . ' ' . $rental['model']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($rental['start_date'])); ?></td>
                                <td>€<?php echo number_format($rental['total_price'], 2); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($rental['status_name']) == 'voltooid' ? 'completed' : 
                                        (strtolower($rental['status_name']) == 'geboekt' ? 'available' : 
                                        (strtolower($rental['status_name']) == 'in behandeling' ? 'pending' : 'unavailable')); ?>">
                                        <?php echo htmlspecialchars($rental['status_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../reservering_edit.php?id=<?php echo $rental['rental_id']; ?>" class="crud-btn">Bewerken</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 2rem;">
                <h2>Snelle Acties</h2>
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <a href="../Crud.php" class="crud-btn">Nieuwe Auto Toevoegen</a>
                    <a href="../Adminreserveringweergave.php" class="crud-btn">Alle Reserveringen Bekijken</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>