<div class="form-container">
    <h2><?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Modificar Ingreso' : 'Registrar Ingreso'; ?></h2>
    
    <?php if (isset($message)): ?>
        <div class="message <?php echo $message['success'] ? 'success' : 'error'; ?>">
            <?php echo $message['message']; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="index.php?controller=income&action=<?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'update' : 'register'; ?>">
        <div class="form-group">
            <label for="month">Mes:</label>
            <select name="month" id="month" class="form-control" <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'disabled' : ''; ?> required>
                <option value="">Seleccione un mes</option>
                <option value="Enero" <?php echo (isset($income) && $income['month'] == 'Enero') ? 'selected' : ''; ?>>Enero</option>
                <option value="Febrero" <?php echo (isset($income) && $income['month'] == 'Febrero') ? 'selected' : ''; ?>>Febrero</option>
                <option value="Marzo" <?php echo (isset($income) && $income['month'] == 'Marzo') ? 'selected' : ''; ?>>Marzo</option>
                <option value="Abril" <?php echo (isset($income) && $income['month'] == 'Abril') ? 'selected' : ''; ?>>Abril</option>
                <option value="Mayo" <?php echo (isset($income) && $income['month'] == 'Mayo') ? 'selected' : ''; ?>>Mayo</option>
                <option value="Junio" <?php echo (isset($income) && $income['month'] == 'Junio') ? 'selected' : ''; ?>>Junio</option>
                <option value="Julio" <?php echo (isset($income) && $income['month'] == 'Julio') ? 'selected' : ''; ?>>Julio</option>
                <option value="Agosto" <?php echo (isset($income) && $income['month'] == 'Agosto') ? 'selected' : ''; ?>>Agosto</option>
                <option value="Septiembre" <?php echo (isset($income) && $income['month'] == 'Septiembre') ? 'selected' : ''; ?>>Septiembre</option>
                <option value="Octubre" <?php echo (isset($income) && $income['month'] == 'Octubre') ? 'selected' : ''; ?>>Octubre</option>
                <option value="Noviembre" <?php echo (isset($income) && $income['month'] == 'Noviembre') ? 'selected' : ''; ?>>Noviembre</option>
                <option value="Diciembre" <?php echo (isset($income) && $income['month'] == 'Diciembre') ? 'selected' : ''; ?>>Diciembre</option>
            </select>
            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
                <input type="hidden" name="month" value="<?php echo $income['month']; ?>">
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="year">Año:</label>
            <input type="number" name="year" id="year" class="form-control" min="2020" max="2100" value="<?php echo isset($income) ? $income['year'] : date('Y'); ?>" <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'readonly' : ''; ?> required>
            <?php if (isset($_GET['action']) && $_GET['action'] == 'edit'): ?>
                <input type="hidden" name="year" value="<?php echo $income['year']; ?>">
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="value">Valor del Ingreso:</label>
            <input type="number" name="value" id="value" class="form-control" min="0.01" step="0.01" value="<?php echo isset($income) ? number_format($income['value'], 2, '.', '') : ''; ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <?php echo isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Actualizar' : 'Registrar'; ?>
        </button>
    </form>
</div>