<?php
/*
*@Autor Christian Hock, Klaus Sobotta, Nick Nolting (refactored Henrik Huckauf)
enthält Teile von deeds_create und deeds_bearbeiten
*/

require './includes/DEF.php';
require './includes/UTILS.php';
require './includes/db_connector.php';
require './includes/_top.php';
$pictures = isset($POST['pictures']) ? $_POST['pictures'] : '';
$name = isset($_GET['name']) ? $_GET['name'] : '';
$description = isset($_GET['description']) ? $_GET['description'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$street = isset($_GET['street']) ? $_GET['street'] : '';
$housenumber = isset($_GET['housenumber']) ? $_GET['housenumber'] : '';
$postalcode = isset($_GET['postalcode']) ? $_GET['postalcode'] : '';
$place = isset($_GET['place']) ? $_GET['place'] : '';
$starttime = isset($_GET['starttime']) ? $_GET['starttime'] : '';
$endtime = isset($_GET['endtime']) ? $_GET['endtime'] : '';
$organization = isset($_GET['organization']) ? $_GET['organization'] : '';
$countHelper = isset($_GET['countHelper']) ? $_GET['countHelper'] : '1';
$idTrust = isset($_GET['tat_verantwortungslevel']) ? $_GET['tat_verantwortungslevel'] : '';

//Zeigt an auf welcher Unterseite man gerade ist.
isset($_GET['Seite']) ? $_GET['Seite'] : '';
if(isset($_GET['Seite'])){
	$Seite = $_GET['Seite'];
	}else{
$Seite =1;
	}
//Damit nur eine Seite aufgerufen wird
$stop='0';
//zurückbuttonvariable
if(isset($_GET['button'])){
$button=$_GET['button'];}


//Name setzen
if($Seite==1 || $Seite==2 ||($Seite=='3' && $button=='zurück')){
//Guckt ob der Aufruf für Seite 2 erfolgreich war
if($Seite==2) {	
	if(DBFunctions::db_doesGuteTatNameExists($name)){
		$Seite=1;
		$stop=1;
	}else if ($name === ''){
		$stop=2;
		$Seite=1;
	}
}
if($Seite==1 ||$Seite==3){

echo'
<h2>Wähle zuerst einen aussagekräftigen Namen für deine Tat. :)</h2>
<h3>Jede Tat hat einen eigenen Namen und kann auch durch diesen gesucht werden. Deine Tat wird dem entsprechend öfter aufgerufen, wenn dein Name intuitiv verständlich ist.</h3>;
<br><br>';
//Fehlermeldung für nicht erfolgreichen Aufruf.
if($stop=='1')echo '<h3><red>Eine andere Tat ist bereits unter diesem Namen veröffentlicht.</red></h3>';
if($stop=='2')echo '<h3><red>Bitte einen neuen Namen eingeben.</red></h3><br>';
//le buttons
echo'
<br><br>
	<form action="" method="GET">
		<br>
		<br>
		<input type="text" name="name" value="';echo $name ;echo'" placeholder="Name der Tat" required />
		<br>
		<br>
		<input type="hidden" name="Seite" value="';echo 2 ; echo'"required />
		<input type="submit" name="button" value="weiter" />
</form>	';
}
}
//Bild setzen
if($Seite==2 || ($Seite==4 && $button=='zurück')){
$Seite==2;
echo'
<h2>Möchtest du ein Bild hochladen?</h2>
<h3>Taten mit Bildern werden eher von anderen Nutzern angeklickt.</h3>
		

<br><br>
<div class="center block deeds_create">
		
		<br>
		<br>
		<form action="" method="POST" enctype="multipart/form-data">	
		<input type="file" name="pictures" accept="image/*">
		<input type="submit" name="button" value="hochladen" />
		<br>
		<br>';
		if(isset($_FILES['pictures'])){
		$pictures='data: ' . mime_content_type($_FILES['pictures']['tmp_name']) . ';base64,' . base64_encode (file_get_contents($_FILES['pictures']['tmp_name'])); 
		echo '<h3><green>Das Bild ist nun hochgeladen</h2></green>
				<center><img src="';echo $pictures;echo'" ></center>
				<br>
				<br>
				<br>';
		}
		
		echo'
		</form>
		<form action="" method="GET" enctype="multipart/form-data">	
		<input type="hidden" name="Seite" value="3"required />		
		<input type="hidden" name="name" value="';echo $name ;echo'"required />
		<input type="hidden" name="pictures" value="';echo $pictures ;echo'"required />
		<input type="submit" name="button" value="zurück" />
		<input type="submit" name="button" value="weiter" />
</form>	';
}
//Beschreibung setzen

if((($Seite==3 || $Seite==4)&& $button=='weiter' )||(($Seite==5)&& $button=='zurück')){	
if($Seite==4){
	$data=$_GET['description'];
	if ($data === ''){
		$stop=1;
		$Seite=3;
		
}
}
if($Seite==3 || $Seite==5){
	if($stop==1)echo '<h3><red>Bitte eine neue Beschreibung eingeben.</red></h3><br>';
echo'
<h2>Beschreibe deine Tat.</h2>
<h3>Was und wie ist es zu tun? </h3>
<br><br>
<div class="center block deeds_create">
		<form action="" method="GET" enctype="multipart/form-data">
		<br>
		<br>
		<textarea id="text" name="description" rows="10" placeholder="Beschreiben Sie die auszuführende Tat. Werben Sie für Ihr Angebot. Nutzen sie ggf. eine Rechtschreibüberprüfung." required>';echo $description ;echo'</textarea>
		<br><br>
		<br>

		
		<input type="hidden" name="Seite" value="4"required />
		<input type="hidden" name="name" value="';echo $name ;echo'"required />
		<input type="hidden" name="pictures" value="';echo $pictures ;echo'"required />
		<input type="submit" name="button" value="zurück" />
		<input type="submit" name="button" value="weiter" />
</form>	';
} 
}
if(($Seite==4 ||$Seite==5)&& $button=='weiter' ){

	if($Seite==5){
	if(!isset($_GET['street'])){
		$stop=1;
	}else if(!isset($_GET['housenumber'])){
		$stop=2;
	}else if(!isset($_GET['postalcode'])|| !is_numeric($_GET['postalcode'])){
		$stop=3;
	}else if(!isset($_GET['place'])){
		$stop=4;
	}else if(!isset($_GET['organization'])){
		$stop=5;
	}else if(!isset($_GET['starttime'] )){
		//|| !DateHandler::isValid($_GET['starttime']) braucht es mit dem Kalender nicht mehr 
		$stop=6;
	}else if(!isset($_GET['endtime'] )){
		//|| !DateHandler::isValid($_GET['endtime'])
		$stop=7;
	}else if ((DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode,$place)==false)){
		$stop=8;
	}
	}	
if($stop!=0)$Seite=4;
if($Seite==4){
echo'
<h2>Rahmendaten</h2>
<h3>Hier noch einmal alle notwendigen Informationen für Bewerber.</h3>';
	if($stop==1)echo '<h3><red>Bitte eine neue Straße eingeben.</red></h3><br>';
	if($stop==2)echo '<h3><red>Bitte eine neue Hausnummer eingeben.</red></h3><br>';	
	if($stop==3)echo '<h3><red>Die Postleitzahl bitte als Zahl eingeben.</red></h3><br>';	
	if($stop==4)echo '<h3><red>Bitte einen Ort angeben.</red></h3><br>';
	if($stop==5)echo '<h3><red>Bitte eine neue Organisation eingeben.</red></h3><br>';
	if($stop==6)echo '<h3><red>Das Format von der Startzeit ist falsch.</red></h3><br>';
	if($stop==7)echo '<h3><red>Das Format von der Endzeit ist falsch.</red></h3><br>';
	if($stop==8)echo '<h3><red>Die Postleitzahl passt nicht zum Ort.</red></h3><br>';	
	
	echo '<br><br>
<div class="center block deeds_create">
<form action="" method="GET" enctype="multipart/form-data">
		<br>
		<br>
		<br>
		<input type="text" name="street" value="';echo $street ;echo'" placeholder="Straßenname" required />
		<input type="text" name="housenumber" value="'; echo $housenumber ;echo '" placeholder="Hausnummer" required />
		<br>
		<input type="text" name="postalcode" value="'; echo $postalcode ;echo '" placeholder="Postleitzahl" required />
		<br>
		<input type="text" name="place" value="'; echo $place ; echo'" placeholder="Stadtteil" required />
		<br>
		<input type="text" name="starttime" value="'; echo $starttime; echo'" placeholder="Startzeitpunkt (dd.mm.yyyy HH:MM)" required />
		<br>
		<input type="text" name="endtime" value="'; echo $endtime; echo'" placeholder="Endzeitpunkt (dd.mm.yyyy HH:MM)" required />
		<br>
		<input type="text" name="organization" value="'; echo $organization; echo'" placeholder="Organisation" />
		<br>
		Benötigte Helfer:<br>
		<input type="number" name="countHelper" value="'; echo $countHelper;echo'" placeholder="Benötigte Helfer" required />
		<br>
		Erforderlicher Verantwortungslevel:<br>
		<select name="tat_verantwortungslevel">
			<option value="1"<?php echo $idTrust == 1?" selected":""; >1</option>
			<option value="2"<?php echo $idTrust == 2?" selected":""; >2</option>
			<option value="3"<?php echo $idTrust == 3?" selected":""; >3</option>
		</select>
		<br><br>
		<br><br>
		<br>
		<input type="hidden" name="name" value="';echo $name ;echo'"required />
		<input type="hidden" name="pictures" value="';echo $pictures ;echo'"required />
		<input type="hidden" name="description" value="';echo $description ;echo'"required />
		<input type="hidden" name="Seite" value="5"required />
		<input type="submit" name="button" value="zurück" />
		<input type="submit" name="button" value="weiter" />
</form>	';
}
}
if($Seite==5&&($button!='zurück')){
	$error = false;
	//Name der guten Tat
	if(empty($name) || DBFunctions::db_doesGuteTatNameExists($name))
	{
		$error = true;
	}

	//Falls eine fehlerhafte PLZ angegeben wird
	if(!is_numeric($postalcode))
	{
		$error = true;
	}

	//TODO Enddatum darf nicht vor dem Startdatum liegen
	
	//Startzeitpunkt
	$start_dh = (new DateHandler())->set($starttime);
	if(!$start_dh)
	{
		$error = true;
	}
	//Endzeitpunkt
	$end_dh = (new DateHandler())->set($endtime);
	if(!$end_dh)
	{
		$error = true;
	}

	if(!DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode, $place))
	{
		$error = true;
	}

	//Anzahl Helfer keine Zahl
	if(!is_numeric($countHelper))
	{
		$error = true;
	}

	if(!$error)
	{
		
		//Einfügen der Guten Tat
		$uid = DBFunctions::db_idOfBenutzername($_USER->getUsername());
		$plz = DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode, $place);

		// TIMM HIER: Temporäre Fix damit amn gute Taten erstellen kann.
		$category = 'keine Angabe';
		//Bugfix zu ende
		DBFunctions::db_createGuteTat($name, $uid, $category, $street, $housenumber, 
									  $plz, $start_dh->get(),$end_dh->get(), $organization, $countHelper,
									  $idTrust, $description, $pictures);
		
		//Versenden der Info-Mails
		
		//Bestimmen der Empfänger
		$mods = DBFunctions::db_getAllModerators();
		$admins = DBFunctions::db_getAllAdministrators();

		//Festlegen des Mail-Inhalts
		$mailSubject = 'Gute Tat ' . "'" . $_GET['name'] . "'" . ' wurde erstellt!';
		$mailContent1 = '<div style="margin-left:10%;margin-right:10%;background-color:#757575"><img src="img/wLogo.png" alt="TueGutes" title="TueGutes" style="width:25%"/></div><h2>Hallo!';
		$mailContent2 = '</h2><br>' . $_USER->getUsername() . ' hat gerade eine neue gute Tat erstellt. <br>Um die gute Tat zu bestätigen oder abzulehnen, klicke auf den folgenden Link: <br><a href="' . $HOST . '/deeds_details?id=' . DBFunctions::db_getIDOfGuteTatByName($_GET['name']) . '">Zur guten Tat</a>';

		//Versenden der Emails an Moderatoren
		for ($i = 0; $i < sizeof($mods); $i++) {
			sendEmail($mods[$i], $mailSubject, $mailContent1 . $mailContent2);
		}
		//Versenden der Emails an Administratoren
		for ($i = 0; $i < sizeof($admins); $i++) {
			sendEmail($admins[$i], $mailSubject, $mailContent1 . $mailContent2);
		}
	}
echo'
<h2><green>Deine Tat wurde erstellt! </green></h2>
<h3>und wird nun von uns geprüft. </h3>
<a href="./deeds.php"><input type="button" name="Toll" value="Toll!"/></a>';
}
require './includes/_bottom.php';
?>



<?php
/*
Die alte Datei zum erstellen von guten Taten
*@author Christian Hock, Klaus Sobotta, Nick Nolting (refactored Henrik Huckauf)
*Verlinkung zu Orten fehlt
*Kategorie soll editierbar sein
*
*DateHandler eingebunden | Henrik Huckauf
*/
/*
require './includes/DEF.php';
require './includes/UTILS.php';

require './includes/db_connector.php';

require './includes/_top.php';


$name = isset($_POST['name']) ? $_POST['name'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$category = isset($_POST['category']) ? $_POST['category'] : '';
$street = isset($_POST['street']) ? $_POST['street'] : '';
$housenumber = isset($_POST['housenumber']) ? $_POST['housenumber'] : '';
$postalcode = isset($_POST['postalcode']) ? $_POST['postalcode'] : '';
$place = isset($_POST['place']) ? $_POST['place'] : '';
$starttime = isset($_POST['starttime']) ? $_POST['starttime'] : '';
$endtime = isset($_POST['endtime']) ? $_POST['endtime'] : '';
$organization = isset($_POST['organization']) ? $_POST['organization'] : '';
$countHelper = isset($_POST['countHelper']) ? $_POST['countHelper'] : '1';
$idTrust = isset($_POST['tat_verantwortungslevel']) ? $_POST['tat_verantwortungslevel'] : '';

$output = '';
if(isset($_POST['set']) && $_POST['set'] == '1')
{
	//TIMM: Übergangsweise wegen DB funktion
	$pictures= '';

	/*
	0-> gerade erst erstellt 
	1-> bewilligt
	2-> warte auf Antwort
	3-> abgelehnt
	*/
	//$status = 0; 
/*
	$error = false;

	//Name der guten Tat
	if(empty($name))
	{
		$output .= '<red>Es wurde kein Name für die gute Tat vergeben.</red>';
		$error = true;
	}

	//Falls eine fehlerhafte PLZ angegeben wird
	if(!is_numeric($postalcode))
	{
		$output .= '<red>Bitte Postleitzahl überprüfen! Als Postleitzahl sind nur fünfstellige Zahlen erlaubt.</red>';
		$error = true;
	}

	//TODO Enddatum darf nicht vor dem Startdatum liegen
	
	//Startzeitpunkt
	$start_dh = (new DateHandler())->set($starttime);
	if(!$start_dh)
	{
		$output .= '<red>Es wurde kein gültiger Startzeitpunkt für die gute Tat festgelegt.</li>';
		$error = true;
	}
	//Endzeitpunkt
	$end_dh = (new DateHandler())->set($endtime);
	if(!$end_dh)
	{
		$output .= '<red>Es wurde kein gültiger Endzeitpunkt für die gute Tat festgelegt.</red>';
		$error = true;
	}

	if(!DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode, $place))
	{
		$output .= '<red>Derzeit sind leider nur Adressen in Hannover möglich. Deine Adresse wird nicht angenommen?<br><a href="./contact">Kontaktiere uns hier</a></red>';
		$error = true;
	}

	//Anzahl Helfer keine Zahl
	if(!is_numeric($countHelper))
	{
		$output .= '<red>Bitte Anzahl der Helfer überprüfen! Als Anzahl muss eine einfache Zahl eingegeben werden.</red>';
		$error = true;
	}

	if(!$error)
	{
		//Einfügen der Guten Tat
		$uid = DBFunctions::db_idOfBenutzername($_USER->getUsername());
		$plz = DBFunctions::db_getIdPostalbyPostalcodePlace($postalcode, $place);
		DBFunctions::db_createGuteTat($name, $uid, $category, $street, $housenumber, 
									  $plz, $start_dh->get(),$end_dh->get(), $organization, $countHelper,
									  $idTrust, $description, $pictures);

		//Versenden der Info-Mails
		
		//Bestimmen der Empfänger
		$mods = DBFunctions::db_getAllModerators();
		$admins = DBFunctions::db_getAllAdministrators();

		//Festlegen des Mail-Inhalts
		$mailSubject = 'Gute Tat ' . "'" . $_POST['name'] . "'" . ' wurde erstellt!';
		$mailContent1 = '<div style="margin-left:10%;margin-right:10%;background-color:#757575"><img src="img/wLogo.png" alt="TueGutes" title="TueGutes" style="width:25%"/></div><h2>Hallo!';
		$mailContent2 = '</h2><br>' . $_USER->getUsername() . ' hat gerade eine neue gute Tat erstellt. <br>Um die gute Tat zu bestätigen oder abzulehnen, klicke auf den folgenden Link: <br><a href="' . $HOST . '/deeds_details?id=' . DBFunctions::db_getIDOfGuteTatByName($_POST['name']) . '">Zur guten Tat</a>';

		//Versenden der Emails an Moderatoren
		for ($i = 0; $i < sizeof($mods); $i++) {
			sendEmail($mods[$i], $mailSubject, $mailContent1 . $mailContent2);
		}
		//Versenden der Emails an Administratoren
		for ($i = 0; $i < sizeof($admins); $i++) {
			sendEmail($admins[$i], $mailSubject, $mailContent1 . $mailContent2);
		}


		$output = '<green>Ihre Tat wurde erfolgreich erstellt und wird nun von uns geprüft.</green>';
		
		$name = '';
		$description = '';
		$category = '';
		$street = '';
		$housenumber = '';
		$postalcode = '';
		$place = '';
		$starttime = '';
		$endtime = '';
		$organization = '';
		$countHelper = '1';
		$idTrust = '';
	}
}
?>	

<h2>Eine Tat erstellen</h2>

<div id='output'><?php echo $output; ?></div>
<br><br>
<div class="center block deeds_create">
	<form action="" method="post">
		<h3>Bitte alle Felder ausfüllen =)</h3>
		<br>
		<br>
		<input type="text" name="name" value="<?php echo $name; ?>" placeholder="Name der Tat" required />
		<br>
		<textarea id="text" name="description" rows="10" placeholder="Beschreiben Sie die auszuführende Tat. Werben Sie für Ihr Angebot. Nutzen sie ggf. eine Rechtschreibüberprüfung." required><?php echo $description; ?></textarea>
		<br><br>
		Kategorie:<br>
		<select name="category">
			<option value="Andere">Andere</option>
			<option value="Altenheim">Altenheim</option>
			<option value="Busbahnhof">Busbahnhof</option>
			<option value="Mülleinsammeln">Müll einsammeln</option>
			<?php
			//TODO get set from db 
			//loop set echo options reselect prev option if $category is set
			?>
		</select>
		<br>
		<input type="submit" value="neue Kategorie vorschlagen" form="suggestCategory" />
		<br>
		
		<!--TODO kategorie vorschlagen-->
		<br>
		<input type="text" name="street" value="<?php echo $street; ?>" placeholder="Straßenname" required />
		<input type="text" name="housenumber" value="<?php echo $housenumber; ?>" placeholder="Hausnummer" required />
		<br>
		<input type="text" name="postalcode" value="<?php echo $postalcode; ?>" placeholder="Postleitzahl" required />
		<br>
		<input type="text" name="place" value="<?php echo $place; ?>" placeholder="Stadtteil" required />
		<br>
		<input type="text" name="starttime" value="<?php echo $starttime; ?>" placeholder="Startzeitpunkt (dd.mm.yyyy HH:MM)" required />
		<br>
		<input type="text" name="endtime" value="<?php echo $endtime; ?>" placeholder="Endzeitpunkt (dd.mm.yyyy HH:MM)" required />
		<br>
		<input type="text" name="organization" value="<?php echo $organization; ?>" placeholder="Organisation" />
		<br>
		Benötigte Helfer:<br>
		<input type="number" name="countHelper" value="<?php echo $countHelper; ?>" placeholder="Benötigte Helfer" required />
		<br>
		Erforderlicher Verantwortungslevel:<br>
		<select name="tat_verantwortungslevel">
			<option value="1"<?php echo $idTrust == 1?' selected':''; ?>>1</option>
			<option value="2"<?php echo $idTrust == 2?' selected':''; ?>>2</option>
			<option value="3"<?php echo $idTrust == 3?' selected':''; ?>>3</option>
		</select>
		<br><br>
		<input type='hidden' name='set' value='1' />
		<input type="submit" name="button" value="Tat erstellen" />
		<br><br>
		<a href="./deeds"><input type="button" name="button" value="Zurück" /></a>
	</form>
	
	<form id="suggestCategory" action="./contact" method="post">
		<input type='hidden' name='suggestCategory' value='1' />
		<input type='hidden' name='message' value='Ich vermisse folgende Kategorie beim Erstellen einer guten Tat: ' />
	</form>
</div>

<?php
require './includes/_bottom.php';
*/?>