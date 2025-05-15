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

    public function checkReportExists($month, $year) {
        try {
            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $month = htmlspecialchars(trim($month));
            if (!in_array($month, $validMonths)) {
                 error_log("Intento de verificar reporte con mes inválido: " . $month);
                 return false;
            }
            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                error_log("Intento de verificar reporte con año inválido: " . $year);
                return false;
            }

            $query = "SELECT id FROM reports WHERE month = :month AND year = :year";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':month', $month, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error PDO al verificar reporte: " . $e->getMessage());
            return false;
        }
    }

    public function createReport($month, $year) {
         try {
            $query = "INSERT INTO reports (month, year) VALUES (:month, :year)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':month', $month, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);

            if ($stmt->execute()) {
                 return $this->db->lastInsertId();
            } else {
                 $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al crear reporte: " . $errorInfo[2]);
                 return false;
            }

         } catch (PDOException $e) {
             error_log("Error PDO al crear reporte: " . $e->getMessage());
             return false;
         }
    }

    public function registerIncome($month, $year, $value) {
        $isAjaxRequest = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
                         || (!empty($_POST['ajax']) && $_POST['ajax'] == 'true');

        try {
            $month = trim($month);
            if (empty($month)) {
                throw new Exception('El mes es obligatorio.');
            }
            $year = trim($year);
            if (empty($year)) {
                throw new Exception('El año es obligatorio.');
            }
            $value = trim($value);
            if (empty($value)) {
                throw new Exception('El valor del ingreso es obligatorio.');
            }

            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            if (!in_array($month, $validMonths)) {
                throw new Exception('Mes no válido.');
            }

            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                throw new Exception('Año no válido. Debe estar entre 1900 y 2100.');
            }

            $value = str_replace(',', '.', $value);
            if (!is_numeric($value)) {
                throw new Exception('El valor del ingreso debe ser numérico.');
            }
            $value = floatval($value);
            if ($value <= 0) {
                throw new Exception('El valor del ingreso debe ser mayor a cero.');
            }

            $reportData = $this->checkReportExists($month, $year);
            $reportId = null;

            if ($reportData) {
                $reportId = $reportData['id'];
                $queryCheckIncome = "SELECT id FROM income WHERE idReport = :reportId LIMIT 1";
                $stmtCheckIncome = $this->db->prepare($queryCheckIncome);
                $stmtCheckIncome->bindParam(':reportId', $reportId, PDO::PARAM_INT);
                $stmtCheckIncome->execute();

                if ($stmtCheckIncome->fetch()) {
                    throw new Exception('Ya existe un ingreso registrado para ' . $month . ' de ' . $year . '.');
                }
            } else {
                $reportId = $this->createReport($month, $year);
                if (!$reportId) {
                    throw new Exception('Error interno al preparar el reporte mensual.');
                }
            }

            $query = "INSERT INTO income (value, idReport) VALUES (:value, :reportId)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $result = [
                    'success' => true,
                    'message' => 'Ingreso registrado correctamente.',
                    'id' => $this->db->lastInsertId()
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al ejecutar INSERT income: " . $errorInfo[2]);
                throw new Exception('Error al ejecutar la consulta de registro de ingreso.');
            }

        } catch (PDOException $e) {
             error_log("Error PDO en registerIncome: " . $e->getMessage());
            $result = [
                'success' => false,
                'message' => 'Error de base de datos al registrar el ingreso.'
            ];
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        if ($isAjaxRequest) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } else {
            return $result;
        }
    }

    public function updateIncome($month, $year, $value) {
        try {
            $month = trim($month);
             if (empty($month)) { throw new Exception('El mes es necesario para identificar el ingreso.'); }
            $year = trim($year);
             if (empty($year)) { throw new Exception('El año es necesario para identificar el ingreso.'); }
            $value = trim($value);
             if (empty($value)) { throw new Exception('El valor del ingreso es obligatorio.'); }

            $value = str_replace(',', '.', $value);
            if (!is_numeric($value)) {
                throw new Exception('El valor del ingreso debe ser numérico.');
            }
            $value = floatval($value);
            if ($value <= 0) {
                throw new Exception('El valor del ingreso no puede ser menor o igual a cero.');
            }

            $reportData = $this->checkReportExists($month, $year);

            if (!$reportData) {
                throw new Exception('No se encontró un ingreso registrado para ' . $month . ' de ' . $year . '.');
            }

            $reportId = $reportData['id'];

            $queryCheck = "SELECT id FROM income WHERE idReport = :reportId LIMIT 1";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $incomeData = $stmtCheck->fetch();

            if (!$incomeData) {
                throw new Exception('Error: Reporte encontrado, pero no se encontró el ingreso asociado.');
            }

            $query = "UPDATE income SET value = :value WHERE idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':value', $value, PDO::PARAM_STR);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                 if ($stmt->rowCount() > 0) {
                    $result = [
                        'success' => true,
                        'message' => 'Ingreso actualizado correctamente.'
                    ];
                 } else {
                     $result = [
                        'success' => true,
                        'message' => 'Ingreso actualizado (sin cambios detectados).'
                     ];
                 }

            } else {
                 $errorInfo = $stmt->errorInfo();
                 error_log("Error DB al ejecutar UPDATE income: " . $errorInfo[2]);
                throw new Exception('Error al actualizar el ingreso.');
            }

        } catch (PDOException $e) {
             error_log("Error PDO en updateIncome: " . $e->getMessage());
            $result = [
                'success' => false,
                'message' => 'Error de base de datos al actualizar el ingreso.'
            ];
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    public function getAllIncomes() {
        try {
            $query = "SELECT i.id, i.value, r.month, r.year
                      FROM income i
                      JOIN reports r ON i.idReport = r.id
                      ORDER BY r.year DESC,
                      FIELD(r.month, 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'), r.id DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error PDO al obtener ingresos: " . $e->getMessage());
            return [];
        }
    }

    public function getIncomeByMonthYear($month, $year) {
        try {
            $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $month = htmlspecialchars(trim($month));
            if (!in_array($month, $validMonths)) {
                 error_log("Intento de obtener ingreso con mes inválido: " . $month);
                 return false;
            }
            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                error_log("Intento de obtener ingreso con año inválido: " . $year);
                return false;
            }

            $query = "SELECT i.id, i.value, r.month, r.year
                      FROM income i
                      JOIN reports r ON i.idReport = r.id
                      WHERE r.month = :month AND r.year = :year";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':month', $month, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error PDO al obtener ingreso por mes/año: " . $e->getMessage());
            return false;
        }
    }
}