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
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$typeList = fetchJSON('https://pokeapi.co/api/v2/type');
$typeNames = array_column($typeList['results'], 'name');

$results = [];
$error = '';

if (!$query && !$type) {
    $error = "Veuillez entrer un nom ou un type de Pokémon.";
} elseif ($type && in_array($type, $typeNames)) {
    $url = "https://pokeapi.co/api/v2/type/{$type}";
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
    <link rel="icon" href="icon.png" type="image/png">
    <link rel="stylesheet" href="style_v2.css">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
</head>
<body>
<div id="loader" style="display: none;">
    <img src="loader.gif" alt="Chargement..." style="width: 100px;">
</div>

<header>
    <div class="navbar">
        <div class="logo"><a href="all.php?page=1">Pokédex</a></div>
        <div class="links" style="display:flex; gap: 15px;">
            <div class="dropdown">
                <button class="dropbtn" style="font-size:13px !important;font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Types  ▼</button>
                <div class="dropdown-content">
                    <?php
                    foreach ($typeList['results'] as $typeItem) {
                        echo '<a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="search.php?type=' . $typeItem['name'] . '">' . ucfirst($typeItem['name']) . '</a>';
                    }
                    ?>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropbtn" style="font-size:13px !important;font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Catégories  ▼</button>
                <div class="dropdown-content">
                    <a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="shiny.php">Shiny</a>
                    <a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="mega.php">Méga-Évolutions</a>
                    <a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="mega_X.php">Méga X</a>
                    <a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="mega_Y.php">Méga Y</a>
                </div>
            </div>
        </div>
        <form class="search-bar" action="search.php" method="GET">
            <input type="text" name="q" placeholder="Rechercher un Pokémon..." autocomplete="off">
            <button type="submit">🔍</button>
        </form>
    </div>
</header>

<main>
    <div class="list_card">
        <?php if ($error): ?>
            <p style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;"><?= $error ?></p>
        <?php elseif (empty($results)): ?>
            <p style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Aucun résultat trouvé pour "<?= htmlspecialchars($query ?: $type) ?>".</p>
        <?php else: ?>
            <?php foreach ($results as $pokemon): ?>
                <div class="card">
                    <a class="page" href="pokemon.php?id=<?= $pokemon['id'] ?>">
                        <img src="<?= $pokemon['image'] ?>" alt="<?= $pokemon['name'] ?>">
                        <br><?= $pokemon['name'] ?><br>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($results)): ?>
        <div class="section_btn" style="margin-bottom:20px;">
            <?php if ($page > 1): ?>
                <a href="?<?= $type ? "type=$type" : "q=$query" ?>&page=<?= $page - 1 ?>">
                    <button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Back</button>
                </a>
            <?php endif; ?>
            <h4 style="font-family: 'Press Start 2P', cursive;">Page <?= $page ?></h4>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $type ? "type=$type" : "q=$query" ?>&page=<?= $page + 1 ?>">
                    <button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Next</button>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    const loader = document.getElementById("loader");

    const searchForm = document.querySelector("form.search-bar");
    if (searchForm) {
        searchForm.addEventListener("submit", (e) => {
            const input = searchForm.querySelector("input[name='q']");
            if (input && input.value.trim() === "") {
                e.preventDefault();
                alert("Veuillez entrer un nom de Pokémon.");
                return;
            }
            loader.style.display = "flex";
        });
    }

    document.querySelectorAll('[data-loader="true"]').forEach(link => {
        link.addEventListener('click', () => {
            loader.style.display = "flex";
        });
    });

    window.addEventListener("load", () => {
        setTimeout(() => {
            loader.style.display = "none";
        }, 500);
    });
</script>
</body>
</html>


