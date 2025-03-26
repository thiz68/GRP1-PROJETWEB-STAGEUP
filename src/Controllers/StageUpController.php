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
        if (isset($_GET['note_min'])) : $note_min = (int)$_GET['note_min']; else : $note_min=0; endif;
        $entreprises = $this->model->getEntreprises($note_min);
        $this->render('entreprises.html', ['entreprises' => $entreprises, 'page' => $page]);
    }

    public function afficher_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_entreprise'])) : $id_entreprise = (int)$_GET['id_entreprise']; else : $id_entreprise=1; endif;
        $offres = $this->model->getOffres();
        $this->render('offres.html', ['offres' => $offres, 'page' => $page, 'id_entreprise' => $id_entreprise]);
    }
}
?>
