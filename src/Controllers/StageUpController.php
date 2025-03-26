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
        $entreprises = $this->model->getAllEntreprises();
        $this->render('entreprises.html', ['entreprises' => $entreprises, 'page' => $page]);
    }

    public function afficher_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_entreprise'])) : $id_entreprise = (int)$_GET['id_entreprise']; else : $id_entreprise=1; endif;
        $offres = $this->model->getAllOffres();
        $this->render('offres.html', ['offres' => $offres, 'page' => $page, 'id_entreprise' => $id_entreprise]);
    }
}
?>
