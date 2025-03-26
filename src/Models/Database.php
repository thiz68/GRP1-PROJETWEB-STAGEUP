<?php
namespace grp1\STAGEUP\Models;

use PDO;
use PDOException;

class Database {
    private $pdo;

    public function __construct($host, $dbname, $username, $password) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur de connexion à la BDD : " . $message_erreur->getMessage());
        }
    }

    public function getAllRecords($table) {
        try {
            $donnees_brutes = $this->pdo->query("SELECT * FROM " . $table);
            return $donnees_brutes->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des données : " . $message_erreur->getMessage());
        }
    }
}
?>
