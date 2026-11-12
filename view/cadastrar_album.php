
<?php
session_start();
include_once("../assets/processamento/funcoesDB.php");
$conexao = conectarBD();

$artistas = mysqli_query($conexao, "SELECT * FROM artistas");
$generos = mysqli_query($conexao, "SELECT * FROM generos");


if (isset($_POST['cadastrar'])) {

    $idAlbum = inserirAlbum(
        $_POST['titulo'],
        $_POST['ano'],
        $_POST['gravadora'],
        $_POST['formato'],
        $_POST['condicao'],
        $_POST['descricao'],
        $_FILES['capa'],
        $_POST['linkCompra'],
        $_POST['linkSpotify'],
        $_POST['idArtista'],
        $_POST['idGenero'],
        $_SESSION['id_usuario']
    );

    foreach ($_POST['faixa'] as $i => $titulo) {

        if (!empty(trim($titulo))) {
            inserirMusica(
                $idAlbum,
                $i + 1,
                $titulo,
                $_POST['duracao'][$i]
            );
        }

    }

    header("Location: ../view/albuns.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Álbum</title>
    <link rel="stylesheet" href="/vinil/assets/style.css">
</head>
<body>

    <main>
        <article class="main-dispo">
            <a href="../view/home.html">
                <img class="logo" src="../assets/img/logo_vinil_fundo_transparente.png" alt="logo">
            </a>
        </article>

        <article class="main-dispo">
            <ul>
                <a href="../view/albuns.php"><li>Álbuns</li></a>
                <a href="../view/generos.html"><li>Gêneros</li></a>
                <a href="../view/artistas.html"><li>Artistas</li></a>
                <a href="../view/novidades.html"><li>Novidades</li></a>
            </ul>
        </article>

        <article class="main-dispo">
            <img class="icon" src="../assets/img/icons-pesquisar.png" alt="pesquisar">
            <img class="icon" src="../assets/img/icons-gostar.png" alt="favoritos">
            <img class="icon" src="../assets/img/icons-usuário.png" alt="usuário">
        </article>
    </main>

    <section class="sessao-cad-album">

        <form class="box-cad-album" method="POST" action="" enctype="multipart/form-data">

            <h1>Cadastrar novo álbum</h1>

            <section class="cad-album-topo">

                <article class="cad-capa-box">
                    <label class="cad-capa" for="capa">
                        <img class="icone-upload" src="../assets/img/icons-upload.png" alt="">
                        <p>Arraste a capa aqui<br>ou clique para enviar</p>
                    </label>

                    <input id="capa" name="capa" type="file" accept="image/png, image/jpeg" required>

                    <small>JPG ou PNG</small>
                </article>

                <article class="cad-campos">

                    <label class="campo-total">Título do álbum
                        <input type="text" name="titulo" placeholder="Título do álbum" required>
                    </label>

                    <label class="campo">Artista
                        <select name="idArtista" required>
                            <option value="">Selecione um artista</option>

                            <?php while ($artista = mysqli_fetch_assoc($artistas)) { ?>
                                <option value="<?php echo $artista['id_artista']; ?>">
                                    <?php echo $artista['nome']; ?>
                                </option>
                            <?php } ?>

                        </select>
                    </label>

                    <label class="campo">Gênero
                        <select name="idGenero" required>
                            <option value="">Selecione um gênero</option>

                            <?php while ($genero = mysqli_fetch_assoc($generos)) { ?>
                                <option value="<?php echo $genero['id_genero']; ?>">
                                    <?php echo $genero['nome']; ?>
                                </option>
                            <?php } ?>

                        </select>
                    </label>

                    <label class="campo">Ano de lançamento
                        <input type="number" name="ano" min="1900" max="2100" placeholder="Ano" required>
                    </label>

                    <label class="campo">Gravadora
                        <input type="text" name="gravadora" placeholder="Gravadora">
                    </label>

                    <label class="campo">Formato
                        <select name="formato" required>
                            <option value="">Selecione o formato</option>
                            <option value="LP 12 polegadas">LP 12 polegadas</option>
                            <option value="LP 7 polegadas">LP 7 polegadas</option>
                            <option value="CD">CD</option>
                            <option value="Fita cassete">Fita cassete</option>
                        </select>
                    </label>

                    <label class="campo">Condição
                        <select name="condicao" required>
                            <option value="">Selecione a condição</option>
                            <option value="Novo">Novo</option>
                            <option value="Excelente estado">Excelente estado</option>
                            <option value="Bom estado">Bom estado</option>
                            <option value="Regular">Regular</option>
                        </select>
                    </label>

                    <label class="campo-total">Link para comprar álbum
                        <input type="url" name="linkCompra" placeholder="https://">
                    </label>

                    <label class="campo-total">Link para escutar
                        <input type="url" name="linkSpotify" placeholder="https://">
                    </label>

                    <label class="campo-total">Descrição
                        <textarea name="descricao" placeholder="Conte um pouco sobre o álbum"></textarea>
                    </label>

                </article>
            </section>

            <section class="cad-faixas">

                <h3>Faixas</h3>

                <div id="lista-faixas">

                    <article class="faixa-linha">
                        <span>01</span>
                        <input class="faixa-nome" type="text" name="faixa[]" placeholder="Nome da faixa">
                        <input class="faixa-tempo" type="text" name="duracao[]" placeholder="0:00">
                        <button class="faixa-remover" type="button">✕</button>
                    </article>

                </div>

                <button class="faixa-add" type="button" onclick="adicionarFaixa()">
                    + Adicionar faixa
                </button>

            </section>

            <article class="cad-botoes">

                <a href="../view/albuns.php">
                    <button class="button-contorno" type="button">Cancelar</button>
                </a>

                <button class="button-ama" type="submit" name="cadastrar">
                    Salvar álbum
                </button>

            </article>

        </form>

    </section>

    <script>

        function atualizarNumeracao() {

            let faixas = document.querySelectorAll('.faixa-linha');

            faixas.forEach((faixa, indice) => {
                faixa.querySelector('span').textContent =
                    String(indice + 1).padStart(2, '0');
            });

        }

        function adicionarFaixa() {

            let lista = document.getElementById('lista-faixas');

            let faixa = document.createElement('article');

            faixa.className = 'faixa-linha';

            faixa.innerHTML = `
                <span>01</span>
                <input class="faixa-nome" type="text" name="faixa[]" placeholder="Nome da faixa">
                <input class="faixa-tempo" type="text" name="duracao[]" placeholder="0:00">
                <button class="faixa-remover" type="button">✕</button>
            `;

            lista.appendChild(faixa);

            atualizarNumeracao();

        }

        document.getElementById('lista-faixas').addEventListener('click', function(event) {

            if (event.target.classList.contains('faixa-remover')) {

                event.target.closest('.faixa-linha').remove();

                atualizarNumeracao();

            }

        });

    </script>

</body>
</html>
