<?php

    require_once "../assets/processamento/funcoesDB.php";

    $generosSelecionados = isset($_GET['generos']) ? $_GET['generos'] : [];
    $decadaSelecionada = isset($_GET['decada']) ? $_GET['decada'] : '';
    $agruparPor = isset($_GET['agrupar']) ? $_GET['agrupar'] : 'genero';
    $busca = isset($_GET['busca']) ? $_GET['busca'] : '';
    $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : 'az';

    $listaAlbuns = retornarAlbuns($generosSelecionados, $decadaSelecionada, $busca, $ordenar, $agruparPor);

    $grupos = [];
    while($album = mysqli_fetch_assoc($listaAlbuns)){
        if ($agruparPor == 'artista') {
            $chave = $album['nome_artista'];
        } elseif ($agruparPor == 'sem_grupo') {
            $chave = 'Todos os Álbuns';
        } else {
            $chave = $album['nome_genero'];
        }
        $grupos[$chave][] = $album;
    }

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

    <form method="GET" action="">
        <section class="all-albuns">
            <section class="barra-album">
                <input type="text" id="busca-album" name="busca" value="<?php echo htmlspecialchars($busca); ?>" placeholder="Buscar álbum, artista ou gênero" onchange="this.form.submit()">

                <select name="ordenar" onchange="this.form.submit()">
                    <option value="az" <?php if($ordenar == 'az') echo 'selected'; ?>>Ordenar: A-Z</option>
                    <option value="za" <?php if($ordenar == 'za') echo 'selected'; ?>>Ordenar: Z-A</option>
                    <option value="ano" <?php if($ordenar == 'ano') echo 'selected'; ?>>Ordenar: ano</option>
                </select>
            </section>

            <section class="corpo-album">
                <aside class="filtros-album">
                    <article class="filtro">
                        <h3>Gênero</h3>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="6" onchange="this.form.submit()" <?php if(in_array(6, $generosSelecionados)) echo "checked"; ?>> Eletrônica
                        </label>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="2" onchange="this.form.submit()" <?php if(in_array(2, $generosSelecionados)) echo "checked"; ?>> MPB
                        </label>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="3" onchange="this.form.submit()" <?php if(in_array(3, $generosSelecionados)) echo "checked"; ?>> Pop
                        </label>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="4" onchange="this.form.submit()" <?php if(in_array(4, $generosSelecionados)) echo "checked"; ?>> Rap / Hip-Hop
                        </label>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="5" onchange="this.form.submit()" <?php if(in_array(5, $generosSelecionados)) echo "checked"; ?>> Rap Nacional
                        </label>
                        <label class="opcao">
                            <input type="checkbox" name="generos[]" value="1" onchange="this.form.submit()" <?php if(in_array(1, $generosSelecionados)) echo "checked"; ?>> Rock
                        </label>
                    </article>

                    <article class="filtro">
                        <h3>Década</h3>
                        <article class="chips-decada">
                            <button type="submit" name="decada" value="<?php echo ($decadaSelecionada == '1960') ? '' : '1960'; ?>" class="chip-decada <?php if($decadaSelecionada == '1960') echo 'chip-ativo'; ?>">60s</button>
                            <button type="submit" name="decada" value="<?php echo ($decadaSelecionada == '1970') ? '' : '1970'; ?>" class="chip-decada <?php if($decadaSelecionada == '1970') echo 'chip-ativo'; ?>">70s</button>
                            <button type="submit" name="decada" value="<?php echo ($decadaSelecionada == '1980') ? '' : '1980'; ?>" class="chip-decada <?php if($decadaSelecionada == '1980') echo 'chip-ativo'; ?>">80s</button>
                            <button type="submit" name="decada" value="<?php echo ($decadaSelecionada == '1990') ? '' : '1990'; ?>" class="chip-decada <?php if($decadaSelecionada == '1990') echo 'chip-ativo'; ?>">90s</button>
                        </article>
                        <?php if($decadaSelecionada != ''): ?>
                            <input type="hidden" name="decada" value="<?php echo $decadaSelecionada; ?>" id="decada-oculta">
                        <?php endif; ?>
                    </article>

                    <article class="filtro">
                        <h3>Agrupar por</h3>
                        <label class="opcao"><input type="radio" name="agrupar" value="artista" onchange="this.form.submit()" <?php if($agruparPor == 'artista') echo 'checked'; ?>> Artista</label>
                        <label class="opcao"><input type="radio" name="agrupar" value="genero" onchange="this.form.submit()" <?php if($agruparPor == 'genero') echo 'checked'; ?>> Gênero</label>
                        <label class="opcao"><input type="radio" name="agrupar" value="sem_grupo" onchange="this.form.submit()" <?php if($agruparPor == 'sem_grupo') echo 'checked'; ?>> Sem grupo</label>
                    </article>
                </aside>

                <section class="lista-grupos">
                    <?php if (empty($generosSelecionados)): ?>
                        <p>Selecione pelo menos um gênero musical para visualizar os álbuns.</p>
                    <?php elseif (empty($grupos)): ?>
                        <p>Nenhum álbum encontrado com os filtros selecionados.</p>
                    <?php else: ?>
                        <?php foreach($grupos as $nomeGrupo => $albunsDoGrupo): ?>
                            <article class="grupo-album">
                                <article class="cab-secao">
                                    <h2><?php echo $nomeGrupo; ?></h2>
                                    <hr>
                                    <p><?php echo count($albunsDoGrupo); ?> álbuns</p>
                                </article>

                                <section class="grade-albuns">
                                    <?php foreach($albunsDoGrupo as $album): ?>
                                        <a class="album-item" href="../view/visualizar_album.php?id=<?php echo $album['id_album']; ?>">
                                            <img class="capa-album" src="../assets/<?php echo $album['capa_url']; ?>" alt="<?php echo $album['titulo']; ?>">
                                            <h3><?php echo $album['titulo']; ?></h3>
                                        </a>
                                    <?php endforeach; ?>
                                </section>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </section>
        </section>
    </form>

    <script>
        document.querySelectorAll('.chip-decada').forEach(btn => {
            btn.addEventListener('click', () => {
                const oculta = document.getElementById('decada-oculta');
                if (oculta) oculta.remove();
            });
        });
    </script>

</body>
</html>