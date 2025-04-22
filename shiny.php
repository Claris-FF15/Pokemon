<?php
function fetchJSON($url) {
    $response = @file_get_contents($url);
    return $response ? json_decode($response, true) : null;
}

function getAllPokemon() {
    $url = "https://pokeapi.co/api/v2/pokemon?limit=1000&offset=0";
    $data = fetchJSON($url);
    return $data['results'] ?? [];
}

function getPokemonShinyDetails($url) {
    $data = fetchJSON($url);
    if (!$data) return null;
    return [
        'id' => $data['id'],
        'name' => ucfirst($data['name']),
        'image' => $data['sprites']['front_shiny'],
        'types' => array_map(fn($t) => ucfirst($t['type']['name']), $data['types'])
    ];
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$allPokemon = getAllPokemon();
$totalResults = count($allPokemon);
$pagedPokemon = array_slice($allPokemon, $offset, $limit);

$results = [];
foreach ($pagedPokemon as $pokemon) {
    $details = getPokemonShinyDetails($pokemon['url']);
    if ($details && $details['image']) $results[] = $details;
}

$totalPages = ceil($totalResults / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Shiny</title>
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
    <div class="list_card">
        <?php foreach ($results as $pokemon): ?>
            <div class="card">
                <a class="page" href="pokemon.php?id=<?= $pokemon['id'] ?>">
                    <img src="<?= $pokemon['image'] ?>" alt="<?= $pokemon['name'] ?>"><br>
                    <?=$pokemon['name'] ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section_btn" style="color:black;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>"><button>← Back</button></a>
        <?php endif; ?>
        <span>Page <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>"><button>Next →</button></a>
        <?php endif; ?>
    </div>
</body>
</html>
