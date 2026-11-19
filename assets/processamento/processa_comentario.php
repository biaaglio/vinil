<?php
session_start();
require_once "funcoesDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../view/login_usuario.html");
    exit;
}

$acao = $_POST['acao'] ?? $_GET['acao'] ?? 'criar';
$idAlbum = intval($_POST['id_album'] ?? $_GET['id_album'] ?? 0);

if ($idAlbum <= 0) {
    header("Location: ../../view/albuns.html");
    exit;
}

if ($acao === 'excluir') {
    excluirComentarioUsuario($_SESSION['id_usuario'], $idAlbum);
    header("Location: ../../view/visualizar_album.html?id=" . $idAlbum . "&coment_excluido=1#comentarios");
    exit;
}

$texto = $_POST['comentario'] ?? '';
$ok = salvarComentarioUnico($_SESSION['id_usuario'], $idAlbum, $texto);

if ($ok) {
    header("Location: ../../view/visualizar_album.html?id=" . $idAlbum . "&coment_ok=1#comentarios");
} else {
    header("Location: ../../view/visualizar_album.html?id=" . $idAlbum . "&coment_erro=1#comentarios");
}
exit;
?>
