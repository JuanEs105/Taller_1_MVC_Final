<div class="container">
    <h1>Reporte Financiero Mensual</h1>

    <div class="mb-3">
        <a href="index.php?controller=report&action=form" class="btn btn-secondary"><i class="fas fa-arrow-circle-left"></i> Volver</a>
    </div>

    <?php if (isset($reportData)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2>Reporte de <?= htmlspecialchars($reportData['month'] ?? '', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($reportData['year'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                <?php if(isset($reportData['month']) && isset($reportData['year'])): ?>
                <a href="index.php?controller=report&action=view&month=<?= urlencode($reportData['month']) ?>&year=<?= urlencode($reportData['year']) ?>"
                   class="btn btn-light btn-sm" target="_blank">
                   <i class="fas fa-external-link-alt"></i> Ver Reporte Completo
                </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title">Ingresos Totales</h5>
                                <p class="card-text h4 text-primary">$<?= number_format($reportData['income'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h5 class="card-title">Gastos Totales</h5>
                                <p class="card-text h4 text-danger">$<?= number_format($reportData['totalExpenses'] ?? 0, 2, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <?php
                        $savings = $reportData['savings'] ?? 0;
                        $savingsPercentage = $reportData['savingsPercentage'] ?? 0;
                        $savingsCardClass = $savingsPercentage >= 10 ? 'border-success' : 'border-warning';
                        $savingsTextClass = $savingsPercentage >= 10 ? 'text-success' : 'text-warning';
                        ?>
                        <div class="card <?= $savingsCardClass ?>">
                            <div class="card-body">
                                <h5 class="card-title">Ahorro</h5>
                                <p class="card-text h4 <?= $savingsTextClass ?>">
                                    $<?= number_format($savings, 2, ',', '.') ?>
                                    (<?= number_format($savingsPercentage, 2, ',', '.') ?>%)
                                </p>
                                <?php if ($savingsPercentage < 10): ?>
                                    <p class="text-danger"><small>Meta no alcanzada (se recomienda 10% o más)</small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mb-3">Análisis de Gastos por Categoría</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
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
                                    <td colspan="6" class="text-center text-muted">No hay análisis de categorías disponible.</td>
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
                                                <span class="badge bg-danger">Excedido</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Dentro del límite</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (isset($category['exceeded']) && $category['exceeded']): ?>
                                                <small class="text-danger">
                                                    Reducir en $<?= number_format(
                                                        ($category['total_spent'] ?? 0) - (($reportData['income'] ?? 0) * ($category['percentage_allowed'] ?? 0) / 100),
                                                        2, ',', '.'
                                                    ) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
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
        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>
</div>