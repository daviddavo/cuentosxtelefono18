
function procLista(lista){
  lista.forEach()
}

console.log("Prueba");
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function(){
  if(this.readyState == 4 && this.status == 200){
    var lineas = JSON.parse(this.responseText);

    // Ahora procesamos la lista
    var table = `
    <table class='table table-dark text-center'>
    <thead><tr>
      <th>Teléfono</th>
      <th>Líneas disponibles</th>
      <th class="d-none d-lg-block">Tiempo abierto/cerrado</th>
    </tr></thead><tbody>
    `
    lineas.forEach(function(linea){
        console.log(linea);

        // TODO: Cambiar fa-phone dependiendo del tipo de lineas
        // TODO: Hacer que jquery haga append por cada linea, y no tener una mega string
        var bg_color;
        if(linea["available"] == 0){
          bg_color = "bg-danger";
        }else if(linea["available"] == linea["total"]){
          bg_color = "bg-success";
        }else{
          bg_color = "bg-warning";
        }

        table += `
        <tr class="${bg_color}" onclick="window.location.href = 'tel:+34${linea["phone_number"]}';">
          <td class="font-weight-bold"><i class="fas fa-phone"></i> ${linea["phone_number"]}</td>
          <td>${linea['available']}/${linea['total']}</td>
          <td class='d-none d-lg-block'>time</td>
        </tr>
        `

    })

    table += "</tbody></table>"
    $("#lineas").append(table);
    console.log(lineas);
  }
}

xmlhttp.open("GET", "api/getLines.php", true);
xmlhttp.send();
