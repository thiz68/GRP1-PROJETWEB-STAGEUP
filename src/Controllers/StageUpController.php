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
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['note_min'])) : $note_min = $_GET['note_min']; else : $note_min=0; endif;
        $entreprises = $this->model->getEntreprises($note_min);
        $this->render('entreprises.html', ['entreprises' => $entreprises, 'page' => $page, 'note_min' => $note_min]);
    }

    public function liste_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_entreprise'])) : $id_entreprise = $_GET['id_entreprise']; else : $id_entreprise=0; endif;
        if (isset($_GET['salaire_min'])) : $salaire_min = $_GET['salaire_min']; else : $salaire_min=0; endif;
        $offres = $this->model->getOffres($id_entreprise, $salaire_min);
        $this->render('offres.html', ['offres' => $offres, 'page' => $page, 'id_entreprise' => $id_entreprise, 'salaire_min' => $salaire_min]);
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




    
}



?>
