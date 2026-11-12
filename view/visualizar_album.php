<?php
    require_once "../assets/processamento/funcoesDB.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Álbum</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <main>
        <article class="main-dispo">
            <a href="../view/home.html"><img class="logo" src="../assets/img/logo_vinil_fundo_transparente.png" alt="logo"></a>
        </article>

        <article class="main-dispo">
            <ul>
                <a class="ativo" href="../view/albuns.php"><li>Álbuns</li></a>
                <a href="../view/generos.html"><li>Gêneros</li></a>
                <a href="../view/artistas.html"><li>Artistas</li></a>
                <a href="../view/novidades.html"><li>Novidades</li></a>
            </ul>
        </article>

        <article class="main-dispo">
            <img class="icon" src="../assets/img/icons-pesquisar.png" alt="pesquisar">
            <img class="icon" src="../assets/img/icons-gostar.png" alt="gostei">
            <img class="icon" src="../assets/img/icons-usuário.png" alt="usuário">
        </article>
    </main>


    <section class="all-album">
        <a class="voltar" href="../view/albuns.php">← Voltar para álbuns</a>

        <?php
$id = $_GET['id'];
$listaAlbuns = retornarAlbumId($id);
$album = mysqli_fetch_assoc($listaAlbuns);

if ($album) {

    echo "<section class=\"album-topo\">";

        echo "<article class=\"album-capa-box\">";
            echo "<div class=\"album-disco\"></div>";
            echo "<img class=\"album-capa\" src=\"../assets/" . $album["capa_url"] . "\" alt=\"capa álbum\">";
        echo "</article>";

        echo "<article class=\"album-info\">";

        echo "<span class=\"tag-genero\">" . $album['nome_genero'] . "</span>";
            echo "<h1>" . $album['titulo'] . "</h1>";
            echo "<a class=\"album-artista\" href=\"../view/artistas.html\">" . $album['nome_artista'] . " →</a>";

            echo "<section class=\"album-dados\">";

                echo "<article>";
                    echo "<p>Ano</p>";
                    echo "<h3>" . $album['ano_lancamento'] . "</h3>";
                echo "</article>";

                echo "<article>";
                    echo "<p>Gravadora</p>";
                    echo "<h3>" . $album['gravadora'] . "</h3>";
                echo "</article>";

            echo "</section>";

            echo "<p class=\"album-desc\">" . $album['descricao'] . "</p>";

            echo "<section class=\"album-botoes\">";
                echo "<a href=\"" . $album['link_spotify'] . "\"><button class=\"button-ama-album\">▶ Ouvir</button></a>";
                echo "<a href=\"" . $album['link_compra'] . "\"><button class=\"button-comprar\">Comprar</button></a>";
                echo "<button class=\"button-curtir\">♡</button>";
            echo "</section>";

        echo "</article>";

    echo "</section>";

} else {
    echo "Álbum não encontrado.";
}
?>
        
        <?php
$listaMusicas = retornarMusicasAlbum($id);
$totalMusicas = mysqli_num_rows($listaMusicas);
?>

<article class="cab-secao">
    <h1>Faixas</h1>
    <hr>
    <p><?php echo $totalMusicas; ?> faixas</p>
</article>

<ul class="lista-faixas">

    <?php
    while($musica = mysqli_fetch_assoc($listaMusicas)){
    ?>

        <li>
            <span class="faixa-num"><?php echo $musica['numero_faixa']; ?></span>

            <span class="faixa-titulo"><?php echo $musica['titulo']; ?></span>

            <span class="faixa-dur"><?php echo $musica['duracao']; ?></span>
        </li>

    <?php
    }
    ?>

</ul>
    </section>

</body>
</html>