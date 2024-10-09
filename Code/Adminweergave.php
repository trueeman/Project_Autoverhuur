<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Auto Weergave</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
         
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 80%;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        header {
            background-color: #d3d3d3;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid blue;
        }

        header h1 {
            font-size: 24px;
        }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            text-decoration: none;
            color: black;
            font-weight: bold;
        }

        .login-register {
            display: flex;
            gap: 10px;
        }

        .login-register button {
            background-color: #696969;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .content {
            padding: 20px;
        }

        .content h2 {
            background-color: #d3d3d3;
            padding: 10px;
            border-radius: 15px;
        }

        .car-list {
            margin-top: 20px;
            background-color: #d3d3d3;
            padding: 20px;
            border-radius: 15px;
        }

        .car-item {
            margin-bottom: 10px;
            font-size: 16px;
        }

        .crud-btn {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .crud-btn button {
            background-color: #d3d3d3;
            border: none;
            padding: 10px 30px;
            border-radius: 15px;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Admin Auto Weergave</h1>
        <nav>
            <a href="#">Huurauto's</a>
            <a href="#">Contact</a>
            <a href="#">Mijn boekingen</a>
        </nav>
        <div class="login-register">
            <button>Login</button>
            <button>Register</button>
        </div>
    </header>

    <div class="content">
        <h2>Auto Weergave</h2>
        <div class="car-list">
            <div class="car-item">Autonaam: Model: Bedrag: Kilometerstand: Bouwjaar: Beschikbaar: Autotype: Apk Keuring datum:</div>
            <div class="car-item">Autonaam: Model: Bedrag: Kilometerstand: Bouwjaar: Beschikbaar: Autotype: Apk Keuring datum:</div>
            <div class="car-item">Autonaam: Model: Bedrag: Kilometerstand: Bouwjaar: Beschikbaar: Autotype: Apk Keuring datum:</div>
        </div>

        <div class="crud-btn">
            <button>Crud</button>
        </div>
    </div>
</div>

</body>
</html>
