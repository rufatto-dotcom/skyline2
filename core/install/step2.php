<?php
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['host'] ?? '');
    $db   = trim($_POST['db'] ?? '');
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if (!$host || !$db || !$user) {
        $error = "Todos os campos são obrigatórios.";
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $configCode = "<?php\nreturn new PDO(\n" .
                "    'mysql:host=" . addslashes($host) . ";dbname=" . addslashes($db) . ";charset=utf8mb4',\n" .
                "    '" . addslashes($user) . "',\n" .
                "    '" . addslashes($pass) . "',\n" .
                "    [\n" .
                "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n" .
                "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n" .
                "        PDO::ATTR_EMULATE_PREPARES => false,\n" .
                "    ]\n" .
                ");\n";

            $configPath = dirname(__DIR__, 1) . '/config/database.connection.php';

            file_put_contents($configPath, $configCode);

            echo "<h1>Conexão Estabelecida</h1>";
            echo "<a href='step3.php' class='btn btn-primary'>Próximo</a>";
            exit;
        } catch (Throwable $e) {
            $error = 'Falha na conexão: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>

<h2>Configurar Banco de Dados</h2>

<?php if ($error): ?>
    <p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="post">
    <input name="host" placeholder="Host" value="<?= htmlspecialchars($_POST['host'] ?? 'localhost') ?>" required>
    <input name="db" placeholder="Banco de dados" value="<?= htmlspecialchars($_POST['db'] ?? '') ?>" required>
    <input name="user" placeholder="Usuário" value="<?= htmlspecialchars($_POST['user'] ?? '') ?>" required>
    <input name="pass" type="password" placeholder="Senha" value="<?= htmlspecialchars($_POST['pass'] ?? '') ?>">
    <button type="submit">Conectar e Finalizar Instalação</button>
</form>