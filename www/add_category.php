<?php

require_once("functions.php");
$adres = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

  sprawdzZalogowanie("","./login.php?return=add_category.php");

  if($privileges != "Administrator") {
    przekierowanie("./index.php");
    pokazKomunikat("Nie posiadasz odpowiednich uprawnień");
  }

  $menu_1 = "Moje konto";
  $menu_2 = "Wyloguj się";
  $link_1 = "./account.php";
  $link_2 = "./logout.php";

  $conn = polaczDB();


  if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_name'] == 'add_category') {

    $nazwa_kategorii = $_POST['kategoria'];
    $id_grupy = $_POST['grupa'];

    if($id_grupy > 0) {
    } else {
      $id_grupy = "NULL";
    }
    //dodawanie filmu do bazy
    $nazwa_kategorii = $conn->real_escape_string($nazwa_kategorii);
    $id_grupy = $conn->real_escape_string($id_grupy);

    $query_count = "SELECT count(ID) FROM categories WHERE Name = '{$nazwa_kategorii}';";
    $result = queryDB($conn,$query_count);
    $row = $result->fetch_row();
    $taka_sama_nazwa = $row[0];

    if($taka_sama_nazwa > 0) {

      pokazKomunikat("Istnieje już kategoria o takiej nazwie");
      przekierowanie("./add_category.php");

    } else {


      $query = "INSERT INTO categories (Name, Group_ID)
      VALUES ('{$nazwa_kategorii}', {$id_grupy})";

      $result = queryDB($conn,$query);
      if($result === TRUE) {
        //dodawanie tagów do bazy
        pokazKomunikat("Kategoria została dodana");
        przekierowanie("./admin.php");
      } else {
        pokazKomunikat("Wystąpił błąd podczas dodawania kategorii");
      }

    }
  }


  $grupy_tablica = array();
  $query = "SELECT Name, ID
  FROM categories_groups;";

  $result= queryDB($conn,$query);

  if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
      $nazwa = $row["Name"];
      $id = $row["ID"];
      array_push($grupy_tablica, array('nazwa' => $nazwa,'id' => $id));

    }
  }


  ?>
  <!DOCTYPE html>
  <html lang="pl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta name="description" content="Telewizja internetowa Uniwersytetu Rzeszowskiego">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/site.webmanifest">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/favicons/favicon.ico">
    <title>Telewizja internetowa Uniwersytetu Rzeszowskiego - Dodawanie kategorii</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="./js/jquery-3.3.1.min.js"></script>
    <script src="./js/walidacja_dodawanie_kategorii.js"></script>
  </head>
  <body>
    <header>
      <div class="kontener">
        <div id="naglowek_tytul">
          <div id="logo_strony">
            <a href="./index.php">
              <img src="./images/logo_UR.png">
            </a>
          </div>
          <h1>Telewizja internetowa<br>Uniwersytetu Rzeszowskiego</h1>
        </div>
        <div id="naglowek_nawigacja">
          <nav>
            <ul>
              <li><a href="index.php">Strona główna</a></li>
              <li><a href="about.php">O serwisie</a></li>
              <li><a href="contact.php">Kontakt</a></li>
              <li><a href="<?php echo $link_1; ?>"><?php echo $menu_1; ?></a></li>
              <li><a href="<?php echo $link_2; ?>"><?php echo $menu_2; ?></a></li>
            </ul>
          </nav>
        </div>
      </div>
    </header>

    <div id="blok_podnaglowka" class="blok_odstep_dolny blok_odstep_gorny">
      <div class="kontener">
        <ul id="sciezka" class="podnaglowek">
          <li><a href="./index.php">Strona główna</a></li>
          <li><a href="./account.php">Moje konto</a></li>
          <li><a href="./admin.php">Panel admina</a></li>
          <li>Dodawanie kategorii</li>
        </ul>

        <form id="wyszukiwarka" method="get" action="./search.php">
          <input type="text" name="phrase" class="podnaglowek" placeholder="Wpisz szukany tekst...">
          <input type="hidden" name="page" value="1">
          <select class="podnaglowek" name="mode">
            <option selected value="Tytul">Tytuł</option>
            <option value="Tagi">Tag</option>
            <option value="Autor">Autor</option>
          </select>
          <button type="submit" class="button_1">Szukaj</button>
        </form>
      </div>
    </div>

    <div id="blok_glowny">
      <div class="kontener">
        <div class="naglowek_formularza"><h1>Dodawanie kategorii</h1></div>
        <div class="tlo">
          <div class="blok_formularza formularz_edycja blok_padding_gorny blok_padding_dolny">
            <form id="add_category" name="add_category" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return Validateadd_category()">
              <input type="hidden" name="form_name" value="add_category">
              <div class="pole">
                <input type="text" class="centrowanie" name="kategoria" id="kategoria" value="" placeholder="">
                <p>Nazwa:</p>
              </div>
              <div class="pole">
                <select name="grupa" class="centrowanie" id="grupa">
                   <option value="0" selected>Brak grupy</option>
                  <?php
                  foreach ($grupy_tablica as $grupa) {
                    $nazwa = $grupa['nazwa'];
                    $id = $grupa['id'];
                    echo '<option value="'.$id.'">'.$nazwa.'</option>';
                  }
                  ?>
                </select>
                <p>Grupa:</p>
              </div>
              <div class="pole ostatnie_pole">
                <button type="submit" id="przycisk_kategorie" class="button_1">Dodaj kategorię</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <footer class="blok_odstep_gorny">
      <div class="kontener">
        <ul>
          <li>© Uniwersytet Rzeszowski 2018</li>
          <li class="desktop"><a href="http://www.ur.edu.pl/">Strona Uniwersytetu</a></li>
          <li><a href="terms.php">Regulamin serwisu</a></li>
          <li><a href="privacy.php">Polityka prywatności</a></li>
        </ul>
      </div>
    </footer>

    <?php
    require_once("functions_end.php");
    ?>
  </body>
  </html>
