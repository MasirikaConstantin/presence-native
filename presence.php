<?php
// Paramètres de connexion à la base de données MySQL
$host = '144.76.112.4';
$nomdb = 'mascodep_api';
$nom_d_utilisateur = 'mascodep_coco';
$mot_de_passe = 'masirika360';

try {
    $db = new PDO("mysql:host=$host;dbname=$nomdb;charset=utf8", $nom_d_utilisateur, $mot_de_passe);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour envoyer une réponse JSON
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['id_utilisateur'], $data['localisation'], $data['date'], $data['heure'])) {

        // Vérifier d'abord si l'utilisateur existe
        $checkUser = $db->prepare('SELECT id FROM utilisateur WHERE id = :id');
        $checkUser->execute([':id' => $data['id_utilisateur']]);

        if ($checkUser->fetch()) {
            // Vérifier si l'utilisateur a déjà signé pour la date donnée
            $checkPresence = $db->prepare('SELECT id FROM presence WHERE id_utilisateur = :id_utilisateur AND date = :date');
            $checkPresence->execute([':id_utilisateur' => $data['id_utilisateur'], ':date' => $data['date']]);

            if ($checkPresence->fetch()) {
                // L'utilisateur a déjà signé sa présence pour la date donnée
                sendJsonResponse(['error' => 'Vous avez déjà signé votre présence aujourd\'hui.'], 400);
            } else {
                // Insérer la présence si elle n'existe pas déjà pour la date donnée
                $stmt = $db->prepare('INSERT INTO presence (id_utilisateur, localisation, date, heure) VALUES (:id_utilisateur, :localisation, :date, :heure)');
                try {
                    $stmt->execute([
                        ':id_utilisateur' => $data['id_utilisateur'],
                        ':localisation' => $data['localisation'],
                        ':date' => $data['date'],
                        ':heure' => $data['heure']
                    ]);
                    sendJsonResponse(['success' => true, 'id' => $db->lastInsertId()]);
                } catch (PDOException $e) {
                    sendJsonResponse(['error' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()], 500);
                }
            }
        } else {
            sendJsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }
    } else {
        sendJsonResponse(['error' => 'Données manquantes'], 400);
    }
} else {
    sendJsonResponse(['error' => 'Méthode non autorisée'], 405);
}
