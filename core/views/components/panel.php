<link rel="stylesheet" href="core/assets/css/panels.php">

<div class="container-sub-listiview">
    <h2 class="sub-listiview-title"><?= $titulo ?></h2>

    <div class="sublistiview-vision">
        <table class="sublistiview">
            <thead>
                <tr>
                    <?php foreach ($fields as $field): ?>
                        <th><?= $field ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <td><?= htmlspecialchars($row[$field] ?? '') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>