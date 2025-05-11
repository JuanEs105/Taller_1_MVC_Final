<div class="container">
    <h1>Control de Gastos</h1>
    
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['success'] ? 'success' : 'danger' ?>">
            <?= $message['message'] ?>
        </div>
    <?php endif; ?>
    
    <!-- Formulario de Edición (se muestra solo cuando está en modo edición) -->
    <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($expenseToEdit)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h2>Modificar Gasto Existente</h2>
        </div>
        <div class="card-body">
            <form action="index.php?controller=expense&action=update" method="post">
                <input type="hidden" name="id" value="<?= $expenseToEdit['id'] ?>">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="category" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" 
                                        <?= $category['id'] == $expenseToEdit['category_id'] ? 'selected' : '' ?>>
                                        <?= $category['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Valor</label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0.01" 
                                   value="<?= number_format($expenseToEdit['value'], 2, '.', '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn btn-primary w-100">Actualizar Gasto</button>
                            <a href="index.php?controller=expense" class="btn btn-secondary w-100 mt-2">Cancelar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Formulario de Registro (se oculta cuando está en modo edición) -->
    <?php if (!isset($_GET['action']) || $_GET['action'] != 'edit' || !isset($expenseToEdit)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h2>Registrar Nuevo Gasto</h2>
        </div>
        <div class="card-body">
            <form action="index.php?controller=expense&action=register" method="post">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Mes</label>
                            <select name="month" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php foreach (['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $mes): ?>
                                    <option value="<?= $mes ?>" <?= isset($_POST['month']) && $_POST['month'] === $mes ? 'selected' : '' ?>>
                                        <?= $mes ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Año</label>
                            <input type="number" name="year" class="form-control" min="2000" max="2100" 
                                   value="<?= $_POST['year'] ?? date('Y') ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select name="category" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= isset($_POST['category']) && $_POST['category'] == $category['id'] ? 'selected' : '' ?>>
                                        <?= $category['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Valor</label>
                            <input type="number" name="value" class="form-control" step="0.01" min="0.01" 
                                   value="<?= $_POST['value'] ?? '' ?>" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">Registrar Gasto</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Listado de Gastos -->
    <div class="card">
        <div class="card-header">
            <h2>Gastos Registrados</h2>
        </div>
        <div class="card-body">
            <?php if (empty($expenses)): ?>
                <div class="alert alert-info">No hay gastos registrados todavía.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Categoría</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= $expense['id'] ?></td>
                                    <td><?= $expense['category_name'] ?></td>
                                    <td><?= $expense['month'] ?></td>
                                    <td><?= $expense['year'] ?></td>
                                    <td>$<?= number_format($expense['value'], 2, ',', '.') ?></td>
                                    <td>
                                        <a href="index.php?controller=expense&action=edit&id=<?= $expense['id'] ?>" 
                                           class="btn btn-sm btn-primary">Modificar</a>
                                        <a href="index.php?controller=expense&action=delete&id=<?= $expense['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           onclick="return confirm('¿Está seguro de eliminar este gasto?')">Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="mt-3">
        <a href="index.php?controller=income" class="btn btn-secondary">Ver Ingresos</a>
    </div>
</div>