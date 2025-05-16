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
$limit = 4;
$offset = ($page - 1) * $limit;

$allPokemon = getAllPokemon();
$megaNormal = array_filter($allPokemon, function($p) {
    return strpos($p['name'], '-mega') !== false &&
           strpos($p['name'], '-mega-x') === false &&
           strpos($p['name'], '-mega-y') === false;
});

$totalResults = count($megaNormal);
$pagedMega = array_slice(array_values($megaNormal), $offset, $limit);

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
    <title>Pokémon Méga (Normaux)</title>
     <link rel="icon" href="icon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
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

        <div class="section_btn" style="margin-bottom:30px; color:black;">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>"><button>← Back</button></a>
            <?php endif; ?>
            <h4>Page <?= $page ?> / <?= $totalPages ?></h4>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>"><button>Next →</button></a>
            <?php endif; ?>
        </div>
    </main>    
</body>
</html>
