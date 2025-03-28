<?php 
namespace grp1\STAGEUP\Controllers;

use grp1\STAGEUP\Models\StageUpModel;

class StageUpController extends Controller {

    public function __construct($templateEngine) {
        parent::__construct($templateEngine);
        $this->model = new StageUpModel();
    }

    public function page_accueil() {
        $this->render('accueil.html');
    }

    public function afficher_entreprises() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['note_min'])) : $note_min = $_GET['note_min']; else : $note_min=0; endif;
        $entreprises = $this->model->getEntreprises($note_min);
        $this->render('entreprises.html', ['entreprises' => $entreprises, 'page' => $page, 'note_min' => $note_min]);
    }

    public function afficher_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_entreprise'])) : $id_entreprise = $_GET['id_entreprise']; else : $id_entreprise=1; endif;
        if (isset($_GET['salaire_min'])) : $salaire_min = $_GET['salaire_min']; else : $salaire_min=0; endif;
        $offres = $this->model->getOffres($id_entreprise, $salaire_min);
        $this->render('offres.html', ['offres' => $offres, 'page' => $page, 'id_entreprise' => $id_entreprise, 'salaire_min' => $salaire_min]);
    }


    public function page_connexion(){

        if (!isset($_COOKIE['user_status'])) {
            setcookie('user_status', '1', time() + 600);
        }
        $user_status_r = $_COOKIE['user_status'];
        $this->render('page_connexion.html', ['user_status_r' => $user_status_r]);
    }

    public function connexion(){

        setcookie('user_status', '2', time() + 600);
        header('Location: /?uri=page_connexion');
    }

    public function deconnexion(){

        setcookie('user_status', '1', time() + 600);
        header('Location: /?uri=page_connexion');
    }

    public function test_connexion(){

        if (!isset($_COOKIE['user_status'])) {
            setcookie('user_status', '1', time() + 600);
        }
        $user_status_r = $_COOKIE['user_status'];
        if ($user_status_r == 2) {
            $this->render('test_connexion.html', ['user_status_r' => $user_status_r]);
        }
        else {
            $this->render('acces_interdit.html');
        }
        
    }
    
}



?>
