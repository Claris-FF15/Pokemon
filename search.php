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

function getPokemonDetails($url) {
    $data = fetchJSON($url);
    if (!$data) return null;
    return [
        'id' => $data['id'],
        'name' => ucfirst($data['name']),
        'image' => $data['sprites']['front_default'],
        'types' => array_map(fn($t) => ucfirst($t['type']['name']), $data['types'])
    ];
}

$query = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$typeList = fetchJSON('https://pokeapi.co/api/v2/type');
$typeNames = array_column($typeList['results'], 'name');

$results = [];

if (!$query) {
    echo "Veuillez entrer un nom ou un type de Pokémon.";
    exit;
}

if (in_array($query, $typeNames)) {
    // 🔍 Recherche par type uniquement
    $url = "https://pokeapi.co/api/v2/type/{$query}";
    $typeData = fetchJSON($url);

    if ($typeData && isset($typeData['pokemon'])) {
        $totalResults = count($typeData['pokemon']);
        $pagedList = array_slice($typeData['pokemon'], $offset, $limit);

        foreach ($pagedList as $p) {
            $details = getPokemonDetails($p['pokemon']['url']);
            if ($details) $results[] = $details;
        }

        $totalPages = ceil($totalResults / $limit);
    }
} else {
    // 🔍 Recherche par nom uniquement
    $allPokemon = getAllPokemon();
    $matchingPokemon = array_filter($allPokemon, fn($pokemon) => strpos($pokemon['name'], $query) !== false);
    $totalResults = count($matchingPokemon);
    $pagedResults = array_slice($matchingPokemon, $offset, $limit);

    foreach ($pagedResults as $pokemon) {
        $details = getPokemonDetails($pokemon['url']);
        if ($details) $results[] = $details;
    }

    $totalPages = ceil($totalResults / $limit);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultats de recherche</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #d62828;
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

        .search-bar button:hover {
            background-color: #001219;
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
        .card img {
    position: relative;
    filter: drop-shadow(0px 10px 15px rgba(255, 255, 255, 0.6));
} 

    </style>
</head>
<body>
<body>
<!-- Loader -->
<div id="loader">
    <div class="spinner"></div>
</div>
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
        <?php if (empty($results)): ?>
            <p>Aucun résultat trouvé pour "<?php echo htmlspecialchars($query); ?>".</p>
        <?php else: ?>
            <?php foreach ($results as $pokemon): ?>
                <div class="card">
                    <a class="page" href="pokemon.php?id=<?php echo $pokemon['id']; ?>">
                        <img src="<?php echo $pokemon['image']; ?>" alt="<?php echo $pokemon['name']; ?>"><br>
                        <?php echo $pokemon['name']; ?><br>
                        <span>Types : <?php echo implode(", ", $pokemon['types']); ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="section_btn" style="margin-bottom:20px;">
            <?php if ($page > 1): ?>
                <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page - 1; ?>"><button>Back</button></a>
            <?php endif; ?>
            <span>Page <?php echo $page; ?> / <?php echo $totalPages; ?></span>
            <?php if ($page < $totalPages): ?>
                <a href="?q=<?php echo urlencode($query); ?>&page=<?php echo $page + 1; ?>"><button>Next</button></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>
<script>
    const form = document.querySelector(".search-bar");
    const loader = document.getElementById("loader");

    // Afficher le loader quand on soumet le formulaire de recherche
    form.addEventListener("submit", () => {
        loader.style.display = "flex";
    });

    // Afficher le loader quand on clique sur un type
    const typeLinks = document.querySelectorAll('.navbar .links a'); 

    typeLinks.forEach(link => {
        link.addEventListener('click', (event) => {
            loader.style.display = "flex"; // Afficher le loader

            setTimeout(() => {
            }, 100);
        });
    });

    window.addEventListener("load", () => {
        loader.style.display = "none";
    });
</script>


</body>
</html>


