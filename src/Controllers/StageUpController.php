<?php 
namespace grp1\STAGEUP\Controllers;

use grp1\STAGEUP\Models\StageUpModel;
use grp1\STAGEUP\Services\SessionManager;

class StageUpController extends Controller {

    public function __construct($templateEngine) {
        parent::__construct($templateEngine);
        $this->model = new StageUpModel();
    }

    protected function requireAuthentication(): void
    {
        if (!SessionManager::isUserLoggedIn() || !SessionManager::validateSession()) {
            header('Location: /?uri=connexion');
            exit;
        }

        // Met à jour l'horodatage de dernière activité
        SessionManager::updateLastActivity();
    }




    public function page_accueil() {
        $this->render('accueil.html');
    }

    public function liste_entreprises() {
        $this->requireAuthentication();

        // Récupération des paramètres
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $note_min = isset($_GET['note_min']) ? (float)$_GET['note_min'] : 0.0;
        $keywords = $_GET['keywords'] ?? '';

        // Récupération des entreprises filtrées
        $entreprises = $this->model->searchEntreprises($keywords, $note_min);

        // Calcul du nombre total de pages
        $itemsPerPage = 10;
        $totalItems = count($entreprises);
        $totalPages = ceil($totalItems / $itemsPerPage);

        // Validation de la page courante
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $this->render('entreprises.html', [
            'entreprises' => $entreprises,
            'page' => $page,
            'totalPages' => $totalPages,
            'note_min' => $note_min,
            'keywords' => $keywords
        ]);
    }

    public function liste_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_entreprise'])) : $id_entreprise = $_GET['id_entreprise']; else : $id_entreprise=0; endif;
        if (isset($_GET['salaire_min'])) : $salaire_min = $_GET['salaire_min']; else : $salaire_min=0; endif;
        $keywords = $_GET['keywords'] ?? '';

        if (!empty($keywords)) {
            $offres = $this->model->searchOffres($id_entreprise, $salaire_min, $keywords);
        } else {
            $offres = $this->model->getOffres($id_entreprise, $salaire_min);
        }

        // Pour chaque offre, récupérer le nom de l'entreprise
        foreach ($offres as $key => $offer) {
            $entreprise = $this->model->getEntreprise($offer['id_enterprise']);
            $offres[$key]['name_enterprise'] = $entreprise[0]['name_enterprise'];
        }

        $itemsPerPage = 10;
        $totalItems = count($offres);
        $totalPages = ceil($totalItems / $itemsPerPage);

        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $this->render('offres.html', [
            'offres' => $offres,
            'page' => $page,
            'totalPages' => $totalPages,
            'id_entreprise' => $id_entreprise,
            'salaire_min' => $salaire_min,
            'keywords' => $keywords
        ]);
    }

    public function page_entreprise() {
        if (isset($_GET['id_entreprise'])) : $id_entreprise = $_GET['id_entreprise']; else : $id_entreprise=1; endif;
        $infos_entreprise = $this->model->getEntreprise($id_entreprise);
        $id_utilisateur = SessionManager::getCurrentUserId();
        $note_attribuee = $this->model->get_note_attribuee($id_utilisateur,$id_entreprise);
        $this->render('page_entreprise.html', ['infos_entreprise' => $infos_entreprise, 'note_attribuee' => $note_attribuee]);
    }

    public function noter_entreprise() {
        if (isset($_GET['id_entreprise'])) : $id_entreprise = $_GET['id_entreprise']; else : $id_entreprise=1; endif;
        $note_attribuee = $_GET['note_attribuee'];
        $id_utilisateur = SessionManager::getCurrentUserId();
        $this->model->post_note_attribuee($id_utilisateur,$id_entreprise,$note_attribuee);
       header("Location: ?uri=page_entreprise&id_entreprise=".$id_entreprise);
    }

    public function page_creer_compte_pilote() {
        $this->render('creer_compte_pilote.html');
    }

    public function creer_compte_pilote() {
        $this->model->post_form_creer_pilote($_POST['nom'],$_POST['prenom'],$_POST['email'],password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT));
        header("Location: ?uri=pilotes");
    }

    public function page_creer_compte_etudiant() {
        $this->render('creer_compte_etudiant.html');
    }

    public function creer_compte_etudiant() {
        $this->model->post_form_creer_etudiant($_POST['nom'],$_POST['prenom'],$_POST['email'],password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT));
        header("Location: ?uri=etudiants");
    }

    public function page_modif_compte_etudiant() {
        $id_etudiant = $_GET['id_etudiant'];
        $this->render('modif_compte_etudiant.html', ['id_etudiant' => $id_etudiant]);
    }

    public function modif_compte_etudiant() {
        $this->model->post_form_modif_etudiant((int)$_POST['id_etudiant'],$_POST['nom'],$_POST['prenom'],$_POST['email']);
        header("Location: ?uri=etudiants");
    }

    public function page_modif_compte_pilote() {
        $id_pilote = $_GET['id_pilote'];
        $this->render('modif_compte_pilote.html', ['id_pilote' => $id_pilote]);
    }

    public function modif_compte_pilote() {
        $this->model->post_form_modif_pilote((int)$_POST['id_pilote'],$_POST['nom'],$_POST['prenom'],$_POST['email']);
        header("Location: ?uri=pilotes");
    }

    public function page_creer_entreprise() {
        $this->render('creer_entreprise.html');
    }

    public function creer_entreprise() {
        $this->model->post_form_creer_entreprise($_POST['nom'],$_POST['description'],$_POST['email'],$_POST['tel']);
        header("Location: ?uri=entreprises");
    }

    public function page_creer_offre() {
        $this->render('creer_offre.html', ['id_entreprise' => $_GET['id_entreprise']]);
    }

    public function creer_offre() {
        $this->model->post_form_creer_offre($_POST['id_entreprise'],$_POST['titre'],
                                            $_POST['description'],$_POST['salaire'],
                                            $_POST['date_debut'],$_POST['date_fin']);
        header("Location: ?uri=offres");
    }


    public function page_modif_entreprise() {
        $this->render('modif_entreprise.html', ['id_entreprise' => $_GET['id_entreprise']]);
    }

    public function modif_entreprise() {
        $this->model->post_form_modif_entreprise($_POST['id_entreprise'],$_POST['nom'],
        $_POST['description'],$_POST['email'],$_POST['tel']);
        header("Location: ?uri=entreprises");
    }

    public function page_modif_offre() {
        
    }

    public function modif_offre() {
        
    }

    public function page_postuler() {
        $this->render('postuler.html', ['id_offre' => $_GET['id_offre']]);
    }

    public function postuler() {
        $id_utilisateur = SessionManager::getCurrentUserId();
        $this->model->post_form_postuler($id_utilisateur,$_POST['id_offre'],$_POST['motivation'],
        $_FILES['cv']);
        header("Location: /");
    }



    public function liste_etudiants() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        $etudiants = $this->model->get_liste_etudiants();
        $this->render('etudiants.html', ['etudiants' => $etudiants, 'page' => $page]);
    }

    public function liste_pilotes() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        $pilotes = $this->model->get_liste_pilotes();
        $this->render('pilotes.html', ['pilotes' => $pilotes, 'page' => $page]);
    }




    public function afficher_login($error = null, $email = null) {
        $session_expired = isset($_GET['expired']);

        if ($session_expired && !$error) {
            $error = 'Votre session a expiré. Veuillez vous reconnecter.';
        }

        $this->render('connexion.html', [
            'error' => $error,
            'email' => $email,
            'session_expired' => $session_expired
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /?uri=connexion');
            exit;
        }

        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $user = $this->model->getUserByEmail($email);

        if ($user && password_verify($password, $user['password_user'])) {
            SessionManager::loginUser($user['id_user']);
            header('Location: /?uri=profil');
            exit;
        }

        SessionManager::startSession();
        $_SESSION['login_error'] = 'Email ou mot de passe incorrect';
        $_SESSION['login_email'] = $email;

        header('Location: /?uri=connexion');
        exit;
    }

    public function logout() {
        SessionManager::logoutUser();
        header('Location: /');
        exit;
    }

    public function afficher_profil() {
        $this->requireAuthentication();

        $userId = SessionManager::getCurrentUserId();
        $user = $this->model->getUserWithRole($userId);
        $this->render('profil.html', ['user' => $user]);
    }

    public function page_non_trouvee() {
        $this->render('404.html');
    }

    public function mentionslegales() {
        $this->render('mentionslegales.html');
    }


    public function wishlist() {
        $this->requireAuthentication();

        $userId = SessionManager::getCurrentUserId();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $keywords = $_GET['keywords'] ?? '';
        $location = $_GET['location'] ?? '';

        if (!empty($keywords) || !empty($location)) {
            $offers = $this->model->searchWishlist($userId, $keywords, $location);
        } else {
            $offers = $this->model->getUserWishlist($userId);
        }

        $offers = is_array($offers) ? $offers : [];

        $itemsPerPage = 10;
        $totalItems = count($offers);
        $totalPages = ceil($totalItems / $itemsPerPage);

        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalPages && $totalPages > 0) {
            $page = $totalPages;
        }

        $paginatedOffers = array_slice($offers, ($page - 1) * $itemsPerPage, $itemsPerPage);

        $this->render('wishlist.html', [
            'offers' => $paginatedOffers,
            'page' => $page,
            'totalPages' => $totalPages,
            'keywords' => $keywords,
            'location' => $location
        ]);
    }

    public function addToWishlist() {
        $this->requireAuthentication();

        if (!isset($_GET['id_offers'])) {
            header('Location: /?uri=offres');
            exit;
        }

        $userId = SessionManager::getCurrentUserId();
        $offerId = (int)$_GET['id_offers'];
        $this->model->addToWishlist($userId, $offerId);

        $referer = $_SERVER['HTTP_REFERER'] ?? '/?uri=offres';
        header('Location: ' . $referer);
        exit;
    }

    public function removeFromWishlist() {
        $this->requireAuthentication();

        if (!isset($_GET['id_offers'])) {
            header('Location: /?uri=wishlist');
            exit;
        }

        $userId = SessionManager::getCurrentUserId();
        $offerId = (int)$_GET['id_offers'];
        $this->model->removeFromWishlist($userId, $offerId);

        header('Location: /?uri=wishlist');
        exit;
    }




    
}



?>
