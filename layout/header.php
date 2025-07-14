<?php
session_start(); // Inicia la sesión
require_once __DIR__ . '/../../config/imagenes.php'; // Importa configuración de rutas de imágenes
require_once __DIR__ . '/../../config/addressDocument.php'; // Importa configuración de rutas de documentos
?>

<body>

    <div class="head"> <div class="logo"> <a href="<?= INICIO ?>"> <figure><img src="<?= LOGO ?>" alt="Logo" height="70px" width="100%" alt="logo"></figure>
                </a>
        </div>

        <nav class="navbar"> <button class="luz"> <figure><img src="<?= LUZ ?>" height="40px" width="40" alt="luz"></figure>
            </button>
            <button class="idioma"> <figure><img src="<?= IDIOMA ?>" height="40px" width="40" alt="luz"></figure>
            </button>

            <?php if (isset($_SESSION["user"]["id_use"])): ?>
                <a href="<?= COMUNIDAD ?>?id=<?= htmlspecialchars($_SESSION["user"]["id_use"]) ?>">comunidad</a>
            <?php endif; ?>
            <a href="<?= TIENDA ?>">tienda</a> <a href="<?= INFORMACION ?>">informacion</a> <a href="<?= CONTACTO ?>">contacto</a> <style>
                .menu-container {
                    position: relative;
                    display: inline-block;
                }

                .menu-button {
                    background: none;
                    border: none;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                }

                .menu-button img {
                    border-radius: 50%;
                    margin-right: 8px;
                }

                .menu-content {
                    display: none;
                    position: absolute;
                    background-color: white;
                    min-width: 160px;
                    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
                    z-index: 1;
                }

                .menu-content a {
                    color: black;
                    padding: 10px;
                    text-decoration: none;
                    display: block;
                }

                .menu-content a:hover {
                    background-color: #f1f1f1;
                }

                .menu-container:hover .menu-content {
                    display: block;
                }
            </style>

            <div class="menu-container">
                <button class="menu-button">
                    <?php
                    // Verifica si $_SESSION["user"] y sus claves existen antes de usarlas
                    $avatar = '';
                    $username = '';
                    if (isset($_SESSION["user"]["avatar"])) {
                        $avatar = htmlspecialchars($_SESSION["user"]["avatar"]);
                    }
                    if (isset($_SESSION["user"]["username"])) {
                        $username = htmlspecialchars($_SESSION["user"]["username"]);
                    }
                    ?>
                    <img src="avatars/<?= $avatar ?>" height="40" width="40" alt="<?= $username ?>">
                    <span><?= $username ?></span>
                </button>
                <div class="menu-content">
                    <?php if (isset($_SESSION["user"]["username"])): ?> <?php if (isset($_SESSION["user"]["id_use"])): ?>
                            <a href="<?= PERFIL ?>?id=<?= htmlspecialchars($_SESSION["user"]["id_use"]) ?>">Ver perfil</a>
                        <?php endif; ?>
                        <a href="#">Ayuda</a>
                        <?php if (isset($_SESSION["user"]["id_use"])): ?>
                            <a href="<?= EDITARPERFIL ?>?id=<?= htmlspecialchars($_SESSION["user"]["id_use"]) ?>">Editar Perfil</a>
                        <?php endif; ?>
                        <a href="<?= CERRAR ?>" class="btn"><span>Cerrar Sesión</span></a> <?php else: ?> <a href="login.php" class="btn"><span>Iniciar Sesión</span></a> <?php endif; ?>
                </div>
            </div>

        </nav>
    </div>