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
    
    /**
     * Verifica si existe un reporte para un mes y año específico
     */
    public function checkReportExists($month, $year) {
        $query = "SELECT id FROM reports WHERE month = :month AND year = :year";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Crea un nuevo reporte
     */
    public function createReport($month, $year) {
        $query = "INSERT INTO reports (month, year) VALUES (:month, :year)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Registra un nuevo ingreso con validaciones mejoradas
     */
    public function registerIncome($month, $year, $value) {
        try {
            // Validaciones mejoradas
            if (!is_numeric($value)) {
                throw new Exception('El valor debe ser numérico');
            }

            $value = (float)$value;
            if ($value <= 0) {
                throw new Exception('El ingreso debe ser mayor a cero');
            }

            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            if (!in_array($month, $validMonths)) {
                throw new Exception('Mes no válido');
            }

            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                throw new Exception('Año no válido');
            }

            // Verificar si ya existe un reporte
            $reportData = $this->checkReportExists($month, $year);
            
            if ($reportData) {
                $reportId = $reportData['id'];
                // Verificar si ya existe un ingreso para este reporte
                $query = "SELECT id FROM income WHERE idReport = :reportId";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':reportId', $reportId);
                $stmt->execute();
                
                if ($stmt->fetch()) {
                    throw new Exception('Ya existe un ingreso registrado para este mes y año');
                }
            } else {
                // Crear nuevo reporte si no existe
                $reportId = $this->createReport($month, $year);
            }

            // Registrar el ingreso con transacción
            $this->db->beginTransaction();
            
            try {
                $query = "INSERT INTO income (value, idReport) VALUES (:value, :reportId)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':value', $value, PDO::PARAM_STR);
                $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
                
                if (!$stmt->execute()) {
                    throw new Exception('Error al ejecutar la consulta');
                }

                $this->db->commit();
                
                return [
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente',
                    'id' => $this->db->lastInsertId()
                ];
            } catch (Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualiza un ingreso existente
     */
    public function updateIncome($month, $year, $value) {
        try {
            // Validaciones
            if (!is_numeric($value) || $value <= 0) {
                throw new Exception('El ingreso no puede ser menor o igual a cero');
            }

            // Verificar si existe un reporte para este mes y año
            $reportData = $this->checkReportExists($month, $year);
            
            if (!$reportData) {
                throw new Exception('No existe un ingreso registrado para este mes y año');
            }
            
            $reportId = $reportData['id'];
            
            // Verificar si existe un ingreso para este reporte
            $query = "SELECT id FROM income WHERE idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':reportId', $reportId);
            $stmt->execute();
            $incomeData = $stmt->fetch();
            
            if (!$incomeData) {
                throw new Exception('No existe un ingreso registrado para este mes y año');
            }
            
            // Actualizar el ingreso
            $query = "UPDATE income SET value = :value WHERE idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Ingreso actualizado correctamente'
                ];
            } else {
                throw new Exception('Error al actualizar el ingreso');
            }
            
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtiene todos los ingresos registrados
     */
    public function getAllIncomes() {
        try {
            $query = "SELECT i.id, i.value, r.month, r.year 
                      FROM income i 
                      JOIN reports r ON i.idReport = r.id 
                      ORDER BY r.year DESC, 
                      FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener ingresos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtiene un ingreso específico por mes y año
     */
    public function getIncomeByMonthYear($month, $year) {
        try {
            $query = "SELECT i.id, i.value, r.month, r.year 
                      FROM income i 
                      JOIN reports r ON i.idReport = r.id 
                      WHERE r.month = :month AND r.year = :year";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener ingreso: " . $e->getMessage());
            return false;
        }
    }
}
?>