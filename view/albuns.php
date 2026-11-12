<?php

    require_once "../assets/processamento/funcoesDB.php";

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Álbuns</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <main>
        <article class="main-dispo">
            <a href="../view/home.html"><img class="logo" src="../assets/img/logo_vinil_fundo_transparente.png" alt="logo"></a>
        </article>

        <article class="main-dispo">
            <ul>
                <a class="ativo" href="../view/albuns.html"><li>Álbuns</li></a>
                <a href="../view/generos.html"><li>Gêneros</li></a>
                <a href="../view/artistas.html"><li>Artistas</li></a>
                <a href="../view/novidades.html"><li>Novidades</li></a>
            </ul>
        </article>

        <article class="main-dispo">
            <a href="../view/cadastrar_album.html"><button class="button-ama button-menor">+ Cadastrar álbum</button></a>
        </article>
    </main>


    <section class="all-albuns">
        <section class="barra-album">
            <input type="text" id="busca-album" placeholder="Buscar álbum, artista ou gênero">

            <select>
                <option>Ordenar: A-Z</option>
                <option>Ordenar: Z-A</option>
                <option>Ordenar: ano</option>
            </select>
        </section>

        <section class="corpo-album">
            <aside class="filtros-album">
                <article class="filtro">
                    <h3>Gênero</h3>
                    <label class="opcao"><input type="checkbox" checked> Rock</label>
                    <label class="opcao"><input type="checkbox"> MPB</label>
                    <label class="opcao"><input type="checkbox"> Pop</label>
                    <label class="opcao"><input type="checkbox"> Rap</label>
                    <label class="opcao"><input type="checkbox"> Eletrônica</label>
                </article>

                <article class="filtro">
                    <h3>Década</h3>
                    <article class="chips-decada">
                        <button class="chip-decada">60s</button>
                        <button class="chip-decada chip-ativo">70s</button>
                        <button class="chip-decada">80s</button>
                        <button class="chip-decada">90s</button>
                    </article>
                </article>

                <article class="filtro">
                    <h3>Agrupar por</h3>
                    <label class="opcao"><input type="radio" name="agrupar" checked> Artista</label>
                    <label class="opcao"><input type="radio" name="agrupar"> Gênero</label>
                    <label class="opcao"><input type="radio" name="agrupar"> Sem grupo</label>
                </article>
            </aside>

            <section class="lista-grupos">
               

                <article class="grupo-album">
                    <article class="cab-secao">
                        <h2></h2>
                        <hr>
                        <p>3 álbuns</p>
                    </article>

                    <section class="grade-albuns">


<?php
                $listaAlbuns = retornarAlbuns();
                while($album = mysqli_fetch_assoc($listaAlbuns)){
                    echo "<a class=\"album-item\" href=\"../view/visualizar_album.php?id=" . $album['id_album'] . "\">";
                    echo "<img class=\"capa-album\" src=\"../assets/" . $album["capa_url"] . "\" alt=\"Led Zeppelin IV\">";
                   
                    echo "<h3>$album[titulo] IV</h3>";
                    
                    echo "</a>";
                }
            ?>

                      
                    </section>
                </article>
            </section>
        </section>
    </section>

</body>
</html>
