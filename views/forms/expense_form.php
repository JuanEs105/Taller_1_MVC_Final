<div class="form-container">
    <h2><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Modificar Gasto' : 'Registrar Gasto'; ?></h2>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $message['success'] ? 'success' : 'error'; ?>">
            <?php echo $message['message']; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="index.php?controller=expense&action=<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'update' : 'register'; ?>">
        <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $expense['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="category">Categoría:</label>
            <select name="category" id="category" class="form-control" required>
                <option value="">Seleccione una categoría</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo isset($expense) && $expense['idCategory'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo $category['name']; ?> (<?php echo $category['percentage']; ?>%)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <?php if (!isset($_GET['action']) || $_GET['action'] != 'edit'): ?>
            <div class="form-group">
                <label for="month">Mes:</label>
                <select name="month" id="month" class="form-control" required>
                    <option value="">Seleccione un mes</option>
                    <option value="Enero">Enero</option>
                    <option value="Febrero">Febrero</option>
                    <option value="Marzo">Marzo</option>
                    <option value="Abril">Abril</option>
                    <option value="Mayo">Mayo</option>
                    <option value="Junio">Junio</option>
                    <option value="Julio">Julio</option>
                    <option value="Agosto">Agosto</option>
                    <option value="Septiembre">Septiembre</option>
                    <option value="Octubre">Octubre</option>
                    <option value="Noviembre">Noviembre</option>
                    <option value="Diciembre">Diciembre</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="year">Año:</label>
                <input type="number" name="year" id="year" class="form-control" min="2020" max="2100" value="<?php echo date('Y'); ?>" required>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="value">Valor del Gasto:</label>
            <input type="number" name="value" id="value" class="form-control" min="0.01" step="0.01" value="<?php echo isset($expense) ? number_format($expense['value'], 2, '.', '') : ''; ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Actualizar' : 'Registrar'; ?>
        </button>
    </form>
</div>