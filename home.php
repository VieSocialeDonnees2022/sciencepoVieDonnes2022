<?php
  session_start();
  function api_all_types($key='code'){
    $all_types=api_call();
    $all_types_key=array();
    for($i=0;$i<count($all_types);$i++){
      $all_types_key[]=$all_types[$i][$key];
    }
    return($all_types_key);
  }
  function api_call($url=NULL,$country=NULL,$year=NULL,$types=array()){
    $login = 'any-login';
    $password = 'HK2EOquTh6BUiN817Gb80R8ui9TDpJgM3F26i8sfSp2un496d8o';
    if($url){
      if (substr($url,0,4)!='http'){
        $request='https://api.footprintnetwork.org/v1/'.$url;
      }else{
        $request=$url;
      }
      
    }
    elseif(!$country){
      if(!$year && (!$types || (is_array($types) && count($types)==0))){
        $request='https://api.footprintnetwork.org/v1/types';
      }else{
        return null;
      }
    }else{
      $request = 'https://api.footprintnetwork.org/v1/data/'.(string)$country;
      if($year){
        $request.= '/'.(string)$year.'/';
        $all_types_code = api_all_types();
        if(is_string($types) && in_array($types,$all_types_code)){
          $request.='/'.$types;
        }elseif(is_array($types) && count($types)>=1){
          for($i=0;$i<count($types);$i++){
            if(in_array($types[$i],$all_types_code)){
              if(substr($request,-1)=='/'){
                $request.=$types[$i];
              }else{
                $request.=','.$types[$i];
              }
            }
          }
        }
      }
    }

    // Initialize the session
    $session = curl_init();

    // Set curl options
    curl_setopt($session, CURLOPT_URL, $request);
    curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($session, CURLOPT_USERPWD, "$login:$password");
    curl_setopt($session, CURLOPT_HTTPHEADER,array('Accept: application/json'));
    curl_setopt($session, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

    $response = json_decode(curl_exec($session),true);

    curl_close($session);

    return($response);
  }

  function deroulant(string $name,$options,$default=NULL,$sort=true,$codes=NULL){
    if(!$codes){
      $codes = array_flip($options);
    }
    if($sort){
      sort($options);
    }
    if(!$default){
      $default=$options[0];
      $i_start=1;
    }else{
      $i_start=0;
    }
    echo '<select name="'.$name.'">';
    if(in_array($default,$options)){
      echo '<option value="'.$codes[$default].'">'.$default.'</option>';
    }else{
      echo '<option value="0">'.$default.'</option>';
    }
    for($i=$i_start ; $i < count($options) ; $i++){
      $element=$options[$i];
      echo '<option value="'.$codes[$element].'">'.$element.'</option>';
    }
    echo '</select>';
  }

  function search($country,$year="",$types=array()){
    $url = 'search.php/?c='.urlencode($country);
    if($year!=""){
      $url.='&y='.urlencode($year);
      if(count($types)>0){
        $url.='&d=';
        foreach($types as $type_of_data){
          $url.=urlencode($type_of_data).',';
        }
      }
    }
    if(substr($url,-1,1)==','){
      $url=substr($url,0,strlen($url)-1);
    }
    echo '<script>window.location.replace("'.$url.'");</script>';
  }

  $all_types_code=api_all_types();
  $all_types_names=api_all_types('name');
  $all_types_code_and_names_associations = array();
  for($i=0;$i<count($all_types_code);$i++){
    $all_types_code_and_names_associations[$all_types_names[$i]]=$all_types_code[$i];
  }

  $all_spaces = api_call('countries');
  $all_countries_name=array();
  $all_countries_code=array();

  for($i=0;$i<count($all_spaces);$i++){
    $country_shortName = $all_spaces[$i]['shortName'];
    $country_code = $all_spaces[$i]['countryCode'];
    if(substr($country_shortName,0,2)!='·' && substr($country_shortName,0,2) != 'º'){
      $all_countries_name[]=$all_spaces[$i]['shortName'];
      $all_countries_code[$country_shortName]=$country_code;
    }
  }

  $all_years_raw = api_call('years');
  $all_years=array();
  for($i=0;$i<count($all_years_raw);$i++){
    $all_years[]=$all_years_raw[$i]['year'];
  }
  $a='stop';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Vie Sociale des Données</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<!--This CSS stylesheet and html is a free of use template provided by W3School and adapted to fit our needs.-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
<style>
body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}
body {font-size:16px;}
.w3-half img{margin-bottom:-6px;margin-top:16px;opacity:0.8;cursor:pointer}
.w3-half img:hover{opacity:1}
</style>
</head>
<body>

<!-- Sidebar/menu -->
<nav class="w3-sidebar w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:300px;font-weight:bold;" id="mySidebar"><br>
  <a href="javascript:void(0)" onclick="w3_close()" class="w3-button w3-hide-large w3-display-topleft" style="width:100%;font-size:22px">Close Menu</a>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>Vie Sociale<br>des données<br>2022</b></h3>
  </div>
  <div class="w3-bar-block">
    <a href="#" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">Accueil</a> 
    <a href="#presentation" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Le Global Footprint Network : Présentation</a> 
    <a href="#background" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Historique</a> 
    <a href="#administrators" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Gestionnaires actuels</a> 
    <a href="#packages" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Packages</a> 
    <a href="#search_data" onclick="w3_close()" class="w3-bar-item w3-button w3-hover-white">> Exploiter les données</a>
  </div>
</nav>

<!-- Top menu on small screens -->
<header class="w3-container w3-top w3-hide-large w3-red w3-xlarge w3-padding">
  <a href="javascript:void(0)" class="w3-button w3-red w3-margin-right" onclick="w3_open()">☰</a>
  <span>Vie Sociale des données 2022</span>
</header>

<!-- Overlay effect when opening sidebar on small screens -->
<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" title="close side menu" id="myOverlay"></div>

<!-- !PAGE CONTENT! -->
<div class="w3-main" style="margin-left:340px;margin-right:40px">

  <!-- Header -->
  <div class="w3-container" style="margin-top:80px" id="presentation">
    <h1 class="w3-jumbo"><b>Vie Sociale des données 2022</b></h1>
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Le Global Footprint Network</u> : <u>Présentation</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p>
      Le Global Footprint Network est un organisme de bienfaisance non-lucratif dont l'objectif principal est de proposer des 
      outils de mesure et des données servant à encourager et faciliter les mesures liées au développement durable. 
      C'est à ces fins qu'ils rendent donc leurs outils et conclusions disponibles en open-source ; notamment, 
      <a href='https://data.footprintnetwork.org/#/'>leur interface en ligne de visualisation de données statistiques</a> a 
      pour vocation de permettre aux particuliers comme aux organisations de réutiliser les données représentées et de mieux 
      les comprendre.
    </p>
    <br>
    <p>
      Le présent site se veut un exemple basique de la façon dont lesdites données peuvent être mobilisées à l'extérieur des 
      plateformes du Global Footprint Network, après avoir contextualisé et présenté l'initiative.
    </p>
  </div>
  
  <!-- Background -->
  <div class="w3-container" id="background" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Historique du GPN</u></b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p>
      L'empreinte écologique ("<i>ecological footprint</i>") est un index créé par <a href='https://en.wikipedia.org/wiki/Mathis_Wackernagel' title='https://en.wikipedia.org/wiki/Mathis_Wackernagel'>Mathis Wackernagel</a> et <a href='https://fr.wikipedia.org/wiki/William_E._Rees' title='https://fr.wikipedia.org/wiki/William_E._Rees'>William Rees</a> 
      <a href='https://www.footprintnetwork.org/about-us/our-history/' title='https://www.footprintnetwork.org/about-us/our-history/'>
      dans les années 90</a>. Wackernagel faisait sa thèse de PhD en “community and regional planning” à l’University of British Columbia et a développé le concept avec W. Rees, son superviseur, qui en était à l’origine. Ce concept est présenté dans l’ouvrage 
      <i><span title='Wackernagel, M. and W. Rees. 1996. Our Ecological Footprint: Reducing Human Impact on the Earth. New Society Publishers.'>
        Our Ecological Footprint: Reducing Human Impact on the Earth</span>
      </i>. Mathis Wackernagel est désormais le Président du Global Footprint Network, ainsi que lauréat du <a href='https://wsforum.org/instructions#awardees' title='https://wsforum.org/instructions#awardees'>World Sustainability Award</a> 
      en 2018. William Rees était son professeur à l’Université de la Colombie-Britannique et est spécalisé dans les recherches
      sur les politiques publiques et l’environnement. Il est à l’origine du concept d’empreinte écologique et co-devéloppeur 
      de la méthode de calcul.
    </p>
  </div>
  
  <!-- Administrators -->
  <div class="w3-container" id="administrators" style="margin-top:75px">
    <h1 class="w3-xxxlarge w3-text-red"><b><u>Gestionnaires actuels</u> :</b></h1>
    <hr style="width:50px;border:5px solid red" class="w3-round">
    <p><b>Les données utilisées pour calculer l’empreinte écologique sont administrées par un consortium composé <a href='https://www.footprintnetwork.org/about-us/people/' title='https://www.footprintnetwork.org/about-us/people/'>du Global 
      Footprint Network</a>, de <a href='https://footprint.info.yorku.ca/people/' title='https://footprint.info.yorku.ca/people/'>l’Université de York</a> et de <a href='https://www.fodafo.org/board.html' title='https://www.fodafo.org/board.html'>la FODAFO (Footprint Data Foundation)</a>, organismes détaillés ci-dessous</b> :</p>
  </div>

  <!-- Administrators list -->
  <div class="w3-row-padding w3-grayscale">
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/GFNlogo.png" alt="Logo of the Global Footing Network" style="width:90%">
        </p>
        <div class="w3-container">
          <h3>Global Footprint Network</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"International think tank working to drive informed, sustainable policy decisions in a world of limited resources. [...] Coordinates research, develops methodological standards, and provides decision-makers with a menu of tools to help the human economy operate within Earth’s ecological limits."
          – <a href='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/' title='https://www.footprintnetwork.org/2015/09/23/eight-countries-meet-two-key-conditions-sustainable-development-united-nations-adopts-sustainable-development-goals/'><i>Only eight countries meet two key conditions for sustainable development as United Nations adopts Sustainable Development Goals</i></a></p></span>
          <span style="white-space: pre-line"><p>
              <u>Président</u> : Mathis Wackernagel, Ph.D
              <u>Direction scientifique</u> : David Lin, Ph.D
              <u>Co-fondatrice</u> : Susan Burns
              <u>Ancienne directrice générale</u> : Julia Marton-Lefèvre
            </p>
          </span>
        </div>
      </div>
    </div>
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/york_logo.png" alt="Logo of the York University" style="width:90%">
        </p>
        <div class="w3-container">
          <h3>York University Ecological Footprint Initiative</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"[...] scholars, students, researchers, and collaborating organizations working together to advance the measurement of Ecological Footprint and Biocapacity and the application of these measures around the world."
          – <a href='https://www.fodafo.org/why-fodafo.html' title='https://www.fodafo.org/why-fodafo.html'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p>
              <u>Doyenne de la Faculté des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte Écologique</u> : Eric Miller
              <u>Chercheure adjointe</u> : Susan Burns
            </p>
          </span>
        </div>
      </div>
    </div>
    <div class="w3-col m4 w3-margin-bottom">
      <div class="w3-light-grey">
        <p align='center'>
          <img src="/images/fodafo_logo.png" alt="Logo of the Footprint Data Foundation" style="width:90%">
      </p>
        <div class="w3-container">
          <h3>Footprint Data Foundation</h3>
          <span style="white-space: pre-line"><p class="w3-opacity">"[...] a not-for-profit called the Footprint Data Foundation (FoDaFO) to be the stewards of these National Footprint & Biocapacity Accounts (NFAs), and to reproduce them with the support of York University and a broader academic network."
          – <a href='https://footprint.info.yorku.ca/' title='https://footprint.info.yorku.ca/'><i>Ecological Footprint Initiative</i></a></p></span>
          <span style="white-space: pre-line"><p>
              <u>Doyenne de la Faculté des Changements Environnementaux et Urbains</u> : Alice Hovorka
              <u>Directeur de l'Initiative Empreinte Écologique</u> : Eric Miller
              <u>Chercheure adjointe</u> : Susan Burns
            </p>
          </span>
        </div>
      </div>
    </div>
  </div>
  
  <!-- API call -->
    <div class="w3-container" id="search_data" style="margin-top:75px">
      <h1 class="w3-xxxlarge w3-text-red"><b><u>Exploiter les données</u> :</b></h1>
      <hr style="width:50px;border:5px solid red" class="w3-round">
      <form action="" method="post">
        <p>La base de donnée du Global Footprint Network est en libre accès et permet à chacun·e de l'exploiter à des fins plus ou moins spécifiques. Nous avons voulu illustrer cette polyvalence en faisant de notre rendu une interface permettant d'accéder aux données de chaque pays inscrits dans la base de données.</p>

        <p align='center'>
          <b>Veuillez choisir un pays :</b> (Obligatoire)
        </p>
        <p align='center'>
          <br>
          <?php
            deroulant('Country',$all_countries_name,NULL,true,$all_countries_code);
          ?>
        </p>
        <br>
        <p align='center'>
          <b>Vous pouvez sélectionner une année précise :</b> (Facultatif)
        </p>
        <br>
        <p align='center'>
          <?php
            deroulant('Year',$all_years,'    ',true);
          ?>
        </p>
        <br>
        <p align='center'>
          <b>Vous pouvez sélectionner les données retournées <u>pour cette année</u> :</b> 
          (N'en cocher aucune retournera toutes les données)
        </p>
        <br>
        <div style="text-align:center;width: max-content;margin: 0 auto;border-style: solid;border-color: #f44336">
          <div class="w3-light-grey">
            <div class="w3-container">
              <?php
                $selected_options = array();
                foreach($all_types_names as $type_name){
                  $type_code=$all_types_code_and_names_associations[$type_name];
                  echo '<p align="center"><div style="text-align:left;border-style: dotted;border-color:#cc3c33;padding: 1em;">
                  <input type="checkbox" id="'.$type_code.'" name="types[]" value="'.$type_code.'">
                  <label for="'.$type_code.'">'.$type_name.'</label>
                  </div></p>';
                }
              ?>
            </div>
          </div>
        </div>
        <br>
        <p align='center'>
          <button class="w3-button w3-block w3-padding-large w3-red w3-margin-bottom" type="submit" name=submit onclick="w3_close()">Je valide ces critères de recherche</button>
        </p>
      </form>
      <?php
          if(isset($_POST['submit'])){
            if($_POST['Year']=="0"){
              search($_POST['Country']);
            }else{
              if(isset($_POST['types'])){
                search($_POST['Country'],$all_years[$_POST['Year']],$_POST['types']);
              }else{
                search($_POST['Country'],$all_years[$_POST['Year']]);
              }
            }
          }
      ?>
    </div>
    <br>
    
    <form action="/action_page.php" target="_blank">
      <div class="w3-section">
        <label>Name</label>
        <input class="w3-input w3-border" type="text" name="Name" required>
      </div>
      <div class="w3-section">
        <label>Email</label>
        <input class="w3-input w3-border" type="text" name="Email" required>
      </div>
      <div class="w3-section">
        <label>Message</label>
        <input class="w3-input w3-border" type="text" name="Message" required>
      </div>
      <button type="submit" class="w3-button w3-block w3-padding-large w3-red w3-margin-bottom">Send Message</button>
    </form>  
  </div>

<!-- End page content -->
</div>

<!-- W3.CSS Container -->
<div class="w3-light-grey w3-container w3-padding-32" style="margin-top:75px;padding-right:58px"><p class="w3-right">Powered by <a href="https://www.w3schools.com/w3css/default.asp" title="W3.CSS" target="_blank" class="w3-hover-opacity">w3.css</a></p></div>

<script>
// Script to open and close sidebar
function w3_open() {
  document.getElementById("mySidebar").style.display = "block";
  document.getElementById("myOverlay").style.display = "block";
}
 
function w3_close() {
  document.getElementById("mySidebar").style.display = "none";
  document.getElementById("myOverlay").style.display = "none";
}

// Modal Image Gallery
function onClick(element) {
  document.getElementById("img01").src = element.src;
  document.getElementById("modal01").style.display = "block";
  var captionText = document.getElementById("caption");
  captionText.innerHTML = element.alt;
}
</script>

</body>
</html>
