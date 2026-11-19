<?php
session_start();
require_once "funcoesDB.php";

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$dataNascimento = $_POST['data_nascimento'] ?? '';
$generoUsuario = $_POST['genero_usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($nome) || empty($email) || empty($senha)) {
    header("Location: ../../view/cadastro_usuario.html?erro=campos");
    exit;
}

$ok = cadastrarUsuario($nome, $email, $dataNascimento, $generoUsuario, $senha);

if ($ok) {
    header("Location: ../../view/login_usuario.html?cadastrado=1");
} else {
    header("Location: ../../view/cadastro_usuario.html?erro=email");
}
exit;
?>
