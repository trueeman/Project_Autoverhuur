<?php
session_start(); // Start de sessie

// Controleer of de feedback_success sessie bestaat
if (!isset($_SESSION['feedback_success'])) {
    // Redirect als er geen feedback_success is
    header("Location: Contact.php");
    exit();
}

// Haal de feedback_success uit de sessie
$message = $_SESSION['feedback_success'];
unset($_SESSION['feedback_success']); // Verwijder de sessie variabele na het gebruiken
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Verzonden</title>
    <meta http-equiv="refresh" content="5;url=Contact.php"> <!-- Automatisch doorsturen na 5 seconden -->
    <link rel="stylesheet" href="styles.css"> <!-- Zorg ervoor dat je de juiste CSS linkt -->
</head>
<body class="bg-gray-100">
    <div class="container">
        <h2>Feedback Verzonden</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p>Je wordt automatisch doorgestuurd naar de contactpagina.</p>
    </div>
</body>
</html>
