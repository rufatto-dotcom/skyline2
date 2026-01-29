<?php

$actionType = (($_REQUEST['action'] ?? '') === 'EditView') ? '' : 'readonly';
$dao = new DAO();
$log = new Log();

$itemsConfig = $metadata['items'] ?? [];

require_once 'formFields.php';
?>

<h1><?= htmlspecialchars($moduleLabel) ?></h1>

<form method="post" id="genericForm"
    action="index.php?modulo=<?= $modulo ?>&operation=save&id=<?= $beanId ?>&redirect=detail">
    <?php
    foreach ($data['fields'] as $name => $info) {
        $value = $bean[$name] ?? '';
        renderFormField($name, $info, $value, $actionType);
    }
    ?>

    <?php if ($itemsConfig !== false)
        foreach ($itemsConfig as $itemModule) {
            if (!($itemModule['enabled'] ?? true)) continue;

            if (empty($itemModule['module'])) continue;

            echo "<h2>" . $itemModule['label'] . "</h2>";
            renderComponent('formItems', [
                'parentModule' => $modulo,
                'modulo' => $itemModule['module'],
                'itemConfig' => $itemModule,
                'beanId' => $beanId,
                'actionType' => $actionType,
            ]);
        }
    ?>

    <?php if ($actionType != 'readonly') { ?>
        <button type="submit" <?= $actionType ?>>Salvar</button>
    <?php } ?>
</form>
<script src="core/assets/js/ux/relatedfield.js"></script>
<script src="core/assets/js/formItems.js"></script>