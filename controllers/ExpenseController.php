<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Expense.php';
require_once 'models/entities/Category.php';
require_once 'controllers/IncomeController.php';

class ExpenseController {
    private $db;
    private $incomeController;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->incomeController = new IncomeController();
    }
    
    // Método para registrar un nuevo gasto
    public function registerExpense($categoryId, $month, $year, $value) {
        try {
            // Validaciones básicas
            if ($value <= 0) {
                throw new Exception('El valor del gasto debe ser mayor a cero');
            }

            // Verificar o crear el reporte
            $reportData = $this->incomeController->checkReportExists($month, $year);
            $reportId = $reportData ? $reportData['id'] : $this->incomeController->createReport($month, $year);

            // Registrar el gasto
            $query = "INSERT INTO bills (value, idCategory, idReport) VALUES (:value, :categoryId, :reportId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al registrar el gasto');
            }

            return [
                'success' => true,
                'message' => 'Gasto registrado correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Método para actualizar un gasto (solo categoría y valor)
    public function updateExpense($id, $categoryId, $value) {
        try {
            // Validaciones
            if ($value <= 0) {
                throw new Exception('El valor del gasto debe ser mayor a cero');
            }

            // Verificar que el gasto existe
            $existingExpense = $this->getExpenseById($id);
            if (!$existingExpense) {
                throw new Exception('El gasto no existe');
            }

            // Actualizar solo categoría y valor
            $query = "UPDATE bills SET value = :value, idCategory = :categoryId WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar el gasto');
            }

            return [
                'success' => true,
                'message' => 'Gasto actualizado correctamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Método para eliminar un gasto
    public function deleteExpense($id) {
        try {
            $query = "DELETE FROM bills WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception('Error al eliminar el gasto');
            }

            return [
                'success' => true,
                'message' => 'Gasto eliminado correctamente'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Método para obtener todos los gastos
    public function getAllExpenses() {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, r.month, r.year, 
                             c.id as category_id, b.idCategory
                      FROM bills b
                      JOIN categories c ON b.idCategory = c.id
                      JOIN reports r ON b.idReport = r.id
                      ORDER BY r.year DESC, 
                      FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener gastos: " . $e->getMessage());
            return [];
        }
    }

    // Método para obtener un gasto específico por ID
    public function getExpenseById($id) {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, r.month, r.year, 
                             c.id as category_id, b.idCategory, b.idReport
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

    // Método para obtener todas las categorías
    public function getAllCategories() {
        try {
            $query = "SELECT id, name FROM categories ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    // Método para obtener gastos por mes y año
    public function getExpensesByMonthYear($month, $year) {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, c.id as category_id
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