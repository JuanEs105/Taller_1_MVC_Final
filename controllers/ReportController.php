<?php
require_once 'models/drivers/Database.php';

class ReportController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function generateMonthlyReport($month, $year) {
        try {
            $reportId = $this->getReportId($month, $year);

            if (!$reportId) {
                 throw new Exception("No se encontró un reporte para " . htmlspecialchars($month, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . ".");
            }

            $income = $this->getMonthlyIncome($reportId);
            if ($income <= 0) {
                throw new Exception("No hay ingresos registrados para " . htmlspecialchars($month, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . ".");
            }

            $expenses = $this->getMonthlyExpenses($reportId);
            if (empty($expenses)) {
                throw new Exception("No hay gastos registrados para " . htmlspecialchars($month, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . ".");
            }

            $totalExpenses = array_sum(array_column($expenses, 'value'));
            $savings = $income - $totalExpenses;
            $savingsPercentage = ($income > 0) ? ($savings / $income) * 100 : 0;
            $categoryAnalysis = $this->analyzeCategories($expenses, $income);

            return [
                'success' => true,
                'data' => [
                    'month' => $month,
                    'year' => $year,
                    'income' => $income,
                    'expenses' => $expenses,
                    'totalExpenses' => $totalExpenses,
                    'savings' => $savings,
                    'savingsPercentage' => $savingsPercentage,
                    'categoryAnalysis' => $categoryAnalysis
                ]
            ];

        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getReportId($month, $year) {
        try {
             $validMonths = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                           'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $month = htmlspecialchars(trim($month), ENT_QUOTES, 'UTF-8');
            if (!in_array($month, $validMonths)) {
                 return false;
            }
            $year = filter_var($year, FILTER_VALIDATE_INT);
            if ($year === false || $year < 1900 || $year > 2100) {
                 return false;
            }

            $stmt = $this->db->prepare("SELECT id FROM reports WHERE month = :month AND year = :year LIMIT 1");
            $stmt->bindParam(':month', $month, PDO::PARAM_STR);
            $stmt->bindParam(':year', $year, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (PDOException $e) {
            error_log("Error PDO getting report ID: " . $e->getMessage());
            return false;
        }
    }

    private function getMonthlyIncome($reportId) {
        try {
             $reportId = filter_var($reportId, FILTER_VALIDATE_INT);
             if ($reportId === false || $reportId <= 0) {
                 return 0;
             }
            $stmt = $this->db->prepare("SELECT value FROM income WHERE idReport = :reportId LIMIT 1");
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (float)$result['value'] : 0;
        } catch (PDOException $e) {
             error_log("Error PDO getting monthly income: " . $e->getMessage());
             return 0;
        }
    }

    private function getMonthlyExpenses($reportId) {
        try {
             $reportId = filter_var($reportId, FILTER_VALIDATE_INT);
             if ($reportId === false || $reportId <= 0) {
                 return [];
             }
            $query = "SELECT b.value, c.name as category_name, c.percentage, c.id as idCategory
                      FROM bills b
                      JOIN categories c ON b.idCategory = c.id
                      WHERE b.idReport = :reportId";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
             error_log("Error PDO getting monthly expenses: " . $e->getMessage());
             return [];
        }
    }

    private function analyzeCategories($expenses, $income) {
        $analysis = [];
        $categoryTotals = [];

        foreach ($expenses as $expense) {
            $categoryId = $expense['idCategory'] ?? null;
            if ($categoryId === null) continue;

            if (!isset($categoryTotals[$categoryId])) {
                $categoryTotals[$categoryId] = [
                    'name' => $expense['category_name'],
                    'percentage_allowed' => (float)$expense['percentage'],
                    'total_spent' => 0
                ];
            }
            $categoryTotals[$categoryId]['total_spent'] += (float)$expense['value'];
        }

        foreach ($categoryTotals as $category) {
            $percentageSpent = ($income > 0) ? ($category['total_spent'] / $income) * 100 : 0;
            $analysis[] = [
                'name' => $category['name'],
                'percentage_allowed' => $category['percentage_allowed'],
                'total_spent' => $category['total_spent'],
                'percentage_spent' => $percentageSpent,
                'exceeded' => $percentageSpent > $category['percentage_allowed']
            ];
        }

        return $analysis;
    }
}