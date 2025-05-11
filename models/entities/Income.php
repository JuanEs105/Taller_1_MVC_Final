<?php
class Income {
    private $id;
    private $value;
    private $idReport;

    public function __construct($id = null, $value = null, $idReport = null) {
        $this->id = $id;
        $this->value = $value;
        $this->idReport = $idReport;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getValue() {
        return $this->value;
    }

    public function setValue($value) {
        $this->value = $value;
    }

    public function getIdReport() {
        return $this->idReport;
    }

    public function setIdReport($idReport) {
        $this->idReport = $idReport;
    }
}
?>