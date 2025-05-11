<?php
// Incluir controladores necesarios
require_once 'controllers/IncomeController.php';
require_once 'controllers/ExpenseController.php';

// Configuración básica
$controller = $_GET['controller'] ?? 'income';
$action = $_GET['action'] ?? 'index';

// Manejo de mensajes
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);

// Inicializar controladores
$incomeController = new IncomeController();
$expenseController = new ExpenseController();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión Financiera</title>
    <link rel="stylesheet" href="views/css/styles.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Sistema de Gestión Financiera</h1>
            <nav class="nav-tabs">
                <a href="index.php?controller=income" class="<?= $controller == 'income' ? 'active' : '' ?>">Ingresos</a>
                <a href="index.php?controller=expense" class="<?= $controller == 'expense' ? 'active' : '' ?>">Gastos</a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message['success'] ? 'success' : 'danger' ?>">
                <?= htmlspecialchars($message['message']) ?>
            </div>
        <?php endif; ?>

        <div class="content">
            <?php
            try {
                switch ($controller) {
                    case 'income':
                        // Manejo de acciones para ingresos
                        switch ($action) {
                            case 'register':
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_STRING);
                                    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
                                    $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
                                    
                                    $_SESSION['message'] = $incomeController->registerIncome($month, $year, $value);
                                    header('Location: index.php?controller=income');
                                    exit;
                                }
                                break;
                                
                            case 'edit':
                                if (isset($_GET['month']) && isset($_GET['year'])) {
                                    $month = filter_input(INPUT_GET, 'month', FILTER_SANITIZE_STRING);
                                    $year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
                                    
                                    $income = $incomeController->getIncomeByMonthYear($month, $year);
                                    if (!$income) {
                                        $_SESSION['message'] = ['success' => false, 'message' => 'Ingreso no encontrado.'];
                                        header('Location: index.php?controller=income');
                                        exit;
                                    }
                                }
                                break;
                                
                            case 'update':
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_STRING);
                                    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
                                    $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
                                    
                                    $_SESSION['message'] = $incomeController->updateIncome($month, $year, $value);
                                    header('Location: index.php?controller=income');
                                    exit;
                                }
                                break;
                        }
                        
                        $incomes = $incomeController->getAllIncomes();
                        include 'views/incomes.php';
                        break;
                        
                    case 'expense':
                        // Manejo de acciones para gastos
                        switch ($action) {
                            case 'register':
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $category = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
                                    $month = filter_input(INPUT_POST, 'month', FILTER_SANITIZE_STRING);
                                    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);
                                    $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
                                    
                                    $_SESSION['message'] = $expenseController->registerExpense($category, $month, $year, $value);
                                    header('Location: index.php?controller=expense');
                                    exit;
                                }
                                break;
                                
                            case 'edit':
                                if (isset($_GET['id'])) {
                                    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                                    
                                    $expense = $expenseController->getExpenseById($id);
                                    if (!$expense) {
                                        $_SESSION['message'] = ['success' => false, 'message' => 'Gasto no encontrado.'];
                                        header('Location: index.php?controller=expense');
                                        exit;
                                    }
                                }
                                break;
                                
                            case 'update':
                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
                                    $category = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
                                    $value = filter_input(INPUT_POST, 'value', FILTER_VALIDATE_FLOAT);
                                    
                                    $_SESSION['message'] = $expenseController->updateExpense($id, $category, $value);
                                    header('Location: index.php?controller=expense');
                                    exit;
                                }
                                break;
                                
                            case 'delete':
                                if (isset($_GET['id'])) {
                                    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                                    
                                    $_SESSION['message'] = $expenseController->deleteExpense($id);
                                    header('Location: index.php?controller=expense');
                                    exit;
                                }
                                break;
                        }
                        
                        $expenses = $expenseController->getAllExpenses();
                        $categories = $expenseController->getAllCategories();
                        include 'views/expense.php';
                        break;
                        
                    default:
                        header('Location: index.php?controller=income');
                        exit;
                }
            } catch (PDOException $e) {
                die("Error de base de datos: " . $e->getMessage());
            } catch (Exception $e) {
                die("Error: " . $e->getMessage());
            }
            ?>
        </div>

        <div class="quick-actions">
            <a href="index.php?controller=income&action=register" class="btn btn-primary">Registrar Ingreso</a>
            <a href="index.php?controller=expense&action=register" class="btn btn-secondary">Registrar Gasto</a>
        </div>
    </div>
</body>
</html>