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
            // 1. Verificar si el reporte ya existe
            $reportId = $this->getReportId($month, $year);
            
            // 2. Obtener ingresos del mes
            $income = $this->getMonthlyIncome($reportId);
            if ($income <= 0) {
                throw new Exception("No hay ingresos registrados para $month $year");
            }
            
            // 3. Obtener gastos del mes
            $expenses = $this->getMonthlyExpenses($reportId);
            if (empty($expenses)) {
                throw new Exception("No hay gastos registrados para $month $year");
            }
            
            // 4. Calcular totales y análisis
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
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function getReportId($month, $year) {
        $stmt = $this->db->prepare("SELECT id FROM reports WHERE month = :month AND year = :year");
        $stmt->bindParam(':month', $month);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['id'] : null;
    }
    
    private function getMonthlyIncome($reportId) {
        $stmt = $this->db->prepare("SELECT value FROM income WHERE idReport = :reportId");
        $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (float)$result['value'] : 0;
    }
    
    private function getMonthlyExpenses($reportId) {
        $query = "SELECT b.value, c.name as category_name, c.percentage 
                  FROM bills b
                  JOIN categories c ON b.idCategory = c.id
                  WHERE b.idReport = :reportId";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reportId', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function analyzeCategories($expenses, $income) {
        $analysis = [];
        $categoryTotals = [];
        
        // Calcular total por categoría
        foreach ($expenses as $expense) {
            $categoryId = $expense['idCategory'] ?? null;
            if (!isset($categoryTotals[$categoryId])) {
                $categoryTotals[$categoryId] = [
                    'name' => $expense['category_name'],
                    'percentage_allowed' => (float)$expense['percentage'],
                    'total_spent' => 0
                ];
            }
            $categoryTotals[$categoryId]['total_spent'] += (float)$expense['value'];
        }
        
        // Analizar cada categoría
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
?>