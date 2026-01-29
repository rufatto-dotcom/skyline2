<style>
    body {
        background: #f4f6f8;
    }

    h1 {
        margin-bottom: 24px;
        color: #333;
    }

    .config-container {
        max-width: 480px;
    }

    .config-container a {
        text-decoration: none;
    }

    .config-container button {
        width: 100%;
        padding: 14px;
        margin-bottom: 12px;
        font-size: 15px;
        font-weight: 600;
        border: none;
        border-radius: 6px;
        background: #2f80ed;
        color: #fff;
        cursor: pointer;
        transition: background 0.2s ease;
    }

    .config-container button:hover {
        background: #1c66d6;
    }

    .config-container form {
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #ddd;
    }

    .config-container label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #555;
    }

    .config-container input[type="text"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        font-size: 14px;
    }

    .config-container form button {
        background: #27ae60;
    }

    .config-container form button:hover {
        background: #1f8f4d;
    }
</style>

<h1>Configurações</h1>

<div class="config-container">

    <a href="?modulo=studio">
        <button>Studio</button>
    </a>

    <a href="?modulo=config&action=generateMetadata">
        <button>Gerar Metadata</button>
    </a>

    <a href="?modulo=config&action=generateModules">
        <button>Carregar Módulos</button>
    </a>

</div>