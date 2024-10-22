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

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

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

class Rental {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getUserRentals($userId) {
        $stmt = $this->db->prepare("
            SELECT r.*, c.make, c.model, c.price_per_day 
            FROM rentals r
            JOIN cars c ON r.car_id = c.car_id
            WHERE r.user_id = :user_id
            ORDER BY r.start_date DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
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
        try {
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
        } catch (\PDOException $e) {
            error_log("Error updating rental: " . $e->getMessage());
            return false;
        }
    }

    public function createRental($userId, $carId, $startDate, $endDate) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO rentals (user_id, car_id, start_date, end_date, status_id)
                VALUES (:user_id, :car_id, :start_date, :end_date, 1)
            ");
            $success = $stmt->execute([
                'user_id' => $userId,
                'car_id' => $carId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            if ($success) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error creating rental: " . $e->getMessage());
            return false;
        }
    }

    public function calculateTotalCost($startDate, $endDate, $pricePerDay) {
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $days = $end->diff($start)->days + 1;
        return $days * $pricePerDay;
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Initialize objects
$db = new Database();
$car = new Car($db);
$rental = new Rental($db);

// Get rental_id from URL
$rental_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// Initialize variables
$startDate = '';
$endDate = '';
$selectedCarId = '';
$totalCost = 0;
$message = '';
$extras = [];

// Get all cars for dropdown
$allCars = $car->getAllCars();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $startDate = $_POST['start_date'];
        $endDate = $_POST['end_date'];
        $selectedCarId = $_POST['car_id'];
        $extras = isset($_POST['extras']) ? $_POST['extras'] : [];

        // Validate dates
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        if ($end < $start) {
            throw new Exception("Einddatum moet na startdatum liggen.");
        }

        // Find selected car
        $selectedCar = null;
        foreach ($allCars as $carOption) {
            if ($carOption['car_id'] == $selectedCarId) {
                $selectedCar = $carOption;
                break;
            }
        }

        if ($selectedCar) {
            $totalCost = $rental->calculateTotalCost($startDate, $endDate, $selectedCar['price_per_day']);

            if (isset($_POST['update']) || isset($_POST['create'])) {
                if (isset($_POST['update'])) {
                    $rentalId = $_POST['rental_id'];
                    $updateResult = $rental->updateRental($rentalId, $userId, $selectedCarId, $startDate, $endDate);
                    if ($updateResult) {
                        $_SESSION['message'] = "Reservering succesvol bijgewerkt.";
                        header("Location: Reservering.php");
                        exit();
                    } else {
                        throw new Exception("Er is een fout opgetreden bij het bijwerken van de reservering.");
                    }
                } else {
                    $createResult = $rental->createRental($userId, $selectedCarId, $startDate, $endDate);
                    if ($createResult) {
                        $_SESSION['last_rental_id'] = $createResult;
                        $_SESSION['message'] = "Nieuwe reservering succesvol aangemaakt.";
                        header("Location: Reservering.php");
                        exit();
                    } else {
                        throw new Exception("Er is een fout opgetreden bij het aanmaken van de reservering.");
                    }
                }
            }
        } else {
            throw new Exception("Geselecteerde auto niet gevonden.");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
} elseif ($rental_id) {
    $rentalData = $rental->getRentalByUserAndId($userId, $rental_id);
    if ($rentalData) {
        $startDate = $rentalData['start_date'];
        $endDate = $rentalData['end_date'];
        $selectedCarId = $rentalData['car_id'];
        $totalCost = $rental->calculateTotalCost($startDate, $endDate, $rentalData['price_per_day']);
    } else {
        $_SESSION['message'] = "Reservering niet gevonden.";
        header("Location: Reservering.php");
        exit();
    }
}

// Get message from session if exists
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $rental_id ? 'Reservering Bewerken' : 'Nieuwe Reservering'; ?></title>

</head>
<body>
    <nav class="nav-bar">
        <div class="container">
            <h1>Reservering</h1>
            <div class="nav-links">
                <a href="Huurauto's.php" class="nav-link">Huurauto's</a>
                <a href="./admin/Adminreserveringweergave.php" class="nav-link">Admin</a>
                <a href="Contact.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="nav-link login">Uitloggen</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link login">Login</a>
                    <a href="register.php" class="nav-link register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container_res">
        <h1 class="h1_res"><?php echo $rental_id ? 'Reservering Bewerken' : 'Nieuwe Reservering'; ?></h1>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'succesvol') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?><?php echo $rental_id ? "?id=$rental_id" : ''; ?>" 
              method="post" 
              class="reservation-form">
            
            <?php if ($rental_id): ?>
                <input type="hidden" name="rental_id" value="<?php echo htmlspecialchars($rental_id); ?>">
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
                <input type="checkbox" name="extras[]" value="kinderzitje" id="kinderzitje"
                       <?php echo in_array('kinderzitje', $extras) ? 'checked' : ''; ?>>
                <label for="kinderzitje">Kinderzitje</label><br>

                <input type="checkbox" name="extras[]" value="stoelverwarming" id="stoelverwarming"
                       <?php echo in_array('stoelverwarming', $extras) ? 'checked' : ''; ?>>
                <label for="stoelverwarming">Stoelverwarming</label><br>

                <input type="checkbox" name="extras[]" value="gps" id="gps"
                       <?php echo in_array('gps', $extras) ? 'checked' : ''; ?>>
                <label for="gps">GPS</label><br>

                <input type="checkbox" name="extras[]" value="extra_bagageruimte" id="extra_bagageruimte"
                       <?php echo in_array('extra_bagageruimte', $extras) ? 'checked' : ''; ?>>
                <label for="extra_bagageruimte">Extra bagageruimte</label><br>
            </div>

            <label for="start_date">Startdatum:</label>
            <input type="date" id="start_date" name="start_date" 
                   value="<?php echo htmlspecialchars($startDate); ?>" required
                   min="<?php echo date('Y-m-d'); ?>">
            
            <label for="end_date">Einddatum:</label>
            <input type="date" id="end_date" name="end_date" 
                   value="<?php echo htmlspecialchars($endDate); ?>" required
                   min="<?php echo date('Y-m-d'); ?>">
            
            <?php if ($rental_id && isset($rentalData['status_id'])): ?>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($rentalData['status_id']); ?></p>
            <?php endif; ?>
            
            <p><strong>Totale kosten:</strong> €<?php echo number_format($totalCost, 2); ?></p>
            
            <input type="submit" value="Bereken kosten" name="calculate" class="crud-btn" />
            <input type="submit" 
                   value="<?php echo $rental_id ? 'Bijwerken' : 'Reserveren'; ?>" 
                   name="<?php echo $rental_id ? 'update' : 'create'; ?>" 
                   class="crud-btn" />
        </form>
    </div>

    <script>