<?php

require "vendor/autoload.php";

use grp1\STAGEUP\Controllers\StageUpController;
use grp1\STAGEUP\Services\SessionManager;

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true
]);

SessionManager::startSession();
if (SessionManager::isUserLoggedIn() && !SessionManager::validateSession()) {
    // Rediriger vers la page de connexion si la session est invalide
    header('Location: /?uri=connexion&expired=1');
    exit;
}

if (isset($_GET['uri'])) {
    $uri = $_GET['uri'];
} 

elseif (isset($_POST['uri'])) {
    $uri = $_POST['uri'];
}

else {
    $uri = '/';
}

$controller = new StageUpController($twig);

switch ($uri) {
    case '/':
        $controller->page_accueil();
        break;
    case 'entreprises':
        $controller->liste_entreprises();
        break;
    case 'offres':
        $controller->liste_offres();
        break;
    case 'page_entreprise':
        $controller->page_entreprise();
        break;
    case 'noter_entreprise':
        $controller->noter_entreprise();
        break;
    case 'page_creer_compte_pilote':
        $controller->page_creer_compte_pilote();
        break;      
    case 'creer_compte_pilote':
        $controller->creer_compte_pilote();
        break;
    case 'page_creer_compte_etudiant':
        $controller->page_creer_compte_etudiant();
        break;   
    case 'creer_compte_etudiant':
        $controller->creer_compte_etudiant();
        break;   
    case 'page_creer_entreprise':
        $controller->page_creer_entreprise();
        break;   
    case 'creer_entreprise':
        $controller->creer_entreprise();
        break; 
    case 'page_creer_offre':
        $controller->page_creer_offre();
        break;   
    case 'creer_offre':
        $controller->creer_offre();
        break; 
    case 'etudiants':
        $controller->liste_etudiants();
        break;
    case 'pilotes':
        $controller->liste_pilotes();
        break;
    case 'page_modif_compte_etudiant':
        $controller->page_modif_compte_etudiant();
        break;
    case 'modif_compte_etudiant':
        $controller->modif_compte_etudiant();
        break;
    case 'page_modif_compte_pilote':
        $controller->page_modif_compte_pilote();
        break;
    case 'modif_compte_pilote':
        $controller->modif_compte_pilote();
        break;
    case 'page_modif_entreprise':
        $controller->page_modif_entreprise();
        break;
    case 'modif_entreprise':
        $controller->modif_entreprise();
        break;
    case 'page_modif_offre':
        $controller->page_modif_offre();
        break;
    case 'modif_offre':
        $controller->modif_offre();
        break;
    case 'supp_entreprise':
        $controller->supp_entreprise();
        break;
    case 'supp_offre':
        $controller->supp_offre();
        break;
    case 'supp_pilote':
        $controller->supp_pilote();
        break;
    case 'supp_etudiant':
        $controller->supp_etudiant();
        break;

    case 'page_postuler':
        $controller->page_postuler();
        break;
    case 'postuler':
        $controller->postuler();
        break;


    case 'connexion':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            SessionManager::startSession();
            $login_error = $_SESSION['login_error'] ?? null;
            $login_email = $_SESSION['login_email'] ?? null;

            unset($_SESSION['login_error'], $_SESSION['login_email']);

            $controller->afficher_login($login_error, $login_email);
        }
        break;
    case 'profil':
        $controller->afficher_profil();
        break;

    case 'mes_candidatures':
        $controller->mes_candidatures();
        break;

    case 'wishlist':
        $controller->wishlist();
        break;
    case 'addToWishlist':
        $controller->addToWishlist();
        break;
    case 'removeFromWishlist':
        $controller->removeFromWishlist();
        break;


    case 'logout':
        $controller->logout();
        break;

    case 'mentionslegales':
        $controller->mentionslegales();
        break;

    case 'test':
        echo __DIR__;
        break;

    case 'page_interdite':
        $controller->page_interdite();
        break;

    case 'statistiques':
        $controller->statistiques();
        break;

    default:
        $controller->page_non_trouvee();
        break;
}
?>