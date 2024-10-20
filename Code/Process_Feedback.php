<?php
session_start(); // Start de sessie

// Databaseverbinding
$host = 'localhost';
$db   = 'autoverhuur';  // Je database naam
$user = 'root';  // Je database username
$pass = "";  // Je database password

$dsn = "mysql:host=$host;dbname=$db;";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Maak een nieuwe PDO-instantie
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error: Kan geen verbinding maken met de database. " . $e->getMessage());
}

// Verwerk het formulier
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naam = $_POST['naam'];
    $email = $_POST['email'];
    $auto = $_POST['auto'];
    $feedback = $_POST['feedback'];
    $tevredenheid = $_POST['tevredenheid'];

    // Prepareer de SQL-insert
    $stmt = $pdo->prepare("INSERT INTO feedback (naam, email, auto, opmerkingen, tevredenheid) 
                            VALUES (:naam, :email, :auto, :feedback, :tevredenheid)");

    // Bind de waarden
    $stmt->bindParam(':naam', $naam);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':auto', $auto);
    $stmt->bindParam(':feedback', $feedback);
    $stmt->bindParam(':tevredenheid', $tevredenheid);

    // Voer de query uit
    if ($stmt->execute()) {
        // Zet een sessiemelding voor de bevestiging
        $_SESSION['feedback_success'] = "Feedback succesvol verzonden! Je wordt nu doorgestuurd naar de contactpagina.";
        header("Location: bevestiging.php"); // Stuur de gebruiker door naar de bevestigingspagina
        exit(); // Stop de verdere uitvoering
    } else {
        echo "Er is een fout opgetreden bij het verzenden van feedback.";
    }
}
?>
