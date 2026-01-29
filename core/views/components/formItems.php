<?php
$dao = new DAO();
$metadata = new Metadata();

$metadataModule = $metadata->__get($modulo);
$fields = $metadataModule['fields'] ?? [];

$itemConfig = $itemConfig ?? null;

if (!$itemConfig || empty($itemConfig['relations'])) {
    throw new Exception("Nenhuma relação definida entre {$parentModule} → {$modulo}");
}

$relations = $itemConfig['relations'];

$items = [];

foreach ($relations as $relation) {
    if (empty($relation['foreign_key'])) {
        continue;
    }

    $rows = $dao->selectFromParent(
        $modulo,
        $relation['foreign_key'],
        $beanId
    );

    $items = array_merge($items, $rows);
}

$behavior = $metadata->getBehavior($modulo);
?>

<table class="tabela-itens" data-modulo="<?= $modulo ?>">
    <thead>
        <tr>
            <?php
            $context = [
                'mode' => 'subitem',
                'parentModule' => $parentModule,
                'childModule' => $modulo,
                'relations' => $relations
            ];
            ?>
            <?php foreach ($fields as $name => $info): ?>
                <?php
                $treatment = $info['treatment'] ?? 'show';
                if ($treatment === 'remove') continue;
                if (($info['extra'] ?? '') === 'auto_increment') continue;
                ?>
                <th><?= htmlspecialchars($info['label'] ?? ucfirst($name)) ?></th>
            <?php endforeach; ?>
            <?php if ($actionType === ''): ?><th>Ações</th><?php endif; ?>
        </tr>
    </thead>

    <tbody>
        <tr data-template="1" style="display:none;">
            <?php foreach ($fields as $name => $info): ?>
                <?php
                $treatment = $info['treatment'] ?? 'show';
                if ($treatment === 'remove') continue;

                if ($treatment === 'hidden' || ($info['extra'] ?? '') === 'auto_increment') {
                    echo "<input type='hidden' name='items[$modulo][__INDEX__][$name]' value=''>";
                    continue;
                }
                ?>
                <td>
                    <?php
                    renderFormField(
                        "items[{$modulo}][__INDEX__][{$name}]",
                        $info,
                        '',
                        $actionType,
                        $context
                    );
                    ?>
                </td>
            <?php endforeach; ?>

            <?php if ($actionType === ''): ?>
                <td><button type="button" class="deleteItem">Excluir</button></td>
            <?php endif; ?>
        </tr>
        <?php foreach ($items as $index => $item): ?>
            <tr>
                <?php foreach ($fields as $name => $info): ?>
                    <?php
                    $treatment = $info['treatment'] ?? 'show';
                    if ($treatment === 'remove') continue;

                    $value = $item[$name] ?? '';

                    if ($treatment === 'hidden' || ($info['extra'] ?? '') === 'auto_increment') {
                        echo "<input type='hidden' name='items[$modulo][$index][$name]' value='" . htmlspecialchars($value, ENT_QUOTES) . "'>";
                        continue;
                    }
                    ?>
                    <td>
                        <?php
                        renderFormField(
                            "items[{$modulo}][{$index}][{$name}]",
                            $info,
                            $value,
                            $actionType,
                            $context
                        );
                        ?>
                    </td>
                <?php endforeach; ?>

                <?php if ($actionType === ''): ?>
                    <td><button type="button" class="deleteItem">Excluir</button></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($actionType === ''): ?>
    <button type="button" class="addItem" data-target="<?= $modulo ?>">Adicionar Item</button>
<?php endif; ?>

<script>
    window.behavior = <?= json_encode($behavior) ?>;
</script>
<script src="core/assets/js/engine.js"></script>