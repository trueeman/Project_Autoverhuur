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

// login.php - Login formulier en verwerking
session_start();
?>



<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
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
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center">Inloggen</h2>
            <?php
            if (isset($_SESSION['success_message'])) {
                echo "<p class='text-green-500 mb-4'>" . $_SESSION['success_message'] . "</p>";
                unset($_SESSION['success_message']);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
                $email = sanitize_input($_POST['email']);
                $password = $_POST['password'];

                try {
                    $stmt = $pdo->prepare("SELECT user_id, password_hash, role_id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['role_id'] = $user['role_id'];
                        
                        // Redirect based on role
                        if ($user['role_id'] == 1) {
                            header("Location: admin/dashboard.php");
                        } else {
                            header("Location: home.html");
                        }
                        exit();
                    } else {
                        echo "<p class='text-red-500 mb-4'>Ongeldige inloggegevens</p>";
                    }
                } catch(PDOException $e) {
                    echo "<p class='text-red-500 mb-4'>Er is een fout opgetreden: " . $e->getMessage() . "</p>";
                }
            }
            ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">E-mail</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="email" name="email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Wachtwoord</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           type="password" name="password" required>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                            type="submit" name="login">
                        Inloggen
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                        Registreren
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>