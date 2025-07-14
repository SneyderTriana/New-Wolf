// js/script.js

// Función para añadir un artículo al carrito
function envia_carrito(ref, titulo, precio, cantidad) {
  // Prepara los parámetros a enviar
  var parametros = {
    ref: ref,
    titulo: titulo,
    precio: precio,
    cantidad: cantidad,
  };
  // Realiza una petición AJAX
  $.ajax({
    data: parametros, // Datos a enviar
    url: "/login_1/controller/auxiliar/cart.php", // URL del script PHP
    type: "POST", // Método de la petición
    dataType: "json", // Tipo de dato esperado en la respuesta
    beforeSend: function () {
      // Código a ejecutar antes de enviar la petición (ej. mostrar spinner)
    },
    success: function (response) {
      // Función a ejecutar si la petición es exitosa
      if (response.status === "OK") {
        // Si la respuesta es OK, no hace nada visualmente (comentado Swal.fire)
      } else {
        // Si hay un error, muestra una alerta
        Swal.fire(
          "Error!",
          response.message || "No se pudo añadir al carrito.",
          "error"
        );
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      // Función a ejecutar si hay un error en la petición
      Swal.fire(
        "Error!",
        "Error al comunicar con el servidor: " + textStatus,
        "error"
      );
    },
  });
}

// Consultar contenido del carrito para el modal
function consultar_carrito() {
  // Realiza una petición AJAX
  $.ajax({
    data: {}, // No envía datos
    type: "POST", // Método de la petición
    url: "/login_1/controller/auxiliar/modal_carrito.php", // URL del script PHP
    success: function (data) {
      // Inserta la respuesta HTML en el elemento con ID "mi_carrito"
      document.getElementById("mi_carrito").innerHTML = data;
    },
    error: function (jqXHR, textStatus, errorThrown) {
      // En caso de error, muestra un mensaje y registra el error en consola
      document.getElementById("mi_carrito").innerHTML =
        '<p class="text-danger">Error al cargar el carrito.</p>';
      console.error("Error al consultar carrito:", textStatus, errorThrown);
    },
  });
}

// Actualizar conteo de productos en el navbar
function count_carrito() {
  // Realiza una petición AJAX
  $.ajax({
    data: {}, // No envía datos
    type: "POST", // Método de la petición
    url: "/login_1/controller/auxiliar/count_carrito.php", // URL del script PHP
    dataType: "json", // Tipo de dato esperado en la respuesta
    success: function (response) {
      // Función a ejecutar si la petición es exitosa
      if (response.status === "OK") {
        // Actualiza el contenido del elemento con ID "count_carrito"
        document.getElementById("count_carrito").innerHTML = response.count;
      } else {
        // Si hay un error, muestra "Error"
        document.getElementById("count_carrito").innerHTML = "Error";
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      // En caso de error, muestra "?"
      document.getElementById("count_carrito").innerHTML = "?";
    },
  });
}

// Vaciar completamente el carrito
function borrar_carrito() {
  // Muestra una alerta de confirmación con SweetAlert2
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¡Esto vaciará tu carrito!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, vaciarlo",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    // Si el usuario confirma
    if (result.isConfirmed) {
      // Realiza una petición AJAX para borrar el carrito
      $.ajax({
        data: {}, // No envía datos
        type: "POST", // Método de la petición
        url: "/login_1/controller/auxiliar/borrarcarro.php", // URL del script PHP
        dataType: "json", // Tipo de dato esperado en la respuesta
        success: function (response) {
          // Función a ejecutar si la petición es exitosa
          if (response.status === "OK") {
            // Muestra un mensaje de éxito y actualiza el carrito y el contador
            Swal.fire("¡Vaciado!", "Tu carrito ha sido vaciado.", "success");
            consultar_carrito();
            count_carrito();
          } else {
            // Si hay un error, muestra una alerta
            Swal.fire(
              "Error",
              response.message || "Error desconocido.",
              "error"
            );
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          // En caso de error, muestra una alerta
          Swal.fire("Error!", "Error al vaciar el carrito.", "error");
        },
      });
    }
  });
}
