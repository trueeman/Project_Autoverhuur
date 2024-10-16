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

    // Variabelen voor auto details
    $carMake = '';
    $carModel = '';
    $carPrice = '';
    $carId = '';

    // Verwerk de formulierinvoer
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['car_id'])) {
            $car_id = $_POST['car_id']; // Verkrijg de geselecteerde auto ID
            
            // Haal de details van de geselecteerde auto op
            $carStmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = :car_id");
            $carStmt->execute(['car_id' => $car_id]);
            $selectedCar = $carStmt->fetch();

            if ($selectedCar) {
                // Vul de formulier velden met de geselecteerde auto details
                $carMake = $selectedCar['make'];
                $carModel = $selectedCar['model'];
                $carPrice = $selectedCar['price_per_day'];
                $carId = $selectedCar['car_id']; // Bewaar de ID voor het bijwerken
            }
        } elseif (isset($_POST['update'])) {
            // Update de gegevens van de geselecteerde auto
            $carId = $_POST['car_id'];
            $carPrice = $_POST['total_price'];

            $updateStmt = $pdo->prepare("UPDATE cars SET price = :price WHERE car_id = :car_id");
            $updateStmt->execute([
                'price' => $carPrice,
                'car_id' => $carId
            ]);
            
            // Omleiden naar Reservering.php na opslaan
            header("Location: Reservering.php?updated=true");
            exit();
        }
    }

    // Fetch all available statuses
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
        <form action="" method="post" class="edit-reservation-form">
            <label for="car_id" class="label-car">Selecteer Auto:</label>
            <select id="car_id" name="car_id" required onchange="this.form.submit()">
                <option value="">-- Kies een auto --</option>
                <?php foreach ($cars as $car): ?>
                    <option value="<?php echo $car['car_id']; ?>" <?php echo ($carId == $car['car_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($car['make'] . ' ' . $car['model']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if (!empty($carMake) && !empty($carModel)): ?>
                <label for="car" class="label-car">Auto:</label>
                <input type="text" id="car" name="car" value="<?php echo htmlspecialchars($carMake . ' ' . $carModel); ?>" readonly>
                
                <label for="total_price" class="label-total-price">Totaalprijs:</label>
                <input type="number" id="total_price" name="total_price" step="0.01" value="<?php echo htmlspecialchars($carPrice); ?>" required>
            <?php endif; ?>

            <label for="start_date" class="label-start-date">Startdatum:</label>
            <input type="date" id="start_date" name="start_date" required>
            
            <label for="end_date" class="label-end-date">Einddatum:</label>
            <input type="date" id="end_date" name="end_date" required>
            
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
