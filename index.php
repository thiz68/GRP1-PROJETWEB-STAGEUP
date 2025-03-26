<?php
require "vendor/autoload.php";

use grp1\STAGEUP\Controllers\StageUpController;

$loader = new \Twig\Loader\FilesystemLoader('templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true
]);

if (isset($_GET['uri'])) {
    $uri = $_GET['uri'];
} else {
    $uri = '/';
}

$controller = new StageUpController($twig);

switch ($uri) {
    case '/':
        $controller->page_accueil();
        break;
    case 'entreprises':
        $controller->afficher_entreprises();
        break;
    case 'offres':
        $controller->afficher_offres();
        break;
    default:
        echo '404 Not Found';
        break;
}
?>