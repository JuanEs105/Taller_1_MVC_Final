<?php
class Category {
    private $id;
    private $name;
    private $percentage;

    public function __construct($id = null, $name = null, $percentage = null) {
        $this->id = $id;
        $this->name = $name;
        $this->percentage = $percentage;
    }

   
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getPercentage() {
        return $this->percentage;
    }


    public function setId($id) {
        $this->id = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setPercentage($percentage) {
        $this->percentage = $percentage;
    }
}
?>