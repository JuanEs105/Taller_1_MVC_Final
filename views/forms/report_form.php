<div class="card">
    <div class="card-body">
        <h2 class="card-title">Generar Reporte Mensual</h2>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-<?= strpos($message, 'Error') === false ? 'success' : 'danger' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="GET" action="index.php">
            <input type="hidden" name="controller" value="report">
            <input type="hidden" name="action" value="generate">
            
            <div class="form-group">
                <label>Mes:</label>
                <select name="month" class="form-control" required>
                    <option value="">Seleccione un mes</option>
                    <?php
                    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    foreach ($months as $m): ?>
                        <option value="<?= $m ?>"><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Año:</label>
                <input type="number" name="year" class="form-control" 
                       min="2000" max="<?= date('Y') + 1 ?>" 
                       value="<?= date('Y') ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Generar Reporte</button>
        </form>
    </div>
</div>