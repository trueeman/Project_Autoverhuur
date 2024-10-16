<?php
include 'navbar.php';
$host = 'localhost';
$db   = 'autoverhuur';
$user = 'root';
$pass = "";

$dsn = "mysql:host=$host;dbname=$db;";
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

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $price_per_day = $_POST['price_per_day'];
    $mileage = $_POST['mileage'];
    $year = $_POST['year'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    $category = $_POST['category'];
    $apk_date = $_POST['apk_date'];

    $stmt = $pdo->prepare("INSERT INTO cars (make, model, price_per_day, mileage, year, availability, category, apk_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$make, $model, $price_per_day, $mileage, $year, $availability, $category, $apk_date]);
    header("Location: Adminweergave.php");
    exit;
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $price_per_day = $_POST['price_per_day'];
    $mileage = $_POST['mileage'];
    $year = $_POST['year'];
    $availability = isset($_POST['availability']) ? 1 : 0;
    $category = $_POST['category'];
    $apk_date = $_POST['apk_date'];

    $stmt = $pdo->prepare("UPDATE cars SET make=?, model=?, price_per_day=?, mileage=?, year=?, availability=?, category=?, apk_date=? WHERE id=?");
    $stmt->execute([$make, $model, $price_per_day, $mileage, $year, $availability, $category, $apk_date, $id]);
    header("Location: Adminweergave.php");
    exit;
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id=?");
    $stmt->execute([$id]);
    header("Location: Adminweergave.php");
    exit;
}

// Fetch cars
$stmt = $pdo->query("SELECT * FROM cars");
$cars = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Admin Auto Weergave</title>
</head>
<body class="bg-gray-100">
    <nav class="nav-container">
        <div class="nav-content">
            <div class="logo-container">
                <svg class="car-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 16H9m10 0h3v-3.15a1 1 0 00-.84-.99L16 11l-2.7-3.6a1 1 0 00-.8-.4H5.24a2 2 0 00-1.8 1.1l-.8 1.63A6 6 0 002 12.42V16h2"></path>
                    <circle cx="6.5" cy="16.5" r="2.5"></circle>
                    <circle cx="16.5" cy="16.5" r="2.5"></circle>
                </svg>
                <h1>Admin Auto Weergave</h1>
            </div>
            <div class="nav-buttons">
                <a href="Adminweergave.php" class="nav-link">Auto's</a>
                <a href="#" class="nav-link">Contact</a>
                <a href="#" class="nav-link">Mijn boekingen</a>
                <a href="Home.html" class="nav-link">Home</a>
                <button class="nav-link login">Login</button>
                <button class="nav-link register">Register</button>
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
                        <?php foreach ($cars as $car): ?>
                            <tr>
                                <td><?= htmlspecialchars($car['make']) ?></td>
                                <td><?= htmlspecialchars($car['model']) ?></td>
                                <td>€<?= number_format($car['price_per_day'], 2) ?>/dag</td>
                                <td><?= number_format($car['mileage']) ?> km</td>
                                <td><?= htmlspecialchars($car['year']) ?></td>
                                <td>
                                    <span class='status-badge <?= $car['availability'] ? "available" : "unavailable" ?>'>
                                        <?= $car['availability'] ? "Beschikbaar" : "Niet beschikbaar" ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($car['category']) ?></td>
                                <td><?= date('d-m-Y', strtotime($car['apk_date'])) ?></td>
                                <td>
                                    <button onclick="openEditModal(<?= $car['id'] ?>)" class="crud-btn">Bewerken</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $car['id'] ?>">
                                        <button type="submit" name="delete" class="crud-btn" onclick="return confirm('Weet je zeker dat je deze auto wilt verwijderen?');">Verwijderen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Toevoegen van een nieuwe auto -->
            <h3>Nieuwe Auto Toevoegen</h3>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="make">Automerk</label>
                        <input type="text" id="make" name="make" required>
                    </div>
                    <div class="form-group">
                        <label for="model">Model</label>
                        <input type="text" id="model" name="model" required>
                    </div>
                    <div class="form-group">
                        <label for="price_per_day">Prijs per dag (€)</label>
                        <input type="number" step="0.01" id="price_per_day" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="mileage">Kilometerstand</label>
                        <input type="number" id="mileage" name="mileage" required>
                    </div>
                    <div class="form-group">
                        <label for="year">Bouwjaar</label>
                        <input type="number" id="year" name="year" required>
                    </div>
                    <div class="form-group">
                        <label for="availability">Beschikbaar</label>
                        <input type="checkbox" id="availability" name="availability">
                    </div>
                    <div class="form-group">
                        <label for="category">Autotype</label>
                        <input type="text" id="category" name="category" required>
                    </div>
                    <div class="form-group">
                        <label for="apk_date">APK Keuring datum</label>
                        <input type="date" id="apk_date" name="apk_date" required>
                    </div>
                </div>
                <button type="submit" name="create" class="search-button">Toevoegen</button>
            </form>

            <!-- Hidden Form for Updating Car -->
            <h3>Auto Bijwerken</h3>
            <form method="POST" id="updateForm" style="display:none;">
                <input type="hidden" name="id" id="update_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="update_make">Automerk</label>
                        <input type="text" id="update_make" name="make" required>
                    </div>
                    <div class="form-group">
                        <label for="update_model">Model</label>
                        <input type="text" id="update_model" name="model" required>
                    </div>
                    <div class="form-group">
                        <label for="update_price_per_day">Prijs per dag (€)</label>
                        <input type="number" step="0.01" id="update_price_per_day" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="update_mileage">Kilometerstand</label>
                        <input type="number" id="update_mileage" name="mileage" required>
                    </div>
                    <div class="form-group">
                        <label for="update_year">Bouwjaar</label>
                        <input type="number" id="update_year" name="year" required>
                    </div>
                    <div class="form-group">
                        <label for="update_availability">Beschikbaar</label>
                        <input type="checkbox" id="update_availability" name="availability">
                    </div>
                    <div class="form-group">
                        <label for="update_category">Autotype</label>
                        <input type="text" id="update_category" name="category" required>
                    </div>
                    <div class="form-group">
                        <label for="update_apk_date">APK Keuring datum</label>
                        <input type="date" id="update_apk_date" name="apk_date" required>
                    </div>
                </div>
                <button type="submit" name="update" class="search-button">Bijwerken</button>
            </form>
        </div>
    </main>
    <script>
        function openEditModal(id) {
            // Fetch car details using AJAX
            fetch(`getCar.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Check if data was returned successfully
                    if (data) {
                        document.getElementById('update_id').value = data.id;
                        document.getElementById('update_make').value = data.make;
                        document.getElementById('update_model').value = data.model;
                        document.getElementById('update_price_per_day').value = data.price_per_day;
                        document.getElementById('update_mileage').value = data.mileage;
                        document.getElementById('update_year').value = data.year;
                        document.getElementById('update_availability').checked = data.availability == 1;
                        document.getElementById('update_category').value = data.category;
                        document.getElementById('update_apk_date').value = data.apk_date;

                        // Show the update form
                        document.getElementById('updateForm').style.display = 'block';
                    } else {
                        alert('Geen gegevens gevonden voor deze auto.');
                    }
                })
                .catch(error => {
                    console.error('Er is een probleem opgetreden:', error);
                    alert('Er kon geen auto-gegevens worden geladen.');
                });
        }
    </script>
</body>
</html>
