<?php
// logout.php
session_start(); // Encontra a sessão atual
session_destroy(); // Destrói o "crachá" do usuário
header("Location: login.php"); // Manda de volta para a tela de login
exit;
?>