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
                <a href="#" class="nav-link">Huurauto's</a>
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
                            <th>Autonaam</th>
                            <th>Model</th>
                            <th>Bedrag</th>
                            <th>Kilometerstand</th>
                            <th>Bouwjaar</th>
                            <th>Beschikbaar</th>
                            <th>Autotype</th>
                            <th>APK Keuring datum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Toyota</td>
                            <td>Camry</td>
                            <td>€45/dag</td>
                            <td>45,000 km</td>
                            <td>2020</td>
                            <td><span class="status-badge available">Beschikbaar</span></td>
                            <td>Sedan</td>
                            <td>01-05-2024</td>
                        </tr>
                        <tr>
                            <td>Volkswagen</td>
                            <td>Golf</td>
                            <td>€55/dag</td>
                            <td>30,000 km</td>
                            <td>2021</td>
                            <td><span class="status-badge unavailable">Niet beschikbaar</span></td>
                            <td>Hatchback</td>
                            <td>15-08-2024</td>
                        </tr>
                        <tr>
                            <td>BMW</td>
                            <td>3 Series</td>
                            <td>€65/dag</td>
                            <td>25,000 km</td>
                            <td>2022</td>
                            <td><span class="status-badge available">Beschikbaar</span></td>
                            <td>Sedan</td>
                            <td>30-11-2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="crud-button-container">
                <button class="crud-btn">CRUD</button>
            </div>
        </div>
    </main>
</body>
</html>