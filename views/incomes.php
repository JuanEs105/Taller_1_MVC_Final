<div class="container">
    <h1>Control de Ingresos</h1>
    
    <?php include 'forms/income_form.php'; ?>
    
    <div class="table-container">
        <h2>Ingresos Registrados</h2>
        
        <?php if (empty($incomes)): ?>
            <p>No hay ingresos registrados todavía.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mes</th>
                        <th>Año</th>
                        <th>Valor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incomes as $income): ?>
                        <tr>
                            <td><?php echo $income['id']; ?></td>
                            <td><?php echo $income['month']; ?></td>
                            <td><?php echo $income['year']; ?></td>
                            <td>$<?php echo number_format($income['value'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="index.php?controller=income&action=edit&month=<?php echo $income['month']; ?>&year=<?php echo $income['year']; ?>" class="btn btn-primary">Modificar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>