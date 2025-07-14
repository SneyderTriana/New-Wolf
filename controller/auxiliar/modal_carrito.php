<?php
session_start(); // Inicia la sesión PHP para acceder a variables de sesión

$carrito_mio = $_SESSION['carrito'] ?? []; // Obtiene el carrito de la sesión, o un array vacío si no existe
$total_carrito = 0; // Inicializa el total del carrito en 0

if (!empty($carrito_mio)) { // Si el carrito no está vacío
    echo '<ul class="list-group mb-3">'; // Inicia una lista de grupo de Bootstrap
    foreach ($carrito_mio as $index => $producto) { // Itera sobre cada producto en el carrito
        // Asegura que las claves existan y escapa HTML para prevenir XSS
        $titulo = htmlspecialchars($producto['titulo'] ?? 'Artículo Desconocido');
        $precio = floatval($producto['precio'] ?? 0);
        $cantidad = intval($producto['cantidad'] ?? 0);
        $ref = htmlspecialchars($producto['ref'] ?? '');

        $subtotal = $precio * $cantidad; // Calcula el subtotal para el producto actual
        $total_carrito += $subtotal; // Suma el subtotal al total general del carrito
        ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-4">
            <div class="row w-100">
                <div class="col-8 p-0" ;">
                    <h6 class="my-0"><?php echo $titulo; ?></h6>
                    <small class="text-muted">Cantidad:
                        <input type="number" min="1" value="<?php echo $cantidad; ?>"
                            onchange="actualizar_cantidad_carrito(<?php echo $index; ?>, this.value)" style="width: 60px;">
                    </small>
                </div>
                <div class="col-4 p-0 text-end" ;">
                    <span class="text-muted"><?php echo number_format($subtotal, 2); ?>€</span>
                    <button type="button" class="btn btn-danger btn-sm ms-2"
                        onclick="eliminar_item_carrito(<?php echo $index; ?>)">X</button>
                </div>
            </div>
        </li>
        <?php
    }
    echo '</ul>'; // Cierra la lista de grupo

    // Muestra el total final del carrito
    echo '<li class="list-group-item d-flex justify-content-between align-items-center" ">';
    echo '<span ">Total (EUR)</span>';
    echo '<strong">' . number_format($total_carrito, 2) . '€</strong>';
    echo '</li>';

} else {
    echo '<p class="text-center">Tu carrito está vacío.</p>'; // Mensaje si el carrito está vacío
}
?>

<script>
    // Función para actualizar la cantidad de un producto en el carrito
    function actualizar_cantidad_carrito(id, nueva_cantidad) {
        if (nueva_cantidad < 0) {
            nueva_cantidad = 0; // Asegura que la cantidad no sea negativa
        }
        $.ajax({
            url: 'auxiliar/cart.php', // URL del script PHP que maneja la lógica del carrito
            type: 'POST', // Método de la petición
            dataType: 'json', // Tipo de dato esperado en la respuesta
            data: { id_item_carrito: id, nueva_cantidad: nueva_cantidad }, // Datos a enviar
            success: function (response) { // Función a ejecutar si la petición es exitosa
                if (response.status === 'OK') {
                    consultar_carrito(); // Recarga el contenido del carrito (función definida en otro lugar)
                    count_carrito(); // Actualiza el contador de ítems en el carrito (función definida en otro lugar)
                } else {
                    // Muestra un mensaje de error si la actualización no fue exitosa
                    Swal.fire('Error', response.message || 'Error al actualizar cantidad', 'error');
                }
            },
            error: function () { // Función a ejecutar si hay un error en la petición
                // Muestra un mensaje de error si hay problemas de comunicación
                Swal.fire('Error', 'Problema al comunicarse con el servidor al actualizar cantidad', 'error');
            }
        });
    }

    // Función para eliminar un producto del carrito
    function eliminar_item_carrito(id_eliminar) {
        Swal.fire({ // Muestra una alerta de confirmación con SweetAlert2
            title: '¿Eliminar este producto?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => { // Maneja la respuesta del usuario
            if (result.isConfirmed) { // Si el usuario confirma la eliminación
                $.ajax({
                    url: 'auxiliar/cart.php', // URL del script PHP para eliminar del carrito
                    type: 'POST', // Método de la petición
                    dataType: 'json', // Tipo de dato esperado en la respuesta
                    data: { id_eliminar_item: id_eliminar }, // Datos a enviar (ID del ítem a eliminar)
                    success: function (response) { // Función a ejecutar si la petición es exitosa
                        if (response.status === 'OK') {
                            Swal.fire( // Muestra un mensaje de éxito
                                '¡Eliminado!',
                                'Producto eliminado del carrito.',
                                'success'
                            );
                            consultar_carrito(); // Recarga el contenido del carrito
                            count_carrito(); // Actualiza el contador del carrito
                        } else {
                            // Muestra un mensaje de error si la eliminación no fue exitosa
                            Swal.fire('Error', response.message || 'Error al eliminar producto', 'error');
                        }
                    },
                    error: function () { // Función a ejecutar si hay un error en la petición
                        // Muestra un mensaje de error si hay problemas de comunicación
                        Swal.fire('Error', 'Problema al comunicarse con el servidor al eliminar', 'error');
                    }
                });
            }
        });
    }
</script>