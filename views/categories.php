<div class="container">
    <h1>Gestión de Categorías</h1>

    <!-- Botón para nueva categoría -->
    <div class="mb-3">
        <a href="index.php?controller=category&action=register" class="btn btn-primary">Nueva Categoría</a>
    </div>

    <!-- Mostrar mensajes -->
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-<?= strpos($_GET['message'], 'Error') === false ? 'success' : 'danger' ?>">
            <?= $_GET['message'] ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de registro -->
    <?php if (isset($action) && $action == 'register'): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Registrar Nueva Categoría</h2>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger"><?= $errorMessage ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?controller=category&action=register">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Porcentaje (0-100%):</label>
                        <input type="number" name="percentage" class="form-control" 
                               value="<?= htmlspecialchars($_POST['percentage'] ?? '') ?>" 
                               min="0.01" max="100" step="0.01" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="index.php?controller=category" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Formulario de edición -->
    <?php if (isset($action) && $action == 'edit' && isset($category)): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Editar Categoría</h2>
                
                <?php if (isset($errorMessage)): ?>
                    <div class="alert alert-danger"><?= $errorMessage ?></div>
                <?php endif; ?>
                
                <form method="POST" action="index.php?controller=category&action=update">
                    <input type="hidden" name="id" value="<?= $category['id'] ?>">
                    
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Porcentaje (0-100%):</label>
                        <input type="number" name="percentage" class="form-control" 
                               value="<?= htmlspecialchars($category['percentage']) ?>" 
                               min="0.01" max="100" step="0.01" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="index.php?controller=category" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabla de categorías -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Porcentaje</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay categorías registradas</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <?php $enUso = $categoryController->isCategoryInUse($cat['id']); ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['id']) ?></td>
                            <td><?= htmlspecialchars($cat['name']) ?></td>
                            <td><?= htmlspecialchars($cat['percentage']) ?>%</td>
                            <td>
                                <?php if (!$enUso): ?>
                                    <a href="index.php?controller=category&action=edit&id=<?= $cat['id'] ?>" 
                                       class="btn btn-sm btn-primary mr-2">Editar</a>
                                    <a href="index.php?controller=category&action=delete&id=<?= $cat['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Está seguro de eliminar esta categoría?')">Eliminar</a>
                                <?php else: ?>
                                    <span class="text-muted">No editable (tiene gastos asociados)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>