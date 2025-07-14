<!-- Librería de SweetAlert2 para ventanas emergentes -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Función para confirmar el pago
    function Pago() {
        Swal.fire({
            title: 'Confirmar Pago',
            text: "¿Estás seguro de que deseas realizar este pago?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, pagar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            // Si el usuario confirma el pago
            if (result.isConfirmed) {
                // Se envía una petición AJAX a borrarcarro.php para vaciar el carrito
                $.ajax({
                    url: '../controller/auxiliar/borrarcarro.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'OK') {
                            // Muestra mensaje de éxito y redirige a la tienda
                            Swal.fire(
                                '¡Pago Realizado!',
                                'Tu compra ha sido procesada exitosamente.',
                                'success'
                            ).then(() => {
                                window.location.href = "tienda.php";
                            });
                        } else {
                            // Muestra error si el servidor devuelve un fallo
                            Swal.fire('Error', response.message || 'Error al procesar el pago.', 'error');
                        }
                    },
                    error: function () {
                        // Muestra error si no se puede conectar al servidor
                        Swal.fire('Error', 'Problema de comunicación al procesar el pago.', 'error');
                    }
                });
            }
        });
    }
</script>
</body>

</html>