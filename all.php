<?php
$limit = 24;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

$url = "https://pokeapi.co/api/v2/pokemon?limit=$limit&offset=$offset";
$response = file_get_contents($url);
$data = json_decode($response, true);

$prev = $page - 1;
$next = $page + 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pokédex</title>
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
        .card img {
    position: relative;
    filter: drop-shadow(0px 10px 15px rgba(255, 255, 255, 0.6));
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
    <main>
        <div class="list_card">
            <?php foreach ($data['results'] as $pokemon): 
                preg_match('/\/pokemon\/(\d+)\//', $pokemon['url'], $matches);
                $id = $matches[1];
                $image = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/$id.png";
            ?>
                <div class="card">
                    <a class="page" href='pokemon.php?id=<?php echo $id; ?>'>
                        <img src='<?php echo $image; ?>' alt='<?php echo $pokemon['name']; ?>'><br>
                        <?php echo ucfirst($pokemon['name']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section_btn">
            <?php if ($page > 1): ?>
                <a href='?page=<?php echo $prev; ?>'><button>Back</button></a>
            <?php endif; ?>
            <h4>Page <?php echo $page; ?></h4>
            <a href='?page=<?php echo $next; ?>'><button>Next</button></a>
        </div>
    </main>
</body>
</html>
