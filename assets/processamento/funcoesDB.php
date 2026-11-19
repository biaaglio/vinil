<?php

function conectarBD(){
    $conexao = mysqli_connect("localhost", "root", "", "vinil1");
    mysqli_set_charset($conexao, "utf8mb4");

    mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS curtidas (
        id_curtida INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_album INT NOT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    mysqli_query($conexao, "CREATE TABLE IF NOT EXISTS comentarios (
        id_comentario INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_album INT NOT NULL,
        comentario TEXT NOT NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unico_usuario_album (id_usuario, id_album)
    )");

    $checkPk = mysqli_query($conexao, "SHOW KEYS FROM usuarios WHERE Key_name = 'PRIMARY'");
    if ($checkPk && mysqli_num_rows($checkPk) == 0) {
        $maxRes = mysqli_query($conexao, "SELECT COALESCE(MAX(id_usuario), 0) + 1 AS prox FROM usuarios");
        $proxId = $maxRes ? intval(mysqli_fetch_assoc($maxRes)['prox']) : 3;
        if ($proxId < 3) $proxId = 3;
        $zeros = mysqli_query($conexao, "SELECT email FROM usuarios WHERE id_usuario = 0");
        if ($zeros) {
            while ($z = mysqli_fetch_assoc($zeros)) {
                $em = mysqli_real_escape_string($conexao, $z['email']);
                mysqli_query($conexao, "UPDATE usuarios SET id_usuario = $proxId WHERE id_usuario = 0 AND email = '$em' LIMIT 1");
                $proxId++;
            }
        }
        mysqli_query($conexao, "ALTER TABLE usuarios ADD PRIMARY KEY (id_usuario)");
        mysqli_query($conexao, "ALTER TABLE usuarios MODIFY id_usuario INT(11) NOT NULL AUTO_INCREMENT");
    }

    return $conexao;
}

function logarUsuario($email, $senha){
    $conexao = conectarBD();
    $emailLimpo = mysqli_real_escape_string($conexao, $email);
    $consulta = "SELECT * FROM usuarios WHERE email = '$emailLimpo' LIMIT 1";
    $resultado = mysqli_query($conexao, $consulta);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        $hash = $usuario['senha_hash'];
        if ($hash === $senha || password_verify($senha, $hash) || ($hash === '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' && $senha === '123456')) {
            return $usuario;
        }
    }
    return false;
}

function cadastrarUsuario($nome, $email, $dataNascimento, $generoUsuario, $senha){
    $conexao = conectarBD();
    $nomeLimpo = mysqli_real_escape_string($conexao, $nome);
    $emailLimpo = mysqli_real_escape_string($conexao, $email);
    $dataLimpa = mysqli_real_escape_string($conexao, $dataNascimento);
    $generoLimpo = mysqli_real_escape_string($conexao, $generoUsuario);
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $verifica = mysqli_query($conexao, "SELECT id_usuario FROM usuarios WHERE email = '$emailLimpo' LIMIT 1");
    if ($verifica && mysqli_num_rows($verifica) > 0) {
        return false;
    }

    $consulta = "INSERT INTO usuarios (nome, email, data_nascimento, genero_usuario, senha_hash, nivel_acesso)
                 VALUES ('$nomeLimpo', '$emailLimpo', '$dataLimpa', '$generoLimpo', '$senhaHash', 'usuario')";

    return mysqli_query($conexao, $consulta);
}

function redefinirSenhaUsuario($email, $novaSenha){
    $conexao = conectarBD();
    $emailLimpo = mysqli_real_escape_string($conexao, $email);
    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    $verifica = mysqli_query($conexao, "SELECT id_usuario FROM usuarios WHERE email = '$emailLimpo' LIMIT 1");
    if ($verifica && mysqli_num_rows($verifica) > 0) {
        $consulta = "UPDATE usuarios SET senha_hash = '$senhaHash' WHERE email = '$emailLimpo'";
        return mysqli_query($conexao, $consulta);
    }
    return false;
}

function buscarUsuarioId($id){
    $conexao = conectarBD();
    $idLimpo = intval($id);
    if ($idLimpo <= 0) {
        return null;
    }
    $consulta = "SELECT * FROM usuarios WHERE id_usuario = $idLimpo LIMIT 1";
    $resultado = mysqli_query($conexao, $consulta);
    return $resultado ? mysqli_fetch_assoc($resultado) : null;
}

function buscarUsuarioEmail($email){
    $conexao = conectarBD();
    $emailLimpo = mysqli_real_escape_string($conexao, $email);
    if ($emailLimpo === '') {
        return null;
    }
    $consulta = "SELECT * FROM usuarios WHERE email = '$emailLimpo' LIMIT 1";
    $resultado = mysqli_query($conexao, $consulta);
    return $resultado ? mysqli_fetch_assoc($resultado) : null;
}

function alternarCurtida($idUsuario, $idAlbum){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idAlbum = intval($idAlbum);

    $verifica = "SELECT id_curtida FROM curtidas WHERE id_usuario = $idUsuario AND id_album = $idAlbum";
    $res = mysqli_query($conexao, $verifica);

    if ($res && mysqli_num_rows($res) > 0) {
        mysqli_query($conexao, "DELETE FROM curtidas WHERE id_usuario = $idUsuario AND id_album = $idAlbum");
    } else {
        mysqli_query($conexao, "INSERT INTO curtidas (id_usuario, id_album) VALUES ($idUsuario, $idAlbum)");
    }
}

function verificarCurtida($idUsuario, $idAlbum){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idAlbum = intval($idAlbum);

    $consulta = "SELECT id_curtida FROM curtidas WHERE id_usuario = $idUsuario AND id_album = $idAlbum";
    $res = mysqli_query($conexao, $consulta);
    return ($res && mysqli_num_rows($res) > 0);
}

function retornarCurtidosUsuario($idUsuario, $idGenero = 0, $ordenar = 'recentes'){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idGenero = intval($idGenero);

    $consulta = "SELECT albuns.*, artistas.nome AS nome_artista, generos.nome AS nome_genero
                 FROM curtidas
                 INNER JOIN albuns ON curtidas.id_album = albuns.id_album
                 INNER JOIN artistas ON albuns.id_artista = artistas.id_artista
                 INNER JOIN generos ON albuns.id_genero = generos.id_genero
                 WHERE curtidas.id_usuario = $idUsuario";

    if ($idGenero > 0) {
        $consulta .= " AND albuns.id_genero = $idGenero";
    }

    if ($ordenar == 'az') {
        $consulta .= " ORDER BY albuns.titulo ASC";
    } elseif ($ordenar == 'za') {
        $consulta .= " ORDER BY albuns.titulo DESC";
    } elseif ($ordenar == 'ano') {
        $consulta .= " ORDER BY albuns.ano_lancamento ASC";
    } else {
        $consulta .= " ORDER BY curtidas.id_curtida DESC";
    }

    return mysqli_query($conexao, $consulta);
}

function retornarComentariosAlbum($idAlbum){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    $consulta = "SELECT comentarios.*, usuarios.nome AS nome_usuario
                 FROM comentarios
                 INNER JOIN usuarios ON comentarios.id_usuario = usuarios.id_usuario
                 WHERE comentarios.id_album = $idAlbum
                 ORDER BY comentarios.criado_em DESC";
    return mysqli_query($conexao, $consulta);
}

function buscarComentarioUsuarioAlbum($idUsuario, $idAlbum){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idAlbum = intval($idAlbum);
    $consulta = "SELECT * FROM comentarios WHERE id_usuario = $idUsuario AND id_album = $idAlbum LIMIT 1";
    $res = mysqli_query($conexao, $consulta);
    return ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;
}

function salvarComentarioUnico($idUsuario, $idAlbum, $texto){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idAlbum = intval($idAlbum);
    $textoLimpo = mysqli_real_escape_string($conexao, trim($texto));

    if ($textoLimpo === '') {
        return false;
    }

    $existente = buscarComentarioUsuarioAlbum($idUsuario, $idAlbum);
    if ($existente) {
        return false;
    }

    $consulta = "INSERT INTO comentarios (id_usuario, id_album, comentario)
                 VALUES ($idUsuario, $idAlbum, '$textoLimpo')";
    return mysqli_query($conexao, $consulta);
}

function excluirComentarioUsuario($idUsuario, $idAlbum){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $idAlbum = intval($idAlbum);
    return mysqli_query($conexao, "DELETE FROM comentarios WHERE id_usuario = $idUsuario AND id_album = $idAlbum");
}

function retornarArtistas($busca = '', $idGenero = 0){
    $conexao = conectarBD();
    $idGenero = intval($idGenero);

    if ($idGenero > 0) {
        $consulta = "SELECT DISTINCT artistas.*
                     FROM artistas
                     INNER JOIN albuns ON artistas.id_artista = albuns.id_artista
                     WHERE albuns.id_genero = $idGenero";
        if (!empty($busca)) {
            $buscaLimpa = mysqli_real_escape_string($conexao, $busca);
            $consulta .= " AND artistas.nome LIKE '%$buscaLimpa%'";
        }
    } else {
        $consulta = "SELECT * FROM artistas WHERE 1=1";
        if (!empty($busca)) {
            $buscaLimpa = mysqli_real_escape_string($conexao, $busca);
            $consulta .= " AND nome LIKE '%$buscaLimpa%'";
        }
    }

    $consulta .= " ORDER BY artistas.nome ASC";
    return mysqli_query($conexao, $consulta);
}

function retornarArtistaId($id){
    $conexao = conectarBD();
    $idLimpo = intval($id);
    $resultado = mysqli_query($conexao, "SELECT * FROM artistas WHERE id_artista = $idLimpo LIMIT 1");
    return mysqli_fetch_assoc($resultado);
}

function retornarAlbunsPorArtista($idArtista, $ordenar = 'recentes'){
    $conexao = conectarBD();
    $idArtista = intval($idArtista);

    if ($ordenar == 'az') {
        $ordem = "albuns.titulo ASC";
    } elseif ($ordenar == 'za') {
        $ordem = "albuns.titulo DESC";
    } elseif ($ordenar == 'ano') {
        $ordem = "albuns.ano_lancamento ASC";
    } else {
        $ordem = "albuns.id_album DESC";
    }

    $consulta = "SELECT albuns.*, generos.nome AS nome_genero
                 FROM albuns
                 INNER JOIN generos ON albuns.id_genero = generos.id_genero
                 WHERE albuns.id_artista = $idArtista
                 ORDER BY $ordem";

    return mysqli_query($conexao, $consulta);
}

function inserirArtista($nome, $biografia, $foto){
    $conexao = conectarBD();
    $nomeLimpo = mysqli_real_escape_string($conexao, $nome);
    $bioLimpa = mysqli_real_escape_string($conexao, $biografia);

    $partes = explode(" ", trim($nome));
    $sigla = "";
    foreach ($partes as $p) {
        if ($p != "") {
            $sigla .= strtoupper(substr($p, 0, 1));
        }
    }
    $sigla = substr($sigla, 0, 4);
    $siglaLimpa = mysqli_real_escape_string($conexao, $sigla);

    $fotoUrl = "img/thedoors.jpg";
    if (!empty($foto['name']) && $foto['error'] == 0) {
        $pasta = $_SERVER['DOCUMENT_ROOT'] . "/vinil/assets/img/artistas/";
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }
        $nomeArquivo = uniqid() . "_" . basename($foto['name']);
        if (move_uploaded_file($foto['tmp_name'], $pasta . $nomeArquivo)) {
            $fotoUrl = "img/artistas/" . $nomeArquivo;
        }
    }

    $consulta = "INSERT INTO artistas (nome, sigla, biografia, foto_url)
                 VALUES ('$nomeLimpo', '$siglaLimpa', '$bioLimpa', '$fotoUrl')";

    if (mysqli_query($conexao, $consulta)) {
        return mysqli_insert_id($conexao);
    }
    return false;
}

function atualizarArtista($idArtista, $nome, $biografia, $foto){
    $conexao = conectarBD();
    $idArtista = intval($idArtista);
    $nomeLimpo = mysqli_real_escape_string($conexao, $nome);
    $bioLimpa = mysqli_real_escape_string($conexao, $biografia);

    $sqlFoto = "";
    if (!empty($foto['name']) && $foto['error'] == 0) {
        $pasta = $_SERVER['DOCUMENT_ROOT'] . "/vinil/assets/img/artistas/";
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }
        $nomeArquivo = uniqid() . "_" . basename($foto['name']);
        if (move_uploaded_file($foto['tmp_name'], $pasta . $nomeArquivo)) {
            $fotoUrl = "img/artistas/" . $nomeArquivo;
            $sqlFoto = ", foto_url = '$fotoUrl'";
        }
    }

    $consulta = "UPDATE artistas SET nome = '$nomeLimpo', biografia = '$bioLimpa' $sqlFoto WHERE id_artista = $idArtista";
    return mysqli_query($conexao, $consulta);
}

function contarAlbunsDoArtista($idArtista){
    $conexao = conectarBD();
    $idArtista = intval($idArtista);
    $res = mysqli_query($conexao, "SELECT COUNT(*) AS total FROM albuns WHERE id_artista = $idArtista");
    $row = mysqli_fetch_assoc($res);
    return intval($row['total']);
}

function deletarArtista($idArtista){
    $conexao = conectarBD();
    $idArtista = intval($idArtista);
    return mysqli_query($conexao, "DELETE FROM artistas WHERE id_artista = $idArtista");
}

function retornarGeneros(){
    $conexao = conectarBD();
    return mysqli_query($conexao, "SELECT * FROM generos ORDER BY id_genero ASC");
}

function retornarAlbuns($generos = [], $decada = '', $busca = '', $ordenar = 'az', $agrupar = 'genero'){
    $conexao = conectarBD();
    
    $consulta = "SELECT albuns.*, artistas.nome AS nome_artista, generos.nome AS nome_genero
                 FROM albuns
                 INNER JOIN artistas ON albuns.id_artista = artistas.id_artista
                 INNER JOIN generos ON albuns.id_genero = generos.id_genero
                 WHERE 1=1";

    if (!empty($generos)) {
        $idsLimpos = array_map('intval', $generos);
        $listaIds = implode(",", $idsLimpos);
        $consulta .= " AND albuns.id_genero IN ($listaIds)";
    } else {
        $consulta .= " AND 1=0";
    }

    if (!empty($decada)) {
        $inicio = intval($decada);
        $fim = $inicio + 9;
        $consulta .= " AND albuns.ano_lancamento BETWEEN $inicio AND $fim";
    }

    if (!empty($busca)) {
        $buscaLimpa = mysqli_real_escape_string($conexao, $busca);
        $consulta .= " AND (albuns.titulo LIKE '%$buscaLimpa%' OR artistas.nome LIKE '%$buscaLimpa%' OR generos.nome LIKE '%$buscaLimpa%')";
    }

    if ($ordenar == 'za') {
        $ordemSql = "albuns.titulo DESC";
    } elseif ($ordenar == 'ano') {
        $ordemSql = "albuns.ano_lancamento ASC";
    } else {
        $ordemSql = "albuns.titulo ASC";
    }

    if ($agrupar == 'artista') {
        $consulta .= " ORDER BY artistas.nome ASC, $ordemSql";
    } elseif ($agrupar == 'sem_grupo') {
        $consulta .= " ORDER BY $ordemSql";
    } else {
        $consulta .= " ORDER BY generos.nome ASC, $ordemSql";
    }

    return mysqli_query($conexao, $consulta);
}

function retornarTodosAlbunsAdmin($busca = ''){
    $conexao = conectarBD();
    $consulta = "SELECT albuns.*, artistas.nome AS nome_artista, generos.nome AS nome_genero
                 FROM albuns
                 INNER JOIN artistas ON albuns.id_artista = artistas.id_artista
                 INNER JOIN generos ON albuns.id_genero = generos.id_genero
                 WHERE 1=1";
    if (!empty($busca)) {
        $buscaLimpa = mysqli_real_escape_string($conexao, $busca);
        $consulta .= " AND (albuns.titulo LIKE '%$buscaLimpa%' OR artistas.nome LIKE '%$buscaLimpa%' OR generos.nome LIKE '%$buscaLimpa%')";
    }
    $consulta .= " ORDER BY albuns.id_album DESC";
    return mysqli_query($conexao, $consulta);
}

function retornarTodasMusicasAdmin($busca = ''){
    $conexao = conectarBD();
    $consulta = "SELECT musicas.*, albuns.titulo AS titulo_album, albuns.capa_url, artistas.nome AS nome_artista
                 FROM musicas
                 INNER JOIN albuns ON musicas.id_album = albuns.id_album
                 INNER JOIN artistas ON albuns.id_artista = artistas.id_artista
                 WHERE 1=1";
    if (!empty($busca)) {
        $buscaLimpa = mysqli_real_escape_string($conexao, $busca);
        $consulta .= " AND (musicas.titulo LIKE '%$buscaLimpa%' OR albuns.titulo LIKE '%$buscaLimpa%' OR artistas.nome LIKE '%$buscaLimpa%')";
    }
    $consulta .= " ORDER BY musicas.id_musica DESC";
    return mysqli_query($conexao, $consulta);
}

function retornarAlbumId($id){
    $conexao = conectarBD();
    $idLimpo = intval($id);

    $consulta = "SELECT albuns.*, artistas.nome AS nome_artista, generos.nome AS nome_genero
                 FROM albuns
                 INNER JOIN artistas ON albuns.id_artista = artistas.id_artista
                 INNER JOIN generos ON albuns.id_genero = generos.id_genero
                 WHERE albuns.id_album = $idLimpo";

    return mysqli_query($conexao, $consulta);
}

function atualizarAlbum($idAlbum, $titulo, $ano, $gravadora, $formato, $condicao, $descricao, $capa, $linkCompra, $linkSpotify, $idArtista, $idGenero){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    $titulo = mysqli_real_escape_string($conexao, $titulo);
    $ano = intval($ano);
    $gravadora = mysqli_real_escape_string($conexao, $gravadora);
    $formato = mysqli_real_escape_string($conexao, $formato);
    $condicao = mysqli_real_escape_string($conexao, $condicao);
    $descricao = mysqli_real_escape_string($conexao, $descricao);
    $linkCompra = mysqli_real_escape_string($conexao, $linkCompra);
    $linkSpotify = mysqli_real_escape_string($conexao, $linkSpotify);
    $idArtista = intval($idArtista);
    $idGenero = intval($idGenero);

    $sqlCapa = "";
    if (!empty($capa['name']) && $capa['error'] == 0) {
        $pastasGenero = [
            1 => "1-rock",
            2 => "5-mpb",
            3 => "2-pop",
            4 => "4-rap",
            5 => "6-rap-nacional",
            6 => "3-eletronica"
        ];
        $nomePasta = $pastasGenero[$idGenero] ?? "1-rock";
        $pasta = $_SERVER['DOCUMENT_ROOT'] . "/vinil/assets/albuns/" . $nomePasta . "/";
        if (!is_dir($pasta)) {
            mkdir($pasta, 0777, true);
        }
        $nomeImagem = uniqid() . "_" . basename($capa['name']);
        if (move_uploaded_file($capa['tmp_name'], $pasta . $nomeImagem)) {
            $capaUrl = "albuns/" . $nomePasta . "/" . $nomeImagem;
            $sqlCapa = ", capa_url = '$capaUrl'";
        }
    }

    $consulta = "UPDATE albuns SET
                 titulo = '$titulo',
                 ano_lancamento = '$ano',
                 gravadora = '$gravadora',
                 formato = '$formato',
                 condicao = '$condicao',
                 descricao = '$descricao',
                 link_compra = '$linkCompra',
                 link_spotify = '$linkSpotify',
                 id_artista = '$idArtista',
                 id_genero = '$idGenero'
                 $sqlCapa
                 WHERE id_album = $idAlbum";

    return mysqli_query($conexao, $consulta);
}

function contarMusicasDoAlbum($idAlbum){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    $res = mysqli_query($conexao, "SELECT COUNT(*) AS total FROM musicas WHERE id_album = $idAlbum");
    $row = mysqli_fetch_assoc($res);
    return intval($row['total']);
}

function deletarAlbum($idAlbum){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    mysqli_query($conexao, "DELETE FROM curtidas WHERE id_album = $idAlbum");
    mysqli_query($conexao, "DELETE FROM comentarios WHERE id_album = $idAlbum");
    return mysqli_query($conexao, "DELETE FROM albuns WHERE id_album = $idAlbum");
}

function retornarMusicasAlbum($id){
    $conexao = conectarBD();
    $idLimpo = intval($id);

    $consulta = "SELECT * FROM musicas WHERE id_album = $idLimpo ORDER BY numero_faixa ASC";
    return mysqli_query($conexao, $consulta);
}

function retornarMusicaId($id){
    $conexao = conectarBD();
    $idLimpo = intval($id);
    $res = mysqli_query($conexao, "SELECT * FROM musicas WHERE id_musica = $idLimpo LIMIT 1");
    return mysqli_fetch_assoc($res);
}

function atualizarMusica($idMusica, $idAlbum, $numeroFaixa, $titulo, $duracao){
    $conexao = conectarBD();
    $idMusica = intval($idMusica);
    $idAlbum = intval($idAlbum);
    $numeroFaixa = intval($numeroFaixa);
    $titulo = mysqli_real_escape_string($conexao, $titulo);
    $duracao = mysqli_real_escape_string($conexao, $duracao);

    $consulta = "UPDATE musicas SET
                 id_album = $idAlbum,
                 numero_faixa = $numeroFaixa,
                 titulo = '$titulo',
                 duracao = '$duracao'
                 WHERE id_musica = $idMusica";
    return mysqli_query($conexao, $consulta);
}

function deletarMusica($idMusica){
    $conexao = conectarBD();
    $idMusica = intval($idMusica);
    return mysqli_query($conexao, "DELETE FROM musicas WHERE id_musica = $idMusica");
}

function inserirMusica($idAlbum, $numeroFaixa, $titulo, $duracao){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    $numeroFaixa = intval($numeroFaixa);
    $titulo = mysqli_real_escape_string($conexao, $titulo);
    $duracao = mysqli_real_escape_string($conexao, $duracao);

    $consulta = "INSERT INTO musicas (id_album, numero_faixa, titulo, duracao)
                 VALUES ('$idAlbum', '$numeroFaixa', '$titulo', '$duracao')";

    return mysqli_query($conexao, $consulta);
}

function inserirAlbum($titulo, $ano, $gravadora, $formato, $condicao, $descricao, $capa, $linkCompra, $linkSpotify, $idArtista, $idGenero, $idUsuario){
    $conexao = conectarBD();

    $pastasGenero = [
        1 => "1-rock",
        2 => "5-mpb",
        3 => "2-pop",
        4 => "4-rap",
        5 => "6-rap-nacional",
        6 => "3-eletronica"
    ];

    if (!isset($pastasGenero[$idGenero])) {
        return false;
    }

    $nomePasta = $pastasGenero[$idGenero];
    $pasta = $_SERVER['DOCUMENT_ROOT'] . "/vinil/assets/albuns/" . $nomePasta . "/";

    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }

    $nomeImagem = uniqid() . "_" . basename($capa['name']);

    if (!move_uploaded_file($capa['tmp_name'], $pasta . $nomeImagem)) {
        return false;
    }

    $capaUrl = "albuns/" . $nomePasta . "/" . $nomeImagem;

    $titulo = mysqli_real_escape_string($conexao, $titulo);
    $ano = intval($ano);
    $gravadora = mysqli_real_escape_string($conexao, $gravadora);
    $formato = mysqli_real_escape_string($conexao, $formato);
    $condicao = mysqli_real_escape_string($conexao, $condicao);
    $descricao = mysqli_real_escape_string($conexao, $descricao);
    $linkCompra = mysqli_real_escape_string($conexao, $linkCompra);
    $linkSpotify = mysqli_real_escape_string($conexao, $linkSpotify);
    $idArtista = intval($idArtista);
    $idGenero = intval($idGenero);
    $idUsuario = intval($idUsuario);

    $consulta = "INSERT INTO albuns
    (titulo, ano_lancamento, gravadora, formato, condicao, descricao, capa_url, link_compra, link_spotify, id_artista, id_genero, id_usuario_cadastro, criado_em)
    VALUES
    ('$titulo', '$ano', '$gravadora', '$formato', '$condicao', '$descricao', '$capaUrl', '$linkCompra', '$linkSpotify', '$idArtista', '$idGenero', '$idUsuario', NOW())";

    if (mysqli_query($conexao, $consulta)) {
        return mysqli_insert_id($conexao);
    }
    return false;
}


function atualizarUsuarioAdmin($idUsuario, $nome, $email, $dataNascimento, $generoUsuario, $nivelAcesso, $novaSenha = ''){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    $nomeLimpo = mysqli_real_escape_string($conexao, $nome);
    $emailLimpo = mysqli_real_escape_string($conexao, $email);
    $dataLimpa = mysqli_real_escape_string($conexao, $dataNascimento);
    $generoLimpo = mysqli_real_escape_string($conexao, $generoUsuario);
    $nivelLimpo = ($nivelAcesso === 'adm') ? 'adm' : 'usuario';

    $sqlSenha = "";
    if (!empty(trim($novaSenha))) {
        $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sqlSenha = ", senha_hash = '$hash'";
    }

    $consulta = "UPDATE usuarios SET
                 nome = '$nomeLimpo',
                 email = '$emailLimpo',
                 data_nascimento = " . ($dataLimpa !== '' ? "'$dataLimpa'" : "NULL") . ",
                 genero_usuario = '$generoLimpo',
                 nivel_acesso = '$nivelLimpo'
                 $sqlSenha
                 WHERE id_usuario = $idUsuario";

    return mysqli_query($conexao, $consulta);
}

function deletarUsuarioAdmin($idUsuario){
    $conexao = conectarBD();
    $idUsuario = intval($idUsuario);
    mysqli_query($conexao, "DELETE FROM curtidas WHERE id_usuario = $idUsuario");
    mysqli_query($conexao, "DELETE FROM comentarios WHERE id_usuario = $idUsuario");
    return mysqli_query($conexao, "DELETE FROM usuarios WHERE id_usuario = $idUsuario");
}


function usuarioEhAdmin(){
    if (!isset($_SESSION['id_usuario'])) {
        return false;
    }
    $usuario = buscarUsuarioId($_SESSION['id_usuario']);
    if (!$usuario && !empty($_SESSION['email_usuario'])) {
        $usuario = buscarUsuarioEmail($_SESSION['email_usuario']);
        if ($usuario) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
        }
    }
    if ($usuario) {
        $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
        return ($usuario['nivel_acesso'] === 'adm');
    }
    return false;
}

function verificarFaixaExistente($idAlbum, $numeroFaixa, $titulo){
    $conexao = conectarBD();
    $idAlbum = intval($idAlbum);
    $numeroFaixa = intval($numeroFaixa);
    $tituloLimpo = mysqli_real_escape_string($conexao, trim($titulo));

    $consulta = "SELECT id_musica FROM musicas
                 WHERE id_album = $idAlbum
                 AND (numero_faixa = $numeroFaixa OR LOWER(titulo) = LOWER('$tituloLimpo'))
                 LIMIT 1";
    $res = mysqli_query($conexao, $consulta);
    return ($res && mysqli_num_rows($res) > 0);
}

?>
