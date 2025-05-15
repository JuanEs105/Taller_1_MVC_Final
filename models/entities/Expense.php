<?php
class Expense {
    private $id;
    private $value;
    private $idCategory;
    private $idReport;

    public function __construct($id = null, $value = null, $idCategory = null, $idReport = null) {
        $this->id = $id;
        $this->value = $value;
        $this->idCategory = $idCategory;
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

    public function getIdCategory() {
        return $this->idCategory;
    }

    public function setIdCategory($idCategory) {
        $this->idCategory = $idCategory;
    }

    public function getIdReport() {
        return $this->idReport;
    }

    public function setIdReport($idReport) {
        $this->idReport = $idReport;
    }
}