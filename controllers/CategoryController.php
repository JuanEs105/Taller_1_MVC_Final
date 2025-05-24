<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Category.php';

class CategoryController {
    private $db;

    const MIN_NAME_LENGTH = 2;
    const MAX_NAME_LENGTH = 50;
    const VALID_NAME_PATTERN = '/^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ\'\-,]+$/u';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function registerCategory($name, $percentage) {
        try {
            $name = trim($name);

            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido.");
            }

            if (mb_strlen($name, 'UTF-8') < self::MIN_NAME_LENGTH || mb_strlen($name, 'UTF-8') > self::MAX_NAME_LENGTH) {
                 throw new Exception("El nombre de la categoría debe tener entre " . self::MIN_NAME_LENGTH . " y " . self::MAX_NAME_LENGTH . " caracteres.");
            }

            if (!preg_match(self::VALID_NAME_PATTERN, $name)) {
                 throw new Exception("El nombre de la categoría contiene caracteres no permitidos.");
            }

            $percentage = str_replace(',', '.', $percentage);

            if (!is_numeric($percentage)) {
                 throw new Exception("El porcentaje debe ser un número válido.");
            }

            $percentage = floatval($percentage);

            if ($percentage < 0.01 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100.");
            }

            $query = "SELECT id FROM categories WHERE name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("Ya existe una categoría con ese nombre.");
            }

            $query = "INSERT INTO categories (name, percentage) VALUES (:name, :percentage)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Categoría registrada correctamente.'
                ];
            } else {
                throw new Exception("Error desconocido al registrar la categoría.");
            }
        } catch (Exception $e) {
            error_log("Error en registerCategory: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateCategory($id, $name, $percentage) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception("ID de categoría inválido para actualizar.");
            }

            // Check if the category is in use before allowing update
            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede editar: la categoría tiene gastos asociados.");
            }

            $name = trim($name);

            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido.");
            }

            if (mb_strlen($name, 'UTF-8') < self::MIN_NAME_LENGTH || mb_strlen($name, 'UTF-8') > self::MAX_NAME_LENGTH) {
                 throw new Exception("El nombre de la categoría debe tener entre " . self::MIN_NAME_LENGTH . " y " . self::MAX_NAME_LENGTH . " caracteres.");
            }

            if (!preg_match(self::VALID_NAME_PATTERN, $name)) {
                 throw new Exception("El nombre de la categoría contiene caracteres no permitidos.");
            }

            $percentage = str_replace(',', '.', $percentage);

            if (!is_numeric($percentage)) {
                 throw new Exception("El porcentaje debe ser un número válido.");
            }

            $percentage = floatval($percentage);

            if ($percentage < 0.01 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100.");
            }

            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                throw new Exception("La categoría a actualizar no existe.");
            }

            $query = "SELECT id FROM categories WHERE name = :name AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("Ya existe otra categoría con ese nombre.");
            }

            $query = "UPDATE categories SET name = :name, percentage = :percentage WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar la categoría.");
            }

             if ($stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'message' => 'Categoría actualizada correctamente.'
                ];
             } else {
                 return [
                    'success' => true,
                    'message' => 'Categoría actualizada (sin cambios detectados).'
                 ];
             }

        } catch (Exception $e) {
            error_log("Error en updateCategory: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteCategory($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception("ID de categoría inválido para eliminar.");
            }

            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                throw new Exception("La categoría a eliminar no existe.");
            }

            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede eliminar: la categoría tiene gastos asociados.");
            }

            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar la categoría.");
            }

            if ($stmt->rowCount() > 0) {
                 return [
                    'success' => true,
                    'message' => 'Categoría eliminada correctamente.'
                 ];
            } else {
                 throw new Exception("Error al eliminar la categoría: No se encontraron coincidencias.");
            }

        } catch (Exception $e) {
            error_log("Error en deleteCategory: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAllCategories() {
        try {
            $stmt = $this->db->query("SELECT id, name, percentage FROM categories ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id) {
        try {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                error_log("Intento de obtener categoría con ID inválido: " . print_r($id, true));
                return false;
            }

            $stmt = $this->db->prepare("SELECT id, name, percentage FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categoría por ID: " . $e->getMessage());
            return false;
        }
    }

    public function isCategoryInUse($categoryId) {
        try {
            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);
            if ($categoryId === false || $categoryId <= 0) {
                 error_log("Intento de verificar categoría en uso con ID inválido: " . print_r($categoryId, true));
                 return true;
            }

            $query = "SELECT 1 FROM bills WHERE idCategory = :categoryId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log("Error al verificar categoría en uso: " . $e->getMessage());
            return true;
        }
    }
}