<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="m-0 font-weight-bold text-primary">
                <?= isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Modificar Ingreso' : 'Registrar Nuevo Ingreso' ?>
            </h2>
        </div>
        <div class="card-body">
            <?php if (isset($message)): ?>
                <div class="alert alert-<?= $message['success'] ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?controller=income&action=<?= isset($_GET['action']) && $_GET['action'] == 'edit' ? 'update' : 'register' ?>" id="incomeForm">
                <?php if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($income)): ?>
                    <input type="hidden" name="month" value="<?= htmlspecialchars($income['month']) ?>">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($income['year']) ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="month">Mes:</label>
                            <select name="month" id="month" class="form-control" <?= isset($_GET['action']) && $_GET['action'] == 'edit' ? 'disabled' : '' ?> required>
                                <option value="">Seleccione un mes</option>
                                <?php
                                $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                foreach ($months as $m): ?>
                                    <option value="<?= $m ?>" <?= (isset($income) && $income['month'] == $m) ? 'selected' : '' ?>>
                                        <?= $m ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="year">Año:</label>
                            <input type="number" name="year" id="year" class="form-control"
                                   min="2000" max="2100"
                                   value="<?= isset($income) ? htmlspecialchars($income['year']) : date('Y') ?>"
                                   <?= isset($_GET['action']) && $_GET['action'] == 'edit' ? 'readonly' : '' ?> required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="value">Valor del Ingreso:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="value" id="value" class="form-control"
                                       min="0.01" step="0.01"
                                       value="<?= isset($income) ? htmlspecialchars(number_format($income['value'], 2, '.', '')) : '' ?>"
                                       required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-right mt-4">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save"></i> <?= isset($_GET['action']) && $_GET['action'] == 'edit' ? 'Actualizar' : 'Registrar' ?>
                    </button>
                    <a href="index.php?controller=income" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('incomeForm');
    
    form.addEventListener('submit', function(e) {
        const valueInput = document.getElementById('value');
        const value = parseFloat(valueInput.value);
        
        if (isNaN(value) || value <= 0) {
            e.preventDefault();
            alert('El valor del ingreso debe ser un número mayor a cero');
            valueInput.focus();
            return false;
        }

        const monthSelect = document.getElementById('month');
        if (monthSelect && monthSelect.value === '') {
            e.preventDefault();
            alert('Seleccione un mes válido');
            monthSelect.focus();
            return false;
        }

        const yearInput = document.getElementById('year');
        const year = parseInt(yearInput.value);
        if (isNaN(year) || year < 2000 || year > 2100) {
            e.preventDefault();
            alert('Ingrese un año válido entre 2000 y 2100');
            yearInput.focus();
            return false;
        }

        return true;
    });
});
</script>