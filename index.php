<?php
require_once 'controllers/IncomeController.php';
require_once 'controllers/ExpenseController.php';
require_once 'controllers/CategoryController.php';
require_once 'controllers/ReportController.php';

// Obtener parámetros de URL de forma segura
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'income';
$action = isset($_GET['action']) ? strtolower($_GET['action']) : 'index';

// Validar controladores permitidos
$allowedControllers = ['income', 'expense', 'category', 'report'];
if (!in_array($controller, $allowedControllers)) {
    $controller = 'income';
}

// Inicializar controladores
$incomeController = new IncomeController();
$expenseController = new ExpenseController();
$categoryController = new CategoryController();
$reportController = new ReportController();

// Procesar mensajes de forma segura
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data:;">
    <title>Sistema de Gestión Financiera</title>
    <link rel="stylesheet" href="views/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container-fluid">
        <header class="header bg-primary text-white p-3 mb-4 rounded">
            <h1 class="text-center">Control Financiero</h1>
            <nav class="nav nav-pills nav-fill">
                <a href="index.php?controller=income" class="nav-link <?= $controller == 'income' ? 'active bg-white text-primary' : 'text-white' ?>">
                    <i class="fas fa-money-bill-wave"></i> Ingresos
                </a>
                <a href="index.php?controller=expense" class="nav-link <?= $controller == 'expense' ? 'active bg-white text-primary' : 'text-white' ?>">
                    <i class="fas fa-receipt"></i> Gastos
                </a>
                <a href="index.php?controller=category" class="nav-link <?= $controller == 'category' ? 'active bg-white text-primary' : 'text-white' ?>">
                    <i class="fas fa-tags"></i> Categorías
                </a>
                <a href="index.php?controller=report&action=form" class="nav-link <?= $controller == 'report' ? 'active bg-white text-primary' : 'text-white' ?>">
                    <i class="fas fa-chart-pie"></i> Reportes
                </a>
            </nav>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-dismissible <?= strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger' ?>">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <main class="content bg-light p-4 rounded">
            <?php
            switch ($controller) {
                case 'income':
                    $incomes = $incomeController->getAllIncomes();
                    
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $result = $incomeController->registerIncome(
                                htmlspecialchars($_POST['month']),
                                (int)$_POST['year'],
                                (float)$_POST['value']
                            );
                            header('Location: index.php?controller=income&message='.urlencode($result['message']));
                            exit;
                        } 
                        elseif ($action == 'update') {
                            $result = $incomeController->updateIncome(
                                htmlspecialchars($_POST['month']),
                                (int)$_POST['year'],
                                (float)$_POST['value']
                            );
                            header('Location: index.php?controller=income&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    
                    include 'views/incomes.php';
                    break;
                    
                case 'expense':
                    $categories = $expenseController->getAllCategories();
                    $expenses = $expenseController->getAllExpenses();
                    
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $result = $expenseController->registerExpense(
                                (int)$_POST['category'],
                                htmlspecialchars($_POST['month']),
                                (int)$_POST['year'],
                                (float)$_POST['value']
                            );
                            header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                            exit;
                        } 
                        elseif ($action == 'update') {
                            $result = $expenseController->updateExpense(
                                (int)$_POST['id'],
                                (int)$_POST['category'],
                                (float)$_POST['value']
                            );
                            header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $expenseToEdit = $expenseController->getExpenseById((int)$_GET['id']);
                        if (!$expenseToEdit) {
                            header('Location: index.php?controller=expense&message=Gasto no encontrado');
                            exit;
                        }
                    }
                    
                    include 'views/expense.php';
                    break;
                    
                case 'category':
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            $result = $categoryController->registerCategory(
                                htmlspecialchars($_POST['name']),
                                (float)$_POST['percentage']
                            );
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        } 
                        elseif ($action == 'update') {
                            $result = $categoryController->updateCategory(
                                (int)$_POST['id'],
                                htmlspecialchars($_POST['name']),
                                (float)$_POST['percentage']
                            );
                            header('Location: index.php?controller=category&message='.urlencode($result['message']));
                            exit;
                        }
                    }
                    
                    if ($action == 'delete' && isset($_GET['id'])) {
                        $result = $categoryController->deleteCategory((int)$_GET['id']);
                        header('Location: index.php?controller=category&message='.urlencode($result['message']));
                        exit;
                    }
                    
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $category = $categoryController->getCategoryById((int)$_GET['id']);
                        if (!$category) {
                            header('Location: index.php?controller=category&message=Categoría no encontrada');
                            exit;
                        }
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
                                $month = htmlspecialchars($_GET['month']);
                                $year = (int)$_GET['year'];
                                
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
                    
                default:
                    header('Location: index.php?controller=income');
                    exit;
            }
            ?>
        </main>

        <div class="quick-actions fixed-bottom mb-4 text-center">
            <div class="btn-group" role="group">
                <a href="index.php?controller=income&action=register" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nuevo Ingreso
                </a>
                <a href="index.php?controller=expense&action=register" class="btn btn-secondary">
                    <i class="fas fa-minus-circle"></i> Nuevo Gasto
                </a>
                <a href="index.php?controller=category&action=register" class="btn btn-info">
                    <i class="fas fa-tag"></i> Nueva Categoría
                </a>
                <a href="index.php?controller=report&action=form" class="btn btn-warning">
                    <i class="fas fa-file-alt"></i> Generar Reporte
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para confirmaciones
        function confirmAction(message) {
            return confirm(message || '¿Está seguro de realizar esta acción?');
        }
        
        // Mostrar mensajes temporales
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas automáticamente después de 5 segundos
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Validación general de formularios
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const numberInputs = form.querySelectorAll('input[type="number"]');
                    numberInputs.forEach(input => {
                        if (input.value && parseFloat(input.value) <= 0) {
                            e.preventDefault();
                            alert('El valor debe ser mayor a cero');
                            input.focus();
                            return false;
                        }
                    });
                    return true;
                });
            });
        });
    </script>
</body>
</html>