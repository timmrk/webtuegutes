<?php
/**
 * Bietet die Möglichkeit sich im System zu registrieren
 *
 * Ein Nutzer gibt seinen gewünschten Benutzernamen, seinen Vornamen, Nachnamen, sein gewünschtes Passwort und seine Email-Adresse ein
 * Falls alle Daten den Anforderungen entsprechen (Benutzername und Email noch nicht registriert) wird eine Email mit einem Bestätigungslink
 * zum aktivieren des Accounts gesendet
 *
 * @author Andreas Blech <andreas.blech@stud.hs-hannover.de>
 * @author Henrik Huckauf <henrik.huckauf@stud.hs-hannover.de>
 */

require "./includes/DEF.php";

//Inkludieren von script-Dateien

//DB Funktionen, die später ausgelagert werden sollten
// TIMM:
// ausgelagert in db_connector, code ist gesaved in einer .txt bei mir


$username = isset($_POST['benutzername']) ? $_POST['benutzername'] : '';
$vorname = isset($_POST['vorname']) ? $_POST['vorname']: '';
$nachname = isset($_POST['nachname']) ? $_POST['nachname'] : '';
$pass = isset($_POST['passwort']) ? $_POST['passwort'] : '';
$passwdh = isset($_POST['passwortwdh']) ? $_POST['passwortwdh'] : '';
$mail = isset($_POST['mail']) ? $_POST['mail'] : '';

$sjdnjghbeid = DBFunctions::db_initNewKey();

$output = '';
if(isset($_GET['c'])) // man kann auch wenn man mit einem Account angemeldet ist, einen anderen Account verifizieren
{
	if(DBFunctions::db_activateAccount($_GET['c']) === true)
	{
		$goto = $HOST . ($_USER->loggedIn() ? "" : "/login?code=101");
		$_USER->redirect($goto);
	}
	else
	{
		//Das Aktivieren des Accounts hat aus unbekanntem Grund nicht funktioniert
		//Informiere den Benutzer darüber
		$output = '<red>Upps, da ist etwas schief gegangen :(</red>';
	}
}
else
{
	if(!$_USER->loggedIn())
	{
		if(isset($_POST['set']) && $_POST['set'] == '1')
		{
			$error = false;
			if(!isset($_POST['cbdatenschutzAgb'])) {
				$output .= "<red>Sie müssen unsere Datenschutzerklärung akzeptieren, damit Sie sich registrieren können<br></red>";
				$error = true;
			}
			if(empty($username))
			{
				$output .= "<red>Geben Sie einen Benutzernamen an!</red><br>";
				$error = true;
			}
			else
			{
				// http://regexr.com/
				if(strlen($username) < 3 || strlen($username) > 20 || !preg_match("/^[a-zA-Z0-9 äöü ÄÖÜ ß]+([_.\-]?[a-zA-Z0-9 äöü ÄÖÜ ß])*$/", $username)) ///^[a-zA-Z0-9 äöü ÄÖÜ ß]+([_.\s\-]?[a-zA-Z0-9 äöü ÄÖÜ ß])*$/
				{
					$output .= "<red>Für den Benutzernamen gilt:<br>- der Benutzername muss zwischen 3 und 20 Zeichen lang sein<br>- der Benutzername darf nicht mit Sonderzeichen beginnen oder enden<br>- es dürfen nicht mehrere Sonderzeichen aufeinander folgen<br>- der Benutzername darf nur aus a-z A-Z 0-9 äöü ÄÖÜ ß _ . und LEERZEICHEN bestehen</red><br>";
					$error = true;
				}
			}
			if(DBFunctions::db_idOfBenutzername($username) != false)
			{
				$output .= "<red>Der gewählte Benutzername ist bereits registriert!</red><br>";
				$error = true;
			}
			if(empty($vorname))
			{
				$output .= "<red>Geben Sie ihren Vornamen an!</red><br>";
				$error = true;
			}
			if(empty($nachname))
			{
				$output .= "<red>Geben Sie ihren Nachnamen an!</red><br>";
				$error = true;
			}
			if(empty($pass))
			{
				$output .= "<red>Geben Sie ein Passwort an!</red><br>";
				$error = true;
			}
			else if(strlen($pass) < 6)
			{
				$output .= "<red>Das Passwort muss mindestens 6 Zeichen lang sein!</red><br>";
				$error = true;
			}
			else if($pass !== $passwdh)
			{
				$output .= "<red>Die Passwörter stimmen nicht überein!</red><br>";
				$error = true;
			}
			if(empty($mail) || !filter_var($mail, FILTER_VALIDATE_EMAIL))
			{
				$output .= "<red>Geben Sie eine gültige Email-Adresse an!</red><br>";
				$error = true;
			}
			else if(DBFunctions::db_idOfEmailAdresse(strtolower($mail)) <> false) {
				$output .= "<red>Die angegebene Email-Adresse wird bereits für einen anderen Account verwendet</red><br>";
				$error = true;
			}


			if(!neuerAcount($sjdnjghbeid))
			{
				// = statt .= weil dieser Fehler alle anderen Überschreiben soll
				$output = "<red>Sie waren zu lange inaktiv. Bitte laden Sie die Seite neu, um sich zu registrieren!</red><br>";
				$error = true;
			}

			if(!$error)
			{
				DBFunctions::db_deleteKey();

				$cryptkey = DBFunctions::db_createBenutzerAccount($username, $vorname, $nachname, $mail, $pass);
				if($cryptkey)
				{
					//Account erfolgreich in Datenbank erstellt
					//Sende Bestätigungslink an Mailadresse
					$actual_link = $HOST . "/registration";

					$mailcontent = "<div style=\"margin-left:10%;margin-right:10%;background-color:#757575\"><img src=\"img/wLogo.png\" alt=\"TueGutes\" title=\"TueGutes\" style=\"width:25%\"/></div><div style=\"margin-left:10%;margin-right:10%\"><h1>Herzlich Willkommen <b>".$vorname."</b> bei 'Tue Gutes in Hannover':</h1> <h3>Klicke auf den <a href=\"$actual_link?c=$cryptkey\">Link</a>, um deine Registrierung abzuschließen </h3></div>";

					if(sendEmail($mail, "Ihre Registrierung bei TueGutes in Hannover", $mailcontent) === true)
						$_USER->redirect($HOST. "/login?code=102");
					else
						$output = '<red>Bestätigungslink an ' . $mail . ' konnte nicht gesendet werden!</red>';
				}
				else
					$output = '<red>Interner Fehler:<br>Es konnte kein Benutzeraccount angelegt werden!</red>';
			}
		}
	}
	else
		$_USER->redirect($HOST);
}

require "./includes/_top.php";
?>

<h2><?php echo $wlang['register_head']; ?></h2>

<div id='output'><?php echo $output; ?></div>
<br><br>
<div class="center">
	<form action="" method="post">
		<input type="text" name="benutzername" value="<?php echo $username; ?>" placeholder="<?php echo "Benutzername"; ?>" required /><br>
		<input type="text" name="vorname" value="<?php echo $vorname; ?>" placeholder="<?php echo "Vorname"; ?>" required /><br>
		<input type="text" name="nachname" value="<?php echo $nachname; ?>" placeholder="<?php echo "Nachname"; ?>" required /><br>
		<input type="password" name="passwort" value="" placeholder="<?php echo "Passwort"; ?>" required /><br>
		<input type="password" name="passwortwdh" value="" placeholder="<?php echo "Passwort wiederholen"; ?>" required /><br>
		<input type="text" name="mail" value="<?php echo $mail; ?>" placeholder="<?php echo "E-Mail Adresse"; ?>" required /><br>
		<br>
		<table class='block'>
			<tr>
				<td><input id="cAGB" type="checkbox" name="cbdatenschutzAgb">
					<label for="cAGB">Ich habe die <a href=<?php echo $HOST . "/privacy"; ?> target="_blank">Datenschutzerklärung</a> gelesen <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;und akzeptiere sie (erforderlich)</label> </td><!-- TODO: AGB hinzufügen sobald verfügbar -->
			</tr>

			<tr>
				<td><input id="cLeitbild" type="checkbox" name="cbLeitbild">
					<label for="cLeitbild">Ich habe das <a href=<?php echo $HOST . "/terms"; ?> target="_blank">Leitbild</a> gelesen und <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;teile die Überzeugungen und Werte</label></td>
			<tr>
		</table>
		<br>
		<br>
		<input type='hidden' name='set' value='1' />
		<input type="submit" value="Registrieren" />
	</form>
	<br><br>
	<a href="./PasswortUpdate">Passwort vergessen?</a>
	<br><br>
	<a href="./login">Bereits registriert?</a>
</div>

<?php
require "./includes/_bottom.php";
?>
