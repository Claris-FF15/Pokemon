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
     <link rel="icon" href="icon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_v2.css">
</head>
<body>
<?php include 'navbar.php';?>
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
            <a href="?page=<?= $page - 1 ?>"><button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">← Back</button></a>
        <?php endif; ?>
        <span>Page <?= $page ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>"><button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Next →</button></a>
        <?php endif; ?>
    </div>
</body>
</html>
