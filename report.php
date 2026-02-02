<?php

require_once 'config.php';
require_once 'db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$pdo = getDbConnection();   



class PresenceManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getPresenceReport(int $userId, string $period): array {
        $query = "
            SELECT 
                COUNT(*) AS total_presences,
                DATE_FORMAT(p.date, '%Y-%m-%d') AS period_date
            FROM presence p
            WHERE p.id_utilisateur = :userId
        ";

        // Ajouter le filtre temporel
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

        $query .= " GROUP BY period_date";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}




$presenceManager = new PresenceManager($pdo);

$userId = $_GET['user_id'] ?? null;
$period = $_GET['period'] ?? 'weekly';

if ($userId) {
    $report = $presenceManager->getPresenceReport($userId, $period);
    echo "<table>";
    echo "<tr><th>Date</th><th>Total Présences</th></tr>";
    foreach ($report as $row) {
        echo "<tr><td>{$row['period_date']}</td><td>{$row['total_presences']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "Veuillez sélectionner un utilisateur.";
}

?>