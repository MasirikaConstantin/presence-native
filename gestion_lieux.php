<?php
// gestion_lieux.php

require_once 'config.php';
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}


$pdo = getDbConnection();

$message = '';

// Traitement de l'ajout d'un lieu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $nom = $_POST['nom'];
        $localisation = $_POST['localisation'];
        $stmt = $pdo->prepare("INSERT INTO lieu (nom, localisation) VALUES (:nom, :localisation)");
        if ($stmt->execute(['nom' => $nom, 'localisation' => $localisation])) {
            $message = "Lieu ajouté avec succès.";
        } else {
            $message = "Erreur lors de l'ajout du lieu.";
        }
    } elseif ($_POST['action'] == 'edit') {
        $id = $_POST['id'];
        $nom = $_POST['nom'];
        $localisation = $_POST['localisation'];
        $stmt = $pdo->prepare("UPDATE lieu SET nom = :nom, localisation = :localisation WHERE id = :id");
        if ($stmt->execute(['id' => $id, 'nom' => $nom, 'localisation' => $localisation])) {
            $message = "Lieu modifié avec succès.";
        } else {
            $message = "Erreur lors de la modification du lieu.";
        }
    }
}

// Traitement de la suppression d'un lieu
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM lieu WHERE id = :id");
    if ($stmt->execute(['id' => $_GET['delete_id']])) {
        $message = "Lieu supprimé avec succès.";
    } else {
        $message = "Erreur lors de la suppression du lieu.";
    }
}

// Récupération de tous les lieux
$stmt = $pdo->query("SELECT * FROM lieu ORDER BY nom");
$lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Lieux</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Gestion des Lieux</h1>
        
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'ajout de lieu -->
        <form action="" method="POST" class="mb-8 bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <input type="hidden" name="action" value="add">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nom">
                    Nom du lieu
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nom" type="text" name="nom" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="localisation">
                    Localisation
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="localisation" type="text" name="localisation" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Ajouter un lieu
                </button>
            </div>
        </form>

        <!-- Liste des lieux -->
        <h2 class="text-xl font-bold mb-4">Liste des lieux</h2>
        <table class="w-full bg-white shadow-md rounded mb-4">
            <thead>
                <tr>
                    <th class="text-left p-3 px-5">Nom</th>
                    <th class="text-left p-3 px-5">Localisation</th>
                    <th class="text-left p-3 px-5">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lieux as $lieu): ?>
                <tr>
                    <td class="p-3 px-5"><?php echo htmlspecialchars($lieu['nom']); ?></td>
                    <td class="p-3 px-5"><?php echo htmlspecialchars($lieu['localisation']); ?></td>
                    <td class="p-3 px-5">
                        <button onclick="editLieu(<?php echo $lieu['id']; ?>, '<?php echo addslashes($lieu['nom']); ?>', '<?php echo addslashes($lieu['localisation']); ?>')" class="text-blue-500 hover:text-blue-700 mr-2">Modifier</button>
                        <a href="?delete_id=<?php echo $lieu['id']; ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu ?');" class="text-red-500 hover:text-red-700">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="inline-block bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Retour au tableau de bord</a>
    </div>

    <script>
    function editLieu(id, nom, localisation) {
        document.querySelector('input[name="action"]').value = 'edit';
        document.querySelector('input[name="nom"]').value = nom;
        document.querySelector('input[name="localisation"]').value = localisation;
        
        // Ajouter un champ caché pour l'ID
        let hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'id';
        hiddenInput.value = id;
        document.querySelector('form').appendChild(hiddenInput);

        // Changer le texte du bouton
        document.querySelector('button[type="submit"]').textContent = 'Modifier le lieu';
    }
    </script>

<?php
require('foot.php');  // Chargement du pied de page.php
?>
</body>
</html>