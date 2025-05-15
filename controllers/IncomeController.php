<?php
require_once 'models/drivers/Database.php';
require_once 'models/entities/Income.php'; // Asegúrate que esta ruta es correcta
require_once 'models/entities/Report.php';   // Asegúrate que esta ruta es correcta

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

        return $stmt->fetch(PDO::FETCH_ASSOC); // Devolver como asociativo
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
     * Registra un nuevo ingreso con validaciones mejoradas y manejo de AJAX
     */
    public function registerIncome($month, $year, $value) {
        // Detectar si es una solicitud AJAX
        $isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
                         || (!empty($_POST['ajax']) && $_POST['ajax'] == 'true');

        try {
            // Validaciones
            if (empty($month)) {
                throw new Exception('El mes es obligatorio.');
            }
            if (empty($year)) {
                throw new Exception('El año es obligatorio.');
            }
            if (empty($value)) {
                throw new Exception('El valor del ingreso es obligatorio.');
            }
            if (!is_numeric($value)) {
                throw new Exception('El valor del ingreso debe ser numérico.');
            }

            $value = (float)$value;
            if ($value <= 0) {
                throw new Exception('El valor del ingreso debe ser mayor a cero.');
            }

            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            if (!in_array($month, $validMonths)) {
                throw new Exception('Mes no válido.');
            }

            if (!is_numeric($year) || $year < 2000 || $year > 2100) {
                throw new Exception('Año no válido. Debe estar entre 2000 y 2100.');
            }

            // Verificar si ya existe un reporte para este mes y año
            $reportData = $this->checkReportExists($month, $year);
            $reportId = null;

            if ($reportData) {
                $reportId = $reportData['id'];
                // Verificar si ya existe un ingreso para este reporte
                $queryCheckIncome = "SELECT id FROM income WHERE idReport = :reportId";
                $stmtCheckIncome = $this->db->prepare($queryCheckIncome);
                $stmtCheckIncome->bindParam(':reportId', $reportId);
                $stmtCheckIncome->execute();

                if ($stmtCheckIncome->fetch()) {
                    throw new Exception('Ya existe un ingreso registrado para este mes y año.');
                }
            } else {
                // Crear nuevo reporte si no existe
                $reportId = $this->createReport($month, $year);
                if (!$reportId) {
                    throw new Exception('Error al crear el reporte mensual.');
                }
            }

            // Registrar el ingreso
            $this->db->beginTransaction();

            $query = "INSERT INTO income (value, idReport) VALUES (:value, :reportId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR); // PDO::PARAM_STR para números puede ser más seguro con algunos drivers/versiones
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $this->db->commit();
                $result = [
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente.',
                    'id' => $this->db->lastInsertId()
                ];
            } else {
                $this->db->rollBack();
                throw new Exception('Error al ejecutar la consulta de registro de ingreso.');
            }

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $result = [
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) { // Asegurarse de hacer rollback si la transacción se inició
                $this->db->rollBack();
            }
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // Si es una solicitud AJAX, devolver JSON y salir
        if ($isAjaxRequest) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            // Para solicitudes no AJAX, devolver el array para que index.php lo maneje (usualmente para redirección)
            return $result;
        }
    }

    /**
     * Actualiza un ingreso existente
     */
    public function updateIncome($month, $year, $value) {
        // Detectar si es una solicitud AJAX (opcional, si también quieres AJAX para actualizar)
        // $isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        try {
            // Validaciones
            if (empty($month) || empty($year)) {
                throw new Exception('El mes y el año son necesarios para identificar el ingreso a actualizar.');
            }
            if (!is_numeric($value) || (float)$value <= 0) {
                throw new Exception('El valor del ingreso no puede ser menor o igual a cero y debe ser numérico.');
            }
            $value = (float)$value;

            // Verificar si existe un reporte para este mes y año
            $reportData = $this->checkReportExists($month, $year);

            if (!$reportData) {
                throw new Exception('No existe un reporte (y por tanto, ingreso) registrado para este mes y año para actualizar.');
            }

            $reportId = $reportData['id'];

            // Verificar si existe un ingreso para este reporte
            $queryCheck = "SELECT id FROM income WHERE idReport = :reportId";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':reportId', $reportId);
            $stmtCheck->execute();
            $incomeData = $stmtCheck->fetch();

            if (!$incomeData) {
                throw new Exception('No existe un ingreso registrado para este mes y año para actualizar.');
            }

            // Actualizar el ingreso
            $query = "UPDATE income SET value = :value WHERE idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $result = [
                    'success' => true,
                    'message' => 'Ingreso actualizado correctamente.'
                ];
            } else {
                throw new Exception('Error al actualizar el ingreso.');
            }

        } catch (PDOException $e) {
            $result = [
                'success' => false,
                'message' => 'Error de base de datos al actualizar: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        // Similar al registro, si quieres AJAX para actualizar:
        // if ($isAjaxRequest) {
        //     header('Content-Type: application/json');
        //     echo json_encode($result);
        //     exit;
        // } else {
            return $result;
        // }
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
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'), r.id DESC"; // Añadido r.id DESC para un orden más consistente si hay múltiples por mes/año (no debería con la lógica actual)
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // En un entorno de producción, loguear el error en lugar de (o además de) mostrarlo.
            error_log("Error al obtener ingresos: " . $e->getMessage());
            // Podrías devolver un array vacío o lanzar una excepción que el controlador principal maneje.
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
            error_log("Error al obtener ingreso por mes/año: " . $e->getMessage());
            return false; // O manejar el error de otra forma
        }
    }
}
?>