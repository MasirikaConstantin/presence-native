<?php
// liste_utilisateurs.php

require_once 'config.php';
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}


$pdo = getDbConnection();

// Traitement de la suppression de l'utilisateur
if (isset($_GET['delete_id'])) {
    $user_id = $_GET['delete_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE id = :id");
        $result = $stmt->execute(['id' => $user_id]);
        if ($result) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?delete_success=1");
            exit;
        } else {
            $error = "La suppression a échoué.";
        }
    } catch (PDOException $e) {
        //$error = "Erreur de base de données : " . $e->getMessage();
        $error = "Cet Utilisateur contient plusieurs données de présence.";
    }
}

// Récupération de tous les utilisateurs
$stmt = $pdo->query("SELECT * FROM utilisateur ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Utilisateurs</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="icon" type="image/png" sizes="32x32" href="mas-product.ico"/>
    <link rel="icon" type="image/png" sizes="16x16" href="mas-product.ico"/>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .delete-link { color: red; text-decoration: none; }
        .back-link { display: block; margin-top: 20px; }
    </style>
</head>
<body>
    <?php
    if (isset($_GET['delete_success'])) {
        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                L\'utilisateur a été supprimé avec succès.
              </div>';
    }
    if (isset($error)) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                ' . htmlspecialchars($error) . '
              </div>';
    }
    ?>

    <h1 class="text-2xl font-bold mb-4">Liste des Utilisateurs</h1>
    <a href="dashboard.php" class="back-link mt-4 inline-block text-blue-600 hover:text-blue-800">Retour au tableau de bord</a>

    <div class="w-full md:w-2/5 lg:w-7/15 mb-6">
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
    <table class="w-full" id="myTable">
        <thead>
            
            <th>id</th>
            
                            <th>Nom</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr id="trs" >
            <td><?= $user["id"]?></td>
                <td><?php echo htmlspecialchars($user['nom']); ?></td>
                <td>
                    <button data-modal-target="deleteModal<?php echo $user['id']; ?>" data-modal-toggle="deleteModal<?php echo $user['id']; ?>" class="text-red-600 hover:text-red-800" type="button">
                        Supprimer
                    </button>
                </td>
            </tr>
            <!-- Modal de confirmation -->
            <div id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-md max-h-full">
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="deleteModal<?php echo $user['id']; ?>">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                            </svg>
                            <span class="sr-only">Fermer</span>
                        </button>
                        <div class="p-6 text-center">
                            <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                            <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Êtes-vous sûr de vouloir supprimer cet utilisateur ?</h3>
                            <a href="?delete_id=<?php echo $user['id']; ?>" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                                Oui, supprimer
                            </a>
                            <button data-modal-hide="deleteModal<?php echo $user['id']; ?>" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">Non, annuler</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="back-link mt-4 inline-block text-blue-600 hover:text-blue-800">Retour au tableau de bord</a>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.5.2/flowbite.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', (event) => {
        const deleteButtons = document.querySelectorAll('[data-modal-toggle]');
        deleteButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-target');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                }
            });
        });

        const cancelButtons = document.querySelectorAll('[data-modal-hide]');
        cancelButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal-hide');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                }
            });
        });
    });
    </script>

<?php
require('foot.php');  // Chargement du pied de page.php
?>
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
            if(i >= 50) {
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
</body>
</html>