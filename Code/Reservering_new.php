<?php
session_start();

class Database {
    private $pdo;
    private $host = 'localhost';
    private $db   = 'autoverhuur';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    public function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
}

// Car.php
class Car {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getAllCars() {
        $stmt = $this->db->prepare("SELECT * FROM cars");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// Rental.php
class Rental {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getRentalByUserAndId($userId, $rentalId) {
        $stmt = $this->db->prepare("
            SELECT r.*, c.make, c.model, c.price_per_day 
            FROM rentals r
            JOIN cars c ON r.car_id = c.car_id
            WHERE r.rental_id = :rental_id AND r.user_id = :user_id
        ");
        $stmt->execute(['rental_id' => $rentalId, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function updateRental($rentalId, $userId, $carId, $startDate, $endDate) {
        $stmt = $this->db->prepare("
            UPDATE rentals 
            SET car_id = :car_id, start_date = :start_date, end_date = :end_date, status_id = 1
            WHERE rental_id = :rental_id AND user_id = :user_id
        ");
        return $stmt->execute([
            'car_id' => $carId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'rental_id' => $rentalId,
            'user_id' => $userId
        ]);
    }

    public function createRental($userId, $carId, $startDate, $endDate) {
        $stmt = $this->db->prepare("
            INSERT INTO rentals (user_id, car_id, start_date, end_date, status_id)
            VALUES (:user_id, :car_id, :start_date, :end_date, 1)
        ");
        return $stmt->execute([
            'user_id' => $userId,
            'car_id' => $carId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function calculateTotalCost($startDate, $endDate, $pricePerDay) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $days = $end->diff($start)->days + 1;
        return $days * $pricePerDay;
    }
}

// Initialisatie van objecten
$db = new Database();
$car = new Car($db);
$rental = new Rental($db);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Haal de rental_id op uit de URL
$rental_id = isset($_GET['id']) ? $_GET['id'] : null;

// Initialiseer variabelen
$startDate = '';
$endDate = '';
$selectedCarId = '';
$totalCost = 0;

// Haal alle auto's op voor het dropdown menu
$allCars = $car->getAllCars();

// Verwerk de formulierinvoer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $selectedCarId = $_POST['car_id'];

    // Haal de prijs van de geselecteerde auto op
    $selectedCar = null;
    foreach ($allCars as $carOption) {
        if ($carOption['car_id'] == $selectedCarId) {
            $selectedCar = $carOption;
            break;
        }
    }

    if ($selectedCar) {
        // Bereken de totale kosten
        $totalCost = $rental->calculateTotalCost($startDate, $endDate, $selectedCar['price_per_day']);

        if (isset($_POST['update']) || isset($_POST['create'])) {
            if (isset($_POST['update'])) {
                $rentalId = $_POST['rental_id'];
                $updateResult = $rental->updateRental($rentalId, $userId, $selectedCarId, $startDate, $endDate);
                $message = $updateResult ? "Reservering succesvol bijgewerkt." : "Er is een fout opgetreden bij het bijwerken van de reservering.";
            } else {
                $createResult = $rental->createRental($userId, $selectedCarId, $startDate, $endDate);
                $message = $createResult ? "Nieuwe reservering succesvol aangemaakt." : "Er is een fout opgetreden bij het aanmaken van de reservering.";
            }

            header("Location: Reservering.php?message=" . urlencode($message));
            exit();
        }
    }
} elseif ($rental_id) {
    // Als er een rental_id is, haal dan de reserveringsgegevens op
    $rentalData = $rental->getRentalByUserAndId($userId, $rental_id);
    if ($rentalData) {
        $startDate = $rentalData['start_date'];
        $endDate = $rentalData['end_date'];
        $selectedCarId = $rentalData['car_id'];
        $totalCost = $rental->calculateTotalCost($startDate, $endDate, $rentalData['price_per_day']);
    } else {
        // Rental not found or doesn't belong to the user
        header("Location: reservations.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $rental_id ? 'Reservering Bewerken' : 'Nieuwe Reservering'; ?></title>
    <style>
        .reservation-form select,
        .reservation-form input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
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
    <div class="container_res">
        <h1 class="h1_res"><?php echo $rental_id ? 'Reservering Bewerken' : 'Nieuwe Reservering'; ?></h1>
        <?php if (isset($_GET['message'])): ?>
            <p class="message"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?><?php echo $rental_id ? "?id=$rental_id" : ''; ?>" method="post" class="reservation-form">
            <?php if ($rental_id): ?>
                <input type="hidden" name="rental_id" value="<?php echo $rental_id; ?>">
            <?php endif; ?>
            
            <label for="car_id">Auto:</label>
            <select name="car_id" id="car_id" required>
                <?php foreach ($allCars as $carOption): ?>
                    <option value="<?php echo $carOption['car_id']; ?>" 
                            <?php echo ($carOption['car_id'] == $selectedCarId) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($carOption['make'] . ' ' . $carOption['model'] . ' - €' . $carOption['price_per_day'] . '/dag'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="extras">Extra's:</label>
            <div id="extras">
                <input type="checkbox" name="extras[]" value="kinderzitje" id="kinderzitje">
                <label for="kinderzitje">Kinderzitje</label><br>

                <input type="checkbox" name="extras[]" value="stoelverwarming" id="stoelverwarming">
                <label for="stoelverwarming">Stoelverwarming</label><br>

                <input type="checkbox" name="extras[]" value="gps" id="gps">
                <label for="gps">GPS</label><br>

                <input type="checkbox" name="extras[]" value="extra_bagageruimte" id="extra_bagageruimte">
                <label for="extra_bagageruimte">Extra bagageruimte</label><br>
            </div>
            <label for="start_date">Startdatum:</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
            
            <label for="end_date">Einddatum:</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
            
            <?php if ($rental_id): ?>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($rentalData['status_id']); ?></p>
            <?php endif; ?>
            
            <p><strong>Totale kosten:</strong> €<?php echo number_format($totalCost, 2); ?></p>
            
            <input type="submit" value="Bereken kosten" name="calculate" class="crud-btn" />
            <input type="submit" value="<?php echo $rental_id ? 'Bijwerken' : 'Reserveren'; ?>" name="<?php echo $rental_id ? 'update' : 'create'; ?>" class="crud-btn" />
        </form>
    </div>
</body>
</html>