<?php
// Verificación segura del controlador
if (!isset($categoryController)) {
    die('Error: Controlador de categorías no disponible');
}
?>

<div class="container">
    <h1 class="mb-4">Gestión de Categorías</h1>

    <!-- Mensajes -->
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-<?= strpos($_GET['message'], 'Error') === false ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- Panel Principal -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Categorías</h6>
            <a href="index.php?controller=category&action=register" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva
            </a>
        </div>
        
        <div class="card-body">
            <!-- Formularios -->
            <?php if (isset($action) && ($action == 'register' || ($action == 'edit' && isset($category)))): ?>
                <div class="row mb-4">
                    <div class="col-md-6 mx-auto">
                        <div class="card">
                            <div class="card-header bg-<?= $action == 'register' ? 'primary' : 'warning' ?> text-white">
                                <?= $action == 'register' ? 'Nueva Categoría' : 'Editar Categoría' ?>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="index.php?controller=category&action=<?= $action ?>">
                                    <?php if ($action == 'edit'): ?>
                                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Nombre</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?= htmlspecialchars($action == 'edit' ? $category['name'] : ($_POST['name'] ?? '')) ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Porcentaje</label>
                                        <input type="number" name="percentage" class="form-control" 
                                               value="<?= htmlspecialchars($action == 'edit' ? $category['percentage'] : ($_POST['percentage'] ?? '')) ?>" 
                                               min="0.01" max="100" step="0.01" required>
                                    </div>
                                    
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-<?= $action == 'register' ? 'primary' : 'warning' ?> btn-sm">
                                            <?= $action == 'register' ? 'Guardar' : 'Actualizar' ?>
                                        </button>
                                        <a href="index.php?controller=category" class="btn btn-secondary btn-sm">Cancelar</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabla de Categorías -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Porcentaje</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay categorías registradas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                // Verificación segura de categorías en uso
                                $enUso = $categoryController->isCategoryInUse($cat['id']);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['id']) ?></td>
                                    <td><?= htmlspecialchars($cat['name']) ?></td>
                                    <td><?= number_format($cat['percentage'], 2) ?>%</td>
                                    <td>
                                        <span class="badge bg-<?= $enUso ? 'warning' : 'success' ?>">
                                            <?= $enUso ? 'En uso' : 'Disponible' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- Botón Editar -->
                                            <a href="index.php?controller=category&action=edit&id=<?= $cat['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                               <i class="fas fa-edit"></i> Editar
                                            </a>
                                            
                                            <!-- Botón Eliminar (condicional) -->
                                            <?php if (!$enUso): ?>
                                                <a href="index.php?controller=category&action=delete&id=<?= $cat['id'] ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('¿Eliminar categoría \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?')">
                                                   <i class="fas fa-trash-alt"></i> Eliminar
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm disabled" title="Con gastos asociados">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
