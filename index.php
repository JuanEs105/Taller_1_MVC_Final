<?php
require_once 'controllers/IncomeController.php';

// Definir controlador y acción por defecto
$controller = isset($_GET['controller']) ? $_GET['controller'] : 'income';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Manejar las rutas
switch ($controller) {
    case 'income':
        $incomeController = new IncomeController();
        
        switch ($action) {
            case 'index':
                // Obtener todos los ingresos
                $incomes = $incomeController->getAllIncomes();
                include 'views/incomes.php';
                break;
                
            case 'register':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $month = $_POST['month'];
                    $year = $_POST['year'];
                    $value = $_POST['value'];
                    
                    $message = $incomeController->registerIncome($month, $year, $value);
                    
                    // Guardar mensaje en sesión
                    $_SESSION['message'] = $message;
                    
                    // Redirigir para evitar reenvío del formulario
                    header('Location: index.php');
                    exit;
                }
                break;
                
            case 'edit':
                if (isset($_GET['month']) && isset($_GET['year'])) {
                    $month = $_GET['month'];
                    $year = $_GET['year'];
                    
                    $income = $incomeController->getIncomeByMonthYear($month, $year);
                    
                    if (!$income) {
                        $_SESSION['message'] = ['success' => false, 'message' => 'Ingreso no encontrado.'];
                        header('Location: index.php');
                        exit;
                    }
                    
                    $incomes = $incomeController->getAllIncomes();
                    include 'views/incomes.php';
                } else {
                    header('Location: index.php');
                    exit;
                }
                break;
                
            case 'update':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $month = $_POST['month'];
                    $year = $_POST['year'];
                    $value = $_POST['value'];
                    
                    $message = $incomeController->updateIncome($month, $year, $value);
                    
                    // Guardar mensaje en sesión
                    $_SESSION['message'] = $message;
                    
                    // Redirigir para evitar reenvío del formulario
                    header('Location: index.php');
                    exit;
                }
                break;
                
            default:
                header('Location: index.php');
                exit;
        }
        break;
        
    default:
        // Por defecto, mostrar la página de ingresos
        $incomeController = new IncomeController();
        $incomes = $incomeController->getAllIncomes();
        include 'views/incomes.php';
        break;
}

// Mostrar mensajes de sesión y limpiarlos
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>