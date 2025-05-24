<?php
if (!isset($categoryController)) {
    die('Error: Controlador de categorías no disponible');
}
?>

<div class="container">
    <h1 class="mb-4">Gestión de Categorías</h1>

    <?php
    $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
    $message_type = 'info'; // Default message type

    if ($message_text) {
        // Determine message type based on content
        if (stripos($message_text, 'Error') !== false || stripos($message_text, 'no encontrado') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'permitidos') !== false || stripos($message_text, 'requerido') !== false || stripos($message_text, 'debe ser') !== false || stripos($message_text, 'existe') !== false) {
            $message_type = 'danger';
        } elseif (stripos($message_text, 'correctamente') !== false || stripos($message_text, 'sin cambios') !== false) {
            $message_type = 'success';
        }
        // If no specific keywords for danger or success, it remains 'info' or you can add more specific checks
        echo "<div class='alert $message_type'>";
        echo $message_text;
        echo '</div>';
    }
    ?>

    <div class="card-custom shadow-custom">
        <div class="card-header-custom primary d-flex-custom justify-content-between-custom align-items-center-custom py-3-custom">
            <h6 class="m-0-custom font-weight-bold-custom text-white-custom">Listado de Categorías</h6>
            <a href="index.php?controller=category&action=register" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva
            </a>
        </div>

        <div class="card-body-custom">
            <?php
            // Show form for 'register' action or 'edit' action if a category is loaded
            if (isset($action) && ($action == 'register' || ($action == 'edit' && isset($category)))):
            ?>
                <div class="row-custom mb-4-custom">
                    <div class="col-md-6-custom mx-auto">
                        <div class="card-custom">
                            <div class="card-header-custom <?= ($action == 'edit' && isset($category)) ? 'warning' : 'primary' ?> text-white-custom">
                                <?= ($action == 'edit' && isset($category)) ? 'Editar Categoría' : 'Nueva Categoría' ?>
                            </div>
                            <div class="card-body-custom">
                                <form id="categoryForm" method="POST" action="index.php?controller=category&action=<?= ($action == 'edit' && isset($category)) ? 'update' : 'register' ?>">
                                    <?php if (isset($action) && $action == 'edit' && isset($category)): ?>
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <?php endif; ?>

                                    <div class="form-group-custom mb-3-custom">
                                        <label for="categoryName" class="form-label-custom">Nombre</label>
                                        <input type="text" name="name" id="categoryName" class="form-control-custom"
                                               value="<?= htmlspecialchars(($action == 'edit' && isset($category)) ? ($category['name'] ?? '') : ($_POST['name'] ?? '') , ENT_QUOTES, 'UTF-8') ?>"
                                               required
                                               maxlength="<?= CategoryController::MAX_NAME_LENGTH ?? 50 ?>"
                                               placeholder="Ej: Alimentación, Transporte">
                                        <span class="text-danger" id="categoryNameError"></span>
                                    </div>

                                    <div class="form-group-custom mb-3-custom">
                                        <label for="categoryPercentage" class="form-label-custom">Porcentaje (%)</label>
                                        <input type="number" name="percentage" id="categoryPercentage" class="form-control-custom"
                                               value="<?= htmlspecialchars(($action == 'edit' && isset($category)) ? ($category['percentage'] ?? '') : ($_POST['percentage'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                               min="0.01" max="100" step="0.01" required
                                               placeholder="Ej: 10.50">
                                        <span class="text-danger" id="categoryPercentageError"></span>
                                    </div>

                                    <div class="d-flex-custom justify-content-end-custom gap-2-custom">
                                        <button type="submit" class="btn btn-<?= ($action == 'edit' && isset($category)) ? 'warning' : 'primary' ?> btn-sm">
                                            <i class="fas fa-save"></i> <?= ($action == 'edit' && isset($category)) ? 'Actualizar' : 'Guardar' ?>
                                        </button>
                                        <a href="index.php?controller=category" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-times-circle"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-responsive-custom">
                <table class="table-custom table-bordered-custom table-hover-custom">
                    <thead class="thead-dark-custom">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Porcentaje</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="text-center-custom py-4-custom text-muted-custom">No hay categorías registradas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <?php
                                $enUso = $categoryController->isCategoryInUse($cat['id']);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($cat['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($cat['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format($cat['percentage'] ?? 0, 2) ?>%</td>
                                    <td>
                                        <span class="badge-custom <?= $enUso ? 'warning' : 'success' ?>">
                                            <?= $enUso ? 'En uso' : 'Disponible' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex-custom gap-2-custom">
                                            <a href="index.php?controller=category&action=edit&id=<?= htmlspecialchars($cat['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                               class="btn btn-primary btn-sm"
                                               title="Editar categoría">
                                               <i class="fas fa-edit"></i> Editar
                                            </a>

                                            <?php if (!$enUso): ?>
                                                <a href="index.php?controller=category&action=delete&id=<?= htmlspecialchars($cat['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                   class="btn btn-danger btn-sm"
                                                   title="Eliminar categoría"
                                                   onclick="return confirm('¿Está seguro de eliminar la categoría \'<?= htmlspecialchars(addslashes($cat['name'] ?? '')) ?>\'? Esta acción es irreversible.')">
                                                   <i class="fas fa-trash-alt"></i> Eliminar
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm disabled" title="No se puede eliminar: tiene gastos asociados">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>