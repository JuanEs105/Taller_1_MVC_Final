<div class="container">
    <div class="card-custom shadow-custom mb-4-custom">
        <div class="card-header-custom primary py-3-custom">
            <h2 class="m-0-custom font-weight-bold-custom text-white-custom">
                <?= isset($action) && $action == 'edit' ? 'Modificar Ingreso' : 'Registrar Nuevo Ingreso' ?>
            </h2>
        </div>
        <div class="card-body-custom">
            <?php
            $message_text_form = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
            $message_type_form = 'info';

             if ($message_text_form) {
                if (stripos($message_text_form, 'Error') !== false || stripos($message_text_form, 'inválido') !== false || stripos($message_text_form, 'no existe') !== false || stripos($message_text_form, 'obligatorio') !== false || stripos($message_text_form, 'duplicar') !== false || stripos($message_text_form, 'numérico') !== false || stripos($message_text_form, 'mayor a cero') !== false) {
                    $message_type_form = 'danger';
                } elseif (stripos($message_text_form, 'correctamente') !== false || stripos($message_text_form, 'sin cambios') !== false) {
                    $message_type_form = 'success';
                }
                 echo "<div class='alert $message_type_form'>";
                 echo $message_text_form;
                 echo '</div>';
             }
            ?>

            <form method="POST" action="index.php?controller=income&action=<?= isset($action) && $action == 'edit' ? 'update' : 'register' ?>" id="incomeForm">
                <?php
                 if (isset($action) && $action == 'edit' && isset($income)):
                 ?>
                    <input type="hidden" name="month" value="<?= htmlspecialchars($income['month'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="year" value="<?= htmlspecialchars($income['year'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <?php endif; ?>

                <div class="row-custom">
                    <div class="col-md-4-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="month" class="form-label-custom">Mes:</label>
                            <select name="month" id="month" class="form-control-custom" <?= isset($action) && $action == 'edit' ? 'disabled' : '' ?> required>
                                <option value="">Seleccione un mes</option>
                                <?php
                                $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                $selectedMonth = isset($income) ? ($income['month'] ?? '') : ($_POST['month'] ?? '');
                                foreach ($months as $m): ?>
                                    <option value="<?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>" <?= ($selectedMonth == $m) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                             <span class="text-danger" id="monthError"></span>
                        </div>
                    </div>

                    <div class="col-md-4-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="year" class="form-label-custom">Año:</label>
                            <input type="number" name="year" id="year" class="form-control-custom"
                                   min="1900" max="2100"
                                   value="<?= isset($income) ? htmlspecialchars($income['year'] ?? '') : ($_POST['year'] ?? date('Y')) ?>"
                                   <?= isset($action) && $action == 'edit' ? 'readonly' : '' ?> required>
                            <span class="text-danger" id="yearError"></span>
                        </div>
                    </div>

                    <div class="col-md-4-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="value" class="form-label-custom">Valor del Ingreso:</label>
                            <div class="input-group-custom">
                                <div class="input-group-prepend-custom">
                                    <span class="input-group-text-custom">$</span>
                                </div>
                                <input type="number" name="value" id="value" class="form-control-custom"
                                       min="0.01" step="0.01"
                                       value="<?= isset($income) ? htmlspecialchars(number_format($income['value'] ?? 0, 2, '.', ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                                 <span class="text-danger" id="valueError"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group-custom text-right-custom mt-4-custom">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-save"></i> <?= isset($action) && $action == 'edit' ? 'Actualizar' : 'Registrar' ?>
                    </button>
                    <a href="index.php?controller=income" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>