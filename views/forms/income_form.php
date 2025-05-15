<div class="container">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="m-0 font-weight-bold text-primary">
                <?= isset($action) && $action == 'edit' ? 'Modificar Ingreso' : 'Registrar Nuevo Ingreso' ?>
            </h2>
        </div>
        <div class="card-body">
            <?php
            $message_text_form = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
            $message_type_form = 'info';

             if ($message_text_form) {
                if (stripos($message_text_form, 'Error') !== false || stripos($message_text_form, 'inválido') !== false || stripos($message_text_form, 'no existe') !== false || stripos($message_text_form, 'obligatorio') !== false || stripos($message_text_form, 'duplicar') !== false || stripos($message_text_form, 'numérico') !== false || stripos($message_text_form, 'mayor a cero') !== false) {
                    $message_type_form = 'danger';
                } elseif (stripos($message_text_form, 'correctamente') !== false || stripos($message_text_form, 'sin cambios') !== false) {
                    $message_type_form = 'success';
                }
                 echo "<div class='alert alert-$message_type_form alert-dismissible fade show' role='alert'>";
                 echo $message_text_form;
                 echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
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

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="month" class="form-label">Mes:</label>
                            <select name="month" id="month" class="form-control" <?= isset($action) && $action == 'edit' ? 'disabled' : '' ?> required>
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

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="year" class="form-label">Año:</label>
                            <input type="number" name="year" id="year" class="form-control"
                                   min="1900" max="2100"
                                   value="<?= isset($income) ? htmlspecialchars($income['year'] ?? '') : ($_POST['year'] ?? date('Y')) ?>"
                                   <?= isset($action) && $action == 'edit' ? 'readonly' : '' ?> required>
                            <span class="text-danger" id="yearError"></span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="value" class="form-label">Valor del Ingreso:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" name="value" id="value" class="form-control"
                                       min="0.01" step="0.01"
                                       value="<?= isset($income) ? htmlspecialchars(number_format($income['value'] ?? 0, 2, '.', ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                                 <span class="text-danger" id="valueError"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group text-right mt-4">
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