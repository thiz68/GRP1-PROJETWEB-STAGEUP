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

    public function getOffres($id_entreprise=0, $salaire_min=0) {
        try {
            if ($id_entreprise == 0) { $donnees = $this->pdo->query("SELECT * from offers where offers.remun_offer >= ".$salaire_min.";"); }
            else { $donnees = $this->pdo->query("
                SELECT * from offers where offers.id_enterprise = ".$id_entreprise." 
                and offers.remun_offer >= ".$salaire_min.";"); }
            return $donnees->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des offres : " . $message_erreur->getMessage());
        }
    }

    public function getEntreprise($id_entreprise=1) {
        try {
            $donnees = $this->pdo->query("
                SELECT * from enterprises where enterprises.id_enterprise = ".$id_entreprise.";");
            return $donnees->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des entreprises : " . $message_erreur->getMessage());
        }
    }

    public function get_note_attribuee($id_utilisateur,$id_entreprise) {
        try {
            $donnees = $this->pdo->query("
                SELECT rating_evaluate from evaluate where evaluate.id_enterprise = ".$id_entreprise."
                AND evaluate.id_user = ".$id_utilisateur.";");
            return $donnees->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération de la note : " . $message_erreur->getMessage());
        }
    }

    public function post_note_attribuee($id_utilisateur, $id_entreprise, $note_attribuee) {
        try {
            $requete = "INSERT INTO evaluate (id_user, id_enterprise, rating_evaluate)
                    VALUES (:id_utilisateur, :id_entreprise, :note_attribuee)
                    ON DUPLICATE KEY UPDATE rating_evaluate = :note_attribuee";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':id_entreprise' => $id_entreprise,
                ':note_attribuee' => $note_attribuee
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion de la note : " . $message_erreur->getMessage());
        }
    }


    public function post_form_creer_pilote($nom, $prenom, $email, $mdp) {
        try {
            $requete = "INSERT INTO _user (firstname_user, lastname_user, email_user, password_user, id_rank)
                    VALUES (:nom, :prenom, :email, :mdp, 2)";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mdp' => $mdp,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees du compte: " . $message_erreur->getMessage());
        }
    }

    public function post_form_creer_etudiant($nom, $prenom, $email, $mdp) {
        try {
            $requete = "INSERT INTO _user (firstname_user, lastname_user, email_user, password_user, id_rank)
                    VALUES (:nom, :prenom, :email, :mdp, 3)";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':mdp' => $mdp,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees du compte: " . $message_erreur->getMessage());
        }
    }

    public function post_form_creer_entreprise($nom, $description, $email, $tel) {
        try {
            $requete = "INSERT INTO enterprises (name_enterprise, description_enterprise, email_enterprise, tel_enterprise)
                    VALUES (:nom, :description, :email, :tel)";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':nom' => $nom,
                ':description' => $description,
                ':email' => $email,
                ':tel' => $tel,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees du compte: " . $message_erreur->getMessage());
        }
    }




    public function getUserByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM _user WHERE email_user = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération de l'utilisateur : " . $message_erreur->getMessage());
        }
    }

    public function getUserById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM _user WHERE id_user = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die ("Erreur lors de la récupération de l'utilisateur : " . $message_erreur->getMessage());
        }
    }

    public function getUserWithRole($id) {
        try {
            $stmt = $this->pdo->prepare("
            SELECT u.*, r.name_rank 
            FROM _user u
            JOIN ranks r ON u.id_rank = r.id_rank
            WHERE u.id_user = :id 
            LIMIT 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération de l'utilisateur et son rôle : " . $message_erreur->getMessage());
        }
    }

}

?>
