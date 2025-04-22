<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
                body {
            margin: 0;
            background-image: url("back.png");
background-size: cover;
background-position: center;
background-repeat: no-repeat;
background-attachment: fixed;
margin: 0;
            font-family: 'Segoe UI', sans-serif;
            color: white;
        }

        .navbar {
            background-color: #ef233c;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.3);
            margin-bottom: 40px;
        }

        .navbar .logo a {
            font-family: 'Press Start 2P', cursive;
            font-size: 16px;
            color: #fefae0;
            text-decoration: none;
            text-shadow: 2px 2px #000;
        }

        .navbar .links a {
            margin: 0 15px;
            text-decoration: none;
            color: white;
            font-weight: bold;
        }

        .navbar .links a:hover {
            text-decoration: underline;
        }
        
        .search-bar {
            display: flex;
            align-items: center;
        }

        .search-bar input {
            padding: 6px 10px;
            border-radius: 6px 0 0 6px;
            border: none;
            outline: none;
            width: 200px;
        }

        .search-bar button {
            padding: 6px 12px;
            border: none;
            border-radius: 0 6px 6px 0;
            background-color: #003049;
            color: white;
            cursor: pointer;
        }
        .type-select-form select {
        padding: 6px;
        border-radius: 6px;
        border: none;
        background-color: #003049;
        color: white;
        font-weight: bold;
        cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #001219;
        }
    </style>
</head>
<body>
<header>
        <div class="navbar">
            <div class="logo"><a href="all.php?page=1">Pokédex</a></div>
            <div class="links" style="display:flex;">
            <form action="search.php" method="GET" class="type-select-form">
                <select name="q" onchange="this.form.submit()">
                    <option value="">-- Types --</option>
                        <?php
                        // Appel à l'API pour récupérer les types
                        $types = json_decode(file_get_contents('https://pokeapi.co/api/v2/type'), true);
                        foreach ($types['results'] as $type) {
                            echo '<option value="' . $type['name'] . '">' . ucfirst($type['name']) . '</option>';
                        }
                        ?>
                </select>
            </form>
                <a href="categories.php" style="padding-left: 15px;">Catégories</a>
            </div>
            <form class="search-bar" action="search.php" method="GET">
                <input type="text" name="q" placeholder="Rechercher un Pokémon...">
                <button type="submit">🔍</button>
            </form>
        </div>
    </header>
    <ul>
        <li><a href="shiny.php">shiny</a></li>
        <li><a href="mega.php">mega</a></li>
        <li><a href="mega_X.php">mega X</a></li>
        <li><a href="mega_Y.php">mega Y</a></li>
    </ul>
</body>
</html>