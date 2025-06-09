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
    <link rel="icon" href="icon.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style_v2.css">
</head>
<body>
    <!-- Loader -->
    <div id="loader" style="display: none;">
        <img src="loader.gif" alt="Chargement..." style="width: 100px;">
    </div>

    <?php include 'navbar.php';?>
    <main>
        <div class="list_card" style="grid-template-columns: repeat(3, 1fr);">
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
                <a href='?page=<?php echo $prev; ?>'><button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Back</button></a>
            <?php endif; ?>
            <h4 style="font-family: 'Press Start 2P', cursive;">Page <?php echo $page; ?></h4>
            <a href='?page=<?php echo $next; ?>'><button style="font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Next</button></a>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loader = document.getElementById("loader");
            const links = document.querySelectorAll('a[data-loader="true"], .search-bar');

            // Afficher le loader pour les liens
            links.forEach(link => {
                link.addEventListener('click', () => {
                    loader.style.display = "flex";
                });
            });

            // Pour le formulaire de recherche
            document.querySelector('.search-bar').addEventListener('submit', () => {
                loader.style.display = "flex";
            });
        });
    </script>
</body>
</html>
