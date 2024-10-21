<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARD Verhuur</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <nav class="nav-bar">
        <div class="container">
            <h1>Huurauto's</h1>
            <div class="nav-links">
                <a href="#" class="nav-link">Huurauto's</a>
                <a href="Adminweergave.php" class="nav-link">Admin</a>
                <a href="Test.php" class="nav-link">Contact</a>
                <a href="Reservering.php" class="nav-link">Mijn boekingen</a>
                <a href="Adminreserveringweergave.php" class="nav-link">res_Weergaven</a>
                <a href="reservering_edit.php" class="nav-link">Edit</a>
                <a href="login.php" class="nav-link login">Login</a>
                <a href="register.php" class="nav-link register">Register</a>
            </div>
        </div>
    </nav>

    <main class="container-rental">
        <div class="rental-form">
            <h2>Huur een auto</h2>
            <form action="Huurautos.php" method="GET">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="pickup-location">Ophaallocatie</label>
                        <select id="pickup-location" name="pickup-location" required>
                            <option value="">Kies een ophaallocatie</option>
                            <option value="amsterdam">Amsterdam</option>
                            <option value="rotterdam">Rotterdam</option>
                            <option value="den-haag">Den Haag</option>
                            <option value="utrecht">Utrecht</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="dropoff-location">Inleverlocatie</label>
                        <select id="dropoff-location" name="dropoff-location" required>
                            <option value="">Kies een inleverlocatie</option>
                            <option value="amsterdam">Amsterdam</option>
                            <option value="rotterdam">Rotterdam</option>
                            <option value="den-haag">Den Haag</option>
                            <option value="utrecht">Utrecht</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pickup-date">Ophaaldatum en -tijd</label>
                        <div class="input-group">
                            <input type="date" id="pickup-date" name="pickup-date" required>
                            <input type="time" id="pickup-time" name="pickup-time" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dropoff-date">Inleverdatum en -tijd</label>
                        <div class="input-group">
                            <input type="date" id="dropoff-date" name="dropoff-date" required>
                            <input type="time" id="dropoff-time" name="dropoff-time" required>
                        </div>
                    </div>
                </div>
                <input type="submit" value="Auto Reserveren" name="Submit" class="crud-btn" />
            </form>
        </div>
    </main>
</body>

</html>