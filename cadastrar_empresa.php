<?php
require_once 'conexao.php';
require_once 'valida_sessao.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_empresa = trim($_POST['nome_empresa']);
    $email_contato = trim($_POST['email_contato']);

    if (!empty($nome_empresa)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO empresas (nome_empresa, email_contato) VALUES (:nome, :email)");
            $stmt->execute([
                'nome' => $nome_empresa,
                'email' => $email_contato
            ]);
            
            $mensagem = "<div class='msg-sucesso'>Empresa '{$nome_empresa}' cadastrada com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='msg-erro'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='msg-erro'>O nome da empresa é obrigatório.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Nova Empresa</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background-color: #ffc107; color: #333; border: none; border-radius: 5px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn:hover { background-color: #e0a800; }
        .btn-voltar { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
        .msg-sucesso { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #c3e6cb; }
        .msg-erro { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="card">
    <h2>Cadastrar Empresa Parceira</h2>
    <?= $mensagem ?>
    <form action="cadastrar_empresa.php" method="POST">
        <div class="form-group">
            <label for="nome_empresa">Nome da Empresa / Agência:</label>
            <input type="text" name="nome_empresa" id="nome_empresa" placeholder="Ex: Agência Viaja Mais" required>
        </div>
        <div class="form-group">
            <label for="email_contato">E-mail de Contato (Opcional):</label>
            <input type="email" name="email_contato" id="email_contato" placeholder="Ex: contato@viajamais.com">
        </div>
        <button type="submit" class="btn">Salvar Empresa</button>
    </form>
    <a href="index.php" class="btn-voltar">← Voltar para Gerador</a>
</div>

</body>
</html>