<?php
// db.php
require_once 'config.php';
ini_set('session.save_path', '/home/mascodep/sessions');

function getDbConnection() {
    static $pdo;

    if (!$pdo) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    return $pdo;
}