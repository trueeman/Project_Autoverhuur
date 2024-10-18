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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Haal alle beschikbare auto's op
    $carsStmt = $pdo->query("SELECT car_id, make, model, price_per_day FROM cars");
    $cars = $carsStmt->fetchAll();

    // Haal alle gebruikers op
    $usersStmt = $pdo->query("SELECT user_id, first_name, last_name FROM users");
    $users = $usersStmt->fetchAll();

    // Variabelen voor auto details en reservering
    $carMake = '';
    $carModel = '';
    $carPrice = '';
    $reservationId = '';  // rental_id
    $userId = '';
    $startDate = '';
    $endDate = '';
    $statusId = '';

    // Verwerk de formulierinvoer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        // ... (code for selecting user remains the same)
    } elseif (isset($_POST['update'])) {
        // Update de gegevens van de reservering in de database
        $rentalId = $_POST['rental_id'];
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $statusId = $_POST['status_id'];
        $totalPrice = $_POST['total_price'];

        // Update query voor reservering
        $updateStmt = $pdo->prepare("
            UPDATE rentals 
            SET start_date = :start_date, end_date = :end_date, status_id = :status_id, total_price = :total_price
            WHERE rental_id = :rental_id
        ");
        $updateResult = $updateStmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status_id' => $statusId,
            'total_price' => $totalPrice,
            'rental_id' => $rentalId
        ]);

        if ($updateResult) {
            // Successful update
            header("Location: Reservering.php?updated=true");
            exit();
        } else {
            // Failed update
            $error = "Er is een fout opgetreden bij het bijwerken van de reservering.";
        }
    }
    }

    // Haal alle beschikbare statussen op
    $statusStmt = $pdo->query("SELECT status_id, status_name FROM rental_statuses");
    $statuses = $statusStmt->fetchAll();

} catch (\PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Reservering Bewerken</title>
</head>
<body>
    <div class="container_res">
        <h1 class="h1_res">Reservering Bewerken</h1>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $rental_id; ?>" method="post" class="edit-reservation-form">
            
            <!-- Selecteer gebruiker -->
            <label for="user_id" class="label-user">Selecteer Gebruiker:</label>
            <select id="user_id" name="user_id" required onchange="this.form.submit()">
                <option value="">-- Kies een gebruiker --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['user_id']; ?>" <?php echo ($userId == $user['user_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Auto informatie en totaalprijs tonen zodra de gebruiker is geselecteerd -->
            <?php if (!empty($carMake) && !empty($carModel)): ?>
                <div id="car-details">
                    <label for="car" class="label-car">Auto:</label>
                    <input type="text" id="car" name="car" value="<?php echo htmlspecialchars($carMake . ' ' . $carModel); ?>" readonly>

                    <label for="total_price" class="label-total-price">Totaalprijs:</label>
                    <input type="text" id="total_price" name="total_price" value="<?php echo htmlspecialchars($carPrice); ?>" readonly>

                    <!-- Verberg rental_id -->
                    <input type="hidden" name="rental_id" value="<?php echo $reservationId; ?>">
                </div>
            <?php endif; ?>

            <!-- Reservering details -->
            <label for="start_date" class="label-start-date">Startdatum:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
            
            <label for="end_date" class="label-end-date">Einddatum:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
            
            <label for="status_id" class="label-status">Status:</label>
            <select id="status_id" name="status_id" required>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status['status_id']; ?>" <?php echo ($statusId == $status['status_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($status['status_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="submit" value="Opslaan" name="update" class="crud-btn" />
        </form>
    </div>
</body>
</html>
