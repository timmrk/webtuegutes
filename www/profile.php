<?php
	//Zum Ausblenden von Features, die Safari nicht kann (oder eben ggf. andere Browser)
	//Clever wäre, das hier in die DEF auszulagern!
	//Und ja, ich weiß dass man den HTTP_USER_AGENT manipulieren kann - dann ist man aber selber Schuld

	/*if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
	   $browser = 'Internet explorer';
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) //IE 11
	    $browser = 'Internet explorer';
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE)
	   $browser = 'Mozilla Firefox';
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== FALSE)
	   $browser = 'Google Chrome';
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
	   $browser = "Opera Mini";
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== FALSE)
	   $browser = "Opera";
	 elseif(strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== FALSE)
	   $browser = "Safari";
	 else
	   echo 'Something else';*/
	
?>

<?php
/*
* @author: Nick Nolting, Alexander Gauggel
*/

	// Funktionen
	// ALEX
	// Returns the day of birth of a given date in format "YYYY-MM-DD".
	/*function getDayOfBirth($pBirthValues)
	{
		return substr($pBirthValues, 8, 2);
	}
	// Returns the month of birth of a given date in format "YYYY-MM-DD".
	function getMonthOfBirth($pBirthValues)
	{
		return substr($pBirthValues, 5, 2);
	}
	// Returns the year of birth of a given date in format "YYYY-MM-DD".
	function getYearOfBirth($pBirthValues)
	{
		return substr($pBirthValues, 0, 4);
	}*/

	// ALEX: Inserts a new postal code.
	// TIMM: Ausgelagert in db_connector.
	// hat auch db_ davor jetzt: db_insertPostalCode

	// TODO: Move to Utils.php.
	// Returns a map of postal code and place to a given address.
	function getPostalPlaceToAddress($pStreet, $pHouseNumber, $pPlace)
	{
		// ALEX2: Added entry retHouseNumber.
		$lRetVals = [
			"retPostal" => "",
			"retPlace" => "",
			"retHouseNumber" => "",
		];
		// ALEX2: Added value $lHouseNumber to set a valid search value if neccessary.
		$lHouseNumber = (!is_numeric($pHouseNumber)) ? 1 : $pHouseNumber;

		// Create address in format "<street>[+<street appendices],<house number>,Hannover".
		$lAddressString = $pStreet . ',' . $pHouseNumber . ',' . $pPlace;
		// Replace empty spaces.
		$lAddressString = str_replace(' ', '+', $lAddressString);
		// Get JSON result.
		$lContents = file('http://nominatim.openstreetmap.org/search?format=json&limit=2&q=' . $lAddressString);
		// Put string in new variable for safety.
		$lResult = $lContents[0];

		// TODO: Raus
		//echo $lResult;
		//echo strlen($lResult);

		// ALEX2: If result string is too short, no address was found.
		if(strlen($lResult) < 10)
		{
			return $lRetVals;
		}

		$lContents = explode('"', $lResult);
		// Get the proper string containing postal code and place.
		$lFoundIndex = -1;
		for($i = 0; $i < sizeof($lContents); $i++)
		{
			if(stripos($lContents[$i], 'display_name') !== false)
			{
				$lFoundIndex = $i + 2;
				break;
			}
		}
		if($lFoundIndex === -1)
		{
			return $lRetVals;
		}
		// Put string in variable for safety.
		$lResult = $lContents[$lFoundIndex];
		$lContents = explode(',', $lResult);

		// Important for correctness.
		// OSM indices for place and postal code.
		if(sizeof($lContents) < 9)
		{
			$lIndexPlace = 2;
		}
		else
		{
			$lIndexPlace = 3;
		}
		$lIndexPostal = sizeof($lContents) - 2;

		$size = sizeof($lContents);
		// Set return values.
		$lRetVals['retPostal'] = $lContents[$lIndexPostal];
		$lRetVals['retPlace'] = $lContents[$lIndexPlace];

		// ALEX2: If housenumber was given, check if it was found.
		if(is_numeric($pHouseNumber))
		{
			if(is_numeric($lContents[0]))
			{
				$lRetVals['retHouseNumber'] = $lContents[0];
			}
			else if(is_numeric($lContents[1]))
			{
				$lRetVals['retHouseNumber'] = $lContents[1];
			}
		}

		return $lRetVals;
	}
	
	function checkPlace($pPlace)
	{
		return (($pPlace == "404") || ($pPlace == 404)) ? "" : $pPlace;
	}

	//Includes
	require "./includes/DEF.php";
	require './includes/UTILS.php';
	
	$browser = $BROWSER_NAME;

	include "./includes/Map.php";
	require "./includes/_top.php";

	// ALEX
	include "./includes/streets.php";
	// ALEX2
	// Darf hier nicht erneut eingebunden werden, da dies schon in "DEF.php" geschieht.
	//include "./includes/mail.php";

	//Profile sind nur für eingeloggte Nutzer sichtbar:
	if (!$_USER->loggedIn()) die ('Profile sind nur für eingeloggte Nutzer sichtbar!<p/><a href="./login">Zum Login</a>');

	$errorMessage = "";
	
	//====Profil löschen====
	if(isset($_POST['deletion_send_code']))
	{
		$deletionCode = DBFunctions::db_initpwNewKey($_USER->getID());
		// FOLGENDES BITTE NICHT EINRÜCKEN!
		$deletionMail = '
<style>
.logo
{
	margin-left: 10%;
	margin-right: 10%;
	background: #757575;
	text-align: center;
}
.logo img
{
	display: inline-block;
	width: 25%;
}
.content
{
	margin-left: 10%;
	margin-right: 10%;
}
span
{
	font-weight: bold;
	font-size: 42px;
}
</style>
<div class="logo"><img src="' . $HOST . '/img/wLogo.png" alt="TueGutes" title="TueGutes" /></div>
<div class="content">
	<h2>Meinen Account bei TueGutes löschen</h2>
	<h3>Schade, dass du deinen Account bei TueGutes löschen möchtest :(<h3>
	<br>
	Vielleicht möchtest du uns <a href="' . $HOST . '/contact" target="_blank">HIER</a> mitteilen, wieso du deinen Account löschen möchest.<br>
	<br>
	Wenn du deinen Account doch nicht löschen möchtest, kannst du diese E-Mail ignorieren.<br>
	<br>
	Wenn du deinen Account löschen möchtest, gib den nachfolgenden Code in deinem Profil ein:<br>
	<span>' . $deletionCode . '</span><br>
	(dieser Code ist für 10 Minuten gültig)
</div>';
		$_USER->sendEmail('TueGutes Account löschen', $deletionMail);
		$errorMessage = '<green>Dir wurde eine E-Mail mit einem Bestätigungscode geschickt.</green>';
	}
	else if(isset($_POST['deletion_code']))
	{
		$deletionError = !DBFunctions::db_getpwKey($_POST['deletion_code'], $_USER->getID());
		if($deletionError)
			$errorMessage = '<red>Der eingegebene Code ist entweder falsch oder ist bereits abgelaufen!</red>';
		else
		{
			DBFunctions::db_delete_user($_USER->getUsername());
			$_USER->redirect($HOST . '/logout');
		}
	}
	//====/Profil löschen====

	//Festlegen des auszulesenden Nutzers:
	if (!isset($_GET['user'])) $_GET['user'] = $_USER->getUsername();
	$thisuser = DBFunctions::db_get_user($_GET['user']);

	//Festlegen der Sichtbarkeitseinstellungen
	if (strtoupper($_USER->getUsername())===strtoupper($thisuser['username']) && !(@$_GET['view']==="public")) {
		$headline = 'Dein Profil';
		$link = '<a href="./profile?view=public">Wie sehen andere Nutzer mein Profil?</a><br><br><br>';
		$shEmail = ($thisuser['email']!="");
		$shRegDate = ($thisuser['regDate']!="");
		$shAvatar = ($thisuser['avatar']!="");
		$shHobbys = ($thisuser['hobbys']!="");
		$shFreitext = ($thisuser['description']!="");
		$shVorname = ($thisuser['firstname']!="");
		$shNachname = ($thisuser['lastname']!="");
		$shGeschlecht = ($thisuser['gender']!="" && $thisuser['gender']!="0");
		$shStrasse = ($thisuser['street']!="");
		$shHausnummer = ($thisuser['housenumber']!="");
		$shOrt = (checkPlace($thisuser['place'])!="");
		$shTelefon = ($thisuser['telefonnumber']!="");
		$shMessenger = ($thisuser['messengernumber']!="");
		// ALEX2: Hides day and month of birth if one value is equal 0 or empty.
		$shGeburtstag = ((substr($thisuser['birthday'],8,2) != 0) && (substr($thisuser['birthday'],8,2) != "")
			&& (substr($thisuser['birthday'],5,2) != 0) && (substr($thisuser['birthday'],5,2) != ""));
		// ALEX2: Hides year of birth in value is equal 0 or empty.
		$shJahrgang = ((substr($thisuser['birthday'],0,4) != "") && (substr($thisuser['birthday'],0,4) != 0));
	} else {
		$headline = "Profil von " . $thisuser['username'];
		$link = '';
		$shEmail = (substr($thisuser['privacykey'],0,1) === "1" && $thisuser['email']!="");
		$shRegDate = (substr($thisuser['privacykey'],1,1) === "1" && $thisuser['regDate']!="");
		$shAvatar = (substr($thisuser['privacykey'],2,1) === "1" && $thisuser['avatar']!="");
		$shHobbys = (substr($thisuser['privacykey'],3,1) === "1" && $thisuser['hobbys']!="");
		$shFreitext = (substr($thisuser['privacykey'],4,1) === "1" && $thisuser['description']!="");
		$shVorname = (substr($thisuser['privacykey'],5,1) === "1" && $thisuser['firstname']!="");
		$shNachname = (substr($thisuser['privacykey'],6,1) === "1" && $thisuser['lastname']!="");
		$shGeschlecht = (substr($thisuser['privacykey'],7,1) === "1" && $thisuser['gender']!="");
		$shStrasse = (substr($thisuser['privacykey'],8,1) === "1" && $thisuser['street']!="");
		$shHausnummer = (substr($thisuser['privacykey'],9,1) === "1" && $thisuser['housenumber']!="");
		$shOrt = (substr($thisuser['privacykey'],10,1) === "1" && (checkPlace($thisuser['place']) != ""));
		$shTelefon = (substr($thisuser['privacykey'],11,1) === "1" && $thisuser['telefonnumber']!="");
		$shMessenger = (substr($thisuser['privacykey'],12,1) === "1" && $thisuser['messengernumber']!="");
		$shGeburtstag = (substr($thisuser['privacykey'],13,1) === "1"
			&& ((substr($thisuser['birthday'],8,2) != 0) && (substr($thisuser['birthday'],8,2) != "")
			&& (substr($thisuser['birthday'],5,2) != 0) && (substr($thisuser['birthday'],5,2) != "")));
		$shJahrgang = (substr($thisuser['privacykey'],14,1) === "1"
			&& ((substr($thisuser['birthday'],0,4) != "") && (substr($thisuser['birthday'],0,4) != 0)));
	}
		// ALEX
		$gender = isset($_POST['txtGender']) ? $_POST['txtGender'] : $thisuser['gender'];
		$birthday = isset($_POST['txtGeburtstag']) ? $_POST['txtGeburtstag'] : $thisuser['birthday'];
		$hobbys =  isset($_POST['txtHobbys']) ? $_POST['txtHobbys'] : $thisuser['hobbys'];
		$description =  isset($_POST['txtBeschreibung']) ? $_POST['txtBeschreibung'] : $thisuser['description'];
		$houseNumber = isset($_POST['txtHausnummer']) ? $_POST['txtHausnummer'] : $thisuser['housenumber'];
		$street = isset($_POST['txtStrasse']) ? $_POST['txtStrasse'] : $thisuser['street'];
		$telephonenumber = isset($_POST['txtTelNr']) ? $_POST['txtTelNr'] : $thisuser['telefonnumber'];
		$messengernumber = isset($_POST['txtMsgNr']) ? $_POST['txtMsgNr'] : $thisuser['messengernumber'];
		$place = isset($_POST['txtOrt']) ? $_POST['txtOrt'] : $thisuser['place'];		
	
	// Fehlerüberprüfung, wenn das Formular zum speichern gesendet wurde.
	if(isset($_POST['action']) && ($_POST['action'] == 'save'))
	{					
		$errorMessage = "";
		
		if($birthday != "")
		{
			$birthday_dh = (new DateHandler())->set($birthday);
			if(!$birthday_dh)
			{
				$errorMessage .= "Bitte ein gültiges Geburtsdatum angeben oder das Feld leerlassen.<br>";
			}
			else
			{
				$thisuser['birthday'] = $birthday_dh->get();
			}
		}			
				
		// Check street value for digits. (Could be solved better probably.)
		if($street != "")
		{
			for($i = 0; $i < strlen($street); $i++)
			{
				if(is_numeric($street[$i]))
				{
					$errorMessage .= "Die Hausnummer bitte im separaten Textfeld angeben.<br>";
					break;
				}
			}
		}
		
		// Important: Has to be the last check to avoid multiple DB inserts.
		if(($street != "") && ($houseNumber != "") && ($place != ""))
		{
			if($errorMessage == "")
			{
				// Check for valid address.
				$lFoundValues = getPostalPlaceToAddress($street, $houseNumber, $place);
				
				if($lFoundValues['retPostal'] == "")
				{
					$errorMessage .= "Diese Adresse wurde nicht gefunden. Hat sich vielleicht ein Schreibfehler eingeschlichen?<br>";
				}
				else
				{
					$postalcode = $lFoundValues['retPostal'];
					
					// If postal code wasn't found in DB, add it and set foreign key in userdata.
					$lIdPostal = DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode, $place);
					
					// If no corresponding postal code was found, add it to DB.
					if($lIdPostal == "")
					{
						DBFunctions::db_insertPostalCode($postalcode, $place);
					}
					
					if($lFoundValues['retHouseNumber'] == "")
					{ 
						// Send mail to administrators if house number was not found.						
						$admins = DBFunctions::db_getAllAdministrators();
						for ($i = 0; $i < sizeof($admins); $i++) 
						{
							sendEmail($admins[$i], "TueGutes - Hausnummer", "Die Hausnummer '" . $houseNumber . "' der Straße '" 
								. $street . "' im Ort '" . $place . "' wurde nicht gefunden. Bitte prüfen.");							
						}		
					}
				}
			}
		}	
		else if(($street == "") && ($houseNumber == "") && ($place == ""))
		{
			$postalcode = "";
		}
		else 
		{
			$errorMessage .= 'Bitte Strasse, Hausnummer und Ort zusammen angeben oder alle Felder leerlassen.<br>';
		}
	}
	
	//Prüfen, ob das Eingabefeld angefordert wurde oder ob ein ungueltiger Eintrag gesetzt wurde.
	if((isset($_POST['action']) && ($_POST['action'] == 'edit')) || ($errorMessage != "")) {
		//Zeige die Seite mit Eingabefeldern zum Bearbeiten der Daten an

		echo '<red>' . $errorMessage . '</red><br><br>';
		
		$form_head = '<form action="" method="post" enctype="multipart/form-data">';

		//Block 0: Avatar
		$blAvatar = 'Hier kannst du ein neues Profilbild hochladen: <br><br>';
		$blAvatar .= '<input type="file" name="neuerAvatar" accept="image/*"><br><br>';
		$blAvatar .= '<input id="delAvatarCheckbox" type="checkbox" name="delAvatar" value="1"><label for="delAvatarCheckbox"> Meinen Avatar löschen</label><br>';
		$blAvatar .= "<br><br><hr><br>";		

		$blPersoenlich = "<br>";
		$blPersoenlich .= '<h3>Persönliche Daten</h3><table>';

		//Namen anzeigen:
		$blPersoenlich .= '<tr><td>Realer Name:</td><td>';
		$blPersoenlich .= $thisuser['firstname'] . ' ';
		$blPersoenlich .= $thisuser['lastname'];
		$blPersoenlich .= '</td></tr>';

		//Geschlecht bearbeiten:
		$blPersoenlich .= '<tr><td colspan="1">Geschlecht:</td><td><select name="txtGender" size=1><option value="0"' . (($gender!='w' && $gender!='m' && $gender!='a')?' select':'') . '>keine Angabe</option><option value="w"' . (($gender==='w')?' selected':'') . '>weiblich</option><option value="m"' . (($gender==='m')?' selected':'') . '>männlich</option><option value="a"' . (($gender==='a')?' selected':'') . '>anderes</option></select></td></tr>';

		// Henrik
		$dh = (new DateHandler())->set($birthday);
		$currentBirthday = $dh ? $dh->get('d.m.Y') : '';
		$blPersoenlich .= "<tr><td>Geboren:</td><td>";
		if($browser !== 'Firefox' && $browser !== 'Internet Explorer')
			$currentBirthday = $dh ? $dh->get('Y-m-d') : '';
		$blPersoenlich .= "<input type='date' name='txtGeburtstag' value='" . $currentBirthday . "' size='10' placeholder='DD.MM.YYYY' /></td></tr>";
		
		
		// ALEX2: Leaves text boxes empty if value is 0. Added $tempValue.
		/*$tempValue = getDayOfBirth($thisuser['birthday']);
		$tempValue = ($tempValue == 0) ? "" : $tempValue;

		// Text box for day of birth.
		$blPersoenlich .= '<tr><td>Geboren:</td><td><input type="text" size="2px"
		name="txtDayOfBirth" placeholder="TT" value="' . $tempValue . '">';

		// Text box for month of birth.
		$tempValue = getMonthOfBirth($thisuser['birthday']);
		$tempValue = ($tempValue == 0) ? "" : $tempValue;
		$blPersoenlich .= '.<input type="text" size="2px" name="txtMonthOfBirth" placeholder="MM" value="' . $tempValue . '">';

		// Text box for year of birth.
		$tempValue = getYearOfBirth($thisuser['birthday']);
		$tempValue = ($tempValue == 0) ? "" : $tempValue;
		$blPersoenlich .= '.<input type="text" size="4px" name="txtYearOfBirth" placeholder="JJJJ" value="' . $tempValue . '"></td></tr>';*/

		//RegDate anzeigen
		$blPersoenlich .= '<tr><td><br>Tut Gutes seit:</td><td><br> ' . substr($thisuser['regDate'],8,2) . '.' . substr($thisuser['regDate'],5,2) . '.' . substr($thisuser['regDate'],0,4) . '</td></tr>';
		$blPersoenlich .= "</table><br><br><hr><br>";

		//Block 2: Über USER
		$blUeber = "";
		$blUeber .= '<h3>Über mich</h3><table>';

		//Hobbys anzeigen:
		$blUeber .= '<tr><td>Hobbys:</td><td><input type="text" name="txtHobbys" placeholder="z.B. Kochen, Fahrrad fahren, ..." value="' . $hobbys . '"></td></tr>';

		//Freitext anzeigen:
		$blUeber .= '<tr><td>Über mich:</td><td><textarea rows=20 cols=80 placeholder="beschreibe dich selbst mit wenigen Worten..." name="txtBeschreibung">' . $description . '</textarea></td></tr>';
		$blUeber .= "</table><br><br><hr><br>";

		//Block 3: Gute Taten
		$blTaten = '';

		//Block 4: Adresse
		$blAdresse = "";
		$blAdresse .= '<h3>Wo findet man dich?</h3><table>';
		$showMap = false;

		//Adresse eingeben:
		$blAdresse .= '<tr><td>Adresse:</td><td>';

		// ALEX: Strassenliste einfügen.
		$blAdresse .= '<input type="search" list="lstStreets" name="txtStrasse" onchange="updatePLZPlace();" placeholder="Strasse" value="' . $street . '">';
		if ($browser != "Safari") $blAdresse .= $streetList;
		$blAdresse .= '</td>';

		// ALEX: Hausnummer einfügen.
		$blAdresse .= '<td><input type="text" size="5%" name="txtHausnummer" placeholder="Nr." value="' . $houseNumber . '"></td></tr>';

		// ALEX: Auskommentiert.
		//PLZ/Ort bearbeiten:
		//TODO: Postleitzahl überprüfen
		/*$blAdresse .= '<tr><td>PLZ:</td><td><input type="text" name="txtPostleitzahl" placeholder="PLZ" value="' . (($thisuser['postalcode']!=0)?$thisuser['postalcode']:'') . '"></td></tr>';
		$blAdresse .= "</table>";*/

//		$blAdresse .= '<tr><td>PLZ:</td><td><input type="text" name="txtPostleitzahl" placeholder="PLZ" value="' . (($thisuser['postalcode']!=0)?$thisuser['postalcode']:'') . '"></td>';
		// Ort bearbeiten.
		$blAdresse .= '<td></td><td><input type="text" name="txtOrt" placeholder="Ort" value="' . checkPlace($place) . '"></td></tr>';

		$blAdresse .= '<tr><td></td><td colspan=2><br><input type="submit" class="anchor" value="Meine Adresse fehlt..." form="suggestAddress"/></td></tr>';
		$blAdresse .= "</table><br><br><hr><br>";

		//Block 5: Kontakt
		$blKontakt = '<h3>Kontakt</h3><table>';

		//Email anzeigen:
		$blKontakt .= '<tr><td>Email:</td><td><a href="mailto:' . $thisuser['email'] . '">' . $thisuser['email'] . '</a></td></tr>';

		//Telefonnummer bearbeiten:
		$blKontakt .= '<tr><td>Telefon:</td><td><input type="text" name="txtTelNr" placeholder="z.B. 051112345678" value="' . $telephonenumber . '"></td></tr>';

		//Messengernummer bearbeiten:
		$blKontakt .= '<tr><td>Messenger:</td><td><input type="text" name="txtMsgNr" value="' . $messengernumber . '"></td></tr>';

		$blKontakt .= "</table><br><br><hr><br>";

		//Block 6: Sichtbarkeit
		$blPrivacy = '<h3>Sichtbarkeitseinstellungen</h3>Welche Einstellungen sollen Besuchern deines Profils angezeigt werden?<br><br><br><table>';
		
		$blPrivacy .= '<tr><th colspan="3"><b>Persönliches</b></th></tr>';
		$blPrivacy .= '<tr>';
		$blPrivacy .= '<td><input id="c6" type="checkbox" name="vsFirstname" value="1" '.(substr($thisuser['privacykey'],5,1)?'checked="checked"':'').' ><label for="c6"> Meinen Vornamen</label></td>';
		$blPrivacy .= '<td><input id="c7" type="checkbox" name="vsLastname" value="1" '.(substr($thisuser['privacykey'],6,1)?'checked="checked"':'').' ><label for="c7"> Meinen Nachnamen</label></td>';
		$blPrivacy .= '<td><input id="c8" type="checkbox" name="vsGender" value="1" '.(substr($thisuser['privacykey'],7,1)?'checked="checked"':'').' ><label for="c8"> Mein Geschlecht</label></td>';
		$blPrivacy .= '</tr><tr>';
		$blPrivacy .= '<td><input id="c14" type="checkbox" name="vsBirthday" value="1" '.(substr($thisuser['privacykey'],13,1)?'checked="checked"':'').' ><label for="c14"> Meinen Geburtstag</label></td>';
		$blPrivacy .= '<td><input id="c15" type="checkbox" name="vsBirthyear" value="1" '.(substr($thisuser['privacykey'],14,1)?'checked="checked"':'').' ><label for="c15"> Mein Geburtsjahr</label></td>';
		$blPrivacy .= '<td><input id="c1" type="checkbox" name="vsMail" value="1" '.(substr($thisuser['privacykey'],0,1)?'checked="checked"':'').' ><label for="c1"> Meine E-Mail Adresse</label></td>';
		$blPrivacy .= '</tr>';

		$blPrivacy .= '<tr><th colspan="3"><b>Adresse</b></th></tr>';
		$blPrivacy .= '<tr>';
		$blPrivacy .= '<td><input id="c9" type="checkbox" name="vsStreet" value="1" '.(substr($thisuser['privacykey'],8,1)?'checked="checked"':'').' ><label for="c9"> Meine Straße</label></td>';
		$blPrivacy .= '<td><input id="c10" type="checkbox" name="vsHousenumber" value="1" '.(substr($thisuser['privacykey'],9,1)?'checked="checked"':'').' ><label for="c10"> Meine Hausnummer</label></td>';
		$blPrivacy .= '<td><input id="c11" type="checkbox" name="vsPlzOrt" value="1" '.(substr($thisuser['privacykey'],10,1)?'checked="checked"':'').' ><label for="c11"> Meinen Wohnort</label></td>';
		$blPrivacy .= '</tr>';

		$blPrivacy .= '<tr><th colspan="3"><b>Über Dich</b></th></tr>';
		$blPrivacy .= '<tr>';
		$blPrivacy .= '<td><input id="c3" type="checkbox" name="vsAvatar" value="1" '.(substr($thisuser['privacykey'],2,1)?'checked="checked"':'').' ><label for="c3"> Mein Profilbild</label></td>';
		$blPrivacy .= '<td><input id="c4" type="checkbox" name="vsHobbys" value="1" '.(substr($thisuser['privacykey'],3,1)?'checked="checked"':'').' ><label for="c4"> Meine Hobbys</label></td>';
		$blPrivacy .= '<td><input id="c5" type="checkbox" name="vsDescription" value="1" '.(substr($thisuser['privacykey'],4,1)?'checked="checked"':'').' ><label for="c5"> Meine Beschreibung von mir selbst</label></td>';
		$blPrivacy .= '</tr>';

		$blPrivacy .= '<tr><th colspan="3"><b>Über Dich</b></th></tr>';
		$blPrivacy .= '<tr>';		
		$blPrivacy .= '<td><input id="c2" type="checkbox" name="vsRegDate" value="1" '.(substr($thisuser['privacykey'],1,1)?'checked="checked"':'').' ><label for="c2"> Datum meiner Registrierung</label></td>';
		$blPrivacy .= '<td><input id="c12" type="checkbox" name="vsTelNr" value="1" '.(substr($thisuser['privacykey'],11,1)?'checked="checked"':'').' ><label for="c12"> Meine Telefonnummer</label></td>';
		$blPrivacy .= '<td><input id="c13" type="checkbox" name="vsMsgNr" value="1" '.(substr($thisuser['privacykey'],12,1)?'checked="checked"':'').' ><label for="c13"> Meine Messengernummer</label></td>';
		$blPrivacy .= '</tr>';

		$blPrivacy .= "</table><br><br><hr><br>";

		$form_bottom = '<p /><p /><input type=submit value="Änderungen speichern"><input type="hidden" name="action" value="save"><br><br><br><br><br></form>';

		$form_bottom .= '<h3>Profil unwiderruflich löschen</h3>
		<p class="deleteMessage">Hier kannst du dein Nutzerprofil bei TueGutes löschen. Beachte aber, dass wir ein einmal gelöschtes Nutzerprofil nicht mehr wiederherstellen können. Du verlierst damit alle deine Einstellungen, Profilinhalte, Karma und gute Taten. Wenn du dir sicher bist, dass du dein Profil löschen willst, schicken wir dir einen  fünfstelligen Code per E-Mail, den du hier eingeben musst. Klicke anschließend auf "PROFIL ENTGÜLTIG LÖSCHEN".</p>
		<form action="" method="post">
			<input type="hidden" name="deletion_send_code" />
			<input type="submit" value="CODE senden" /><br>
			(Der Code ist 10 Minuten gültig)
		</form><br>
		<br>
		<form action="" method="post">
			<input type="text" size="10" name="deletion_code" placeholder="CODE" required /><br>
			<br>
			<input type="submit" value="Profil entgültig löschen" />
		</form>';

	} else {
		//Zeige das Profil ohne Änderungsmöglichkeit an

		//Ggf. Speichern / Verwerfen der geänderten Werte
		if(isset($_POST['action']) && ($_POST['action'] == 'save'))
		{
			// Nutzerdaten überschreiben:

			// ALEX
			$lHandler = new DateHandler();
			$lHandler->set($birthday);
			$thisuser['birthday'] = $lHandler->get();
			
			//Daten überschreiben
			$thisuser['street'] = $street;
			$thisuser['housenumber'] = $houseNumber;
			$thisuser['gender'] = $gender;
			$thisuser['messengernumber'] = $messengernumber;
			$thisuser['telefonnumber'] = $telephonenumber;
			$thisuser['hobbys'] = $hobbys;
			$thisuser['description'] = $description;
			$thisuser['postalcode'] = $postalcode;
			$thisuser['place'] = $place;			

			//Speichern des Profilbildes
			if ($_FILES['neuerAvatar']['name'] != '') {
				$uploadDir = './img/profiles/'.$thisuser['idUser'].'/';
				if (!is_dir('./img/profiles/')) {
					mkdir('./img/profiles/');
					chmod('./img/profiles/', 0775);
				}
				if (!is_dir($uploadDir)) {
					mkdir($uploadDir);
					chmod($uploadDir, 0775);
				}
				imagepng(imagecreatefromstring(file_get_contents($_FILES['neuerAvatar']['tmp_name'])), $uploadDir . 'converted.png');
				$size = getimagesize($uploadDir . 'converted.png');

				//Anlegen der Dateien
				$uploaded = imagecreatefrompng($uploadDir . 'converted.png');
				$avatar_512 = imagecreatetruecolor(512,512);
				$avatar_256 = imagecreatetruecolor(256,256);
				$avatar_128 = imagecreatetruecolor(128,128);
				$avatar_64 = imagecreatetruecolor(64,64);
				$avatar_32 = imagecreatetruecolor(32,32);

				//Resizing
				imagecopyresized($avatar_512, $uploaded, 0, 0, 0, 0, 512, 512 , $size[0], $size[1]);
				imagecopyresized($avatar_256, $uploaded, 0, 0, 0, 0, 256, 256 , $size[0], $size[1]);
				imagecopyresized($avatar_128, $uploaded, 0, 0, 0, 0, 128, 128 , $size[0], $size[1]);
				imagecopyresized($avatar_64, $uploaded, 0, 0, 0, 0, 64, 64 , $size[0], $size[1]);
				imagecopyresized($avatar_32, $uploaded, 0, 0, 0, 0, 32, 32 , $size[0], $size[1]);

				imagepng($avatar_512, $uploadDir . '512x512.png');
				imagepng($avatar_256, $uploadDir . '256x256.png');
				imagepng($avatar_128, $uploadDir . '128x128.png');
				imagepng($avatar_64, $uploadDir . '64x64.png');
				imagepng($avatar_32, $uploadDir . '32x32.png');

				//chmod('./img/profiles/', 0775);
				chmod($uploadDir.'512x512.png', 0775);
				chmod($uploadDir.'256x256.png', 0775);
				chmod($uploadDir.'128x128.png', 0775);
				chmod($uploadDir.'64x64.png', 0775);
				chmod($uploadDir.'32x32.png', 0775);

				unlink($uploadDir . 'converted.png');

				$thisuser['avatar'] = $uploadDir.'512x512.png';

			} else if (@$_POST['delAvatar']=="1") {
				//Avatar durch Standard ersetzen falls das Bild gelöscht UND kein neues hochgeladen wurde
				$thisuser['avatar'] = $isset($_POST['vsGender'])?($thisuser['gender']=='m'?'./img/profiles/standard_male.png':$thisuser['gender']=='w'?'./img/profiles/standard_female.png':'./img/profiles/standard_other.png'):'./img/profiles/standard_other.png';
			}

			//Überschreiben der Sichtbarkeitseinstellungen
			$thisuser['privacykey'] = isset($_POST['vsMail'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsRegDate'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsAvatar'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsHobbys'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsDescription'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsFirstname'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsLastname'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsGender'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsStreet'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsHousenumber'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsPlzOrt'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsTelNr'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsMsgNr'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsBirthday'])?'1':'0';
			$thisuser['privacykey'] .= isset($_POST['vsBirthyear'])?'1':'0';

			//Änderungen speichern
			
			DBFunctions::db_update_user($thisuser);
			$_USER->set('privacykey', $thisuser['privacykey']);
			$_USER->set('gender', $thisuser['gender']);
			header("Refresh:0");
		}

		//Anzeige des eigentlichen Nutzerprofils

		$form_head = '<form action="" method="post">';

		//Block 0: Avatar
		$blAvatar = '';
		if ($shAvatar) $blAvatar .= '<div align=center><img id="avatar" src="' . $thisuser['avatar'] . '" width="150px"></div>';

		//Block 1: Persönliches
		$blPersoenlich = "";
		if ($shVorname || $shNachname || $shGeburtstag || $shJahrgang || $shRegDate) {

			$blPersoenlich .= '<h3>Persönliche Daten</h3><table>';

			//Namen anzeigen:
			if ($shVorname || $shNachname) {
				$blPersoenlich .= '<tr><td>Realer Name:</td><td>';
				if ($shVorname) $blPersoenlich .= $thisuser['firstname'] . ' ';
				if ($shNachname) $blPersoenlich .= $thisuser['lastname'];
				$blPersoenlich .= "</td></tr>";
			}

			//Geschlecht anzeigen:
			if ($shGeschlecht) $blPersoenlich .= '<tr><td>Geschlecht:</td><td>' . (($thisuser['gender']==='w')?'weiblich':(($thisuser['gender']==='m')?'männlich':'anderes')) . '</td></tr>';

			//Geburtstag anzeigen:
			if ($shGeburtstag || $shJahrgang) {
				$blPersoenlich .= '<tr><td>Geboren:</td><td>';
				if ($shGeburtstag) $blPersoenlich .= substr($thisuser['birthday'],8,2) . '. ' . substr($thisuser['birthday'],5,2) . '. ';
				if ($shJahrgang) $blPersoenlich .= substr($thisuser['birthday'],0,4);
				$blPersoenlich .= "</td></tr>";
			}

			//RegDate anzeigen
			if ($shRegDate) $blPersoenlich .= '<tr><td>Tut Gutes seit: </td><td>' . substr($thisuser['regDate'],8,2) . '. ' . substr($thisuser['regDate'],5,2) . '. ' . substr($thisuser['regDate'],0,4) . '</td></tr>';

			$blPersoenlich .= "</table><br><br><hr><br>";
		}

		//Block 2: Über User
		$blUeber = "";
		if ($shHobbys || $shFreitext) {
			$blUeber .= '<h3>Über ' . $thisuser['username'] . '</h3><table>';

			//Hobbys anzeigen:
			if ($shHobbys) $blUeber .= '<tr><td>Hobbys:</td><td>' . $thisuser['hobbys'] . '</td></tr>';

			//Freitext anzeigen:
			if ($shFreitext) $blUeber .= '<tr><td>Über mich:</td><td>' . $thisuser['description'] . '</td></tr>';

			$blUeber .= "</table><br><br><hr><br>";
		}

		//Block 3: Taten
		$blTaten = '<h3>Taten von ' . $thisuser['username'] . '</h3><table>';
		$blTaten .= 'Karma: ' . $thisuser['points'] . ' (' . $thisuser['trustleveldescription'] . ')<br>';


		//Die letzten Taten des Nutzers
		$arr = DBFunctions::db_getGuteTatenForUser(0,3,'alle',$thisuser['idUser']);
		if (sizeof($arr)==0) {
			$blTaten .= '<br>' . $thisuser['username'] . ' hat noch keine guten Taten...' . ((($thisuser['username']==$_USER->getUsername()) && !(@$_GET['view']!='public'))?'<br><a href="./deeds">Jetzt gute Taten finden</a>':'');
		} else {
			$maxZeichenFürDieKurzbeschreibung = 150;
			$blTaten .= '<br><br><h5>Aktuelle Taten:</h5>';
			for($i = 0; $i < sizeof($arr); $i++){
					$blTaten .= '<br>';
					$blTaten .=  "<a href='./deeds_details?id=" . $arr[$i]->idGuteTat . "' class='deedAnchor'><div class='deed" . ($arr[$i]->status == "geschlossen" ? " closed" : "") . "'>";
					$blTaten .=  "<div class='name'><h4>" . $arr[$i]->name . "</h4></div><div class='category'>" . $arr[$i]->category . "</div>";
					$blTaten .=  "<br><br><br><br><div class='description'>" . (strlen($arr[$i]->description) > $maxZeichenFürDieKurzbeschreibung ? substr($arr[$i]->description, 0, $maxZeichenFürDieKurzbeschreibung) . "...<br>mehr" : $arr[$i]->description) . "</div>";
					$blTaten .=  "<div class='address'>" . $arr[$i]->street .  "  " . $arr[$i]->housenumber . "<br>" . $arr[$i]->postalcode . ' / ' . $arr[$i]->place . "</div>";
					$blTaten .=  "<div>" . (is_numeric($arr[$i]->countHelper) ? "Anzahl der Helfer: " . $arr[$i]->countHelper : '') ."</div><div class='trustLevel'>Minimaler Vertrauenslevel: " . $arr[$i]->idTrust . " (" . $arr[$i]->trustleveldescription . ")</div>";
					$blTaten .=  "<div>" . $arr[$i]->organization . "</div>";
					$blTaten .=  "</div></a>";
					$blTaten .=  "<br>";
				}
			$blTaten .= ((($thisuser['username']==$_USER->getUsername()) && !(@$_GET['view']!='public'))?'<br><a href="./deeds?user=' . $thisuser['username'] . '">Alle deine guten Taten</a>':'<br><a href="./deeds?user=' . $thisuser['username'] . '">Alle guten Taten des Nutzers</a>');
		}
		$blTaten .= "<br><br><hr><br>";
		
		//Block 4: Adresse
		$blAdresse = "";
		if ($shStrasse || $shHausnummer || $shOrt) {

			$blAdresse .= '<h3>Wo findet man ' . $thisuser['username'] . '?</h3><table>';
			//Adresse anzeigen:
			$blAdresse .= '<tr><td>Adresse:</td><td>';
			$blAdresse .= $thisuser['street'];
			if ($shHausnummer)
			{
				$blAdresse .= ' ' . $thisuser['housenumber'];
			}
			$blAdresse .= "</td></tr>";
		
			//Ort anzeigen:
			if ($shOrt) $blAdresse .= '<tr><td>Ort:</td><td>' . checkPlace($thisuser['place']) . '</td></tr>';

			$blAdresse .= "</table><br><br><hr><br>";
		}
		$showMap = ($shOrt && $shStrasse && $shHausnummer);

		//Block 5: Kontakt
		$blKontakt = "";
		if ($shEmail || $shTelefon || $shMessenger) {

			$blKontakt .= '<h3>Kontakt</h3><table>';

			//Email anzeigen:
			if ($shEmail) $blKontakt .= '<tr><td>Email:</td><td><a href="mailto:' . $thisuser['email'] . '">' . $thisuser['email'] . '</a></td></tr>';

			//Telefonnummer anzeigen:
			if ($shTelefon) $blKontakt .= '<tr><td>Telefon:</td><td>' . $thisuser['telefonnumber'] . '</td></tr>';

			//Messengernummer anzeigen:
			if ($shMessenger) $blKontakt .= '<tr><td>Messenger:</td><td>' . $thisuser['messengernumber'] . '</td></tr>';

			$blKontakt .= "</table>";

		}

		$blPrivacy = '';

		$form_bottom = (((strtoupper($_USER->getUsername())===strtoupper($thisuser['username'])) && (!isset($_GET['view']) || @$_GET['view'] != "public") )?'<input type="submit" value="Profil bearbeiten"><input type="hidden" name="action" value="edit"></form>':'</form>');
	}

?>

<style>
	#mapid {
		height:350px;
		width:350px;
	}
</style>

<!--Überschrift-->
<h2 id="profileheader"><?php echo $headline; ?></h2>

<?php if($_USER->hasGroup($_GROUP_MODERATOR)) echo '<a href="./admin?page=user&user=' . $thisuser['username'] . '">Diesen Nutzer bearbeiten</a>'; ?>
<!--Ggf. ausgeben des Links zur öffentlichen Ansicht-->

<div class="profile">

<?php if (!(@$_POST['action'] == 'edit')) echo $link; ?>

<!--Beginn des Formulars zum Ändern der Profileinstellungen:-->
<?php echo $form_head; ?>

<!--Ausgabe der einzelnen Blöcke-->
<?php

	echo $blAvatar;
	echo '<div align="center">' . $blPersoenlich . '</div>';
	echo '<div align="center">' . $blUeber . '</div>';
	echo '<div align="center">' . $blTaten . '</div>';
	echo '<div align="center">' . $blAdresse . '</div>';
	echo '<div align="center">' . $blKontakt . '</div>';

	if ($showMap) {
		$map = new Map();
		$map->createSpace('30%','50%','40%');
		$map->createMap($thisuser['postalcode'] . ',' . $thisuser['street'] . ',' . $thisuser['housenumber']);
		echo '<br><br><hr><br><br>';
	}

	echo '<div align="center">' . $blPrivacy . '</div>';
	
	echo $form_bottom;

?>
</div>

<?php require "./includes/_bottom.php"; ?>


<!--Formular zum Vorschlagen einer neuen Adresse-->
<form id="suggestAddress" action="./contact" method="post">
	<input type='hidden' name='suggestCategory' value='1' />
	<input type='hidden' name='message' value='Ich vermisse folgende Adresse: ' />
</form>
