<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Income.php';
require_once 'models/entities/Report.php';

class IncomeController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Método para verificar si ya existe un reporte para un mes y año específico
    public function checkReportExists($month, $year) {
        $query = "SELECT id FROM reports WHERE month = :month AND year = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    // Método para crear un nuevo reporte
    public function createReport($month, $year) {
        $query = "INSERT INTO reports (month, year) VALUES (:month, :year)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    // Método para registrar un ingreso
    public function registerIncome($month, $year, $value) {
        // Validar que el valor del ingreso no sea menor a cero
        if ($value <= 0) {
            return ['success' => false, 'message' => 'El ingreso no puede ser menor o igual a cero.'];
        }
        
        // Verificar si ya existe un reporte para este mes y año
        $reportData = $this->checkReportExists($month, $year);
        
        if ($reportData) {
            $reportId = $reportData['id'];
            
            // Verificar si ya existe un ingreso para este reporte
            $query = "SELECT id FROM income WHERE idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':reportId', $reportId);
            $stmt->execute();
            $incomeExists = $stmt->fetch();
            
            if ($incomeExists) {
                return ['success' => false, 'message' => 'Ya existe un ingreso registrado para este mes y año.'];
            }
        } else {
            // Crear un nuevo reporte si no existe
            $reportId = $this->createReport($month, $year);
        }
        
        // Registrar el ingreso
        $query = "INSERT INTO income (value, idReport) VALUES (:value, :reportId)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':reportId', $reportId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Ingreso registrado correctamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al registrar el ingreso.'];
        }
    }
    
    // Método para modificar un ingreso existente
    public function updateIncome($month, $year, $value) {
        // Validar que el valor del ingreso no sea menor a cero
        if ($value <= 0) {
            return ['success' => false, 'message' => 'El ingreso no puede ser menor o igual a cero.'];
        }
        
        // Verificar si existe un reporte para este mes y año
        $reportData = $this->checkReportExists($month, $year);
        
        if (!$reportData) {
            return ['success' => false, 'message' => 'No existe un ingreso registrado para este mes y año.'];
        }
        
        $reportId = $reportData['id'];
        
        // Verificar si existe un ingreso para este reporte
        $query = "SELECT id FROM income WHERE idReport = :reportId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reportId', $reportId);
        $stmt->execute();
        $incomeData = $stmt->fetch();
        
        if (!$incomeData) {
            return ['success' => false, 'message' => 'No existe un ingreso registrado para este mes y año.'];
        }
        
        // Actualizar el ingreso
        $query = "UPDATE income SET value = :value WHERE idReport = :reportId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':reportId', $reportId);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Ingreso actualizado correctamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar el ingreso.'];
        }
    }
    
    // Método para obtener todos los ingresos registrados
    public function getAllIncomes() {
        $query = "SELECT i.id, i.value, r.month, r.year 
                  FROM income i 
                  JOIN reports r ON i.idReport = r.id 
                  ORDER BY r.year DESC, FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Método para obtener un ingreso específico por mes y año
    public function getIncomeByMonthYear($month, $year) {
        $query = "SELECT i.id, i.value, r.month, r.year 
                  FROM income i 
                  JOIN reports r ON i.idReport = r.id 
                  WHERE r.month = :month AND r.year = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}
?>