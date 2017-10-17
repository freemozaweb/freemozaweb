<!DOCTYPE html>
<html>
<head>
	<title>FreeMozaWeb - Az ingyenes MozaWeb</title>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<link rel="stylesheet" type="text/css" href="css/style.css">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
  <script src="js/main.js" type="text/javascript"></script>

</head>
<body>

<div class="dialog" title="Üzenet"></div>

<div style="text-align: center;">
<img style="width: 320px; height: 240px;" src="freemozaweb.png">
</div>


<br>
<p class="text-center">Kontakt: freemozaweb@protonmail.ch</p>
<br>
<div class="text-center">
<a class="text-center" href="#" onclick="alert('Nyomd az F3-at vagy keresd meg a keresési funckiót a böngésződbe!');">Hogyan keress?</a>
<br><br>
<a class="text-center" href="#" onclick="alert('Egyes PDF olvasók lekicsínyitik a lapokat. Használd a nagyitás funkcióját, a minőség jó!');">PDF kicsi?</a>
<br><br>
<a class="text-center" href="#" onclick="alert('Nem nyílnak meg rendesen, használd a nyitott mappát!');">Könyvek '?'-el!</a>
<br><br>
<a class="text-center" href="könyvek/";">Nyitott mappa, a profiknak!</a>
<br><br>
<a href="könyvek/Extrák">Extrák</a> 
</div>
<br>
<p class="text-center">Enjoy!</p>
<div class="container">
<div id="products" class="row list-group">
</div>
</div>


<?php 
$btc_address = "1HkJGiwphqQEYFEhQwPCogaphAUoS7qyrq";
$root = "https://freemozaweb.eu/";

$books = scandir("könyvek/",0);

$books = array_diff($books, array(".", "..", "Extrák"));

foreach($books as $dir)
{
      $name = $dir;
      $info = file_get_contents("könyvek/".$dir."/machine_info.txt");
      
      if (is_file("könyvek/".$dir."/bocs.txt")) {
      	$unavailable = "Nincs, ok: MozaWeben nincsen";
        $additional_options = 'disabled=disabled';
      }  else {
        $unavailable = "Elérhető";
        $additional_options = '';
      }
      	
      $info = explode(":", $info);
      $name = base64_decode($info[0]);
      $code = base64_decode($info[1]);
      $description = base64_decode($info[2]);
      $additional_description = base64_decode($info[3]);
      $subject = base64_decode($info[4]);
      $subsubject = base64_decode($info[5]);
      $authors = base64_decode($info[6]);
      $price = base64_decode($info[7]);
      $maxpage = base64_decode($info[8]);

      $cover_link = $root."könyvek/".rawurlencode($dir)."/cover.jpg";
      $jpeg_link = $root."könyvek/".$dir."/JPEG";
      $pdf_link = $root."könyvek/".$dir."/PDF/".$code." - ".$name.".pdf";

   
      //match subject with full subject name
      $array = array('AKO' => 'Környezetismeret', 'ENK' => "Ének-Zene", "ERK" => "Erkölcstan", "FIZ" => "Fizika", "FOL" => "Földrajz", "AMT" => "Alsós matematika", "ANY" => "Alsós anyanyelv", "BIO" => "Biológia", "HON" => "Hon- és népismeret", "IDN" => "Idegen nyelv", "INF" => "Informatika", "TAN" => "Iskola előkészítés", "TCH" => "Technika, háztartástan", "TER" => "Természetismeret", "TOR" => "Történelem", "VIZ" => "Vizuális művészetek", "KEM" => "Kémia", "MAT" => "matematika", "MGY" => "Magyar nyelv és irodalom", "OLV" => "Olvasás, írás");
      $subject = $array[$subject];

      //print
      /*
      echo "<div style='display:inline-block;
      }
  width: 33%;'>";
      echo "<span>Nev: ".$name."</span><br>";
      echo "<span>Kod: ".$code."</span><br>";
      echo "<span>Kiadas: ".$description."</span><br>";
      echo "<span>Leiras: ".$additional_description."</span><br>";
      echo "<span>Tantargy: ".$subject."</span><br>";
      echo "<span>Tema: ".$subsubject."</span><br>";
      echo "<span>Szerzök: ".$authors."</span><br>";
      echo "<span>Ar HUF; ".$price."</span><br>";
      echo "<span>Lapszam: ".$maxpage."</span><br>";
      echo "</div>";
      */
      /*
      echo "<div class='book'>";
      echo "<img src='".$cover_link."'></img>";
      echo "<br>";
      echo "<span>Nev: ".$name."</span><br>";
      echo "</div>";
	  */
      echo '<div class="item  col-xs-4 col-lg-4">
      <div class="thumbnail">
          <img class="group list-group-image" src="'.$cover_link.'" alt="" />
          <div class="caption">
              <h4 class="group inner list-group-item-heading">
                  '.$code.' - '.$name.'</h4>
              <p class="group inner list-group-item-text">
                  Kiadás: '.$description.'<br>
                  Leirás: '.$additional_description.'<br>
                  Tantárgy: '.$subject.'<br>
                  Téma: '.$subsubject.'<br>
                  Szerzők: '.$authors.'<br>
                  Ár HUF: '.$price.'<br>
                  Lapszám: '.$maxpage.'<br>
                  Elérhetőség: '.$unavailable.'<br>

              <div class="row">
                      <a class="btn btn-success" style="float: right;" '.$additional_options.' onClick="konyvLetoltes(this, \''.$price.'\',\''.$btc_address.'\'); return false;" href="'.$jpeg_link.'">Megnyitás JPEG</a>
                      <br><br>
                      <a class="btn btn-success" style="float: right;" '.$additional_options.' onClick="konyvLetoltes(this, \''.$price.'\',\''.$btc_address.'\'); return false;" href="'.$pdf_link.'">Letöltés PDF</a>
              </div>
          </div>
      </div>
  </div>   ';
}	

?>

<script type="text/javascript">

</script>
</body>
</html>