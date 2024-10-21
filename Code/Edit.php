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

$car = null;
$error = '';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = ?");
    $stmt->execute([$id]);
    $car = $stmt->fetch();

    if (!$car) {
        $error = "Auto niet gevonden.";
    }
} else {
    $error = "Ongeldig auto ID.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = intval($_POST['year']);
    $price_per_day = floatval($_POST['price_per_day']);
    $availability = isset($_POST['availability']) ? 1 : 0;
    $category = $_POST['category'];
    $mileage = intval($_POST['mileage']);
    $apk_date = $_POST['apk_date'];

    $stmt = $pdo->prepare("UPDATE cars SET make = ?, model = ?, year = ?, price_per_day = ?, availability = ?, category = ?, mileage = ?, apk_date = ? WHERE car_id = ?");
    $stmt->execute([$make, $model, $year, $price_per_day, $availability, $category, $mileage, $apk_date, $id]);

    header("Location: ./admin/Adminreserveringweergave.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auto Bewerken</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100">
<nav class="nav-bar">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <div class="nav-links">
                <a href="../admin/Adminweergave.php" class="nav-link">Beheer Auto's</a>
                <a href="../admin/Adminreserveringweergave.php" class="nav-link">Beheer Reserveringen</a>
                <a href="../admin/dashboard.php" class="nav-link">Dashboard</a>
                <a href="../logout.php" class="nav-link login">Uitloggen</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Auto Bewerken</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif ($car): ?>
                <form method="POST" class="edit-form">
                    <input type="hidden" name="id" value="<?php echo $car['car_id']; ?>">
                    
                    <div class="form-group">
                        <label for="make">Automerk:</label>
                        <input type="text" id="make" name="make" value="<?php echo htmlspecialchars($car['make']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="model">Model:</label>
                        <input type="text" id="model" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="year">Bouwjaar:</label>
                        <input type="number" id="year" name="year" value="<?php echo $car['year']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="price_per_day">Prijs per dag:</label>
                        <input type="number" id="price_per_day" name="price_per_day" step="0.01" value="<?php echo $car['price_per_day']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="availability">Beschikbaar:</label>
                        <input type="checkbox" id="availability" name="availability" <?php echo $car['availability'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="form-group">
                        <label for="category">Autotype:</label>
                        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($car['category']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="mileage">Kilometerstand:</label>
                        <input type="number" id="mileage" name="mileage" value="<?php echo $car['mileage']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="apk_date">APK Keuring datum:</label>
                        <input type="date" id="apk_date" name="apk_date" value="<?php echo $car['apk_date']; ?>" required>
                    </div>

                    <button type="submit" name="update" class="crud-btn">Opslaan</button>
                    <a href="./admin/Adminreserveringweergave.php" class="crud-btn">Annuleren</a>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>