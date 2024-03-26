<?php
session_start();

function getPokemonData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;

$limit = 6; 
$offset = ($page - 1) * $limit;

if (!isset($_SESSION['pokemonData'][$page])) {
    $url = "https://pokeapi.co/api/v2/pokemon/?offset={$offset}&limit={$limit}";
    $_SESSION['pokemonData'][$page] = getPokemonData($url);
}

function getPokemonImageUrlFromApi($url) {
    $pokemonData = getPokemonData($url);
    return $pokemonData['sprites']['front_default'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon API Demo</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Pokémon API</h1>
        <div id="pokemonList" class="pokemon-list">
            <?php
            $pokemonData = $_SESSION['pokemonData'][$page];
            if ($pokemonData) {
                foreach ($pokemonData['results'] as $pokemon) {
                    $pokemonDetails = getPokemonData($pokemon['url']);
                    $imageUrl = $pokemonDetails['sprites']['front_default'];
                    $name = $pokemonDetails['name'];
                    $abilities = '';
                    foreach ($pokemonDetails['abilities'] as $ability) {
                        $abilities .= $ability['ability']['name'] . ', ';
                    }
                    $abilities = rtrim($abilities, ', ');
                    $stats = implode('<br>', array_map(function($stat) {
                        return "{$stat['stat']['name']}: {$stat['base_stat']}";
                    }, $pokemonDetails['stats']));
                    echo '<div class="pokemon">';
                    echo "<img src=\"{$imageUrl}\" alt=\"{$name}\">";
                    echo "<p><strong>Name:</strong> {$name}</p>";
                    echo "<p><strong>Abilities:</strong> {$abilities}</p>";
                    echo "<p><strong>Stats:</strong><br>{$stats}</p>";
                    echo '</div>';
                }
            } else {
                echo '<p class="no-pokemon">Geen Pokémon gevonden.</p>';
            }
            ?>
        </div>
        
        <div class="pagination">
            <button id="prevPage" class="btn"><i class="fas fa-arrow-left"></i></button>
            <input type="text" id="pageNumber" value="<?php echo $page; ?>" class="page-input">
            <button id="nextPage" class="btn"><i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#prevPage').click(function() {
                var prevPage = parseInt($('#pageNumber').val()) - 1;
                if (prevPage >= 1) {
                    loadPage(prevPage);
                }
            });

            $('#nextPage').click(function() {
                var nextPage = parseInt($('#pageNumber').val()) + 1;
                loadPage(nextPage);
            });

            $('#pageNumber').keypress(function(event) {
                if (event.which === 13) { // Enter key
                    var pageNumber = parseInt($('#pageNumber').val());
                    loadPage(pageNumber);
                }
            });

            function loadPage(pageNumber) {
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>?page=' + pageNumber,
                    type: 'GET',
                    success: function(data) {
                        $('#pokemonList').html($(data).find('#pokemonList').html());
                        $('#pageNumber').val(pageNumber);
                    }
                });
            }
        });
    </script>
</body>
</html>
