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

        global $host, $dbname, $username, $password;
        if (is_null($connection)) {
            require_once __DIR__ . '/bdd_info.php';
            $this->connection = new Database($host, $dbname, $username, $password);

        } else {
            $this->connection = $connection;

        }

        $this->pdo = $this->connection->get_pdo();
    }
    
    public function getEntrepriseById($id_entreprise = 1) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM enterprises WHERE enterprises.id_enterprise = :id_entreprise");
            $stmt->execute([':id_entreprise' => $id_entreprise]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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


    public function getOffres($id_entreprise = 0, $salaire_min = 0) {
        try {
            if ($id_entreprise == 0) {
                $stmt = $this->pdo->prepare("SELECT * FROM offers WHERE offers.remun_offer >= :salaire_min");
                $stmt->execute([':salaire_min' => $salaire_min]);
            } else {
                $stmt = $this->pdo->prepare("
                SELECT * FROM offers 
                WHERE offers.id_enterprise = :id_entreprise 
                AND offers.remun_offer >= :salaire_min");
                $stmt->execute([
                    ':id_entreprise' => $id_entreprise,
                    ':salaire_min' => $salaire_min
                ]);
            }

            $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($offres as $key => $offre) {
                $offres[$key]['skills'] = $this->getOfferSkills($offre['id_offers']);
            }

            return $offres;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des offres : " . $message_erreur->getMessage());
        }
    }

    public function getOffreById($id_offre) {
        try {
            $requete = "SELECT * FROM offers WHERE id_offers = :id_offre";
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([':id_offre' => $id_offre]);

            $offre = $requete_prep->fetch(PDO::FETCH_ASSOC);

            if (!$offre) {
                return []; //Tableau vide si pas trouvé
            }

            //Ajout Compétences offres
            $offre['skills'] = $this->getOfferSkills($id_offre);

            return $offre;
        } catch (PDOException $message_erreur) {
            error_log("Erreur getOffreById: " . $message_erreur->getMessage());
            return [];
        }
    }


    public function searchOffres($id_entreprise=0, $salaire_min=0, $keywords='') {
        try {
            $query = "SELECT o.* 
                FROM offers o
                LEFT JOIN requerir r ON o.id_offers = r.id_offers
                LEFT JOIN skills s ON r.id_skill = s.id_skill
                WHERE o.remun_offer >= :salaire_min";

            if ($id_entreprise != 0) {
                $query .= " AND o.id_enterprise = :id_entreprise";
            }

            if (!empty($keywords)) {
                $query .= " AND (o.title_offer LIKE :keywords 
                      OR o.desc_offer LIKE :keywords
                      OR s.label_skill LIKE :keywords)";
            }

            // Ajout du GROUP BY pour éviter les doublons
            $query .= " GROUP BY o.id_offers";

            $stmt = $this->pdo->prepare($query);

            $params = [':salaire_min' => $salaire_min];

            if ($id_entreprise != 0) {
                $params[':id_entreprise'] = $id_entreprise;
            }

            if (!empty($keywords)) {
                $params[':keywords'] = '%' . $keywords . '%';
            }

            $stmt->execute($params);
            $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Ajouter les compétences à chaque offre
            foreach ($offres as $key => $offre) {
                $offres[$key]['skills'] = $this->getOfferSkills($offre['id_offers']);
            }

            return $offres;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la recherche des offres : " . $message_erreur->getMessage());
        }
    }

    public function getSkills() {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM skills ORDER BY label_skill");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des compétences : " . $message_erreur->getMessage());
        }
    }

    public function getOfferSkills($id_offre) {
        try {
            $requete = "SELECT s.id_skill, s.label_skill 
                    FROM skills s 
                    JOIN requerir r ON s.id_skill = r.id_skill 
                    WHERE r.id_offers = :id_offre
                    ORDER BY s.label_skill";
            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([':id_offre' => $id_offre]);
            return $requete_prep->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des compétences de l'offre : " . $message_erreur->getMessage());
        }
    }

    public function addOfferSkills($id_offre, $skills = []) {
        try {
            // Supprimer d'abord toutes les compétences associées à cette offre
            $delete = $this->pdo->prepare("DELETE FROM requerir WHERE id_offers = :id_offre");
            $delete->execute([':id_offre' => $id_offre]);

            // Si aucune compétence n'est fournie, on s'arrête là
            if (empty($skills)) {
                return true;
            }

            // Préparer la requête d'insertion
            $requete = "INSERT INTO requerir (id_offers, id_skill) VALUES (:id_offre, :id_skill)";
            $stmt = $this->pdo->prepare($requete);

            // Insérer chaque compétence
            foreach ($skills as $skill_id) {
                $stmt->execute([
                    ':id_offre' => $id_offre,
                    ':id_skill' => $skill_id
                ]);
            }

            return true;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'ajout des compétences à l'offre : " . $message_erreur->getMessage());
        }
    }

    public function get_note_attribuee($id_utilisateur, $id_entreprise) {
        try {
            $stmt = $this->pdo->prepare("
            SELECT rating_evaluate FROM evaluate 
            WHERE evaluate.id_enterprise = :id_entreprise
            AND evaluate.id_user = :id_utilisateur");
            $stmt->execute([
                ':id_entreprise' => $id_entreprise,
                ':id_utilisateur' => $id_utilisateur
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    VALUES (:prenom, :nom, :email, :mdp, 2);";
    
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
                    VALUES (:prenom, :nom, :email, :mdp, 3);";
    
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

        public function post_form_creer_offre($id_entreprise, $titre, $description, $salaire, $date_debut, $date_fin, $skills = []) {
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

            // Récupérer l'ID de l'offre nouvellement créée
            $id_offre = $this->pdo->lastInsertId();

            // Ajouter les compétences requises
            if (!empty($skills)) {
                $this->addOfferSkills($id_offre, $skills);
            }

            return $id_offre;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de l'insertion des donnees de l'offre: " . $message_erreur->getMessage());
        }
    }

    public function get_liste_etudiants() {
        try {
            $stmt = $this->pdo->query("
            SELECT * FROM _user 
            INNER JOIN ranks ON _user.id_rank = ranks.id_rank 
            WHERE ranks.name_rank = 'etudiant'");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération des étudiants : " . $message_erreur->getMessage());
        }
    }

    public function get_liste_pilotes() {
        try {
            $stmt = $this->pdo->query("
            SELECT * FROM _user 
            INNER JOIN ranks ON _user.id_rank = ranks.id_rank 
            WHERE ranks.name_rank = 'pilote'");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function post_form_modif_offre($id_offre, $titre, $description, $salaire, $date_debut, $date_fin, $skills = []) {
        try {
            $requete = "UPDATE offers SET 
                    title_offer = :titre, 
                    desc_offer = :description, 
                    remun_offer = :salaire, 
                    s_date_offer = :date_debut, 
                    e_date_offer = :date_fin
                    WHERE id_offers = :id_offre";

            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_offre' => $id_offre,
                ':titre' => $titre,
                ':description' => $description,
                ':salaire' => $salaire,
                ':date_debut' => $date_debut,
                ':date_fin' => $date_fin
            ]);

            // Mettre à jour les compétences requises
            $this->addOfferSkills($id_offre, $skills);

            return true;
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la modification des donnees de l'offre: " . $message_erreur->getMessage());
        }
    }


    public function supp_entreprise($id_entreprise) {
        try {
            $requete = "DELETE FROM enterprises WHERE id_enterprise = :id_entreprise;";

            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_entreprise' => $id_entreprise
            ]);

        } catch (PDOException $message_erreur) {
            die("Erreur lors de la suppression de l'entreprise : " . $message_erreur->getMessage());
        }
    }

    public function supp_offre($id_offre) {
        try {
            $requete = "DELETE FROM offers WHERE id_offers = :id_offre;";

            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_offre' => $id_offre
            ]);

        } catch (PDOException $message_erreur) {
            die("Erreur lors de la suppression de l'offre : " . $message_erreur->getMessage());
        }
    }

    public function supp_pilote($id_pilote) {
        try {
            $requete = "DELETE FROM _user WHERE id_user = :id_pilote;";

            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_pilote' => $id_pilote
            ]);

        } catch (PDOException $message_erreur) {
            die("Erreur lors de Erreur lors de la suppression du pilote: " . $message_erreur->getMessage());
        }
    }

    public function supp_etudiant($id_etudiant) {
        try {
            $requete = "DELETE FROM _user WHERE id_user = :id_etudiant;";

            $requete_prep = $this->pdo->prepare($requete);
            $requete_prep->execute([
                ':id_etudiant' => $id_etudiant
            ]);

        } catch (PDOException $message_erreur) {
            die("Erreur lors de la suppression de l'etudiant : " . $message_erreur->getMessage());
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
            die("Erreur lors de la recherche dans la wishlist: " . $message_erreur->getMessage());
        }
    }

    public function getUser_candidatures($userId) {
        try {
            $stmt = $this->pdo->prepare("
            SELECT offers.* , application.*
            FROM offers
            JOIN application ON offers.id_offers = application.id_offers
            WHERE application.id_user = :userId
            ");
            $stmt->execute([':userId' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $message_erreur) {
            die("Erreur lors de la récupération de la wishlist : " . $message_erreur->getMessage());
        }
    }

    public function search_candidatures($userId, $keywords = '', $location = '') {
        try {
            $query = "
            SELECT offers.*, application.*
            FROM offers
            JOIN application ON offers.id_offers = application.id_offers
            WHERE application.id_user = :userId
            ";

            $params = [':userId' => $userId];

            if (!empty($keywords)) {
                $query .= " AND (offers.title_offer LIKE :keywords OR offers.desc_offer LIKE :keywords)";
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

    public function getTotalOffres() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM offers");
        return $stmt->fetchColumn();
    }

    public function getTotalEntreprises() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM enterprises");
        return $stmt->fetchColumn();
    }

    public function getTotalEtudiants() {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM _user WHERE id_rank = 3");
        return $stmt->fetchColumn();
    }

    public function getAvgCandidatures() {
        $stmt = $this->pdo->query("SELECT AVG(nb_candidatures) FROM _user WHERE id_rank = 3");
        return $stmt->fetchColumn();
    }

    public function getSkillsStats() {
        $query = "
        SELECT s.label_skill, COUNT(r.id_offers) as count
        FROM skills s
        LEFT JOIN requerir r ON s.id_skill = r.id_skill
        GROUP BY s.label_skill
        ORDER BY count DESC
    ";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDurationStats() {
        $query = "
        SELECT 
            CASE 
                WHEN DATEDIFF(e_date_offer, s_date_offer) < 30 THEN 'Moins d\'1 mois'
                WHEN DATEDIFF(e_date_offer, s_date_offer) BETWEEN 30 AND 89 THEN '1 à 3 mois'
                WHEN DATEDIFF(e_date_offer, s_date_offer) BETWEEN 90 AND 179 THEN '3 à 6 mois'
                ELSE 'Plus de 6 mois'
            END as duration_range,
            COUNT(*) as count
        FROM offers
        GROUP BY duration_range
        ORDER BY 
            CASE duration_range
                WHEN 'Moins d\'1 mois' THEN 1
                WHEN '1 à 3 mois' THEN 2
                WHEN '3 à 6 mois' THEN 3
                ELSE 4
            END
    ";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getWishlistStats() {
        $query = "
        SELECT o.title_offer, COUNT(w.id_user) as wishlist_count
        FROM wishlist w
        JOIN offers o ON w.id_offers = o.id_offers
        GROUP BY o.title_offer
        ORDER BY wishlist_count DESC
        LIMIT 5
    ";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalaryStats() {
        $query = "
        SELECT 
            CASE 
                WHEN remun_offer < 500 THEN 'Moins de 500€'
                WHEN remun_offer BETWEEN 500 AND 999 THEN '500-999€'
                WHEN remun_offer BETWEEN 1000 AND 1499 THEN '1000-1499€'
                ELSE '1500€ et plus'
            END as salary_range,
            COUNT(*) as count
        FROM offers
        GROUP BY salary_range
        ORDER BY 
            CASE salary_range
                WHEN 'Moins de 500€' THEN 1
                WHEN '500-999€' THEN 2
                WHEN '1000-1499€' THEN 3
                ELSE 4
            END
    ";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function verif_perm($id_fonction, $id_utilisateur) {
        try {
            $requete_permission = "SELECT id_rank from _user WHERE id_user = :id_utilisateur;";
            $requete_permission_prep = $this->pdo->prepare($requete_permission);
            $requete_permission_prep->execute([':id_utilisateur' => $id_utilisateur]);
            $permission = "r".$requete_permission_prep->fetchColumn();

            $requete_verification = "SELECT $permission from perms WHERE id_page = :id_fonction;";
            $requete_verification_prep = $this->pdo->prepare($requete_verification);
            $requete_verification_prep->execute([':id_fonction' => $id_fonction]);

            $verification = $requete_verification_prep->fetchColumn();
            return ($verification);

        
        } catch (PDOException $message_erreur) {
            error_log("Erreur lors de la verification des permissions : " . $message_erreur->getMessage());
        }
    }

}

?>
