<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en'; // Langue par défaut : anglais


$apiUrl = "https://pokeapi.co/api/v2/pokemon/$id";
$speciesUrl = "https://pokeapi.co/api/v2/pokemon-species/$id";


$pokemon = json_decode(file_get_contents($apiUrl), true);
$species = json_decode(file_get_contents($speciesUrl), true);


$name = $pokemon['name'];
foreach ($species['names'] as $entry) {
    if ($entry['language']['name'] === $lang) {
        $name = $entry['name'];
        break;
    }
}


$description = '';
foreach ($species['flavor_text_entries'] as $entry) {
    if ($entry['language']['name'] === $lang && $entry['version']['name'] === 'x') {
        $description = str_replace(["\n", "\f"], ' ', $entry['flavor_text']);
        break;
    }
}
$types = array_map(function($t) {
    return $t['type']['name'];
}, $pokemon['types']);


$image = $pokemon['sprites']['other']['official-artwork']['front_default'];


$stats = $pokemon['stats'];


$evolutionUrl = $species['evolution_chain']['url'];
$evolutionData = json_decode(file_get_contents($evolutionUrl), true);


function getEvolutions($chain, &$evolutions = [], $level = null) {
    preg_match('/\/pokemon-species\/(\d+)\//', $chain['species']['url'], $match);
    $id = $match[1];
    $evolutions[] = [
        'id' => $id,
        'name' => $chain['species']['name'],
        'level' => $level
    ];
    foreach ($chain['evolves_to'] as $next) {
        $nextLevel = $next['evolution_details'][0]['min_level'] ?? null;
        getEvolutions($next, $evolutions, $nextLevel);
    }
    return $evolutions;
}


$evolutions = getEvolutions($evolutionData['chain']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <title>Pokémon: <?= $name ?></title>
    <link rel="icon" href="icon.png" type="image/png">
        <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            background-image:url("back.png");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: white;
            font-family: 'Segoe UI', sans-serif;
            text-align: center;
            padding: 20px;
        }
        .section_btn {
            display: flex;
            justify-content: flex-end;
        }
        .card {
            background-color: rgba(8, 0, 0, 0.74);
            border-radius: 20px;
            padding: 30px;
            display: inline-block;
            margin: 20px;
        }
        .stats {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .evo-chain ,.shiny-chain {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .evo-card,.shiny-card {
            background-color: rgba(8, 0, 0, 0.74);
            padding: 10px;
            border-radius: 12px;
        }
        .shiny-card{
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            width: 100px;
            height: 160px;
        }
        button {
            padding: 8px 16px;
            border: none;
            margin-bottom: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            text-shadow: 2px 2px #000;
        }
        .text{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="section_btn">
            <a href='all.php?page=1'><button style="color: white;background-color: red;">← Back to Pokédex</button></a>
        </div>
    </header>
    <main>
    <div class="card" style="height: auto;width: 500px;">
        <h1 style="font-family: 'Press Start 2P';font-size:20px;">N°<?= $id ?>  <?= ucfirst($name) ?></h1>
        <img src="<?= $image ?>" alt="<?= $name ?>" width="200">
<div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 20px;">
    <?php if ($id > 1): ?>
        <a href="pokemon.php?id=<?= $id - 1 ?>&lang=<?= $lang ?>">
            <button style="background-color: crimson; color: white;">← Back</button>
        </a>
    <?php endif; ?>

    <a href="pokemon.php?id=<?= $id + 1 ?>&lang=<?= $lang ?>">
        <button style="background-color: royalblue; color: white;">Next →</button>
    </a>
</div>

        <p class="text"><strong style="font-family: 'Press Start 2P'; font-size:12px;">Types</strong> <?= implode(", ", $types) ?></p>
        <p class="text"><strong style="font-family: 'Press Start 2P'; font-size:12px;">height</strong><?= $pokemon['height'] / 10 ?> m</p>
        <p class="text"><strong style="font-family: 'Press Start 2P'; font-size:12px;">weight</strong><?= $pokemon['weight'] / 10 ?> kg</p>
        <p class="text"><strong style="font-family: 'Press Start 2P'; font-size:12px;">Description</strong> <?= $description ?></p>

        <div class="stats">
            <h3 style="font-family: 'Press Start 2P'; font-size:12px;">Stats</h3>
            <?php foreach ($stats as $stat): ?>
                <div>
                    <?= ucfirst($stat['stat']['name']) ?>: <?= $stat['base_stat'] ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h2 style="font-size:20px !important;font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Evolutions</h2>
    <div class="evo-chain">
            <?php foreach ($evolutions as $evo): ?>
            <?php
                $evoName = $evo['name'];
                $speciesEvo = json_decode(file_get_contents("https://pokeapi.co/api/v2/pokemon-species/{$evo['id']}"), true);
                foreach ($speciesEvo['names'] as $entry) {
                    if ($entry['language']['name'] === $lang) {
                        $evoName = $entry['name'];
                        break;
                    }
                }
            ?>
            <div class="evo-card">
                <a href="pokemon.php?id=<?= $evo['id'] ?>&lang=<?= $lang ?>" style="color: white; text-decoration: none;">
                    <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/<?= $evo['id'] ?>.png" width="100">
                    <p style="font-family: 'Press Start 2P'; font-size: 10px;"><?= ucfirst($evoName) ?></p>
                    <?php if ($evo['level'] !== null): ?>
                        <p style="font-size: 14px;">LVL  <?= $evo['level'] ?></p>
                    <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <h2 style="font-size:20px !important;font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Shiny</h2>
    <div class="shiny-chain">
    <div class="shiny-card">
        <a href="pokemon.php?id=<?= $evo['id'] ?>&lang=<?= $lang ?>" style="color: white; text-decoration: none;">
        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/shiny/<?= $id ?>.png" alt="<?= $name ?> Shiny" width="120">
        <p style="font-family: 'Press Start 2P'; font-size: 10px;"><?= ucfirst($name) ?></p></a>
    </div>
    </div>
</main>
</body>
</html>