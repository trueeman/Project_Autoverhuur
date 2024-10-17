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

    // Verwerk de formulierinvoer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['user_id'])) {
            $userId = $_POST['user_id']; // Verkrijg de geselecteerde user ID
            
            // Haal de details van de reservering van de gebruiker op
            $carStmt = $pdo->prepare("
                SELECT r.rental_id, c.make, c.model, c.price_per_day 
                FROM rentals r
                JOIN cars c ON r.car_id = c.car_id
                WHERE r.user_id = :user_id
            ");
            $carStmt->execute(['user_id' => $userId]);
            $selectedCar = $carStmt->fetch();

            if ($selectedCar) {
                // Vul de formulier velden met de geselecteerde auto details
                $carMake = $selectedCar['make'];
                $carModel = $selectedCar['model'];
                $carPrice = $selectedCar['price_per_day'];
                $reservationId = $selectedCar['rental_id']; // Store rental_id
            }
        } elseif (isset($_POST['update'])) {
            // Update de gegevens van de reservering in de database
            $rentalId = $_POST['rental_id'];  // Retrieve rental_id from the form
            $carPrice = $_POST['total_price'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $statusId = $_POST['status_id'];

            // Update query voor reservering
            $updateStmt = $pdo->prepare("
                UPDATE rentals 
                SET start_date = :start_date, end_date = :end_date, status_id = :status_id, total_price = :total_price 
                WHERE rental_id = :rental_id
            ");
            $updateStmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status_id' => $statusId,
                'total_price' => $carPrice,
                'rental_id' => $rentalId  // Use rental_id to target the correct record
            ]);

            // Omleiden naar Reservering.php na opslaan
            header("Location: Reservering.php?updated=true");
            exit(); // Zorg ervoor dat de code stopt na omleiding
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
    <script>
        function submitForm() {
            document.getElementById('reservation-form').submit();
        }
    </script>
</head>
<body>
    <div class="container_res">
        <h1 class="h1_res">Reservering Bewerken</h1>
        <form action="" method="post" id="reservation-form" class="edit-reservation-form">
            
            <!-- Selecteer gebruiker -->
            <label for="user_id" class="label-user">Selecteer Gebruiker:</label>
            <select id="user_id" name="user_id" required onchange="submitForm()">
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
                    <input type="number" id="total_price" name="total_price" step="0.01" value="<?php echo htmlspecialchars($carPrice); ?>" required>

                    <!-- Verberg rental_id -->
                    <input type="hidden" name="rental_id" value="<?php echo $reservationId; ?>">
                </div>
            <?php endif; ?>

            <!-- Reservering details -->
            <label for="start_date" class="label-start-date">Startdatum:</label>
            <input type="date" id="start_date" name="start_date" value="" required>
            
            <label for="end_date" class="label-end-date">Einddatum:</label>
            <input type="date" id="end_date" name="end_date" value="" required>
            
            <label for="status_id" class="label-status">Status:</label>
            <select id="status_id" name="status_id" required>
                <?php foreach ($statuses as $status): ?>
                    <option value="<?php echo $status['status_id']; ?>">
                        <?php echo htmlspecialchars($status['status_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <input type="submit" value="Opslaan" name="update" class="crud-btn" />
        </form>
    </div>
</body>
</html>
