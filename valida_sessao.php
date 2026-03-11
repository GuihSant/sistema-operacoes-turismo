<?php
// Inicia a sessão (a memória do navegador)
session_start();

// Se não existir a variável de usuário na sessão, expulsa para o login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit; // Para a execução do código imediatamente
}
?>