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
    $controller = 'income'; // Default controller
}

// Inicializar controladores
$incomeController = new IncomeController();
$expenseController = new ExpenseController();
$categoryController = new CategoryController();
$reportController = new ReportController();

// Procesar mensajes de forma segura (para non-AJAX redirects)
$message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
$message_type = 'info'; // Default type, can be overridden based on context if needed

if ($message_text) {
    // Basic check for error messages to style them differently
    if (stripos($message_text, 'error') !== false || stripos($message_text, 'no encontrado') !== false) {
        $message_type = 'danger';
    } elseif (stripos($message_text, 'correctamente') !== false) {
        $message_type = 'success';
    }
}


// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
// Nota: La directiva CSP se establece en la sección <head> del HTML para mayor flexibilidad.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net 'unsafe-inline'; img-src 'self' data:;">
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

        <?php if ($message_text): ?>
            <div id="globalMessage" class="alert alert-dismissible alert-<?= $message_type ?>">
                <?= $message_text ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <main class="content bg-light p-4 rounded">
            <?php
            switch ($controller) {
                case 'income':
                    $incomes = $incomeController->getAllIncomes(); // Get all incomes for display
                    $isEditForm = false; // Initialize $isEditForm
                    $income = null; // Initialize $income for the form

                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        if ($action == 'register') {
                            // The IncomeController->registerIncome will handle AJAX responses and exit.
                            // If it doesn't exit, it means it's a non-AJAX request or an error before AJAX handling.
                            $result = $incomeController->registerIncome(
                                htmlspecialchars($_POST['month']),
                                (int)$_POST['year'],
                                (float)$_POST['value']
                            );
                            // This redirection is for non-AJAX fallback
                            if (isset($result) && !$result['success']) { // Redirect only if there's an error for non-ajax
                                header('Location: index.php?controller=income&message='.urlencode($result['message']));
                                exit;
                            } elseif (isset($result) && $result['success']) { // Non-ajax success
                                 header('Location: index.php?controller=income&message='.urlencode($result['message']));
                                exit;
                            }
                            // If $result is not set, it means AJAX handled it and exited in the controller.
                        }
                        elseif ($action == 'update') {
                            // AJAX is not implemented for update in this example, so it works as before.
                            $result = $incomeController->updateIncome(
                                htmlspecialchars($_POST['month']), // Assuming month/year are PKs and POSTed
                                (int)$_POST['year'],
                                (float)$_POST['value']
                            );
                            header('Location: index.php?controller=income&message='.urlencode($result['message']));
                            exit;
                        }
                    } elseif ($_SERVER['REQUEST_METHOD'] == 'GET' && $action == 'edit') {
                        if (isset($_GET['month']) && isset($_GET['year'])) {
                            $isEditForm = true;
                            $income = $incomeController->getIncomeByMonthYear(
                                htmlspecialchars($_GET['month']),
                                (int)$_GET['year']
                            );
                            if (!$income) {
                                header('Location: index.php?controller=income&message='.urlencode('Error: Ingreso a modificar no encontrado.'));
                                exit;
                            }
                        } else {
                             header('Location: index.php?controller=income&message='.urlencode('Error: Faltan parámetros para modificar el ingreso.'));
                            exit;
                        }
                    }
                    // Pass $incomes, $isEditForm, and $income to the view
                    // views/incomes.php will include views/forms/income_form.php
                    include 'views/incomes.php';
                    break;
                    
                case 'expense':
                    $categories = $expenseController->getAllCategories();
                    $expenses = $expenseController->getAllExpenses();
                    $expenseToEdit = null; // Initialize
                    
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
                    
                    if ($action == 'delete' && isset($_GET['id'])) {
                        $result = $expenseController->deleteExpense((int)$_GET['id']);
                        header('Location: index.php?controller=expense&message='.urlencode($result['message']));
                        exit;
                    }
                    
                    if ($action == 'edit' && isset($_GET['id'])) {
                        $expenseToEdit = $expenseController->getExpenseById((int)$_GET['id']);
                        if (!$expenseToEdit) {
                            header('Location: index.php?controller=expense&message=Gasto no encontrado');
                            exit;
                        }
                    }
                    
                    include 'views/expense.php'; // Pass $categories, $expenses, $expenseToEdit
                    break;
                    
                case 'category':
                    $category = null; // Initialize
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
                    include 'views/categories.php'; // Pass $categories, $category (for editing)
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
                                    include 'views/report.php'; // Pass $reportData
                                } else {
                                    $error = $result['message'];
                                    include 'views/report.php'; // Pass $error
                                }
                            } else {
                                // If parameters are missing, redirect to form with a message
                                header('Location: index.php?controller=report&action=form&message='.urlencode('Error: Mes y año son requeridos para generar el reporte.'));
                                exit;
                            }
                            break;
                            
                        default:
                            header('Location: index.php?controller=report&action=form');
                            exit;
                    }
                    break;
                    
                default:
                    // Fallback to income controller if no valid controller is specified
                    header('Location: index.php?controller=income');
                    exit;
            }
            ?>
        </main>

        <div class="quick-actions fixed-bottom mb-4 text-center">
            <div class="btn-group" role="group">
                <a href="index.php?controller=income" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nuevo Ingreso
                </a>
                 <a href="index.php?controller=expense" class="btn btn-secondary">
                    <i class="fas fa-minus-circle"></i> Nuevo Gasto
                </a>
                <a href="index.php?controller=category" class="btn btn-info">
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
        // Función para confirmaciones (si la necesitas globalmente)
        function confirmAction(message) {
            return confirm(message || '¿Está seguro de realizar esta acción?');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas globales automáticamente después de 5 segundos
            const globalAlert = document.getElementById('globalMessage');
            if (globalAlert) {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(globalAlert);
                    bsAlert.close();
                }, 5000);
            }
            
            // Validación general de formularios (para formularios que NO usan AJAX)
            // The AJAX income form has its own more specific validation + AJAX handling.
            document.querySelectorAll('form:not(#incomeForm)').forEach(form => { // Exclude incomeForm if it's fully handled by AJAX
                form.addEventListener('submit', function(e) {
                    let formIsValid = true;
                    const numberInputs = form.querySelectorAll('input[type="number"]');
                    numberInputs.forEach(input => {
                        if (input.value && parseFloat(input.value) <= 0) {
                            // This basic alert is fine for non-AJAX forms
                            alert('El valor debe ser un número mayor a cero.');
                            input.focus();
                            formIsValid = false;
                        }
                    });
                    if (!formIsValid) {
                         e.preventDefault();
                    }
                    return formIsValid;
                });
            });

             // Specific handling for incomeForm if it exists and is NOT an edit form
            const incomeForm = document.getElementById('incomeForm');
            if (incomeForm) {
                const isEditForm = incomeForm.action.includes('action=update'); // A way to check if it's an edit form
                
                if (!isEditForm) {
                    // The AJAX submission logic for incomeForm is now expected to be in income_form.php
                    // This general validation can be a fallback or complement.
                    // If income_form.php handles its own validation entirely before fetch, this might be redundant for that specific form.
                } else {
                     // For edit form (non-AJAX), ensure basic validation
                    incomeForm.addEventListener('submit', function(e) {
                        let formIsValid = true;
                        const valueInput = incomeForm.querySelector('#value'); // Assuming ID 'value'
                        if (valueInput && (isNaN(parseFloat(valueInput.value)) || parseFloat(valueInput.value) <= 0)) {
                            alert('El valor del ingreso debe ser un número mayor a cero.');
                            valueInput.focus();
                            formIsValid = false;
                        }
                        // Add other non-AJAX validations for edit form if needed
                        if (!formIsValid) {
                            e.preventDefault();
                        }
                        return formIsValid;
                    });
                }
            }
        });
    </script>
</body>
</html>