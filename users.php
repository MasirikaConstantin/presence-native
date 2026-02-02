<?php

require_once 'config.php';
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}
if (!isset($_GET['users']) || empty($_GET['users'])) {
    header('Location: index.php');
    exit;
}

$pdo = getDbConnection();   
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

    public function getFilteredPresences(int $userId, ?string $period = null, ?string $status = null): array {
        $query = "
            SELECT 
                p.id,
                p.date,
                p.heure,
                p.localisation,
                u.nom AS utilisateur_nom,
                l.nom AS lieu_nom,
                l.localisation AS lieu_localisation
            FROM presence p
            JOIN utilisateur u ON p.id_utilisateur = u.id
            JOIN lieu l ON u.lieu_id = l.id
            WHERE p.id_utilisateur = :userId
        ";

        $params = ['userId' => $userId];

        // Ajouter un filtre de période
        if ($period) {
            switch ($period) {
                case 'weekly':
                    $query .= " AND WEEK(p.date) = WEEK(NOW()) AND YEAR(p.date) = YEAR(NOW())";
                    break;
                case 'monthly':
                    $query .= " AND MONTH(p.date) = MONTH(NOW()) AND YEAR(p.date) = YEAR(NOW())";
                    break;
                case 'yearly':
                    $query .= " AND YEAR(p.date) = YEAR(NOW())";
                    break;
            }
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);

        $presences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Processus de calcul de la présence
        foreach ($presences as &$presence) {
            $workCoords = GeoCalculator::parseCoordinates($presence['lieu_localisation']);
            $userCoords = GeoCalculator::parseCoordinates($presence['localisation']);
            
            if (!$workCoords || !$userCoords) {
                $presence['status'] = 'Erreur';
                continue;
            }

            $distance = GeoCalculator::calculateDistance(
                $workCoords['latitude'], $workCoords['longitude'],
                $userCoords['latitude'], $userCoords['longitude']
            );

            $presence['status'] = ($distance <= GeoCalculator::DEFAULT_RADIUS) ? 'Présent' : 'Absent';
            $presence['distance'] = round($distance);
        }

        return $presences;
    }
}
?>

<?php
$userId = $_GET['users'] ?? null;

if ($userId) {
    $presenceManager = new PresenceManager($pdo);

    $period = $_GET['period'] ?? null;
    $presences = $presenceManager->getFilteredPresences($userId, $period);
} else {
    echo "Veuillez fournir un ID utilisateur dans l'URL.";
    exit;
}
?>
<?php require __DIR__ . '/header.php'; ?>
<body>
    <h1>Rapport de présences pour l'utilisateur ID <?= htmlspecialchars($userId) ?></h1>

    <form method="GET">
        <input type="hidden" name="id" value="<?= htmlspecialchars($userId) ?>">
        <label for="period">Filtrer par période :</label>
        <select name="period" id="period">
            <option value="">Tous</option>
            <option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Hebdomadaire</option>
            <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Mensuelle</option>
            <option value="yearly" <?= $period === 'yearly' ? 'selected' : '' ?>>Annuelle</option>
        </select>
        <button type="submit">Filtrer</button>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Lieu</th>
                <th>Status</th>
                <th>Distance (m)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($presences as $presence): ?>
                <tr>
                    <td><?= htmlspecialchars($presence['date']) ?></td>
                    <td><?= htmlspecialchars($presence['heure']) ?></td>
                    <td><?= htmlspecialchars($presence['lieu_nom']) ?></td>
                    <td><?= htmlspecialchars($presence['status']) ?></td>
                    <td><?= htmlspecialchars($presence['distance'] ?? 'N/A') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>






