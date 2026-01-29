<h1>Instalação – Verificação do Ambiente</h1>

<ul>
    <li>PHP ≥ 7.4: <?= PHP_VERSION ?></li>
    <li>PDO: <?= extension_loaded('pdo') ? 'OK' : 'FALTANDO' ?></li>
    <li>PDO MySQL: <?= extension_loaded('pdo_mysql') ? 'OK' : 'FALTANDO' ?></li>
</ul>

<?php if (extension_loaded('pdo') && extension_loaded('pdo_mysql')): ?>
    <a href="step2.php" class="btn btn-primary">
        Próximo
    </a>
<?php else: ?>
    <p>Corrija os requisitos antes de continuar.</p>
<?php endif; ?>
