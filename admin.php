<?php
require_once 'valida_sessao.php';
require_once 'conexao.php';

// BARREIRA DE SEGURANÇA: Se não for admin, expulsa para a tela inicial
if ($_SESSION['nivel_acesso'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$mensagem = '';

// LÓGICA DE CADASTRO DE NOVO USUÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_usuario'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha_plana = $_POST['senha'];
    $nivel = $_POST['nivel_acesso'];

    if (!empty($nome) && !empty($email) && !empty($senha_plana)) {
        // Criptografa a senha antes de salvar!
        $senha_hash = password_hash($senha_plana, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES (:nome, :email, :hash, :nivel)");
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'hash' => $senha_hash,
                'nivel' => $nivel
            ]);
            $mensagem = "<div class='bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 mb-6 rounded'>Usuário cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            // Código 23505 é o erro do PostgreSQL para "E-mail já existe" (UNIQUE constraint)
            if ($e->getCode() == '23505') {
                $mensagem = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded'>Erro: Este e-mail já está cadastrado.</div>";
            } else {
                $mensagem = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded'>Erro no banco de dados: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// BUSCA TODOS OS USUÁRIOS PARA LISTAR NA TABELA
try {
    $stmt_users = $pdo->query("SELECT id, nome, email, nivel_acesso, TO_CHAR(criado_em, 'DD/MM/YYYY') as data_criacao FROM usuarios ORDER BY id DESC");
    $lista_usuarios = $stmt_users->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar usuários.");
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - Operações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 min-h-screen pb-12">

    <nav class="bg-slate-900 shadow-md px-6 py-4 flex justify-between items-center mb-8">
        <div class="font-bold text-xl text-white tracking-tight">Painel <span class="text-emerald-400">Administrativo</span></div>
        <div class="flex items-center gap-6">
            <a href="index.php" class="text-sm text-slate-300 hover:text-white transition-colors">← Voltar ao Gerador</a>
            <div class="h-6 w-px bg-slate-700"></div>
            <span class="text-sm font-medium text-slate-300">Admin: <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
            <a href="logout.php" class="text-sm text-red-400 hover:text-red-300 font-semibold transition-colors">Sair</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6">
                <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                    Novo Colaborador
                </h2>

                <?= $mensagem ?>

                <form action="admin.php" method="POST" class="space-y-4">
                    <input type="hidden" name="cadastrar_usuario" value="1">
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Nome Completo</label>
                        <input type="text" name="nome" required placeholder="Ex: João Silva" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">E-mail Corporativo</label>
                        <input type="email" name="email" required placeholder="joao@empresa.com" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Senha Inicial</label>
                        <input type="password" name="senha" required placeholder="••••••••" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Permissão de Acesso</label>
                        <select name="nivel_acesso" required class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                            <option value="operador">Operador (Gera Relatórios)</option>
                            <option value="admin">Administrador (Acesso Total)</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors mt-4">
                        Criar Usuário
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-800">Equipe Cadastrada</h2>
                    <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full"><?= count($lista_usuarios) ?> usuários</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-600">
                        <thead class="text-xs text-slate-500 uppercase bg-slate-50">
                            <tr>
                                <th class="px-6 py-3">Nome</th>
                                <th class="px-6 py-3">E-mail</th>
                                <th class="px-6 py-3">Nível</th>
                                <th class="px-6 py-3">Criado em</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista_usuarios as $user): ?>
                                <tr class="bg-white border-b hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-900"><?= htmlspecialchars($user['nome']) ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($user['nivel_acesso'] === 'admin'): ?>
                                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded border border-purple-200">Admin</span>
                                        <?php else: ?>
                                            <span class="bg-emerald-100 text-emerald-800 text-xs font-medium px-2.5 py-0.5 rounded border border-emerald-200">Operador</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4"><?= $user['data_criacao'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</body>
</html>