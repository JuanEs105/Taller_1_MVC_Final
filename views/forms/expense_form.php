<div class="form-container">
    <h2><?php echo isset($action) && $action == 'edit' ? 'Modificar Gasto' : 'Registrar Gasto'; ?></h2>

    <?php
     $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : (isset($message) ? htmlspecialchars($message['message'] ?? '', ENT_QUOTES, 'UTF-8') : null);
     $message_type = 'info';

     if ($message_text) {
        if (stripos($message_text, 'Error') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'existe') !== false || stripos($message_text, 'válida') !== false) {
            $message_type = 'danger';
        } elseif (stripos($message_text, 'correctamente') !== false) {
            $message_type = 'success';
        }
         echo "<div class='alert $message_type'>";
         echo $message_text;
         echo '</div>';
     }
    ?>

    <form method="POST" action="index.php?controller=expense&action=<?php echo isset($action) && $action == 'edit' ? 'update' : 'register'; ?>">
        <?php
         if (isset($action) && $action == 'edit' && isset($expense)):
         ?>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($expense['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?php endif; ?>

        <div class="form-group-custom">
            <label for="category" class="form-label-custom">Categoría:</label>
            <select name="category" id="category" class="form-control-custom" required>
                <option value="">Seleccione una categoría</option>
                <?php
                 foreach ($categories as $category):
                 ?>
                    <option value="<?php echo htmlspecialchars($category['id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo (isset($expense) && isset($expense['idCategory']) && $expense['idCategory'] == ($category['id'] ?? '')) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars($category['percentage'] ?? 0, ENT_QUOTES, 'UTF-8'); ?>%)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php
        if (!isset($action) || $action != 'edit'):
        ?>
            <div class="form-group-custom">
                <label for="month" class="form-label-custom">Mes:</label>
                <select name="month" id="month" class="form-control-custom" required>
                    <option value="">Seleccione un mes</option>
                    <?php
                     $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                     foreach ($months as $mes):
                    ?>
                       <option value="<?php echo htmlspecialchars($mes, ENT_QUOTES, 'UTF-8'); ?>"
                           <?php echo isset($_POST['month']) && $_POST['month'] === $mes ? 'selected' : ''; ?>>
                           <?php echo htmlspecialchars($mes, ENT_QUOTES, 'UTF-8'); ?>
                       </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group-custom">
                <label for="year" class="form-label-custom">Año:</label>
                <input type="number" name="year" id="year" class="form-control-custom"
                       min="1900" max="2100"
                       value="<?php echo htmlspecialchars($_POST['year'] ?? date('Y'), ENT_QUOTES, 'UTF-8'); ?>" required>
            </div>
        <?php endif; ?>

        <div class="form-group-custom">
            <label for="value" class="form-label-custom">Valor del Gasto:</label>
            <input type="number" name="value" id="value" class="form-control-custom"
                   min="0.01" step="0.01"
                   value="<?php echo isset($expense) ? htmlspecialchars(number_format($expense['value'] ?? 0, 2, '.', ''), ENT_QUOTES, 'UTF-8') : htmlspecialchars($_POST['value'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo isset($action) && $action == 'edit' ? 'Actualizar' : 'Registrar'; ?>
        </button>
        <a href="index.php?controller=expense" class="btn btn-secondary">Cancelar</a>
    </form>
</div>