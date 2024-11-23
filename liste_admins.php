<?php
// liste_admins.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'config.php';
require_once 'db.php';

$pdo = getDbConnection();

// Traitement de la mise à jour du statut
if (isset($_GET['toggle_id'])) {
    $admin_id = $_GET['toggle_id'];
    $stmt = $pdo->prepare("UPDATE administrateurs SET etat = NOT etat WHERE id = :id");
    $stmt->execute(['id' => $admin_id]);
}

// Récupération de tous les administrateurs
$stmt = $pdo->query("SELECT * FROM administrateurs ORDER BY email");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Administrateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />

    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .active { color: green; }
        .inactive { color: red; }
        .toggle-link { text-decoration: none; color: #007bff; }
        .back-link { display: block; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Liste des Administrateurs</h1>
    <table>
        <thead>
            <tr>
                <th>Email</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($admins as $admin): ?>
            <tr>
                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                <td class="<?php echo $admin['etat'] ? 'active' : 'inactive'; ?>">
                    <?php echo $admin['etat'] ? 'Actif' : 'Inactif'; ?>
                </td>
                <td>
                    <a href="?toggle_id=<?php echo $admin['id']; ?>" class="toggle-link">
                        <?php echo $admin['etat'] ? 'Désactiver' : 'Activer'; ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php" class="back-link">Retour au tableau de bord</a>

    <?php
require('foot.php');  // Chargement du pied de page.php
?>
</body>
</html>