<!-- Enlace al archivo de estilos CSS -->
<link rel="stylesheet" href="assets/styles/style.css">

<?php
// Se incluye el archivo que define rutas absolutas del proyecto
require_once __DIR__ . '/../config/addressDocument.php';
// Se incluye la cabecera común del sitio web
require_once HEADER;
require_once PRESENTACION;
?>

<!-- Sección principal del contenido "about" -->
<section class="content about">
  <section class="presentacion-section">
    <!-- Título principal -->
    <h1>EL MUNDO DE LOS TATUAJES</h1>

    <!-- Imagen de presentación -->
    <figure class="presentacion">
      <img src="/login_1/assets/imagenes/IMAGEN.jpeg" alt="presentacion">
    </figure>

    <!-- Párrafo con contenido ficticio (texto de ejemplo) -->
    <p>
      deknmkencjkdnkdcndjcknjkchshcjkbhhbhcbdcbdhbdshcbdjbhdhbhds
      bchsdchhdbdhbcdjdsgjhdhbdcsdjsdjbkbhksdhdkcbhdsbdkjckbdsjsdkk
      jdchbsdcjjhdkbdhbsdcdbhdsdbcsdjhbdcsdjbdcbhsdjbshdncjnhscbsdjcb
      jhsdydshnbyudbshcvjgiehwgchlsdchsdjsdchbcsldjsdbhdcbdsldbldchlvadjcv
      dshcvjshcvhjhvcvjhvdcvhsdcvdsvhdjdcjvhdvbdsgjhvsdklschkdchjvbjdsv
      bcjhdcvuydzhjdcbcsbcsshdcbdcbscjbchdcwvjhvdabckjeuucdbhcbjhdsbcuvgf
      hhsdhcbhjdshjfgjgcjbjdgbhhabdcbjhddchdsgdhjcdsbcsjhdjbhdsdcvahcbj cabjhahhabclasblafkbhlyulvtbh barjvjlkjhb
      a4ufabluuglucyygvcchasglcigceawvygcylyaschhyhcylucelheacjheesalucgayuc
      egylhcaycalcehyahyaeibhealhealbyebyabchyeaahullhbcelabcelbhaescbyahb ycabeclbcceaajeyhnca
    </p>

    <!-- Botón de acción (sin funcionalidad definida aún) -->
    <div class="btn-home">
      <button>
        <span>Saber Mas</span>
      </button>
    </div>
  </section>
</section>

<!-- Sección de precios -->
<section class="content price">
  <article class="contain">
    <!-- Título de la sección -->
    <h2 class="title">precio</h2>

    <!-- Texto de ejemplo sobre precios -->
    <p>
      Lorem ipsum dolor, sit amet consectetur adipisicing elit. In eius alias aspernatur iusto necessitatibus quae fuga
      voluptates cumque hic repellat tempore odit quaerat, officiis culpa corrupti quasi repellendus libero at!
    </p>

    <!-- Botón para saber precios (comentado el enlace anterior) -->
    <!--<a href="#" class="btn">saber precio</a>-->
    <button>
      <span>Saber Precio</span>
    </button>
  </article>
</section>

<!-- Sección de contacto -->
<section class="content contact">
  <!-- Título de la sección -->
  <h2 class="title">Contacto</h2>

  <!-- Información de contacto -->
  <p>
    3213250468
    sneyder.gonzalez156@gmail.com
  </p>

  <!-- Mapa de ubicación usando una constante PHP -->
  <figure class="map">
    <img src="<?= MAPA ?>" height="500px" alt="mapa">
  </figure>
</section>

</body>

</html>

<?php
// Se incluye el pie de página desde una constante definida
require_once FOOTER
  ?>

</body>

</html>