<?php

$modulo = $_REQUEST['modulo'] ?? '';
$action = $_REQUEST['action'] ?? '';
$id = $_REQUEST['id'] ?? '';

?>

<div class="action-btn">
    <div class="dropdown-container" id="dropdown-container">
        <button class="dropdown-toggle" id="dropdown-toggle">
            Ações
        </button>

        <ul class="dropdown-menu" id="dropdown-menu">
            <li class="dropdown-item">
                <?php if ($action === 'DetailView'): ?>
                    <a href="index.php?modulo=<?= $modulo ?>&action=EditView&id=<?= $id ?>">
                        Editar
                    </a>
                <?php else: ?>
                    <a href="index.php?modulo=<?= $modulo ?>&action=DetailView&id=<?= $id ?>">
                        Cancelar
                    </a>
                <?php endif; ?>
            </li>
            <li class="dropdown-item">
                <button type="button" data-action="export" data-format="csv">Exportar CSV</button>
            </li>
            <li class="dropdown-item">
                <button type="button" data-action="export" data-format="pdf">Exportar PDF</button>
            </li>
            <!-- <li class="dropdown-item">
                <button type="button" data-action="export" data-format="xlsx">Exportar XLSX</button>
            </li> -->
        </ul>
    </div>
</div>

<script src="core/assets/js/ux/actionBtn.js"></script>