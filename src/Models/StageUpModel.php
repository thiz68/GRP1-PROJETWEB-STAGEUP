<?php
namespace grp1\STAGEUP\Models;

use PDO;
use PDOException;

class Database {
    private $pdo;

    public function __construct($host, $dbname, $username, $password) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        } catch (PDOException $message_erreur) {
            die("Erreur de connexion à la BDD : " . $message_erreur->getMessage());
        }
    }

    public function get_pdo() { return $this->pdo;}
}




class StageUpModel extends Model {

    public function __construct($connection = null) {

        if (is_null($connection)) {
            require_once __DIR__ . '/bdd_info.php';
            $this->connection = new Database($host, $dbname, $username, $password);

        } else {
            $this->connection = $connection;

        }

        $this->pdo = $this->connection->get_pdo();
    }




    public function getEntreprises($note_min=0.0) {
        try {
            $donnees = $this->pdo->query("
                SELECT * from enterprises where enterprises.average_rating_enterprise >= ".$note_min.";");
            return $donnees->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des entreprises : " . $message_erreur->getMessage());
        }
    }

    public function getOffres($id_entreprise, $salaire_min=0) {
        try {
            $donnees = $this->pdo->query("
                SELECT * from offers where offers.id_enterprise = ".$id_entreprise." 
                and offers.remun_offer >= ".$salaire_min.";");
            return $donnees->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des offres : " . $message_erreur->getMessage());
        }
    }
}

?>
