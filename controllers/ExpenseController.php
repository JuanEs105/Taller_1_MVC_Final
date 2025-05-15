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
            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId <= 0) {
                throw new Exception('Seleccione una categoría válida.');
            }

            $value = str_replace(',', '.', $value);
            if (!is_numeric($value) || floatval($value) <= 0) {
                throw new Exception('El valor del gasto debe ser un número mayor a cero.');
            }
             $value = floatval($value);

            $category = $this->getCategoryById($categoryId);
            if (!$category) {
                throw new Exception('La categoría seleccionada no existe.');
            }

            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $month = htmlspecialchars(trim($month));
            if (!in_array($month, $validMonths)) {
                throw new Exception('Mes no válido.');
            }

            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                throw new Exception('Año no válido.');
            }

            $reportData = $this->incomeController->checkReportExists($month, $year);
            $reportId = $reportData ? $reportData['id'] : $this->incomeController->createReport($month, $year);

            $query = "INSERT INTO bills (value, idCategory, idReport)
                      VALUES (:value, :categoryId, :reportId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al registrar gasto: " . $errorInfo[2]);
                throw new Exception('Error al registrar el gasto.');
            }

            return [
                'success' => true,
                'message' => 'Gasto registrado correctamente',
                'id' => $this->db->lastInsertId()
            ];

        } catch (PDOException $e) {
             error_log("Error PDO en registerExpense: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al registrar el gasto.'
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
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception('ID de gasto inválido para actualizar.');
            }

            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId <= 0) {
                throw new Exception('Seleccione una categoría válida.');
            }

             $value = str_replace(',', '.', $value);
            if (!is_numeric($value) || floatval($value) <= 0) {
                throw new Exception('El valor del gasto debe ser un número mayor a cero.');
            }
             $value = floatval($value);

            $category = $this->getCategoryById($categoryId);
            if (!$category) {
                throw new Exception('La categoría seleccionada no existe.');
            }

            $existingExpense = $this->getExpenseById($id);
            if (!$existingExpense) {
                throw new Exception('El gasto no existe.');
            }

            $query = "UPDATE bills
                      SET value = :value, idCategory = :categoryId
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al actualizar gasto: " . $errorInfo[2]);
                throw new Exception('Error al actualizar el gasto.');
            }

             if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Gasto actualizado correctamente'
                ];
             } else {
                  return [
                    'success' => true,
                    'message' => 'Gasto actualizado (sin cambios detectados).'
                 ];
             }

        } catch (PDOException $e) {
             error_log("Error PDO en updateExpense: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el gasto.'
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
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception('ID de gasto inválido para eliminar.');
            }

            $query = "DELETE FROM bills WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al eliminar gasto: " . $errorInfo[2]);
                throw new Exception('Error al eliminar el gasto.');
            }

             if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Gasto eliminado correctamente'
                ];
             } else {
                 return [
                    'success' => false,
                    'message' => 'Error al eliminar el gasto: No se encontraron coincidencias.'
                 ];
             }


        } catch (PDOException $e) {
            error_log("Error PDO en deleteExpense: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error de base de datos al eliminar el gasto.'
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
            error_log("Error PDO al obtener gastos: " . $e->getMessage());
            return [];
        }
    }

    public function getExpenseById($id) {
        try {
             $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                 error_log("Intento de obtener gasto con ID inválido: " . print_r($id, true));
                 return false;
            }

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
            error_log("Error PDO al obtener gasto por ID: " . $e->getMessage());
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
            error_log("Error PDO al obtener categorías desde ExpenseController: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id) {
         try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                 error_log("Intento de obtener categoría desde ExpenseController con ID inválido: " . print_r($id, true));
                 return false;
            }

            $query = "SELECT id, name, percentage FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
         } catch (PDOException $e) {
            error_log("Error PDO al obtener categoría por ID desde ExpenseController: " . $e->getMessage());
            return false;
         }
    }

    public function getExpensesByMonthYear($month, $year) {
        try {
             $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $month = htmlspecialchars(trim($month));
            if (!in_array($month, $validMonths)) {
                error_log("Intento de obtener gastos con mes inválido: " . $month);
                return [];
            }

            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                error_log("Intento de obtener gastos con año inválido: " . $year);
                return [];
            }

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
            error_log("Error PDO al obtener gastos por mes/año: " . $e->getMessage());
            return [];
        }
    }
}