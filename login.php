<?php
// Paramètres de connexion à la base de données MySQL
$host = '144.76.112.4';
$dbname = 'mascodep_api';
$username = 'mascodep_coco';
$password = 'masirika360';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données JSON envoyées
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // Vérifier si l'ID et le mot de passe sont fournis
    if (isset($data['id']) && isset($data['password'])) {
        $id = $data['id'];
        $password = $data['password'];

        // Rechercher l'utilisateur dans la base de données
        $stmt = $db->prepare('SELECT id, nom, adresse, password FROM utilisateur WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Vérifier si le mot de passe correspond
            if (password_verify($password, $user['password'])) {
                // Supprimer le mot de passe avant de renvoyer les données
                unset($user['password']);
                sendJsonResponse(['message' => 'Connexion réussie', 'user' => $user]);
            } else {
                sendJsonResponse(['error' => 'Mot de passe incorrect'], 401);
            }
        } else {
            sendJsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }
    } else {
        sendJsonResponse(['error' => 'ID ou mot de passe manquant dans les données JSON'], 400);
    }
} else {
    sendJsonResponse(['error' => 'Méthode non autorisée. Utilisez POST.'], 405);
}
