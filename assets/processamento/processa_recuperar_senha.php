<?php
session_start();
require_once "funcoesDB.php";

$email = $_POST['email'] ?? '';
$novaSenha = $_POST['nova_senha'] ?? '';

if (empty($email) || empty($novaSenha)) {
    header("Location: ../../view/recuperar_senha.html?erro=1");
    exit;
}

$ok = redefinirSenhaUsuario($email, $novaSenha);

if ($ok) {
    header("Location: ../../view/login_usuario.html?senha_alterada=1");
} else {
    header("Location: ../../view/recuperar_senha.html?erro=1");
}
exit;
?>
