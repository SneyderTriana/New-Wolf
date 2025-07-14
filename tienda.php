<?php
// Incluye el archivo que contiene las rutas definidas del sistema (como HEADER, FOOTER, JS)
require_once __DIR__ . '/../config/addressDocument.php';
// Incluye el archivo que establece la conexión a la base de datos
include __DIR__ . '/../config/conexion_mysqli_proc.php';
// Inserta el encabezado HTML común de la aplicación
require_once HEADER;
require_once PRESENTACION;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8"> <!-- Define el conjunto de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hace que el sitio sea responsive -->
    <title>Mi Tienda AJAX</title> <!-- Título de la pestaña del navegador -->
    <!-- Carga Bootstrap desde CDN para estilos y componentes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Carga Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Carga hoja de estilos personalizada -->
    <link rel="stylesheet" href="/login_1/assets/styles/style.css">
</head>

<body>

    <!-- Barra de navegación principal -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">New Wolf</a>
            <!-- Logo o nombre del sitio que redirige al index -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <!-- Enlace que abre el modal del carrito -->
                        <a class="nav-link" data-bs-toggle="modal" data-bs-target="#modal_cart" style="cursor:pointer;"
                            onclick="consultar_carrito();">
                            <i class="fas fa-shopping-cart"></i> Mi Carrito (<span id="count_carrito">0</span>)
                            <!-- Contador dinámico de productos -->
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="store-content-wrapper">
        <div class="container">
            <h2 class="store-title mb-4">Nuestros Articulos</h2>
            <div class="products-grid"> <?php
            // Consulta todos los artículos disponibles en la base de datos
            $buscardor = mysqli_query($conexion, "SELECT * FROM articulos");
            if (mysqli_num_rows($buscardor) > 0) {
                while ($resultado = mysqli_fetch_assoc($buscardor)) {
                    ?>
                        <!-- Tarjeta individual del producto -->
                        <div class="product-col">
                            <div class="product-card">
                                <!-- Imagen del producto -->
                                <img src="img/<?php echo htmlspecialchars($resultado["img"]); ?>.jpg" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($resultado["nombre"]); ?>">
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($resultado["nombre"]); ?></h4>
                                    <!-- Precio del producto -->
                                    <div class="product-price"><?php echo number_format($resultado["precio"], 2); ?>pesos</div>
                                    <div class="product-actions">
                                        <!-- Botón de detalles (sin funcionalidad implementada aquí) -->
                                        <button type="button" class="btn-details">Detalles</button>
                                        <!-- Botón para añadir producto al carrito -->
                                        <button id="al_carro" type="button" class="btn-add-cart" onclick="envia_carrito(
                                                $('#ref<?php echo htmlspecialchars($resultado['id']); ?>').val(),
                                                $('#titulo<?php echo htmlspecialchars($resultado['id']); ?>').val(),
                                                $('#precio<?php echo htmlspecialchars($resultado['id']); ?>').val(),
                                                $('#cantidad<?php echo htmlspecialchars($resultado['id']); ?>').val()
                                            );
                                            setTimeout(function() { count_carrito(); }, 500);">
                                            Añadir al carrito
                                        </button>
                                        <!-- Campos ocultos con datos del producto -->
                                        <input name="ref" type="hidden"
                                            id="ref<?php echo htmlspecialchars($resultado["id"]); ?>"
                                            value="<?php echo htmlspecialchars($resultado["id"]); ?>" />
                                        <input name="precio" type="hidden"
                                            id="precio<?php echo htmlspecialchars($resultado["id"]); ?>"
                                            value="<?php echo htmlspecialchars($resultado["precio"]); ?>" />
                                        <input name="titulo" type="hidden"
                                            id="titulo<?php echo htmlspecialchars($resultado["id"]); ?>"
                                            value="<?php echo htmlspecialchars($resultado["nombre"]); ?>" />
                                        <input name="cantidad" type="hidden"
                                            id="cantidad<?php echo htmlspecialchars($resultado["id"]); ?>" value="1"
                                            class="pl-2" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                }
            } else {
                // Si no hay productos en la base de datos
                echo "<p class='col-12 text-center'>No hay articulos disponibles.</p>";
            }
            ?>
            </div>
        </div>
    </main>

    <!-- Modal para ver el contenido del carrito -->
    <div class="modal fade" id="modal_cart" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Mi carrito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <div class="p-2">
                            <div id="mi_carrito">
                            </div> <!-- Aquí se carga dinámicamente el contenido del carrito -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Botón para cerrar modal -->
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <!-- Botón para vaciar todo el carrito -->
                    <button type="button" class="btn btn-primary"
                        onclick="borrar_carrito(); setTimeout(function() {count_carrito();}, 500);">Vaciar
                        carrito</button>
                    <!-- Enlace para proceder al pago -->
                    <a href="confirmar_pago.php" class="btn btn-success">Proceder al pago</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts JavaScript necesarios -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert para alertas interactivas -->
    <script src="<?php echo JS; ?>"></script> <!-- Archivo JS personalizado -->

    <script>
        // Ejecuta al cargar la página: actualiza el contador de productos en el carrito
        $(document).ready(function () {
            count_carrito();
        });

        // Función para eliminar un producto del carrito
        function eliminar_item_carrito(id_eliminar) {
            Swal.fire({
                title: 'eliminar este producto?',
                text: "Esta accion no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'S, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/login_1/controller/auxiliar/cart.php', // Ruta al controlador que elimina del carrito
                        type: 'POST',
                        dataType: 'json',
                        data: { id_eliminar_item: id_eliminar }, // Envía ID al backend
                        success: function (response) {
                            if (response.status === 'OK') {
                                Swal.fire(
                                    'Eliminado!',
                                    'Producto eliminado del carrito.',
                                    'success'
                                );
                                consultar_carrito(); // Recarga el carrito actualizado
                                count_carrito(); // Actualiza contador en la barra de navegación
                            } else {
                                Swal.fire('Error', response.message || 'Error al eliminar producto', 'error');
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Problema al comunicarse con el servidor al eliminar', 'error');
                        }
                    });
                }
            });
        }
    </script>
    <?php require_once FOOTER ?> <!-- Inserta el pie de página -->
</body>

</html>