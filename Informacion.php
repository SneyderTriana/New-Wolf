<?php
// Se incluye el archivo que define rutas absolutas para las plantillas del proyecto
require_once __DIR__ . '/../config/addressDocument.php';
// Se incluye la cabecera general del sitio web (HEADER es una constante definida en addressDocument.php)
require_once HEADER;
?>

<!-- Se enlaza la hoja de estilos CSS -->
<link rel="stylesheet" href="/login_1/assets/styles/style.css">

<!-- Sección principal: Presentación sobre el mundo de los tatuajes -->
<section class="content about">
    <section class="presentacion-section">
        <!-- Título de sección -->
        <h1>EL MUNDO DE LOS TATUAJES</h1>

        <!-- Imagen representativa -->
        <figure class="presentacion">
            <img src="/login_1/assets/imagenes/IMAGEN.jpeg" alt="presentacion">
        </figure>

        <!-- Texto de ejemplo o relleno -->
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
    </section>
</section>

<!-- Sección: Quiénes somos -->
<section class="content about">
    <section class="presentacion-section">
        <!-- Título de sección -->
        <h1>QUIENES SOMOS</h1>

        <!-- Imagen representativa -->
        <figure class="presentacion">
            <img src="/login_1/assets/imagenes/IMAGEN.jpeg" alt="presentacion">
        </figure>

        <!-- Texto de ejemplo o relleno -->
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
    </section>
</section>

<!-- Sección: Nuestra visión -->
<section class="content about">
    <section class="presentacion-section">
        <!-- Título de sección -->
        <h1>NUESTRA VISION</h1>

        <!-- Imagen representativa -->
        <figure class="presentacion">
            <img src="/login_1/assets/imagenes/IMAGEN.jpeg" alt="presentacion">
        </figure>

        <!-- Texto de ejemplo o relleno -->
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
    </section>
</section>

</section>

<!-- Sección de contacto que muestra el mapa -->
<section class="content contact">
    <!-- Imagen de mapa, su ruta está definida por la constante MAPA -->
    <figure class="map">
        <img src="<?= MAPA ?>" height="100px" alt="mapa">
    </figure>
</section>

<!-- Inclusión del pie de página -->
<?php require_once FOOTER ?>

</body>

</html>