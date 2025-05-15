<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Category.php';

class CategoryController {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function registerCategory($name, $percentage) {
        try {
            // Validaciones básicas
            $name = trim($name);
            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido");
            }
            
            $percentage = floatval(str_replace(',', '.', $percentage));
            
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
            // Validaciones
            $name = trim($name);
            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido");
            }
            
            $percentage = floatval(str_replace(',', '.', $percentage));
            
            if (!is_numeric($percentage) || $percentage <= 0 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100");
            }
            
            // Verificar si la categoría existe
            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                throw new Exception("La categoría no existe");
            }
            
            // Verificar si el nombre ya está en uso por otra categoría
            $query = "SELECT id FROM categories WHERE name = :name AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                throw new Exception("Ya existe otra categoría con ese nombre");
            }
            
            // Actualizar la categoría
            $query = "UPDATE categories SET name = :name, percentage = :percentage WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar la categoría");
            }
            
            return [
                'success' => true, 
                'message' => 'Categoría actualizada correctamente'
            ];
        } catch (Exception $e) {
            return [
                'success' => false, 
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function deleteCategory($id) {
        try {
            // Primero verificar si la categoría existe
            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            if (!$stmt->fetch()) {
                throw new Exception("La categoría no existe");
            }
            
            // Verificar si la categoría está en uso (versión optimizada)
            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede eliminar: categoría tiene gastos asociados");
            }
            
            // Eliminar la categoría
            $query = "DELETE FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar la categoría");
            }
            
            return [
                'success' => true, 
                'message' => 'Categoría eliminada correctamente'
            ];
        } catch (Exception $e) {
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
            // Consulta optimizada con LIMIT 1 para mejor rendimiento
            $query = "SELECT 1 FROM bills WHERE idCategory = :categoryId LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Retorna true si encuentra al menos un gasto asociado
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log("Error al verificar categoría en uso: " . $e->getMessage());
            return true; // Por seguridad, asumir que está en uso si hay error
        }
    }
}