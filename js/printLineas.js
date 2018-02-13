
function procLista(lista){
  lista.forEach()
}

console.log("Prueba");
var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function(){
  if(this.readyState == 4 && this.status == 200){
    var lineas = JSON.parse(this.responseText);

    // Ahora procesamos la lista

    var linesObjects = {};
    lineas.forEach(function(linea){
        console.log(linea);
        if(linea["phone_number"] in linesObjects){
          var obj = linesObjects[ linea["phone_number"] ];
          obj["open"] += linea["status"];
          obj["total"]++;
          // Last open y last close
        }else{
          var obj = {};
          obj["open"] = linea["status"];
          obj["total"] = 1;
        }
    })

    console.log(linesObjects);
  }
}

xmlhttp.open("GET", "telegram/getLines.php", true);
xmlhttp.send();

// Y ahora a procesarlas
// Crearemos un diccionario usando el numero de telefono como clave
// phone_number : [ open, total, last_update ]
