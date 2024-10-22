<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

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
            <h1>Login</h1>
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
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Inloggen</h2>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<p class='text-green-500 mb-4'>" . $_SESSION['success_message'] . "</p>";
            unset($_SESSION['success_message']);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
            $email = User::sanitizeInput($_POST['email']);
            $password = $_POST['password'];

            $result = $user->login($email, $password);
            
            if ($result['success']) {
                if ($result['role_id'] == 1) {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: Home.php");
                }
                exit();
            } else {
                echo "<p class='text-red-500 mb-4'>" . $result['message'] . "</p>";
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