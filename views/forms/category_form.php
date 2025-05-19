<div class="card-custom">
    <div class="card-body-custom">
        <h2 class="card-title-custom"><?= isset($category) ? 'Editar Categoría' : 'Nueva Categoría' ?></h2>

        <?php
         $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
         $message_type = 'info';

         if ($message_text) {
            if (stripos($message_text, 'Error') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'no existe') !== false || stripos($message_text, 'permitidos') !== false) {
                $message_type = 'danger';
            } elseif (stripos($message_text, 'correctamente') !== false) {
                $message_type = 'success';
            }
             echo "<div class='alert $message_type'>";
             echo $message_text;
             echo '</div>';
         }
        ?>

        <form method="POST" action="index.php?controller=category&action=<?= isset($category) ? 'update' : 'register' ?>">
            <?php if (isset($category)): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($category['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>

            <div class="form-group-custom">
                <label for="categoryName" class="form-label-custom">Nombre:</label>
                <input type="text" name="name" id="categoryName" class="form-control-custom"
                       value="<?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group-custom">
                <label for="categoryPercentage" class="form-label-custom">Porcentaje (0-100%):</label>
                <input type="number" name="percentage" id="categoryPercentage" class="form-control-custom"
                       value="<?= htmlspecialchars($category['percentage'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       min="0.01" max="100" step="0.01" required>
            </div>

            <button type="submit" class="btn btn-primary">
                <?= isset($category) ? 'Actualizar' : 'Guardar' ?>
            </button>
            <a href="index.php?controller=category" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>