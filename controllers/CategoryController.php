<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Category.php';

class CategoryController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
     // Método para registrar nueva categoría 
    public function registerCategory($name, $percentage) {
        try {
            // Validaciones básicas
            if (empty(trim($name))) {
                throw new Exception("El nombre de la categoría es requerido");
            }
            
            if (!is_numeric($percentage) || $percentage <= 0 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100");
            }
            
            // Verificar si ya existe una categoría con ese nombre
            $query = "SELECT id FROM categories WHERE name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                throw new Exception("Ya existe una categoría con ese nombre");
            }
            
            // Registrar la nueva categoría
            $query = "INSERT INTO categories (name, percentage) VALUES (:name, :percentage)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Categoría registrada correctamente'
                ];
            } else {
                throw new Exception("Error al registrar la categoría");
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
     public function updateCategory($id, $name, $percentage) {
        try {
            // Primero verificar si la categoría está en uso
            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede modificar una categoría que tiene gastos asociados");
            }
            
            // Resto de validaciones y lógica de actualización
            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido");
            }
            
            if (!is_numeric($percentage) || $percentage <= 0 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100");
            }
            
            $query = "UPDATE categories SET name = :name, percentage = :percentage WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar la categoría");
            }
            
            return ['success' => true, 'message' => 'Categoría actualizada correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Método para eliminar categoría
    public function deleteCategory($id) {
        try {
            // Primero verificar si la categoría está en uso
            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede eliminar una categoría que tiene gastos asociados");
            }
            
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar la categoría");
            }
            
            return ['success' => true, 'message' => 'Categoría eliminada correctamente'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
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
            $stmt = $this->db->prepare("SELECT id, name, percentage FROM categories WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categoría: " . $e->getMessage());
            return false;
        }
    }
    
   public function isCategoryInUse($categoryId) {
        try {
            $query = "SELECT COUNT(*) as count FROM expenses WHERE category_id = :categoryId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ($result['count'] > 0);
        } catch (Exception $e) {
            error_log("Error al verificar categoría en uso: " . $e->getMessage());
            return true; // Por seguridad, asumir que está en uso si hay error
        }
    }

}
?>