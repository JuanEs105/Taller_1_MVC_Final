<?php
class Report {
    private $id;
    private $month;
    private $year;

    public function __construct($id = null, $month = null, $year = null) {
        $this->id = $id;
        $this->month = $month;
        $this->year = $year;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getMonth() {
        return $this->month;
    }

    public function setMonth($month) {
        $this->month = $month;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($year) {
        $this->year = $year;
    }
}
?>