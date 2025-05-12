<?php
require_once 'controllers/IncomeController.php';
require_once 'controllers/ExpenseController.php';
require_once 'controllers/CategoryController.php';
require_once 'controllers/ReportController.php';

// Obtener parámetros de URL
$controller = $_GET['controller'] ?? 'income';
$action = $_GET['action'] ?? 'index';

// Inicializar controladores
$incomeController = new IncomeController();
$expenseController = new ExpenseController();
$categoryController = new CategoryController();
$reportController = new ReportController();

// Procesar mensajes
$message = $_GET['message'] ?? null;
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
            <h1>Control Financiero</h1>
            <nav class="nav-tabs">
                <a href="index.php?controller=income" class="<?= $controller == 'income' ? 'active' : '' ?>">Ingresos</a>
                <a href="index.php?controller=expense" class="<?= $controller == 'expense' ? 'active' : '' ?>">Gastos</a>
                <a href="index.php?controller=category" class="<?= $controller == 'category' ? 'active' : '' ?>">Categorías</a>
                <a href="index.php?controller=report&action=form" class="<?= $controller == 'report' ? 'active' : '' ?>">Reportes</a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <main class="content">
            <?php
            switch ($controller) {
                case 'income':
                    // Lógica para ingresos
                    include 'views/incomes.php';
                    break;
                    
                case 'expense':
                    // Lógica para gastos
                    include 'views/expense.php';
                    break;
                    
                case 'category':
                    // Manejo de categorías
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $name = $_POST['name'] ?? '';
                            $percentage = (float)($_POST['percentage'] ?? 0);
                            
                            $result = $categoryController->registerCategory($name, $percentage);
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        } 
                        elseif ($action == 'update') {
                            $id = (int)($_POST['id'] ?? 0);
                            $name = $_POST['name'] ?? '';
                            $percentage = (float)($_POST['percentage'] ?? 0);
                            
                            $result = $categoryController->updateCategory($id, $name, $percentage);
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    
                    if ($action == 'delete' && isset($_GET['id'])) {
                        $id = (int)$_GET['id'];
                        $result = $categoryController->deleteCategory($id);
                        header('Location: index.php?controller=category&message='.urlencode($result['message']));
                        exit;
                    }
                    
                    // Verificación adicional para edición
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $id = (int)$_GET['id'];
                        if ($categoryController->isCategoryInUse($id)) {
                            header('Location: index.php?controller=category&message='.urlencode('No se puede editar: categoría tiene gastos asociados'));
                            exit;
                        }
                        $category = $categoryController->getCategoryById($id);
                    }
                    
                    $categories = $categoryController->getAllCategories();
                    include 'views/categories.php';
                    break;
                    
                case 'report':
    switch ($action) {
        case 'form':
            include 'views/forms/report_form.php';
            break;
            
        case 'generate':
            if (isset($_GET['month']) && isset($_GET['year'])) {
                $month = $_GET['month'];
                $year = (int)$_GET['year'];
                
                $reportController = new ReportController();
                $result = $reportController->generateMonthlyReport($month, $year);
                
                if ($result['success']) {
                    $reportData = $result['data'];
                    include 'views/report.php';
                } else {
                    $error = $result['message'];
                    include 'views/report.php';
                }
            }
            break;
            
        default:
            header('Location: index.php?controller=report&action=form');
            exit;
    }
    break;
            }
            ?>
        </main>

        <div class="quick-actions">
            <a href="index.php?controller=income&action=register" class="btn btn-primary">Nuevo Ingreso</a>
            <a href="index.php?controller=expense&action=register" class="btn btn-secondary">Nuevo Gasto</a>
            <a href="index.php?controller=category&action=register" class="btn btn-info">Nueva Categoría</a>
            <a href="index.php?controller=report&action=form" class="btn btn-warning">Generar Reporte</a>
        </div>
    </div>
</body>
</html>