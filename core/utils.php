<?php

function renderComponent(string $componentName, array $data = []) {
    $filePath = __DIR__ . "/views/components/$componentName.php";
    
    if (!file_exists($filePath)) {
        echo "<p>Componente $componentName não encontrado.</p>";
        return;
    }

    extract($data);

    include $filePath;
}
?>
