<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Instalação do Sistema</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .panel {
            background: #fff;
            padding: 30px;
            width: 420px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
        }
        .panel h1 {
            margin-top: 0;
            font-size: 22px;
        }
        .panel p {
            color: #555;
            line-height: 1.5;
        }
        .actions {
            margin-top: 25px;
            display: flex;
            gap: 10px;
        }
        .btn {
            flex: 1;
            padding: 10px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-danger {
            background: #d9534f;
            color: #fff;
        }
        .btn-secondary {
            background: #ccc;
        }
    </style>
</head>
<body>

<div class="panel">
    <h1>Sistema não configurado</h1>

    <p>
        Não foi possível conectar ao banco de dados.
        Isso normalmente acontece quando o sistema ainda não foi instalado
        ou quando as configurações do banco mudaram.
    </p>

    <p>
        Deseja reinstalar ou reconfigurar o sistema?
        <strong>Essa ação não apaga dados automaticamente</strong>,
        mas permitirá configurar um novo banco.
    </p>

    <div class="actions">
        <a href="step1.php">
            <button class="btn btn-danger" type="button">
                Reinstalar / Reconfigurar
            </button>
        </a>

        <form method="get" action="/">
            <button class="btn btn-secondary" type="submit">
                Cancelar
            </button>
        </form>
    </div>
</div>

</body>
</html>
