<?php
session_start();
require_once "funcoesDB.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../view/login_usuario.html");
    exit;
}

$idAlbum = isset($_GET['id']) ? intval($_GET['id']) : 0;
$origem = $_GET['origem'] ?? '';

if ($idAlbum > 0) {
    alternarCurtida($_SESSION['id_usuario'], $idAlbum);
}

if ($origem == 'curtidos') {
    header("Location: ../../view/curtidos_usuario.html");
} elseif ($idAlbum > 0) {
    header("Location: ../../view/visualizar_album.html?id=" . $idAlbum);
} else {
    header("Location: ../../view/albuns.html");
}
exit;
?>
