<?php
namespace grp1\STAGEUP\Models;

class StageUpModel extends Model {

    public function __construct($connection = null) {

        if (is_null($connection)) {
            require_once __DIR__ . '/bdd_info.php';
            $this->connection = new Database($host, $dbname, $username, $password);

        } else {
            $this->connection = $connection;

        }
    }

    public function getAllEntreprises() {
        return $this->connection->getAllRecords('enterprises');
    }

    public function getAllOffres() {
        return $this->connection->getAllRecords('offers');
    }

}
?>
