<div class="container">
    <h1>Control de Ingresos</h1>

    <?php
    $message_text = isset($_GET['message']) ? htmlspecialchars($_GET['message'], ENT_QUOTES, 'UTF-8') : null;
    $message_type = 'info';

     if ($message_text) {
        if (stripos($message_text, 'Error') !== false || stripos($message_text, 'inválido') !== false || stripos($message_text, 'no existe') !== false || stripos($message_text, 'obligatorio') !== false || stripos($message_text, 'duplicar') !== false || stripos($message_text, 'numérico') !== false || stripos($message_text, 'mayor a cero') !== false) {
            $message_type = 'danger';
        } elseif (stripos($message_text, 'correctamente') !== false || stripos($message_text, 'sin cambios') !== false) {
            $message_type = 'success';
        }
         echo "<div class='alert alert-$message_type alert-dismissible fade show' role='alert'>";
         echo $message_text;
         echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
         echo '</div>';
     }
    ?>

    <?php
    include 'views/forms/income_form.php';
    ?>

    <div class="card shadow mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ingresos Registrados</h6>
        </div>
         <div class="card-body">
            <div class="table-container">
                <?php if (empty($incomes)): ?>
                    <div class="alert alert-info">No hay ingresos registrados todavía.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Mes</th>
                                    <th>Año</th>
                                    <th>Valor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($incomes as $income):
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($income['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($income['month'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars($income['year'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>$<?= htmlspecialchars(number_format($income['value'] ?? 0, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <a href="index.php?controller=income&action=edit&month=<?= urlencode($income['month'] ?? '') ?>&year=<?= urlencode($income['year'] ?? '') ?>"
                                               class="btn btn-sm btn-primary">
                                               <i class="fas fa-edit"></i> Modificar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
         </div>
    </div>
</div>