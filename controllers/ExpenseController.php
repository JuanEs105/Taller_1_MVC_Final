<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Expense.php';
require_once 'controllers/IncomeController.php';

class ExpenseController {
    private $db;
    private $incomeController;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->incomeController = new IncomeController();
    }
    
    public function registerExpense($category, $month, $year, $value) {
        try {
            if ($value <= 0) {
                throw new Exception('El valor del gasto no puede ser menor o igual a cero.');
            }
            
            $reportData = $this->incomeController->checkReportExists($month, $year);
            $reportId = $reportData ? $reportData['id'] : $this->incomeController->createReport($month, $year);
            
            $query = "INSERT INTO bills (value, idCategory, idReport) VALUES (:value, :idCategory, :idReport)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':idCategory', $category, PDO::PARAM_INT);
            $stmt->bindParam(':idReport', $reportId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al registrar el gasto.');
            }
            
            return ['success' => true, 'message' => 'Gasto registrado correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function updateExpense($id, $category, $value) {
        try {
            if ($value <= 0) {
                throw new Exception('El valor del gasto no puede ser menor o igual a cero.');
            }
            
            $query = "UPDATE bills SET value = :value, idCategory = :idCategory WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':idCategory', $category, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar el gasto.');
            }
            
            return ['success' => true, 'message' => 'Gasto actualizado correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function deleteExpense($id) {
        try {
            $query = "DELETE FROM bills WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al eliminar el gasto.');
            }
            
            return ['success' => true, 'message' => 'Gasto eliminado correctamente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getAllExpenses() {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, r.month, r.year 
                     FROM bills b 
                     JOIN categories c ON b.idCategory = c.id 
                     JOIN reports r ON b.idReport = r.id 
                     ORDER BY r.year DESC, FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                     'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener gastos: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExpenseById($id) {
        try {
            $query = "SELECT b.id, b.value, b.idCategory, c.name as category_name, r.month, r.year 
                     FROM bills b 
                     JOIN categories c ON b.idCategory = c.id 
                     JOIN reports r ON b.idReport = r.id 
                     WHERE b.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener gasto: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllCategories() {
        try {
            $query = "SELECT id, name, percentage FROM categories ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExpensesByMonthYear($month, $year) {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, c.id as category_id, c.percentage
                     FROM bills b 
                     JOIN categories c ON b.idCategory = c.id 
                     JOIN reports r ON b.idReport = r.id 
                     WHERE r.month = :month AND r.year = :year
                     ORDER BY c.name";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':month', $month, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener gastos por mes/año: " . $e->getMessage());
            return [];
        }
    }
}
?>