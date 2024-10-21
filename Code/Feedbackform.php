<?php
// Hier komt je PHP-code voor het verwerken van de feedbackformulier (optioneel)
// Bijvoorbeeld, je kunt de gegevens opslaan in de database of een e-mail verzenden.
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Feedback Formulier</title>
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
                <h1>Feedback Formulier</h1>
            </div>
            <div class="nav-links">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="./admin/Adminreserveringweergave.php" class="nav-link">Admin</a>
                <a href="Contact.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">Reserveringen Weergave</a>
                <a href="Insertimg.php" class="nav-link login">Login</a>
                <a href="register" class="nav-link register">Register</a>
            </div>
        </div>
    </nav>

    <main class="main-container">
        <div class="content-container">
            <h2>Geef je Feedback</h2>
            <form action="Process_Feedback.php" method="post" class="rental-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="naam">Naam:</label>
                        <input type="text" id="naam" name="naam" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="auto">Welke auto heb je gebruikt:</label>
                        <input type="text" id="auto" name="auto" required>
                    </div>
                    <div class="form-group">
                        <label for="feedback">Feedback/Service opmerkingen:</label>
                        <textarea id="feedback" name="feedback" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="tevredenheid">Tevredenheid:</label>
                        <select id="tevredenheid" name="tevredenheid" required>
                            <option value="">Kies een optie</option>
                            <option value="1">1 - Zeer ontevreden</option>
                            <option value="2">2 - Ontevreden</option>
                            <option value="3">3 - Neutraal</option>
                            <option value="4">4 - Tevreden</option>
                            <option value="5">5 - Zeer tevreden</option>
                        </select>
                    </div>
                </div>
                <input type="submit" value="Verstuur Feedback" class="search-button">
            </form>
        </div>
    </main>
</body>
</html>
