<div class="container">
    <h1>Reporte Financiero Mensual</h1>
    
    <div class="mb-3">
        <a href="index.php?controller=report&action=form" class="btn btn-secondary">Volver</a>
    </div>
    
    <?php if (isset($reportData)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2>Reporte de <?= $reportData['month'] ?> <?= $reportData['year'] ?></h2>
            </div>
            
            <div class="card-body">
                <!-- Resumen Financiero -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="card-title">Ingresos Totales</h5>
                                <p class="card-text h4 text-primary">$<?= number_format($reportData['income'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h5 class="card-title">Gastos Totales</h5>
                                <p class="card-text h4 text-danger">$<?= number_format($reportData['totalExpenses'], 2) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card <?= $reportData['savingsPercentage'] >= 10 ? 'border-success' : 'border-warning' ?>">
                            <div class="card-body">
                                <h5 class="card-title">Ahorro</h5>
                                <p class="card-text h4 <?= $reportData['savingsPercentage'] >= 10 ? 'text-success' : 'text-warning' ?>">
                                    $<?= number_format($reportData['savings'], 2) ?>
                                    (<?= number_format($reportData['savingsPercentage'], 2) ?>%)
                                </p>
                                <?php if ($reportData['savingsPercentage'] < 10): ?>
                                    <p class="text-danger"><small>Meta no alcanzada (se recomienda 10% o más)</small></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Detalle de Gastos por Categoría -->
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
                            <?php foreach ($reportData['categoryAnalysis'] as $category): ?>
                                <tr>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td>$<?= number_format($category['total_spent'], 2) ?></td>
                                    <td><?= number_format($category['percentage_spent'], 2) ?>%</td>
                                    <td><?= number_format($category['percentage_allowed'], 2) ?>%</td>
                                    <td>
                                        <?php if ($category['exceeded']): ?>
                                            <span class="badge badge-danger">Excedido</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Dentro del límite</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($category['exceeded']): ?>
                                            <small class="text-danger">
                                                Reducir en $<?= number_format(
                                                    $category['total_spent'] - ($reportData['income'] * $category['percentage_allowed'] / 100), 
                                                2
                                                ) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
</div>