<?php
// config.php - Database configuratie
$db_host = 'localhost';
$db_name = 'autoverhuur';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// functions.php - Helper functies
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


// register.php - Registratie formulier en verwerking
session_start();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registreren</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="nav-bar">
        <div class="container">
            <h1>Registreer</h1>
            <div class="nav-links">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="Adminweergave.php" class="nav-link">Admin</a>
                <a href="Test.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <a href="reservering_edit.php" class="nav-link">Edit</a>
                <a href="Insertimg.php" class="nav-link login">Login</a>
                <a href="register" class="nav-link register">Register</a>
            </div>
        </div>
    </nav>
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center">Registreren</h2>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
                $firstname = sanitize_input($_POST['firstname']);
                $lastname = sanitize_input($_POST['lastname']);
                $email = sanitize_input($_POST['email']);
                $password = $_POST['password'];
                $phone = sanitize_input($_POST['phone']);

                // Validatie
                $errors = [];
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Ongeldig e-mailadres";
                }

                if (empty($errors)) {
                    try {
                        // Check of email al bestaat
                        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        
                        if ($stmt->rowCount() > 0) {
                            echo "<p class='text-red-500 mb-4'>Dit e-mailadres is al geregistreerd</p>";
                        } else {
                            // Gebruiker toevoegen
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, email, password_hash, phone_number, role_id) VALUES (?, ?, ?, ?, ?, 2)");
                            $stmt->execute([$firstname, $lastname, $email, $password_hash, $phone]);
                            
                            $_SESSION['success_message'] = "Registratie succesvol! U kunt nu inloggen.";
                            header("Location: login.php");
                            exit();
                        }
                    } catch(PDOException $e) {
                        echo "<p class='text-red-500 mb-4'>Er is een fout opgetreden: " . $e->getMessage() . "</p>";
                    }
                } else {
                    foreach ($errors as $error) {
                        echo "<p class='text-red-500 mb-4'>$error</p>";
                    }
                }
            }
            ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="firstname">Voornaam</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="text" name="firstname" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="lastname">Achternaam</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="text" name="lastname" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">E-mail</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Wachtwoord</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="password" name="password" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">Telefoonnummer</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="tel" name="phone" required>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit" name="register">
                        Registreren
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="login.php">
                        Al een account?
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

