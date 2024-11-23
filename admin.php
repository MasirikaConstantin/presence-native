<?php
// creer_admin.php

session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config.php';
require_once 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $etat = isset($_POST['etat']) ? 1 : 0;

    if (empty($email) || empty($password)) {
        $message = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $pdo = getDbConnection();

            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM administrateurs WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $message = 'Cet email est déjà utilisé.';
            } else {
                // Hacher le mot de passe
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insérer le nouvel administrateur
                $stmt = $pdo->prepare("INSERT INTO administrateurs (email, password, etat) VALUES (:email, :password, :etat)");
                $stmt->execute([
                    'email' => $email,
                    'password' => $hashedPassword,
                    'etat' => $etat
                ]);

                $message = 'Administrateur créé avec succès.';
            }
        } catch (PDOException $e) {
            $message = 'Erreur lors de la création de l\'administrateur : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Créer un Administrateur</h1>
        
        <?php if ($message): ?>
            <p class="<?php echo strpos($message, 'succès') !== false ? 'text-green-600' : 'text-red-600'; ?> mb-4">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold">Email :</label>
                <input type="email" id="email" name="email" required class="mt-2 p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold">Mot de passe :</label>
                <input type="password" id="password" name="password" required class="mt-2 p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="mb-6">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="etat" checked class="form-checkbox text-blue-600">
                    <span class="ml-2 text-gray-700">Compte actif</span>
                </label>
            </div>
            
            <button type="submit" class="w-full bg-blue-500 text-white font-semibold p-2 rounded-lg hover:bg-blue-600 transition-colors">Créer l'administrateur</button>
        </form>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Retour</a>
        </div>

        <?php
require('foot.php');  // Chargement du pied de page.php
?>
    </div>
    
   
</body>

    
</body>
</html>