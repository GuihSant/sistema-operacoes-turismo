<?php
// criar_admin.php
require_once 'conexao.php';

$nome = 'Administrador';
$email = 'guilherme@ihu.com';
$senha_plana = 'gui123';

// A MÁGICA DA CIBERSEGURANÇA: Essa função gera um hash real e indecifrável
$senha_criptografada = password_hash($senha_plana, PASSWORD_DEFAULT);

try {
    // Primeiro, vamos apagar aquele usuário falso que criamos no SQL
    $pdo->query("DELETE FROM usuarios WHERE email = 'admin@aprendiaviajar.com'");

    // Agora, inserimos o usuário com a criptografia real do PHP
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :hash)");
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'hash' => $senha_criptografada
    ]);

    echo "<h2 style='color: green; text-align: center; font-family: sans-serif; margin-top: 50px;'>
            ✅ Usuário Admin recriado com Criptografia Real!<br>
            Você já pode acessar o sistema com a senha: admin123
          </h2>";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>