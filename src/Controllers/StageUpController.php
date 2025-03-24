<?php 
namespace grp1\STAGEUP\Controllers;

use App\Models\StageUpModel;

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
        $enterprises = $this->model->getAllEntreprises();
        $this->render('entreprises.html', ['enterprises' => $enterprises, 'page' => $page]);
    }

    public function afficher_offres() {
        if (isset($_GET['page'])) : $page = (int)$_GET['page']; else : $page=1; endif;
        if (isset($_GET['id_enterprise'])) : $id_enterprise = (int)$_GET['id_enterprise']; else : $id_enterprise=1; endif;
        $offers = $this->model->getAllOffres();
        $this->render('offres.html', ['offers' => $offers, 'page' => $page, 'id_enterprise' => $id_enterprise]);
    }
}
?>
