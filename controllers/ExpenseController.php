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
    
    public function registerExpense($categoryId, $month, $year, $value) {
        try {
            // Validaciones mejoradas
            if (!is_numeric($value) || $value <= 0) {
                throw new Exception('El valor del gasto debe ser un número mayor a cero');
            }

            if (!is_numeric($categoryId) || $categoryId <= 0) {
                throw new Exception('Seleccione una categoría válida');
            }

            // Verificar que la categoría existe
            $category = $this->getCategoryById($categoryId);
            if (!$category) {
                throw new Exception('La categoría seleccionada no existe');
            }

            // Validar mes y año
            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            if (!in_array($month, $validMonths)) {
                throw new Exception('Mes no válido');
            }

            if ($year < 2000 || $year > 2100) {
                throw new Exception('Año no válido (debe estar entre 2000 y 2100)');
            }

            // Verificar o crear el reporte
            $reportData = $this->incomeController->checkReportExists($month, $year);
            $reportId = $reportData ? $reportData['id'] : $this->incomeController->createReport($month, $year);

            // Registrar el gasto en la tabla bills (sin created_at)
            $query = "INSERT INTO bills (value, idCategory, idReport) 
                      VALUES (:value, :categoryId, :reportId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Error al registrar el gasto: ' . $errorInfo[2]);
            }

            return [
                'success' => true,
                'message' => 'Gasto registrado correctamente',
                'id' => $this->db->lastInsertId()
            ];
            
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

    public function updateExpense($id, $categoryId, $value) {
        try {
            // Validaciones
            if (!is_numeric($value) || $value <= 0) {
                throw new Exception('El valor del gasto debe ser mayor a cero');
            }

            if (!is_numeric($categoryId) || $categoryId <= 0) {
                throw new Exception('Seleccione una categoría válida');
            }

            // Verificar que la categoría existe
            $category = $this->getCategoryById($categoryId);
            if (!$category) {
                throw new Exception('La categoría seleccionada no existe');
            }

            // Verificar que el gasto existe
            $existingExpense = $this->getExpenseById($id);
            if (!$existingExpense) {
                throw new Exception('El gasto no existe');
            }

            // Actualizar (sin updated_at)
            $query = "UPDATE bills 
                      SET value = :value, idCategory = :categoryId
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Error al actualizar: ' . $errorInfo[2]);
            }

            return [
                'success' => true,
                'message' => 'Gasto actualizado correctamente'
            ];
            
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

    public function deleteExpense($id) {
        try {
            $query = "DELETE FROM bills WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Error al eliminar: ' . $errorInfo[2]);
            }

            return [
                'success' => true,
                'message' => 'Gasto eliminado correctamente'
            ];
            
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

    public function getAllExpenses() {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, 
                             r.month, r.year, c.id as idCategory, b.idReport
                      FROM bills b
                      JOIN categories c ON b.idCategory = c.id
                      JOIN reports r ON b.idReport = r.id
                      ORDER BY r.year DESC, 
                      FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre')";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener gastos: " . $e->getMessage());
            return [];
        }
    }

    public function getExpenseById($id) {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, 
                             r.month, r.year, c.id as idCategory, b.idReport
                      FROM bills b
                      JOIN categories c ON b.idCategory = c.id
                      JOIN reports r ON b.idReport = r.id
                      WHERE b.id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
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
            
        } catch (PDOException $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id) {
        try {
            $query = "SELECT id, name, percentage FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener categoría: " . $e->getMessage());
            return false;
        }
    }

    public function getExpensesByMonthYear($month, $year) {
        try {
            $query = "SELECT b.id, b.value, c.name as category_name, c.id as idCategory
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
            
        } catch (PDOException $e) {
            error_log("Error al obtener gastos por mes/año: " . $e->getMessage());
            return [];
        }
    }
}
?>