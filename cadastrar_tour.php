<?php
// 1. Conecta ao banco de dados
require_once 'conexao.php';
require_once 'valida_sessao.php';

$mensagem = '';

// 2. Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_tour = trim($_POST['nome_tour']);
    // Troca vírgula por ponto caso o usuário digite "150,50" em vez de "150.50"
    $valor_base = str_replace(',', '.', $_POST['valor_base']); 

    if (!empty($nome_tour) && is_numeric($valor_base)) {
        try {
            // 3. Insere o novo tour no Supabase de forma segura (prevenindo SQL Injection)
            $stmt = $pdo->prepare("INSERT INTO tours (nome_tour, valor_base) VALUES (:nome, :valor)");
            $stmt->execute([
                'nome' => $nome_tour,
                'valor' => $valor_base
            ]);
            
            $mensagem = "<div class='msg-sucesso'>Tour '{$nome_tour}' cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='msg-erro'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
        }
    } else {
        $mensagem = "<div class='msg-erro'>Por favor, preencha todos os campos corretamente.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Novo Tour</title>
    <style>
        /* Mantendo o mesmo padrão visual limpo */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; padding: 40px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        h2 { text-align: center; color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        /* input step="0.01" permite centavos */
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background-color: #28a745; color: white; border: none; border-radius: 5px; font-size: 18px; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn:hover { background-color: #218838; }
        .btn-voltar { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; }
        .btn-voltar:hover { text-decoration: underline; }
        .msg-sucesso { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #c3e6cb; }
        .msg-erro { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="card">
    <h2>Cadastrar Novo Tour</h2>
    
    <?= $mensagem ?>

    <form action="cadastrar_tour.php" method="POST">
        <div class="form-group">
            <label for="nome_tour">Nome do Tour / Passeio:</label>
            <input type="text" name="nome_tour" id="nome_tour" placeholder="Ex: City Tour Completo" required>
        </div>

        <div class="form-group">
            <label for="valor_base">Valor Base ($):</label>
            <input type="number" step="0.01" name="valor_base" id="valor_base" placeholder="Ex: 150.00" required>
        </div>

        <button type="submit" class="btn">Salvar Tour</button>
    </form>

    <a href="index.php" class="btn-voltar">← Voltar para Gerador de Derivações</a>
</div>

</body>
</html>