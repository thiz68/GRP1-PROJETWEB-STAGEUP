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


    public function searchEntreprises($keywords = '', $note_min = 0.0) {
        try {
            $query = "SELECT * FROM enterprises WHERE average_rating_enterprise >= :note_min";

            if (!empty($keywords)) {
                $query .= " AND (name_enterprise LIKE :keywords OR description_enterprise LIKE :keywords)";
            }

            $stmt = $this->pdo->prepare($query);
            $params = [':note_min' => $note_min];

            if (!empty($keywords)) {
                $params[':keywords'] = '%' . $keywords . '%';
            }

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la recherche des entreprises : " . $message_erreur->getMessage());
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


    public function searchOffres($id_entreprise=0, $salaire_min=0, $keywords='') {
        try {
            $query = "SELECT * FROM offers WHERE remun_offer >= :salaire_min";

            if ($id_entreprise != 0) {
                $query .= " AND id_enterprise = :id_entreprise";
            }

            if (!empty($keywords)) {
                $query .= " AND (title_offer LIKE :keywords OR desc_offer LIKE :keywords)";
            }

            $stmt = $this->pdo->prepare($query);

            $params = [':salaire_min' => $salaire_min];

            if ($id_entreprise != 0) {
                $params[':id_entreprise'] = $id_entreprise;
            }

            if (!empty($keywords)) {
                $params[':keywords'] = '%' . $keywords . '%';
            }

            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la recherche des offres : " . $message_erreur->getMessage());
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
                    VALUES (:nom, :prenom, :email, :mdp, 2);";
    
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
                    VALUES (:nom, :prenom, :email, :mdp, 3);";
    
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
                    VALUES (:nom, :description, :email, :tel);";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':nom' => $nom,
                ':description' => $description,
                ':email' => $email,
                ':tel' => $tel,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees dde l'entreprise: " . $message_erreur->getMessage());
        }
    }

    public function post_form_creer_offre($id_entreprise, $titre, $description, $salaire, $date_debut, $date_fin) {
        try {
            $requete = "INSERT INTO offers (id_enterprise, title_offer, desc_offer, remun_offer, s_date_offer, e_date_offer)
                    VALUES (:id_entreprise, :titre, :description, :salaire, :date_debut, :date_fin);";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_entreprise' => $id_entreprise,
                ':titre' => $titre,
                ':description' => $description,
                ':salaire' => $salaire,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees de l'offre: " . $message_erreur->getMessage());
        }
    }



    public function get_liste_etudiants() {
        try {
            $requete = "SELECT * from _user inner join ranks 
            on _user.id_rank = ranks.id_rank 
            where ranks.name_rank = 'etudiant';";
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute();
            return $requete_prep->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des étudiants : " . $message_erreur->getMessage());
        }
    }

    public function get_liste_pilotes() {
        try {
            $requete = "SELECT * from _user inner join ranks 
            on _user.id_rank = ranks.id_rank 
            where ranks.name_rank = 'pilote';";
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute();
            return $requete_prep->fetchAll();
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des pilotes : " . $message_erreur->getMessage());
        }
    }



    public function post_form_modif_etudiant($id_user,$nom, $prenom, $email) {
        try {
            $requete = "UPDATE _user SET firstname_user = :prenom, lastname_user = :nom, email_user = :email
            WHERE id_user = :id_user;";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_user' => $id_user,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la modification des donnees du compte: " . $message_erreur->getMessage());
        }
    }

    public function post_form_modif_pilote($id_user,$nom, $prenom, $email) {
        try {
            $requete = "UPDATE _user SET firstname_user = :prenom, lastname_user = :nom, email_user = :email
            WHERE id_user = :id_user;";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_user' => $id_user,
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la modification des donnees du compte: " . $message_erreur->getMessage());
        }
    }

    public function post_form_modif_entreprise($id_entreprise, $nom, $description, $email, $tel) {
        try {
            $requete = "UPDATE enterprises SET name_enterprise = :nom, description_enterprise = :description, 
                        email_enterprise = :email, tel_enterprise = :tel WHERE id_enterprise = :id_entreprise;";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_entreprise' => $id_entreprise,
                ':nom' => $nom,
                ':description' => $description,
                ':email' => $email,
                ':tel' => $tel,
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees de l'entreprise: " . $message_erreur->getMessage());
        }
    }

    public function post_form_postuler($id_utilisateur, $id_offre, $motivation, $cv) {
        try {
            $requete = "SELECT COUNT(*) FROM application WHERE id_user = :id_utilisateur AND id_offers = :id_offre;";
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':id_offre' => $id_offre
            ]);
        
            if ($requete_prep->fetchColumn() > 0) {
                die("Vous avez déjà postulé à cette offre.");
            }

            $extensions_autorisees = ["pdf", "png", "jpg", "odt", "docx"];
            $mimes_autorises = [
                "pdf" => "application/pdf",
                "png" => "image/png",
                "jpg" => "image/jpeg",
                "odt" => "application/vnd.oasis.opendocument.text",
                "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
            ];
            
            
            if (!isset($cv) || empty($cv['name'])) {
                die("Aucun fichier sélectionné.");
            }
    
            
            $extension = strtolower(pathinfo($cv['name'], PATHINFO_EXTENSION));
            $type_mime = mime_content_type($cv['tmp_name']);
    
            
            if (!in_array($extension, $extensions_autorisees) || $type_mime !== $mimes_autorises[$extension]) {
                die("Format de fichier non autorisé.");
            }
    
            
            if ($cv['size'] > 8 * 1024 * 1024) {
                die("Le fichier dépasse la taille maximale autorisée de 8 Mo.");
            }
    
            
            $nom_fichier = "etudiant_" . $id_utilisateur . "_offre_" . $id_offre . "." . $extension;
            $chemin_destination = __DIR__ . "/../../uploads/cv/" . $nom_fichier;

                        
            if (!move_uploaded_file($cv['tmp_name'], $chemin_destination)) {
                die("Erreur lors du téléchargement du fichier.");
            }
    
            
            $requete = "INSERT INTO application (id_user, id_offers, date_application, motiv_application, cv_application)
                        VALUES (:id_utilisateur, :id_offre, NOW(), :motivation, :nom_fichier);";
    
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_utilisateur' => $id_utilisateur,
                ':id_offre' => $id_offre,
                ':motivation' => $motivation,
                ':nom_fichier' => $nom_fichier,
            ]);

        
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la candidature : " . $message_erreur->getMessage());
        }
    }


    public function getUserWishlist($userId) {
        try {
            $stmt = $this->pdo->prepare("
        SELECT o.* 
        FROM offers o
        JOIN wishlist w ON o.id_offers = w.id_offers
        WHERE w.id_user = :userId
        ");
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération de la wishlist : " . $message_erreur->getMessage());
        }
    }

    public function addToWishlist($userId, $offerId) {
        try {
            $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO wishlist (id_user, id_offers)
            VALUES (:userId, :offerId)
        ");
            return $stmt->execute([
                ':userId' => $userId,
                ':offerId' => $offerId
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'ajout à la wishlist : " . $message_erreur->getMessage());
        }
    }

    public function removeFromWishlist($userId, $offerId) {
        try {
            $stmt = $this->pdo->prepare("
            DELETE FROM wishlist 
            WHERE id_user = :userId AND id_offers = :offerId
        ");
            return $stmt->execute([
                ':userId' => $userId,
                ':offerId' => $offerId
            ]);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la suppression de la wishlist : " . $message_erreur->getMessage());
        }
    }

    public function isInWishlist($userId, $offerId) {
        try {
            $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as count
            FROM wishlist 
            WHERE id_user = :userId AND id_offers = :offerId
        ");
            $stmt->execute([
                ':userId' => $userId,
                ':offerId' => $offerId
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la vérification de la wishlist : " . $message_erreur->getMessage());
        }
    }

    public function searchWishlist($userId, $keywords = '', $location = '') {
        try {
            $query = "
            SELECT o.* 
            FROM offers o
            JOIN wishlist w ON o.id_offers = w.id_offers
            WHERE w.id_user = :userId
        ";

            $params = [':userId' => $userId];

            if (!empty($keywords)) {
                $query .= " AND (o.title_offer LIKE :keywords OR o.desc_offer LIKE :keywords)";
                $params[':keywords'] = '%' . $keywords . '%';
            }

            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la recherche dans la wishlist : " . $message_erreur->getMessage());
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
