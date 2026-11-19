<?php
session_start();
require_once "funcoesDB.php";

if (!usuarioEhAdmin()) {
    header("Location: ../../view/login_usuario.html");
    exit;
}

$tipo = $_GET['tipo'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    if ($tipo === 'usuario') {
    if ($id === intval($_SESSION['id_usuario'])) {
        header("Location: ../../view/usuarios_cadastrados.html?erro_proprio=1");
        exit;
    }
    deletarUsuarioAdmin($id);
    header("Location: ../../view/usuarios_cadastrados.html?excluido=1");
    exit;
}

header("Location: ../../view/gerenciar_catalogo.html");
    exit;
}

if ($tipo === 'artista') {
    $qtdAlbuns = contarAlbunsDoArtista($id);
    if ($qtdAlbuns > 0) {
        header("Location: ../../view/gerenciar_catalogo.html?aba=artistas&erro_dep=artista&qtd=" . $qtdAlbuns);
        exit;
    }
    deletarArtista($id);
    header("Location: ../../view/gerenciar_catalogo.html?aba=artistas&excluido=1");
    exit;
}

if ($tipo === 'album') {
    $qtdMusicas = contarMusicasDoAlbum($id);
    if ($qtdMusicas > 0) {
        header("Location: ../../view/gerenciar_catalogo.html?aba=albuns&erro_dep=album&qtd=" . $qtdMusicas);
        exit;
    }
    deletarAlbum($id);
    header("Location: ../../view/gerenciar_catalogo.html?aba=albuns&excluido=1");
    exit;
}

if ($tipo === 'musica') {
    deletarMusica($id);
    header("Location: ../../view/gerenciar_catalogo.html?aba=musicas&excluido=1");
    exit;
}

if ($tipo === 'usuario') {
    if ($id === intval($_SESSION['id_usuario'])) {
        header("Location: ../../view/usuarios_cadastrados.html?erro_proprio=1");
        exit;
    }
    deletarUsuarioAdmin($id);
    header("Location: ../../view/usuarios_cadastrados.html?excluido=1");
    exit;
}

header("Location: ../../view/gerenciar_catalogo.html");
exit;
?>
