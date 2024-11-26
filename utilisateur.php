<?php

// creer_utilisateur.php
require_once 'config.php';
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getDbConnection();

$message = '';

// Récupérer la liste des lieux
$stmt = $pdo->query("SELECT id, nom FROM lieu ORDER BY nom");
$lieux = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    $lieu_id = $_POST['lieu_id'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nom) || empty($adresse) || empty($lieu_id) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        try {
            // Hash du mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insérer le nouvel utilisateur
            $stmt = $pdo->prepare(
                "INSERT INTO utilisateur (nom, adresse, lieu_id, password) 
                 VALUES (:nom, :adresse, :lieu_id, :password)"
            );
            $stmt->execute([
                'nom' => $nom,
                'adresse' => $adresse,
                'lieu_id' => $lieu_id,
                'password' => $hashedPassword,
            ]);

            $message = 'Utilisateur créé avec succès.';
        } catch (PDOException $e) {
            $message = 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Créer un Utilisateur</h1>
        
        <?php if ($message): ?>
            <div class="<?php echo strpos($message, 'succès') !== false ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nom">
                    Nom :
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" id="nom" name="nom" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="adresse">
                    Adresse :
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" id="adresse" name="adresse" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="lieu_id">
                    Lieu :
                </label>
                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="lieu_id" name="lieu_id" required>
                    <option value="">Sélectionnez un lieu</option>
                    <?php foreach ($lieux as $lieu): ?>
                        <option value="<?php echo $lieu['id']; ?>"><?php echo htmlspecialchars($lieu['nom']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Mot de passe :
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="password" id="password" name="password" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    Créer l'utilisateur
                </button>
            </div>
        </form>
        
        <a href="dashboard.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
            Retour au tableau de bord
        </a>
    </div>

    <?php
    require('foot.php');  // Chargement du pied de page.php
    ?>
</body>
</html>
