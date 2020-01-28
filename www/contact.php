<?php
require_once("functions.php");
sprawdzZalogowanie("","");

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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_name'] == 'contact_form')
{

  $email = $_POST['email'];
  $nazwa = $_POST['name'];
  $temat = $_POST['topic'];
  $wiadomosc = $_POST['message'];

  if(wyslijEmail("kontakt@urtv.tk",$temat,$wiadomosc,$email,$nazwa)) {
    pokazKomunikat("Wiadomość została wysłana");
  } else {
    pokazKomunikat("Wystąpił błąd podczas wysyłania wiadomości");
  }
  przekierowanie($adres_obecny);
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
  <title>Telewizja internetowa Uniwersytetu Rzeszowskiego - Formularz kontaktowy</title>
  <link rel="stylesheet" href="./css/style.css">
  <script src="./js/jquery-3.3.1.min.js"></script>
  <script src="./js/walidacja_kontakt.js"></script>
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
        <li>Kontakt</li>
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
      <div class="naglowek_formularza"><h1>Formularz kontaktowy</h1></div>
      <div class="tlo">
        <div class="blok_formularza formularz_kontakt blok_padding_gorny blok_padding_dolny">
          <form id="contact_form" name="contact_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" onsubmit="return Validatecontact_form()">
            <input type="hidden" name="form_name" value="contact_form">
            <div class="pole">
              <input type="text" class="centrowanie" name="name" id="name" placeholder="">
              <p>Imię i nazwisko:</p>
            </div>
            <div class="pole">
              <input type="email" class="centrowanie" name="email" id="email" placeholder="">
              <p>Adres e-mail:</p>
            </div>
            <div class="pole">
              <input type="text" class="centrowanie" name="topic" id="topic" placeholder="">
              <p>Temat:</p>
            </div>
            <div class="pole">
              <textarea name="message" id="message" rows=10></textarea>
              <p>Wiadomość:</p>
            </div>
            <div class="pole ostatnie_pole">
              <button type="submit" class="button_1">Wyślij</button>
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
