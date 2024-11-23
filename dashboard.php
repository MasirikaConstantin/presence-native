<?php
declare(strict_types=1);
ob_start();
require_once 'db.php';
session_start();


if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}


$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM administrateurs WHERE id = :id");
$stmt->execute(['id' => $_SESSION['admin_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin || !$admin['etat']) {
    session_destroy();
    header('Location: index.php');
    exit;
}

class GeoCalculator {
    private const EARTH_RADIUS = 6371000;
    public const DEFAULT_RADIUS = 50;

    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return self::EARTH_RADIUS * $c;
    }

    public static function parseCoordinates(string $location): ?array {
        $location = str_replace(['(', ')'], '', $location);
        $coords = explode(',', $location);
        
        if (count($coords) !== 2) {
            return null;
        }

        return [
            'latitude' => (float) trim($coords[0]),
            'longitude' => (float) trim($coords[1])
        ];
    }
}

class PresenceManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPresences(?string $date = null): array {
        $query = "
            SELECT 
                p.id,
                p.id_utilisateur,
                p.localisation,
                p.date,
                p.heure,
                u.nom,
                u.lieu_id,
                l.nom AS nom_lieu,
                l.localisation AS lieu_localisation
            FROM presence p
            JOIN utilisateur u ON p.id_utilisateur = u.id
            JOIN lieu l ON u.lieu_id = l.id
        ";

        $params = [];
        if ($date !== null) {
            $query .= " WHERE p.date = :date";
            $params['date'] = $date;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function processPresences(array $presences): array {
        foreach ($presences as &$presence) {
            $workCoords = GeoCalculator::parseCoordinates($presence['lieu_localisation']);
            $userCoords = GeoCalculator::parseCoordinates($presence['localisation']);

            if (!$workCoords || !$userCoords) {
                $presence['status'] = 'Erreur';
                continue;
            }

            $distance = GeoCalculator::calculateDistance(
                $workCoords['latitude'],
                $workCoords['longitude'],
                $userCoords['latitude'],
                $userCoords['longitude']
            );

            $presence['status'] = ($distance <= GeoCalculator::DEFAULT_RADIUS) 
                ? 'Présent' 
                : 'Absent';
            $presence['distance'] = round($distance);
        }

        return $presences;
    }
}




    $presenceManager = new PresenceManager($pdo);
    $selectedDate = $_GET['date'] ?? null;
    $presences = $presenceManager->getPresences($selectedDate);
    $processedPresences = $presenceManager->processPresences($presences);

    //$_SESSION['impression'] = $processedPresences;
    $selectedStatus = $_POST['status'] ?? '';
// Création du filtre en fonction des paramètres POST
$filteredPresences = array_filter($processedPresences, function($presence) {
    $conditions = true;
    
    // Filtre par status si spécifié
    if(isset($_POST['status']) && $_POST['status'] !== '') {
        $conditions = $conditions && ($presence['status'] === $_POST['status']);
    }
    
    // Filtre par date si spécifiée
    if(isset($_POST['date']) && $_POST['date'] !== '') {
        $conditions = $conditions && ($presence['date'] === $_POST['date']);
    }
    
    return $conditions;
});

$_SESSION['impression'] = $filteredPresences;

//var_dump($filteredPresences);

    //var_dump($processedPresences);
    ?>
    
       
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Presence</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
         <link rel="icon" type="image/png" sizes="32x32" href="mas-product.ico"/>
        <link rel="icon" type="image/png" sizes="16x16" href="mas-product.ico"/>
        
        <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
        <!-- Scripts -->
          <link rel="stylesheet" href="style2.css">
          <style>
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
            }
            .present { color: green; }
            .absent { color: red; }
        </style>

          <style>
        .dropdown-menu {
            display: none;
        }
        .dropdown-menu.show {
            display: block;
        }
    </style>

    </head>
<body class="font-sans antialiased">
        

    <div class="min-h-screen bg-gray-100">
        <nav class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="dashboard.php">
                                <svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" class="block h-9 w-auto fill-current text-gray-800">
                                    <!-- SVG content here -->
                                </svg>
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                            <a class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out" href="dashboard.php">
                                Tableau de bord
                            </a>
                            
                        </div>
                    </div>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center sm:hidden">
                        <button id="mobileMenuButton" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

           
        </nav>



        <!-- Responsive Navigation Menu -->
        <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a class="block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-indigo-700 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out" href="dashboard.php">
                    Tableau de bord
                </a>
            </div>
        
        </div>
    </nav>
    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Tableau de bord
            </h2>
        </div>
    </header>
    <main>
        <div class="py-12">
            <div class=""><!--max-w-7xl mx-auto sm:px-6 lg:px-8-->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <p class="mb-2">Bienvenue, <?php echo htmlspecialchars($admin['email']); ?>!   <?="      "."      " ?>        <a class="font-medium text-blue-600 dark:text-blue-500 hover:underline" href="modif.php?id=<?=$admin['id']?>">Modifier mes informations</a></p>
                        <a href="logout.php" class="text-white mt-5 right-2.5 bottom-2.5 bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 transition duration-150 ease-in-out">Déconnexion</a>
                        <!--a class="text-white mt-5 right-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition duration-150 ease-in-out" href="admin.php">Ajouter un Administrateur</a>
                        <a class="text-white mt-5 right-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition duration-150 ease-in-out" href="utilisateur.php">Ajouter un utilisateur</a-->
                        <a class="text-white mt-5 right-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition duration-150 ease-in-out" href="print_page.php"target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Imprimer" >Imprimer</a>
                        <div class="relative dropdown inline-flex items-center px-1 pt-1 ">
                            <button id="dropdownDefaultButton" data-dropdown-toggle="dropdown" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-6 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out" type="button">Gestion des utilisateurs 
                                <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                </svg>
                            </button>
                            <div id="dropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-84 ">
                                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
                                    <li>
                                        <a href="utilisateur.php" class="block px-4 py-2 hover:bg-gray-100">Créer un utilisateur</a>
                                    </li>
                                    <li>
                                        <a href="admin.php" class="block px-4 py-2 hover:bg-gray-100">Créer un admin</a>
                                    </li>
                                    <li>
                                        <a href="liste_admins.php" class="block px-4 py-2 hover:bg-gray-100">Voir tous les admins</a>
                                    </li>
                                    <li>
                                        <a href="liste_utilisateurs.php" class="block px-4 py-2 hover:bg-gray-100">Voir tous les utilisateurs</a>
                                    </li>
                                    <li>
                                        <a href="gestion_lieux.php" class="block px-4 py-2 hover:bg-gray-100">Gérer les lieux</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-8 rounded-lg shadow-lg">
                    <div class="mb-8 space-y-2 md:space-y-0 md:flex md:justify-between ">
    <div class="w-full md:w-2/5 lg:w-7/15">
        <form>
            <label for="default-search" class="mb-2 text-sm font-medium text-gray-900 sr-only">Rechercher</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="search" id="myInput" onkeyup="filterTable()" class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500" placeholder="Recherche Rapide" required />
                <button type="submit" class="text-white absolute right-2.5 bottom-2.5 bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition duration-150 ease-in-out">Rechercher</button>
            </div>
        </form>
    </div>

    <div class="w-full md:w-2/5 lg:w-1/2">
        <div class="flex items-center space-x-2">
            <form action="dashboard.php" method="GET" class="flex items-center">
                <div class="relative max-w-sm me-3">
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                        </svg>
                    </div>
                    <input datepicker id="default-datepicker" name="date" datepicker-format="yyyy-mm-dd" value="" type="text" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5" placeholder="Select date">
                </div>
                <button type="submit" class="text-white  bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 transition duration-150 ease-in-out">
                    Rechercher
                </button>
            </form>

            
        </div>

       
        </div>
        
    </div>
    <div class="w-full md:w-2/5 lg:w-1/2" >
            <!-- Sélecteur pour trier les absences ou présences -->
        <div class="flex items-center space-x-2">

            <div class="relative max-w-sm me-3">
                <form action="" method="POST" class="flex items-center relative ">
                    <select name="status" class="bg-gray-50 me-3 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" <?php if ($selectedStatus === '') echo 'selected'; ?>>Tous</option>
                        <option value="Présent" <?php if ($selectedStatus === 'Présent') echo 'selected'; ?>>Présents</option>
                        <option value="Absent" <?php if ($selectedStatus === 'Absent') echo 'selected'; ?>>Absents</option>

                    </select>
                    <button type="submit" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2.5 transition duration-150 ease-in-out">
                        Trier
                    </button>
                </form>
            </div>

            <!-- Bouton pour annuler le tri -->
            <form action="dashboard.php">
            <div class="">
                    <button type="submit" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2.5 transition duration-150 ease-in-out">
                        Annuler tous les filtres
                    </button>
            </div> 
            </form>
            </div> 
</div>

                    
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">

                    <table id="myTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom</th>
                                <th>Localisation actuelle</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Status</th>
                                <th>Distance (m)</th>
                                <th>Action</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredPresences as $presence): ?>
                                <?php 
                                        // Générer le lien Google Maps en utilisant la localisation du tableau $presence
                                        $googleMapsLink = "https://www.google.com/maps?q={$presence['localisation']}";
                                    ?>
                            <tr id="trs">
                                <td><?= htmlspecialchars((string)$presence['id_utilisateur']) ?></td>
                                <td><?= htmlspecialchars($presence['nom']) ?></td>
                                <!--td>< ?= htmlspecialchars($presence['nom_lieu']) . '(' . htmlspecialchars($presence['lieu_localisation']) . ')' ?></td-->
                                <td><?= htmlspecialchars($presence['localisation']) ?></td>
                                <td><?= htmlspecialchars($presence['date']) ?></td>
                                <td><?= htmlspecialchars($presence['heure']) ?></td>
                                <td class="<?= $presence['status'] === 'Présent' ? 'present' : 'absent' ?>">
                                    <?= htmlspecialchars($presence['status']) ?>
                                </td>
                                <td><?= htmlspecialchars((string)($presence['distance'] ?? 'N/A')) ?></td>
                                <td class="px-6 py-4"><a class="font-medium text-blue-600 hover:underline" href="<?= $googleMapsLink ?>" target="_blank" class="text-blue-600 hover:underline">Google Maps</a></td>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>


                        <!--table id="myTable" class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Num</th>
                                    <th scope="col" class="px-6 py-3">Nom de l&#039;utilisateur</th>
                                    <th scope="col" class="px-6 py-3">date</th>
                                    <th scope="col" class="px-6 py-3">Heure de Pointe</th>
                                    <th scope="col" class="px-6 py-3">Emplacement</th>
                                    <th scope="col" class="px-6 py-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($presences as $presence): ?> 
                                    <?php 
                                        $x++;
                                        // Générer le lien Google Maps en utilisant la localisation du tableau $presence
                                        $googleMapsLink = "https://www.google.com/maps?q={$presence['localisation']}";
                                    ?>
                                    <tr id="trs" class="bg-white border-b hover:bg-gray-50">
                                        <th class="px-6 py-4 "><?= $presence['id']?></th>
                                        <td class="px-6 py-4 font-medium text-gray-900"><?= $presence['nom']?></td>
                                        <td class="px-6 py-4"><?= $presence['date']?></td>
                                        <td class="px-6 py-4"><?= $presence['heure']?></td>
                                        <td class="px-6 py-4"><?= $presence['localisation']?></td>
                                        <td class="px-6 py-4"><a class="font-medium text-blue-600 hover:underline" href="<?= $googleMapsLink ?>" target="_blank" class="text-blue-600 hover:underline">Ouvrir sur Google Maps</a></td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table-->
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
    <script>
        // Dropdown menu
        const dropdownButton = document.getElementById('dropdownButton');
        const dropdownMenu = document.getElementById('dropdownMenu');

        dropdownButton.addEventListener('click', () => {
            dropdownMenu.classList.toggle('show');
        });

        // Close the dropdown when clicking outside
        window.addEventListener('click', (event) => {
            if (!event.target.matches('#dropdownButton') && !event.target.closest('#dropdownMenu')) {
                dropdownMenu.classList.remove('show');
            }
        });

        // Mobile menu
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
            mobileMenuButton.querySelector('svg path:first-child').classList.toggle('hidden');
            mobileMenuButton.querySelector('svg path:last-child').classList.toggle('hidden');
        });


        
    </script>
    <script>
                
        // Sélectionnez tous les éléments du tableau
var elements = document.querySelectorAll("table #tr");

// Parcourez tous les éléments
for(var i = 0; i < elements.length; i++) {
    // Si l'élément est supérieur à 3, cachez-le
    if(i >= 15) {
        elements[i].style.display = "none";
    }
}

// Sélectionnez tous les éléments du tableau
var elements = document.querySelectorAll("table #trs");

// Parcourez tous les éléments
for(var i = 0; i < elements.length; i++) {
    // Si l'élément est supérieur à 3, cachez-le
    if(i >= 100) {
        elements[i].style.display = "none";
    }
}

function filterTable() {

    // Declare variables
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("myTable");
    tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[1];

        if (td) {
            txtValue = td.textContent || td.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }

}

</script>
<?php
require('foot.php');  // Chargement du pied de page.php
?>


    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</html>
