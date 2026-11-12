<?php

function conectarBD(){
    $conexao = mysqli_connect("localhost", "root", "", "vinil1");
    return($conexao);
}
function retornarAlbuns(){
    $conexao = conectarBD();
    $consulta = "SELECT * FROM albuns where id_genero = 4";
    $listaAlbuns = mysqli_query($conexao,$consulta);
    return $listaAlbuns;
}
function retornarAlbumId($id){
    $conexao = conectarBD();

    $consulta = "SELECT albuns.*, artistas.nome AS nome_artista, generos.nome AS nome_genero
                 FROM albuns
                 INNER JOIN artistas
                 ON albuns.id_artista = artistas.id_artista
                 INNER JOIN generos
                 ON albuns.id_genero = generos.id_genero
                 WHERE albuns.id_album = $id";

    $resultado = mysqli_query($conexao, $consulta);

    return $resultado;
}
function retornarMusicasAlbum($id){
    $conexao = conectarBD();

    $consulta = "SELECT * FROM musicas WHERE id_album = $id ORDER BY numero_faixa ASC";

    $resultado = mysqli_query($conexao, $consulta);

    return $resultado;
}
function inserirMusica($idAlbum, $numeroFaixa, $titulo, $duracao){

    $conexao = conectarBD();

    $consulta = "INSERT INTO musicas
    (id_album, numero_faixa, titulo, duracao)
    VALUES
    ('$idAlbum', '$numeroFaixa', '$titulo', '$duracao')";

    mysqli_query($conexao, $consulta);
}
function inserirAlbum($titulo, $ano, $gravadora, $formato, $condicao, $descricao, $capa, $linkCompra, $linkSpotify, $idArtista, $idGenero, $idUsuario){

    $conexao = conectarBD();

    $pasta = "../assets/img/albuns/" . $idGenero . "/";

    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeImagem = uniqid() . "_" . basename($capa['name']);

    move_uploaded_file($capa['tmp_name'], $pasta . $nomeImagem);

    $capaUrl = "albuns/" . $idGenero . "/" . $nomeImagem;

    $consulta = "INSERT INTO albuns
    (titulo, ano_lancamento, gravadora, formato, condicao, descricao, capa_url, link_compra, link_spotify, id_artista, id_genero, id_usuario_cadastro, criado_em)
    VALUES
    ('$titulo', '$ano', '$gravadora', '$formato', '$condicao', '$descricao', '$capaUrl', '$linkCompra', '$linkSpotify', '$idArtista', '$idGenero', '$idUsuario', NOW())";

    mysqli_query($conexao, $consulta);

    return mysqli_insert_id($conexao);
}
?>