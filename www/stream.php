<?php

//error_reporting(0);

require_once("functions.php");

if(isset($_GET["id"]) && is_numeric($_GET["id"])) {
  $id_streamu = $_GET["id"];
} else {
  przekierowanie("./index.php");
}

sprawdzZalogowanie("","");
$adres = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//dodawanie komentarza
$status_subskrybcji = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'comment_form' && $username != null)
{
  $data = date("Y-m-d G:i:s");
  $komentarz = trim($_POST['pole_komentarza']);

  $conn = polaczDB();
  $user_id = $conn->real_escape_string($user_id);
  $id_streamu = $conn->real_escape_string($id_streamu);
  $komentarz = $conn->real_escape_string($komentarz);
  $query = "INSERT INTO comments_streams (User_ID, Stream_ID, Date, Comment)
  VALUES ({$user_id}, {$id_streamu}, '{$data}', '{$komentarz}');";

  $result = queryDB($conn,$query);
  if($result === TRUE) {
    pokazKomunikat("Komentarz został dodany");
  } else {
    pokazKomunikat("Wystąpił błąd podczas dodawania komentarza");
  }
  przekierowanie($adres);
}

//dodawanie komentarza

if(isset($_POST['type'])) {
  if($username == null) {
    pokazKomunikat("Musisz się zalogować, aby ocenić transmisję");

  } else {

    $typ = $_POST['type'];
    $conn = polaczDB();
    $user_id = $conn->real_escape_string($user_id);
    $id_streamu = $conn->real_escape_string($id_streamu);
    $query = "SELECT ID, Type FROM likes_streams WHERE User_ID = {$user_id} AND Stream_ID = {$id_streamu};";
    $result = queryDB($conn,$query);

    if ($result->num_rows > 0) {

      while($row = $result->fetch_assoc()) {

        $typ_db = $row['Type'];
        $id_oceny = $row['ID'];

        if($typ_db == $typ) {
          $query = "DELETE FROM likes_streams WHERE ID = {$id_oceny};";
          $result = queryDB($conn,$query);

        } else {
          $typ = $conn->real_escape_string($typ);
          $user_id = $conn->real_escape_string($user_id);
          $id_streamu = $conn->real_escape_string($id_streamu);
          $query = "UPDATE likes_streams SET Type = '{$typ}' WHERE User_ID = {$user_id} AND Stream_ID = {$id_streamu};";
          $result = queryDB($conn,$query);
        }
        break;
      }
    } elseif ($result->num_rows == 0) {

      $id_streamu = $conn->real_escape_string($id_streamu);
      $user_id = $conn->real_escape_string($user_id);
      $typ = $conn->real_escape_string($typ);
      $query = "INSERT INTO likes_streams (Stream_ID, User_ID, Type)
      VALUES ({$id_streamu}, {$user_id},'{$typ}')";

      $result = queryDB($conn,$query);

    } else {
      pokazKomunikat("Wystąpił błąd podczas oceny transmisji");
    }
  }
  przekierowanie($adres);
}

if($username == null) {

  $menu_1 = "Rejestracja";
  $menu_2 = "Zaloguj się";
  $link_1 = "./register.php";
  $link_2 = "./login.php";

} else {

  $menu_1 = "Moje konto";
  $menu_2 = "Wyloguj się";
  $link_1 = "./account.php";
  $link_2 = "./logout.php";

}

$adres = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$miniaturka = "miniatures_streams/{$nazwa_pliku}.jpeg";

$conn = polaczDB();
$id_streamu = $conn->real_escape_string($id_streamu);
$query = "SELECT streams.User_ID, Title, Views, Filename, Author, Streamkey_active, streams.Date, Describtion, streams.Password, Login FROM streams INNER JOIN users ON users.ID = streams.User_ID WHERE streams.ID = {$id_streamu};";
$result = queryDB($conn,$query);
$tytul = $opis = $wyswietlenia = $ocena_up = $ocena_down = $data_publikacji = $przesylajacy = $nazwa_pliku = null;

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

    $haslo = $row["Password"];

    if($haslo != null) {

      if(isset($_SESSION['access'])) {
        $dostep = $_SESSION['access'];

        if (!in_array($id_streamu, $dostep)) {
          przekierowanie("./protection.php?tryb=stream&id={$id_streamu}");
        }
      } else {
        przekierowanie("./protection.php?tryb=stream&id={$id_streamu}");
      }
    }
    $streamkey = $row["Streamkey_active"];
    $tytul = $row["Title"];
    $nazwa_pliku = $row["Filename"];
    $opis = $row["Describtion"];
    $autor = $row["Author"];
    $wyswietlenia = $row["Views"];
    $wyswietlenia = number_format($wyswietlenia, 0, ',', ' ');
    $data_publikacji = $row["Date"];
    $date = new DateTime($data_publikacji);
    $data_publikacji = $date->format('d.m.Y');
    $przesylajacy = $row["Login"];
    $przesylajacy_id = $row["User_ID"];
    break;
  }

  if($przesylajacy == $username || $privileges == "Administrator") {
    $link_usuniecie_streamu = "";
  } else {
    $link_usuniecie_streamu = "display: none";
  }

  $streamy_sciezka = "/usr/local/antmedia/webapps/LiveApp/streams";
  $typ = "LiveApp";
  $znaleziono = false;
  if (file_exists("{$streamy_sciezka}/{$streamkey}.m3u8")) {
    $znaleziono = true;
  }
  if($znaleziono === false && $privileges != "Administrator") {
    pokazKomunikat("Transmisja została zakończona");
    przekierowanie("./index.php");
  }
  $stream = "http://{$adres_serwera}:5080/{$typ}/play.html?name={$streamkey}&autoplay=true";

} else {
  przekierowanie("./index.php");
}


$kategorie_ids = array();
$query = "SELECT Category_ID FROM categories_videos WHERE Stream_ID = '{$id_streamu}';";
$result = queryDB($conn,$query);


if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

    array_push($kategorie_ids,$row["Category_ID"]);

  }
}

$kategorie_ids_string = implode(', ', $kategorie_ids);

$kategorie_nazwy = array();
$query = "SELECT Name FROM categories WHERE ID IN ({$kategorie_ids_string});";
$result = queryDB($conn,$query);


if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

    array_push($kategorie_nazwy,$row["Name"]);

  }
}
$kategorie_nazwy_string = implode(',', $kategorie_nazwy);



$ocena_up = 0;
$ocena_down = 0;
$id_streamu = $conn->real_escape_string($id_streamu);
$query = "SELECT Type, COUNT(*) AS Liczba FROM likes_streams WHERE Stream_ID = {$id_streamu} GROUP BY Type;";
$result = queryDB($conn,$query);

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {

    $typ = $row["Type"];
    $liczba = $row["Liczba"];

    if($typ == "Like") {
      $ocena_up = $liczba;
    } else {
      $ocena_down = $liczba;
    }
  }
}
$status_subskrybcji = "Subskrybuj autora";

// subskrybcja

if($username != null) {

  $przesylajacy_id = $conn->real_escape_string($przesylajacy_id);
  $user_id = $conn->real_escape_string($user_id);
  $query = "SELECT ID FROM subscription WHERE User_ID = {$user_id} AND Author_ID = {$przesylajacy_id} LIMIT 1;";
  $result = queryDB($conn,$query);

  if ($result->num_rows > 0) {
    $status_subskrybcji = "Odsubskrybuj autora";
  }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form_name']) && $_POST['form_name'] == 'subscribe_form')
{
  if($_POST['typ'] == "autor") {

    if($username == null) {
      pokazKomunikat("Musisz się zalogować, aby dodać subskrybcję");

    } else {

      if($status_subskrybcji == "Subskrybuj autora") {

        $user_id = $conn->real_escape_string($user_id);
        $przesylajacy_id = $conn->real_escape_string($przesylajacy_id);
        if($user_id == $przesylajacy_id) {
          pokazKomunikat("Nie możesz dodać subskrypcji samemu sobie");
        } else {
          $query = "INSERT INTO subscription (User_ID, Author_ID) VALUES ({$user_id}, {$przesylajacy_id})";
          $result2 = queryDB($conn,$query);

          if($conn->affected_rows > 0) {
            pokazKomunikat("Subskrypcja została dodana");
            $status_subskrybcji = "Odsubskrybuj autora";
          } else {
            pokazKomunikat("Nie udało się zmienić subskrypcji");
          }
        }

      } else {

        $user_id = $conn->real_escape_string($user_id);
        $przesylajacy_id = $conn->real_escape_string($przesylajacy_id);
        $query = "DELETE FROM subscription WHERE User_ID = {$user_id} AND Author_ID = {$przesylajacy_id};";
        $result = queryDB($conn,$query);

        if($conn->affected_rows > 0) {
          pokazKomunikat("Subskrybcja została usunięta");
        } else {
          pokazKomunikat("Nie udało się zmienić subskrybcji");
        }
      }
    }
  }
  przekierowanie($adres);
}

// subskrybcja
// podobne

$podobne_filmy_tablica = array();
$limit_podobnych = 5;
$id_streamu = $conn->real_escape_string($id_streamu);
$query = "SELECT ID, Title, Filename FROM movies WHERE ID IN (SELECT Movie_ID FROM categories_videos WHERE Category_ID IN ({$kategorie_ids_string})) AND NOT ID = '{$id_streamu}' ORDER BY Date DESC LIMIT {$limit_podobnych};";
$result = queryDB($conn,$query);
$tytul_podobny = $id_streamu_podobny = $miniaturka_podobny = $adres_podobny = null;

if ($result->num_rows > 0) {
  // output data of each row
  while($row = $result->fetch_assoc()) {
    $id_streamu_podobny = $row["ID"];
    $nazwa_pliku_podobny = $row["Filename"];
    $adres_podobny = "./video.php?id=".$id_streamu_podobny;
    $tytul_podobny = $row["Title"];
    $miniaturka_podobny = "miniatures/".$nazwa_pliku_podobny.".jpeg";
    array_push($podobne_filmy_tablica,array('id' => $id_streamu_podobny,'adres' => $adres_podobny,'tytul' => $tytul_podobny,'miniaturka' => $miniaturka_podobny));
  }
}

// Komentarze

$komentarze_tablica = array();
$id_streamu = $conn->real_escape_string($id_streamu);
$query = "SELECT comments_streams.ID,comments_streams.User_ID, comments_streams.Stream_ID, comments_streams.Comment, comments_streams.Date, users.Login FROM comments_streams INNER JOIN users ON comments_streams.User_ID = users.ID
WHERE Stream_ID = {$id_streamu};";

$result = queryDB($conn,$query);

if ($result->num_rows > 0) {

  while($row = $result->fetch_assoc()) {
    $komentarz = $row['Comment'];
    $komentujacy = $row['Login'];
    $id_komentarza = $row['ID'];
    $data = $row['Date'];
    $data = date("d.m.Y H:i", strtotime($data));
    $id_komentujacego = $row['User_ID'];
    if($username == $komentujacy || $privileges == "Administrator" || $privileges == "Moderator") {
      $link_usuniecie = true;
    } else {
      $link_usuniecie = false;
    }
    array_push($komentarze_tablica,array('komentarz' => $komentarz,'komentujacy' => $komentujacy,'id_komentarza' => $id_komentarza,'id_komentujacego' => $id_komentujacego,'data' => $data,'link_usuniecie' => $link_usuniecie));
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
  <meta property="og:image" content="<?php echo $miniaturka; ?>"/>
  <meta property="og:title" content="<?php echo htmlentities($tytul); ?>"/>
  <meta property="og:description" content="<?php echo htmlentities($opis); ?>"/>
  <title>Telewizja internetowa Uniwersytetu Rzeszowskiego - Transmisja na żywo</title>
  <link rel="stylesheet" href="./css/style.css">
  <script src="./js/jquery-3.3.1.min.js"></script>
  <script src="./js/walidacja_komentarza.js"></script>
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
        <li>Transmisja na żywo</li>
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

      <div id="blok_lewy" class="blok_wideo">
        <div id="blok_wideo">

          <h1><span style="<?php echo $link_usuniecie_streamu; ?>"><a href="#" onclick="potwierdzUsuniecieFilmu(<?php echo $id_streamu; ?>);return false;">[Usuń] </a></span><?php echo $tytul; ?></h1>
          <iframe id="stream" scrolling="no" src="<?php echo $stream; ?>"></iframe>
          <div id="przyciski_filmu" class="clearfix">
            <div id="przyciski_filmu_lewa">

              <form id="subscribe_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?id='.$id_streamu; ?>">
                <input type="hidden" name="typ" value="autor">
                <input type="hidden" name="form_name" value="subscribe_form">
                <button type="submit" id="subskrybcja_autora" class="przyciski_filmu_lewa"><?php echo $status_subskrybcji; ?></button>
              </form>

            </div>
            <div id="przyciski_filmu_prawa">

              <button type="submit" onclick="wyslijEmail();"><img src="./images/mail_icon.png"></button>
              <button type="submit" onclick="window.open('https://twitter.com/intent/tweet?url=<?php echo $adres."&text=".$tytul; ?>','targetWindow','toolbar=no,location=0,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=250');"><img src="./images/twitter_icon.png"></button>
              <button type="submit" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u=<?php echo $adres; ?>','targetWindow','toolbar=no,location=0,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=250');"><img src="./images/fb_icon.png"></button>
            </div>
          </div>
          <div id="dane_filmu" class="clearfix">

            <div id="dane_lewa">
              <p class="dane_przesylajacy">Autor: <?php echo $autor; ?></p>
              <p>Opublikowano: <?php echo $data_publikacji; ?></p>
              <p class="dane_kategorie">Kategorie: <?php echo $kategorie_nazwy_string; ?></p>
            </div>


            <div id="dane_prawa">
              <p>Wyświetlenia: <?php echo $wyswietlenia; ?></p>
              <div class ="polubienie clearfix"><p><?php echo $ocena_up; ?></p>
                <form id="like_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?id='.$id_streamu; ?>">
                  <input type="hidden" name="type" value="Like" id="type">
                  <input type="hidden" name="form_name" value="like_form">
                  <button type="submit"><img src="./images/thumb_up.png"></button>
                </form>
              </div>
              <div class ="polubienie clearfix"><p><?php echo $ocena_down; ?></p>
                <form id="unlike_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?id='.$id_streamu; ?>">
                  <input type="hidden" name="type" value="Unlike" id="type">
                  <input type="hidden" name="form_name" value="unlike_form">
                  <button type="submit"><img src="./images/thumb_down.png"></button>
                </form>
              </div>
            </div>

            <div id="opis_filmu">
              <p class="opis"><?php echo $opis; ?></p>
            </div>

          </div>
        </div>

        <div id="blok_lewy_dolny">
          <div id="komentarze">
            <h1>Komentarze:</h1>
            <ul>

              <?php
              foreach ($komentarze_tablica as $wynik) {
                $komentarz = $wynik['komentarz'];
                $komentujacy = $wynik['komentujacy'];
                $id_komentarza = $wynik['id_komentarza'];
                $czy_usunac = $wynik['link_usuniecie'];

                if($czy_usunac === true) {
                  $link_usuniecie = "";
                } else {
                  $link_usuniecie = "display: none";
                }



                echo '<li>
                <p class="komentarze"><span style="'.$link_usuniecie.'"><a href="#" onclick="potwierdzUsuniecieKomentarza('.$id_komentarza.');return false;">[Usuń] </a></span><span class="komentujacy">'.$komentujacy.':</span> '.$komentarz.'
                </li>';
              }
              ?>
            </ul>
            <form name="comment_form" class="clearfix" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?id='.$id_streamu; ?>" id="comment_form" onsubmit="return Validatecomment_form()">
              <input type="hidden" name="form_name" value="comment_form">
              <textarea name="pole_komentarza" id="pole_komentarza" rows=10 placeholder="Wpisz treść komentarza..."></textarea>
              <div id="przyciski_komentarza">
                <button type="button" id="wyczysc_komentarz" class="przyciski_komentarza" onclick="wyczyscKomentarz();">Wyczyść komentarz</button>
                <button type="submit" id="dodaj_komentarz" class="przyciski_komentarza">Dodaj komentarz</button>
              </div>
            </form>
          </div>
        </div>

      </div>

      <div id="blok_prawy">
        <div id="lista_filmow_podobnych">
          <h1>Podobne filmy:</h1>
          <ul>
            <?php
            foreach ($podobne_filmy_tablica as $wynik) {
              $tytul = $wynik['tytul'];
              $miniaturka = $wynik['miniaturka'];
              $id = $wynik['id'];
              $adres = $wynik['adres'];
              echo '<li>
              <a href="'.$adres.'"><img class="miniaturka_filmu_podobnego" src="'.$miniaturka.'"></a>
              <h2><a href="'.$adres.'">'.$tytul.'</a></h2>
              </li>';
            }
            ?>
          </ul>
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
  <script src="./js/stream.js"></script>
  <?php

  if ($username == null) {
    echo "<script> ustawPoleKomentarza(false); </script>";
  }
  $id_streamu = $conn->real_escape_string($id_streamu);
  $query = "UPDATE streams SET Views = Views + 1 WHERE ID = {$id_streamu};";
  $result = queryDB($conn,$query);
  require_once("functions_end.php");
  ?>
</body>
</html>
