<div class="card">
    <div class="card-body">
        <h2 class="card-title"><?= isset($category) ? 'Editar Categoría' : 'Nueva Categoría' ?></h2>
        
        <form method="POST" action="index.php?controller=category&action=<?= isset($category) ? 'update' : 'register' ?>">
            <?php if (isset($category)): ?>
                <input type="hidden" name="id" value="<?= $category['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Nombre:</label>
                <input type="text" name="name" class="form-control" value="<?= isset($category) ? $category['name'] : '' ?>" required>
            </div>
            
            <div class="form-group">
                <label>Porcentaje (0-100%):</label>
                <input type="number" name="percentage" class="form-control" 
                       value="<?= isset($category) ? $category['percentage'] : '' ?>" 
                       min="0.01" max="100" step="0.01" required>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <?= isset($category) ? 'Actualizar' : 'Guardar' ?>
            </button>
            <a href="index.php?controller=category" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>