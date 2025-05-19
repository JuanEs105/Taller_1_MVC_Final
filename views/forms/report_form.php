<div class="card-custom">
    <div class="card-body-custom">
        <h2 class="card-title-custom">Generar Reporte Mensual</h2>

        <?php
        $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
        $message_type = 'info';
        if ($message_text) {
            if (stripos($message_text, 'Error') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'no existe') !== false || stripos($message_text, 'requerido') !== false) {
                $message_type = 'danger';
            } elseif (stripos($message_text, 'correctamente') !== false) {
                $message_type = 'success';
            }
             echo "<div class='alert $message_type'>";
             echo $message_text;
             echo '</div>';
         }
        ?>

        <form method="GET" action="index.php">
            <input type="hidden" name="controller" value="report">
            <input type="hidden" name="action" value="generate">

            <div class="form-group-custom mb-3-custom">
                <label for="reportMonth" class="form-label-custom">Mes:</label>
                <select name="month" id="reportMonth" class="form-control-custom" required>
                    <option value="">Seleccione un mes</option>
                    <?php
                    $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                              'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                    foreach ($months as $m): ?>
                        <option value="<?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group-custom mb-3-custom">
                <label for="reportYear" class="form-label-custom">Año:</label>
                <input type="number" name="year" id="reportYear" class="form-control-custom"
                       min="1900" max="2100"
                       value="<?= htmlspecialchars($_GET['year'] ?? date('Y'), ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-file-alt"></i> Generar Reporte
            </button>
        </form>
    </div>
</div>