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

if (!$query && !$type) {
    echo "Veuillez entrer un nom ou un type de Pokémon.";
    exit;
}

if ($type && in_array($type, $typeNames)) {
    // 🔍 Recherche par type
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
    // 🔍 Recherche par nom
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
<!-- Loader -->
<div id="loader">
    <div class="spinner"></div>
</div>

<?php include 'navbar.php'; ?>

<main>
    <div class="list_card">
        <?php if (empty($results)): ?>
            <p>Aucun résultat trouvé pour "<?php echo htmlspecialchars($query ?: $type); ?>".</p>
        <?php else: ?>
            <?php foreach ($results as $pokemon): ?>
                <div class="card">
                    <a class="page" href="pokemon.php?id=<?php echo $pokemon['id']; ?>">
                        <img src="<?php echo $pokemon['image']; ?>" alt="<?php echo $pokemon['name']; ?>"><br>
                        <?php echo $pokemon['name']; ?><br>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($results) && $totalPages > 1): ?>
        <div class="section_btn" style="margin-bottom:20px;">
            <?php if ($page > 1): ?>
                <a href="?<?php echo $type ? "type=$type" : "q=$query"; ?>&page=<?php echo $page - 1; ?>"><button>Back</button></a>
            <?php endif; ?>
            <h4>Page <?php echo $page; ?> / <?php echo $totalPages; ?></h4>
            <?php if ($page < $totalPages): ?>
                <a href="?<?php echo $type ? "type=$type" : "q=$query"; ?>&page=<?php echo $page + 1; ?>"><button>Next</button></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</main>

<script>
    const form = document.querySelector(".search-bar");
    const loader = document.getElementById("loader");

    // Afficher le loader lors de la soumission du formulaire
    form.addEventListener("submit", () => {
        loader.style.display = "flex";
    });

    // Afficher le loader pour les liens de type
    const typeLinks = document.querySelectorAll('.navbar .links a');
    typeLinks.forEach(link => {
        link.addEventListener('click', () => {
            loader.style.display = "flex";
        });
    });

    // Masquer le loader une fois la page chargée
    window.addEventListener("load", () => {
        loader.style.display = "none";
    });
</script>
</body>
</html>



