<?php
function fetchJSON($url) {
    $response = @file_get_contents($url);
    return $response ? json_decode($response, true) : null;
}

function getAllPokemon() {
    $url = "https://pokeapi.co/api/v2/pokemon?limit=2000&offset=0";
    $data = fetchJSON($url);
    return $data['results'] ?? [];
}

function getPokemonDetails($url) {
    $data = fetchJSON($url);
    if (!$data) return null;
    return [
        'id' => $data['id'],
        'name' => ucfirst($data['name']),
        'image' => $data['sprites']['other']['official-artwork']['front_default'] ?? $data['sprites']['front_default'],
        'types' => array_map(fn($t) => ucfirst($t['type']['name']), $data['types'])
    ];
}

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 24;
$offset = ($page - 1) * $limit;

$allPokemon = getAllPokemon();
$megaXPokemon = array_filter($allPokemon, fn($p) => strpos($p['name'], 'mega-x') !== false);

$totalResults = count($megaXPokemon);
$pagedMega = array_slice(array_values($megaXPokemon), $offset, $limit);

$results = [];
foreach ($pagedMega as $pokemon) {
    $details = getPokemonDetails($pokemon['url']);
    if ($details) $results[] = $details;
}

$totalPages = ceil($totalResults / $limit);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Méga X</title>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
     <link rel="icon" href="icon.png" type="image/png">
    <link rel="stylesheet" href="style_v2.css">
    <style>
        /* Grille pour la liste des cartes */
        .list_card {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Créer 3 colonnes égales */
            gap: 20px; /* Espacement entre les cartes */
            margin-top: 20px;
        }

        /* Réactivité : Grille en 1 colonne sur petits écrans */
        @media (max-width: 768px) {
            .list_card {
                grid-template-columns: 1fr; /* 1 colonne sur mobile */
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php';?> 
    <main>
        <div class="list_card">
            <?php foreach ($results as $pokemon): ?>
                <div class="card" style="width:auto;">
                        <img style="max-width:300px;"src="<?= $pokemon['image'] ?>" alt="<?= $pokemon['name'] ?>"><br>
                        <?= $pokemon['name'] ?><br>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section_btn">
            <a href='all.php?page=1'><button style="margin-top: 20px;color: white;background-color: red;">Back to Pokédex</button></a>
        </div>
    </main>
</body>
</html>

