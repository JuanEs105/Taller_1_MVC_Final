<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Category.php';

class CategoryController {
    private $db;

    // Definir constantes para validación de nombre
    const MIN_NAME_LENGTH = 2;
    const MAX_NAME_LENGTH = 50;
    // Permitir letras (acentuadas y ñ), números, espacios, guiones y apóstrofes
    // El modificador 'u' es para soporte UTF-8
    const VALID_NAME_PATTERN = '/^[a-zA-Z0-9\sáéíóúÁÉÍÓÚñÑ\'\-,]+$/u';


    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Registra una nueva categoría.
     *
     * @param string $name Nombre de la categoría.
     * @param float|string $percentage Porcentaje asociado a la categoría.
     * @return array Resultado de la operación (success, message).
     */
    public function registerCategory($name, $percentage) {
        try {
            // === Validaciones ===
            $name = trim($name);

            // Validación de nombre vacío
            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido.");
            }

            // Validación de longitud del nombre
            // Usamos mb_strlen para contar correctamente caracteres multi-byte (UTF-8)
            if (mb_strlen($name, 'UTF-8') < self::MIN_NAME_LENGTH || mb_strlen($name, 'UTF-8') > self::MAX_NAME_LENGTH) {
                 throw new Exception("El nombre de la categoría debe tener entre " . self::MIN_NAME_LENGTH . " y " . self::MAX_NAME_LENGTH . " caracteres.");
            }

            // Validación de caracteres permitidos en el nombre
            // Usamos preg_match con el patrón y el modificador 'u'
            if (!preg_match(self::VALID_NAME_PATTERN, $name)) {
                 throw new Exception("El nombre de la categoría contiene caracteres no permitidos.");
            }

            // Sanitizar porcentaje (reemplazar coma por punto si es necesario para asegurar floatval correcto)
            $percentage = str_replace(',', '.', $percentage);

            // Validación del porcentaje
            if (!is_numeric($percentage)) {
                 throw new Exception("El porcentaje debe ser un número válido.");
            }

            $percentage = floatval($percentage); // Convertir a float después de verificar que es numérico

            // Validar rango del porcentaje (coincide con el front-end min="0.01")
            if ($percentage < 0.01 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100.");
            }

            // Verificar si ya existe una categoría con ese nombre (sensible a mayúsculas/minúsculas por defecto de MySQL)
            // Si necesitas que no sea sensible, usa LOWER(name) en la consulta y LOWER(:name) en el bind
            $query = "SELECT id FROM categories WHERE name = :name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("Ya existe una categoría con ese nombre.");
            }

            // === Registrar la nueva categoría ===
            $query = "INSERT INTO categories (name, percentage) VALUES (:name, :percentage)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':percentage', $percentage);

            // Ejecutar la inserción
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Categoría registrada correctamente.'
                ];
            } else {
                // Error en la ejecución de la consulta
                // Puedes loguear $stmt->errorInfo() para depuración si es necesario
                throw new Exception("Error desconocido al registrar la categoría.");
            }
        } catch (Exception $e) {
            // Capturar excepciones y retornar el mensaje de error
            error_log("Error en registerCategory: " . $e->getMessage()); // Loguear el error en el servidor
            return [
                'success' => false,
                'message' => $e->getMessage() // Retornar el mensaje específico de la validación o error
            ];
        }
    }

    /**
     * Actualiza una categoría existente.
     *
     * @param int $id ID de la categoría a actualizar.
     * @param string $name Nuevo nombre de la categoría.
     * @param float|string $percentage Nuevo porcentaje asociado.
     * @return array Resultado de la operación (success, message).
     */
    public function updateCategory($id, $name, $percentage) {
        try {
            // === Validaciones ===
            // Validar que el ID es un entero positivo válido
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception("ID de categoría inválido para actualizar.");
            }

            $name = trim($name);

            // Validación de nombre vacío
            if (empty($name)) {
                throw new Exception("El nombre de la categoría es requerido.");
            }

             // Validación de longitud del nombre
            if (mb_strlen($name, 'UTF-8') < self::MIN_NAME_LENGTH || mb_strlen($name, 'UTF-8') > self::MAX_NAME_LENGTH) {
                 throw new Exception("El nombre de la categoría debe tener entre " . self::MIN_NAME_LENGTH . " y " . self::MAX_NAME_LENGTH . " caracteres.");
            }

            // Validación de caracteres permitidos en el nombre
             if (!preg_match(self::VALID_NAME_PATTERN, $name)) {
                 throw new Exception("El nombre de la categoría contiene caracteres no permitidos.");
            }

            // Sanitizar porcentaje
             $percentage = str_replace(',', '.', $percentage);

             // Validación del porcentaje
            if (!is_numeric($percentage)) {
                 throw new Exception("El porcentaje debe ser un número válido.");
            }

            $percentage = floatval($percentage);

            if ($percentage < 0.01 || $percentage > 100) {
                throw new Exception("El porcentaje debe ser un número entre 0.01 y 100.");
            }

            // Verificar si la categoría con este ID existe
            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                throw new Exception("La categoría a actualizar no existe.");
            }

            // Verificar si el nuevo nombre ya está en uso por OTRA categoría
            $query = "SELECT id FROM categories WHERE name = :name AND id != :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->fetch()) {
                throw new Exception("Ya existe otra categoría con ese nombre.");
            }

            // === Actualizar la categoría ===
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

    /**
     * Elimina una categoría por su ID.
     *
     * @param int $id ID de la categoría a eliminar.
     * @return array Resultado de la operación (success, message).
     */
    public function deleteCategory($id) {
        try {
            // Validar que el ID es un entero positivo válido
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                throw new Exception("ID de categoría inválido para eliminar.");
            }

            // Primero verificar si la categoría existe
            $query = "SELECT id FROM categories WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if (!$stmt->fetch()) {
                throw new Exception("La categoría a eliminar no existe.");
            }

            // Verificar si la categoría está en uso antes de eliminar
            if ($this->isCategoryInUse($id)) {
                throw new Exception("No se puede eliminar: la categoría tiene gastos asociados.");
            }

            // === Eliminar la categoría ===
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

    /**
     * Obtiene todas las categorías.
     *
     * @return array Lista de categorías o array vacío en caso de error.
     */
    public function getAllCategories() {
        try {
            $stmt = $this->db->query("SELECT id, name, percentage FROM categories ORDER BY name");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una categoría por su ID.
     *
     * @param int $id ID de la categoría.
     * @return array|false Array asociativo de la categoría o false si no se encuentra/error.
     */
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

    /**
     * Verifica si una categoría está siendo utilizada por algún gasto.
     *
     * @param int $categoryId ID de la categoría.
     * @return bool True si está en uso, false si no, true en caso de error por seguridad.
     */
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