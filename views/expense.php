<div class="container">
    <h1>Control de Gastos</h1>

    <?php
    $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
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

    <?php
    if (isset($action) && $action == 'edit' && isset($expenseToEdit)):
    ?>
    <div class="card-custom mb-4-custom">
        <div class="card-header-custom warning text-white-custom">
            <h2>Modificar Gasto Existente</h2>
        </div>
        <div class="card-body-custom">
            <form action="index.php?controller=expense&action=update" method="post">
                <input type="hidden" name="id" value="<?= htmlspecialchars($expenseToEdit['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="row-custom">
                    <div class="col-md-4-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="editCategory" class="form-label-custom">Categoría</label>
                            <select name="category" id="editCategory" class="form-control-custom" required>
                                <option value="">Seleccione...</option>
                                <?php
                                foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        <?= (isset($expenseToEdit['idCategory']) && $category['id'] == $expenseToEdit['idCategory']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                             <span class="text-danger" id="editCategoryError"></span>
                        </div>
                    </div>

                    <div class="col-md-4-custom">
                         <div class="form-group-custom mb-3-custom">
                            <label class="form-label-custom">Mes (No modificable)</label>
                            <input type="text" class="form-control-custom" value="<?= htmlspecialchars($expenseToEdit['month'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                         </div>
                    </div>
                     <div class="col-md-4-custom">
                         <div class="form-group-custom mb-3-custom">
                             <label class="form-label-custom">Año (No modificable)</label>
                            <input type="text" class="form-control-custom" value="<?= htmlspecialchars($expenseToEdit['year'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                         </div>
                    </div>

                    <div class="col-md-4-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="editValue" class="form-label-custom">Valor</label>
                            <input type="number" name="value" id="editValue" class="form-control-custom"
                                   step="0.01" min="0.01"
                                   value="<?= htmlspecialchars(number_format($expenseToEdit['value'] ?? 0, 2, '.', ''), ENT_QUOTES, 'UTF-8') ?>" required>
                            <span class="text-danger" id="editValueError"></span>
                        </div>
                    </div>
                </div>

                 <div class="d-flex-custom justify-content-end-custom gap-2-custom mt-3-custom">
                    <button type="submit" class="btn btn-warning btn-sm">
                         <i class="fas fa-edit"></i> Actualizar Gasto
                    </button>
                    <a href="index.php?controller=expense" class="btn btn-secondary btn-sm">
                         <i class="fas fa-times-circle"></i> Cancelar
                    </a>
                 </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php
     if (!isset($action) || $action != 'edit' || !isset($expenseToEdit)):
    ?>
    <div class="card-custom mb-4-custom">
        <div class="card-header-custom primary text-white-custom">
            <h2>Registrar Nuevo Gasto</h2>
        </div>
        <div class="card-body-custom">
            <form action="index.php?controller=expense&action=register" method="post">
                <div class="row-custom">
                    <div class="col-md-3-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="regMonth" class="form-label-custom">Mes</label>
                            <select name="month" id="regMonth" class="form-control-custom" required>
                                <option value="">Seleccione...</option>
                                <?php
                                $months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                                          'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                                foreach ($months as $mes): ?>
                                    <option value="<?= htmlspecialchars($mes, ENT_QUOTES, 'UTF-8') ?>"
                                        <?= isset($_POST['month']) && $_POST['month'] === $mes ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mes, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-danger" id="regMonthError"></span>
                        </div>
                    </div>

                    <div class="col-md-3-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="regYear" class="form-label-custom">Año</label>
                            <input type="number" name="year" id="regYear" class="form-control-custom"
                                   min="1900" max="2100"
                                   value="<?= htmlspecialchars($_POST['year'] ?? date('Y'), ENT_QUOTES, 'UTF-8') ?>" required>
                            <span class="text-danger" id="regYearError"></span>
                        </div>
                    </div>

                    <div class="col-md-3-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="regCategory" class="form-label-custom">Categoría</label>
                            <select name="category" id="regCategory" class="form-control-custom" required>
                                <option value="">Seleccione...</option>
                                <?php
                                foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                        <?= isset($_POST['category']) && $_POST['category'] == ($category['id'] ?? '') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        (<?= htmlspecialchars($category['percentage'] ?? 0, ENT_QUOTES, 'UTF-8') ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-danger" id="regCategoryError"></span>
                        </div>
                    </div>

                    <div class="col-md-3-custom">
                        <div class="form-group-custom mb-3-custom">
                            <label for="regValue" class="form-label-custom">Valor</label>
                            <input type="number" name="value" id="regValue" class="form-control-custom"
                                   step="0.01" min="0.01"
                                   value="<?= htmlspecialchars($_POST['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                            <span class="text-danger" id="regValueError"></span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3-custom btn-sm">
                    <i class="fas fa-plus"></i> Registrar Gasto
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card-custom shadow-custom">
        <div class="card-header-custom primary py-3-custom">
             <h6 class="m-0-custom font-weight-bold-custom text-white-custom">Gastos Registrados</h6>
        </div>
        <div class="card-body-custom">
            <?php if (empty($expenses)): ?>
                <div class="alert info">No hay gastos registrados todavía.</div>
            <?php else: ?>
                <div class="table-responsive-custom">
                    <table class="table-custom table-striped-custom table-hover-custom">
                        <thead class="thead-dark-custom">
                            <tr>
                                <th>ID</th>
                                <th>Categoría</th>
                                <th>Mes</th>
                                <th>Año</th>
                                <th>Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($expenses as $expense): ?>
                                <tr>
                                    <td><?= htmlspecialchars($expense['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($expense['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($expense['month'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($expense['year'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>$<?= htmlspecialchars(number_format($expense['value'] ?? 0, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="d-flex-custom gap-2-custom">
                                            <a href="index.php?controller=expense&action=edit&id=<?= htmlspecialchars($expense['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Modificar
                                            </a>
                                            <a href="index.php?controller=expense&action=delete&id=<?= htmlspecialchars($expense['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('¿Está seguro de eliminar este gasto? Esta acción es irreversible.')">
                                               <i class="fas fa-trash-alt"></i> Eliminar
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-3-custom">
         <a href="index.php?controller=income" class="btn btn-secondary">
            <i class="fas fa-arrow-circle-left"></i> Ver Ingresos
         </a>
    </div>
</div>