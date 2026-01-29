    <?php

    function renderFormField(
        string $name,
        array $info,
        $value,
        string $actionType,
        array $context = []
    ) {
        $treatment = $info['treatment'] ?? 'show';

        if ($treatment === 'remove') {
            return;
        }

        $id        = normalizeFieldId($name);
        $fieldName = extractFieldName($name);
        $relation = null;

        if (!empty($context['relations'])) {
            foreach ($context['relations'] as $rel) {
                if (($rel['foreign_key'] ?? null) === $fieldName) {
                    $relation = $rel;
                    break;
                }
            }
        }

        $isRelationField = $relation !== null;

        if ($isRelationField) {
            renderRelatedField(
                $name,
                $id,
                $relation,
                $value,
                $actionType
            );
            return;
        }

        if ($treatment === 'hidden') {
            echo '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' .
                htmlspecialchars($value, ENT_QUOTES) . '">';
            return;
        }

        renderDefaultField(
            $name,
            $id,
            $info,
            $value,
            $actionType
        );
    }


    function renderRelatedField(
        string $name,
        string $id,
        array $relation,
        $value,
        string $actionType,
    ) {
        $searchField = $relation['search_field']
            ?? $relation['related_field']
            ?? 'id';

        $relatedPk = $relation['related_field'] ?? 'id';
    ?>
        <div style="margin-bottom: 10px;">

            <input
                type="hidden"
                id="<?= $id ?>"
                name="<?= htmlspecialchars($name) ?>"
                value="<?= htmlspecialchars($value, ENT_QUOTES) ?>">

            <input
                type="text"
                id="<?= $id ?>_label"
                data-related-name="<?= $id ?>"
                data-related-module="<?= htmlspecialchars($relation['related_module']) ?>"
                data-related-pk="<?= htmlspecialchars($relatedPk) ?>"
                data-search-field="<?= htmlspecialchars($searchField) ?>"
                <?= $actionType ?>>

            <div class="resultado-busca"></div>
        </div>
    <?php
    }

    function renderDefaultField(
        string $name,
        string $id,
        array $info,
        $value,
        string $actionType
    ) {
        $type = $info['input'] ?? 'text';
    ?>
        <div style="margin-bottom: 10px;">

            <?php if ($type === 'checkbox'): ?>
                <input type="hidden" name="<?= htmlspecialchars($name) ?>" value="0">
                <input
                    type="checkbox"
                    id="<?= $id ?>"
                    name="<?= htmlspecialchars($name) ?>"
                    value="1"
                    <?= $value ? 'checked' : '' ?>
                    <?= $actionType ?>>
            <?php else: ?>
                <input
                    type="<?= htmlspecialchars($type) ?>"
                    id="<?= $id ?>"
                    name="<?= htmlspecialchars($name) ?>"
                    value="<?= htmlspecialchars($value, ENT_QUOTES) ?>"
                    data-field="<?= htmlspecialchars($id) ?>"
                    <?= $actionType ?>>
            <?php endif; ?>
        </div>
    <?php
    }

    function normalizeFieldId(string $name): string
    {
        return preg_replace('/[\[\]]+/', '_', $name);
    }

    function extractFieldName(string $name): string
    {
        if (preg_match('/\[(.*?)\]$/', $name, $m)) {
            return $m[1];
        }
        return $name;
    }
