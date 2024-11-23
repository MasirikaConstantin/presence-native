<?php
require_once 'db.php';
$pdo = getDbConnection();

// Vérifier si l'ID de l'administrateur à modifier est passé en paramètre
if (isset($_GET['id'])) {
    $admin_id = $_GET['id'];

    // Récupérer les informations de l'administrateur actuel
    $sql = "SELECT nom, email FROM administrateurs WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        $nom = $admin['nom'];
        $email = $admin['email'];
    } else {
        die("Administrateur non trouvé.");
    }

    // Vérifier si le formulaire de mise à jour est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_nom = $_POST['nom'];
        $new_email = $_POST['email'];
        $new_password = $_POST['password'];

        // Vérifier si l'email existe déjà pour un autre administrateur
        $sql = "SELECT COUNT(*) FROM administrateurs WHERE email = :email AND id != :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
        $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);
        $stmt->execute();
        $emailExists = $stmt->fetchColumn();

        if ($emailExists) {
            $message = "Erreur : cet email est déjà utilisé par un autre administrateur.";
        } else {
            // Hachage du mot de passe seulement si un nouveau mot de passe est fourni
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            } else {
                // Garder l'ancien mot de passe
                $sql = "SELECT password FROM administrateurs WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);
                $stmt->execute();
                $hashed_password = $stmt->fetchColumn();
            }

            // Mise à jour de l'administrateur
            $sql = "UPDATE administrateurs SET nom = :nom, email = :email, password = :password WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nom', $new_nom, PDO::PARAM_STR);
            $stmt->bindParam(':email', $new_email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':id', $admin_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $message = "L'administrateur a été mis à jour avec succès.";
            } else {
                $message = "Erreur lors de la mise à jour.";
            }
        }
    }
} else {
    die("Aucun ID d'administrateur spécifié.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Modifier l'Administrateur</h1>

        <?php if (isset($message)): ?>
            <p class="<?php echo strpos($message, 'succès') !== false ? 'text-green-600' : 'text-red-600'; ?> mb-4">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label for="nom" class="block text-gray-700 font-semibold">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required class="mt-2 p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-semibold">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="mt-2 p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-semibold">Mot de passe (laisser vide pour garder l'actuel) :</label>
                <input type="password" id="password" name="password" class="mt-2 p-2 w-full border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-500 text-white font-semibold p-2 rounded-lg hover:bg-blue-600 transition-colors">Mettre à jour</button>
        </form>

        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-blue-500 hover:underline">Retour</a>
        </div>
        <?php
require('foot.php');  // Chargement du pied de page.php
?>
    </div>
    
</body>
</html>
