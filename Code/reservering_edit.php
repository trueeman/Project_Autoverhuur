<?php
class Database {
    private $host = 'localhost';
    private $db = 'autoverhuur';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';
    private $pdo;

    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (\PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

class Rental {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRental($rental_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                r.rental_id,
                r.user_id,
                r.car_id,
                r.start_date,
                r.end_date,
                r.total_price,
                r.status_id,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                c.make,
                c.model
            FROM rentals r
            JOIN users u ON r.user_id = u.user_id
            JOIN cars c ON r.car_id = c.car_id
            WHERE r.rental_id = :rental_id
        ");
        $stmt->execute(['rental_id' => $rental_id]);
        return $stmt->fetch();
    }

    public function updateRental($data) {
        $stmt = $this->pdo->prepare("
            UPDATE rentals 
            SET start_date = :start_date, 
                end_date = :end_date, 
                total_price = :total_price, 
                status_id = :status_id
            WHERE rental_id = :rental_id
        ");

        $stmt->execute([
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_price' => $data['total_price'],
            'status_id' => $data['status_id'],
            'rental_id' => $data['rental_id']
        ]);
    }

    public function getRentalStatuses() {
        $stmt = $this->pdo->query("SELECT status_id, status_name FROM rental_statuses");
        return $stmt->fetchAll();
    }
}

// Initialisatie van de database en het Rental-object
$db = new Database();
$rentalModel = new Rental($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verwerk het formulier en update de reservering
    $rentalModel->updateRental($_POST);
    // Redirect terug naar de overzichtspagina
    header("Location: Adminreserveringweergave.php");
    exit();
}

// Haal de reserveringsgegevens op
if (isset($_GET['id'])) {
    $rental_id = $_GET['id'];
    $reservation = $rentalModel->getRental($rental_id);

    if (!$reservation) {
        die("Reservering niet gevonden.");
    }

    $statuses = $rentalModel->getRentalStatuses();
} else {
    die("Geen reserverings-ID opgegeven.");
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
<div class="container_res">
    <h1 class="h1_res">Reservering Bewerken</h1>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>?id=<?php echo $rental_id; ?>" method="post" class="edit-reservation-form">
        <input type="hidden" name="rental_id" value="<?php echo htmlspecialchars($reservation['rental_id']); ?>">
        
        <label for="customer_name" class="label-customer-name">Klant:</label>
        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($reservation['customer_name']); ?>" readonly>
        
        <label for="car" class="label-car">Auto:</label>
        <input type="text" id="car" name="car" value="<?php echo htmlspecialchars($reservation['make'] . ' ' . $reservation['model']); ?>" readonly>
        
        <label for="start_date" class="label-start-date">Startdatum:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($reservation['start_date']); ?>" required>
        
        <label for="end_date" class="label-end-date">Einddatum:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($reservation['end_date']); ?>" required>
        
        <label for="total_price" class="label-total-price">Totaalprijs:</label>
        <input type="number" id="total_price" name="total_price" step="0.01" value="<?php echo htmlspecialchars($reservation['total_price']); ?>" required>
        
        <label for="status_id" class="label-status">Status:</label>
        <select id="status_id" name="status_id" required>
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo $status['status_id']; ?>" <?php echo ($status['status_id'] == $reservation['status_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($status['status_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <input type="submit" value="Opslaan">
    </form>
</div>
</body>
</html>
