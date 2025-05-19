<div class="container">
    <h1>Reporte Financiero Mensual</h1>

    <div class="button-container">
        <a href="index.php?controller=report&action=form" class="btn btn-secondary"><i class="fas fa-arrow-circle-left"></i> Volver</a>
    </div>

    <?php if (isset($reportData)): ?>
        <div class="card-custom mb-4-custom">
            <div class="card-header-custom primary d-flex-custom justify-content-between-custom align-items-center-custom">
                <h2>Reporte de <?= htmlspecialchars($reportData['month'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($reportData['year'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if(isset($reportData['month']) && isset($reportData['year'])): ?>
                <a href="index.php?controller=report&action=view&month=<?= urlencode($reportData['month']) ?>&year=<?= urlencode($reportData['year']) ?>"
                   class="btn btn-light btn-sm" target="_blank">
                   <i class="fas fa-external-link-alt"></i> Ver Reporte Completo
                </a>
                <?php endif; ?>
            </div>

            <div class="card-body-custom">
                <div class="row-custom mb-4-custom">
                    <div class="col-md-4-custom">
                        <div class="card-custom border-primary-custom">
                            <div class="card-body-custom">
                                <h5 class="card-title-custom">Ingresos Totales</h5>
                                <p class="card-text-custom h4-custom text-primary-custom">$<?= number_format($reportData['income'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4-custom">
                        <div class="card-custom border-danger-custom">
                            <div class="card-body-custom">
                                <h5 class="card-title-custom">Gastos Totales</h5>
                                <p class="card-text-custom h4-custom text-danger-custom">$<?= number_format($reportData['totalExpenses'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4-custom">
                        <?php
                        $savings = $reportData['savings'] ?? 0;
                        $savingsPercentage = $reportData['savingsPercentage'] ?? 0;
                        $savingsCardClass = $savingsPercentage >= 10 ? 'border-success-custom' : 'border-warning-custom';
                        $savingsTextClass = $savingsPercentage >= 10 ? 'text-success-custom' : 'text-warning-custom';
                        ?>
                        <div class="card-custom <?= $savingsCardClass ?>">
                            <div class="card-body-custom">
                                <h5 class="card-title-custom">Ahorro</h5>
                                <p class="card-text-custom h4-custom <?= $savingsTextClass ?>">
                                    $<?= number_format($savings, 2, ',', '.') ?>
                                    (<?= number_format($savingsPercentage, 2, ',', '.') ?>%)
                                </p>
                                <?php if ($savingsPercentage < 10): ?>
                                    <p class="text-danger-custom"><small>Meta no alcanzada (se recomienda 10% o más)</small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3-custom">Análisis de Gastos por Categoría</h4>
                <div class="table-responsive-custom">
                    <table class="table-custom table-bordered-custom table-hover-custom">
                        <thead class="thead-dark-custom">
                            <tr>
                                <th>Categoría</th>
                                <th>Total Gastado</th>
                                <th>% del Ingreso</th>
                                <th>% Permitido</th>
                                <th>Estado</th>
                                <th>Recomendación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportData['categoryAnalysis'])): ?>
                                <tr>
                                    <td colspan="6" class="text-center-custom text-muted-custom">No hay análisis de categorías disponible.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData['categoryAnalysis'] as $category): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($category['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>$<?= number_format($category['total_spent'] ?? 0, 2, ',', '.') ?></td>
                                        <td><?= number_format($category['percentage_spent'] ?? 0, 2, ',', '.') ?>%</td>
                                        <td><?= number_format($category['percentage_allowed'] ?? 0, 2, ',', '.') ?>%</td>
                                        <td>
                                            <?php if (isset($category['exceeded']) && $category['exceeded']): ?>
                                                <span class="badge-custom danger">Excedido</span>
                                            <?php else: ?>
                                                <span class="badge-custom success">Dentro del límite</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($category['exceeded']) && $category['exceeded']): ?>
                                                <small class="text-danger-custom">
                                                    Reducir en $<?= number_format(
                                                        ($category['total_spent'] ?? 0) - (($reportData['income'] ?? 0) * ($category['percentage_allowed'] ?? 0) / 100),
                                                        2, ',', '.'
                                                    ) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted-custom">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
</div>