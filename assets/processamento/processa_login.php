<?php
session_start();
require_once "funcoesDB.php";

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$usuario = logarUsuario($email, $senha);

if ($usuario) {
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nome_usuario'] = $usuario['nome'];
    $_SESSION['email_usuario'] = $usuario['email'];
    $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
    header("Location: ../../view/home.html");
} else {
    header("Location: ../../view/login_usuario.html?erro=1");
}
exit;
?>
