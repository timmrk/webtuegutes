<?php
/*
*@author Klaus Sobotta, Lukas Buttke
*/

require './includes/DEF.php';

include './includes/ACCESS.php';

include './includes/db_connector.php';

require './includes/_top.php';

if(!isset($_GET['page']) /* || db_getGuteTatenAnzahl() >=  */) $_GET['page'] = 1;
?>

<h2><?php echo $wlang['deeds_head']; ?> </h2>

<div>
<form action="guteTatErstellenHTML.php" method="post">
<input type="submit" value="Gute Tat erstellen" target="_self">
<br><hr>
</div>
<br> 
	
		<?php
			$placeholder = 'alle';
			/*$intZahl=0;
			$tatAusgeben=new tatAusgeben($intZahl);
			$tatAusgeben->toStringTat();
			*/
			//$allDeedsCount = db_connector blabla
			//$neededPages = $allDeedsCount/10;
			
			$tatenProSeite=10; //anzahl der tataen die pro seite angezeigt werden
			$arr = db_getGuteTatenForList($tatenProSeite*($_GET['page']-1), $tatenProSeite, $placeholder);

			$maxZeichenFürDieKurzbeschreibung = 150;

			for($i = 0; $i < sizeof($arr); $i++){
				echo "<a href='./deeds_details?id=" . $arr[$i]->idGuteTat . "' class='deedAnchor'><div class='deed" . ($arr[$i]->status == "geschlossen" ? " closed" : "") . "'>";
					echo "<div class='name'><h4>" . $arr[$i]->name . "</h4></div><div class='category'>" . $arr[$i]->category . "</div>";
					echo "<br><br><br><br><div class='description'>" . (strlen($arr[$i]->description) > $maxZeichenFürDieKurzbeschreibung ? substr($arr[$i]->description, 0, $maxZeichenFürDieKurzbeschreibung) . "...<br>mehr" : $arr[$i]->description) . "</div>";
					echo "<div class='address'>" . $arr[$i]->street .  "  " . $arr[$i]->housenumber . "<br>" . $arr[$i]->postalcode . ' / ' . $arr[$i]->place . "</div>";
					echo "<div>" . (is_numeric($arr[$i]->countHelper) ? "Anzahl der Helfer: " . $arr[$i]->countHelper : '') ."</div><div class='trustLevel'>Minimaler Vertrauenslevel: " . $arr[$i]->idTrust . " (" . $arr[$i]->trustleveldescription . ")</div>";
					echo "<div>" . $arr[$i]->organization . "</div>";
				echo "</div></a>";
				echo "<br><br><hr><br>";
			}
			
		?>
	
		<?php

		$anzahlAllerTaten=db_getGuteTatenAnzahl();
		$aktuelleSeite=$_GET['page'];
		$letzteseite=intval(db_getGuteTatenAnzahl() / $tatenProSeite);
		if ($letzteseite * $tatenProSeite < db_getGuteTatenAnzahl()) $letzteseite++;

		if($anzahlAllerTaten%$tatenProSeite==0){
			$seitenanzahl=$anzahlAllerTaten/$tatenProSeite;// berechnet die benötigte seiten anzahl
		}else{
			$seitenanzahl=$anzahlAllerTaten/$tatenProSeite+1; // berechnet die benötigte seiten anzahl
		}
		$maxSeitenLinks=7 ; // die menge der gleichzeitig angezeigten seiten zB bei 7 -> 3 4 5 6 7 8 9 wir befinden uns auf seite 6
					
					function zahlenausgabe($von,$bis, $letzteseite){//schreibt die seiten zahlen
						for($i=$von;$i<=$bis;$i++){
							//der link get auf eine falsche seite er muss diese seite nochmal aufgerufen werden nur mit der pasenden seiten nummer
							if ($i>0 && $i<=$letzteseite) echo '<a href="./deeds?page=' . $i . '">&nbsp'. $i .'&nbsp</a>';
						}
					}
					
					if($seitenanzahl>$maxSeitenLinks){
						if($aktuelleSeite<($maxSeitenLinks/2)+1){
							zahlenausgabe(1,$aktuelleSeite-1, $letzteseite);
							echo '&nbsp' . $aktuelleSeite . '&nbsp';
							zahlenausgabe($aktuelleSeite+1,$maxSeitenLinks, $letzteseite);
							if ($letzteseite > $maxSeitenLinks) printf ('<a href="./deeds?page=' . $letzteseite . '"> ... ' . $letzteseite . '</a>');
						} else if ($aktuelleSeite > $letzteseite- ($maxSeitenLinks/2)-1) {
							if ($letzteseite > $maxSeitenLinks) printf ("<a href='./deeds'>". '1 ... ' ."</a>");// der link geht jetzt
							zahlenausgabe($letzteseite-$maxSeitenLinks,$aktuelleSeite-1, $letzteseite);
							echo '&nbsp' . $aktuelleSeite . '&nbsp';
							zahlenausgabe($aktuelleSeite+1,$letzteseite, $letzteseite);
						}else{
							printf ("<a href='./deeds'>". '1 ... ' ."</a>");// der link geht jetzt
							zahlenausgabe($aktuelleSeite-intval($maxSeitenLinks/2),$aktuelleSeite-1,$letzteseite);
							echo '&nbsp' . $aktuelleSeite . '&nbsp';
							zahlenausgabe($aktuelleSeite+1,$aktuelleSeite+intval($maxSeitenLinks/2),$letzteseite);
							printf ('<a href="./deeds?page=' . $letzteseite . '"> ... ' . $letzteseite . '</a>');
						}
					}else{
						zahlenausgabe(1,$seitenanzahl, $letzteseite);
					}
		?>

<br><br><hr>
</form>

<!--Zurück / Weiter Buttons-->
<?php
	$placeholder = 'alle';
	$vor = $_GET['page'] > 1;
	$nach = db_getGuteTatenForList($tatenProSeite*($_GET['page']), $tatenProSeite, $placeholder); //Sobald Deeds nicht mehr doppelt ausgegeben werden -> Mit Anzahl prüfen!
	if ($vor) echo '<a href="./deeds?page=' . ($_GET['page']-1) . '"><input type="button" value="Zurück"></a>';
	if ($vor && $nach) echo '&nbsp';
	if ($nach) echo '<a href="./deeds?page=' . ($_GET['page']+1) . '"><input type="button" value="Weiter"></a>';
?>

<br> 


<?php
require './includes/_bottom.php';
?>
