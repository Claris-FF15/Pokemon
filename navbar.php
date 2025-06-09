<header>
    <div class="navbar">
        <div class="logo"><a href="all.php?page=1">Pokédex</a></div>
        <div class="links" style="display:flex; gap: 15px;">        
            <!-- Types -->
            <div class="dropdown">
                <button class="dropbtn" style="font-size:13px !important;font-family: 'Press Start 2P', cursive;text-shadow: 2px 2px #000;">Types  ▼</button>
                <div class="dropdown-content">
                    <?php
                    $types = json_decode(file_get_contents('https://pokeapi.co/api/v2/type'), true);
                    foreach ($types['results'] as $type) {
                        echo '<a data-loader="true" style="margin-top:5px; margin-bottom:5px;" href="search.php?type=' . $type['name'] . '">' . ucfirst($type['name']) . '</a>';
                    }
                    ?>
                </div>
            </div>
            <!-- Catégories  -->
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
        <!-- Barre de recherche -->
        <form class="search-bar" action="search.php" method="GET">
            <input type="text" name="q" placeholder="Rechercher un Pokémon...">
            <button type="submit">🔍</button>
        </form>
    </div>
</header>
