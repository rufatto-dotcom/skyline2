<?php

function renderComponent(string $componentName, array $data = []) {
    $filePath = CORE_COMPONENTS . "/$componentName.php";
    
    if (!file_exists($filePath)) {
        echo "<p>Componente $componentName não encontrado.</p>";
        return;
    }

    extract($data);

    include $filePath;
}
?>