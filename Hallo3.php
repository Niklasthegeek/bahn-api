<!DOCTYPE html>
<html>
<head>
	<title>Bahnhofsfahrplan</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<h1>Bahnhofsfahrplan</h1>

	<form method="post">
		<label for="bahnhof">Bahnhof:</label>
		<input type="text" id="bahnhof" name="bahnhof" required>

		<label for="datum">Datum:</label>
		<input type="date" id="datum" name="datum" required>

		<label for="stunde">Stunde:</label>
		<input type="hour" id="stunde" name="stunde" required>

		<fieldset>
			<legend>Anzeigeoptionen:</legend>
			<label>
				<input type="radio" name="anzeige" value="an" checked>
				Ankunft
			</label>
			<label>
				<input type="radio" name="anzeige" value="ab">
				Abfahrt
			</label>
		</fieldset>

		<button type="submit">Anzeigen</button>
	</form>

	<div id="fahrplan"></div>
	<div id="fahrstuhl"></div>

	<script src="script.js"></script>
</body>
</html>

<?php
// Bahnhof und Datum aus dem Formular auslesen
$bahnhof = isset($_POST['bahnhof']) ? $_POST['bahnhof'] : null;
$datum = isset($_POST['datum']) ? $_POST['datum'] : null;

// Anzeigemodus aus dem Formular auslesen (an = Ankunft, ab = Abfahrt)
$anzeige = isset($_POST['anzeige']) ? $_POST['anzeige'] : 'an';

$ch = curl_init();
//$bahnhof_id = $_GET['Bahn'];
$header = array(
	'Accept: application/json',
	'DB-Client-Id: 00bea519d9600016a3d4a78e11b4db2b',
	'DB-Api-Key: 2000e58bb000e0a55fe3f0b8b2a752ba'
	);
	curl_setopt($ch, CURLOPT_URL, "https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

	//execute!
	$response = curl_exec($ch);
	$stations = json_decode($response, true);
				
	//$bahnhof["result"][0]["mailingAddress"]["city"];
	//$stations["result"][0]["hasSteplessAccess"];
	//$stations["result"][0]["federalState"];

/*if (!empty($bahnhof) && !empty($datum)) 
	// API-URL für die Abfrage von Bahnhofsdaten
	$url = "https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations/";

	// HTTP-Header für die API-Abfrage
	$header = [
		"Accept: application/json",
		'DB-Client-Id: 00bea519d9600016a3d4a78e11b4db2b',
        'DB-Api-Key: 2000e58bb000e0a55fe3f0b8b2a752ba}',
	];

	// API-Abfrage durchführen und JSON-Daten in ein Array konvertieren
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);

	$stations = json_decode($response, true);
	$stations = ["result"][0]["mailingAddress"]["city"];*/

	if (!empty($stations['result'])) 
		$station_id = $stations['result'][0]['number'];

		// API-URL für die Abfrage von Fahrplandaten
		$url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/" . $evaNo . "/" . $datum . "/" . $hour;

		// Anzeigeoptionen festlegen (Ankunft oder Abfahrt)
		$departures = ($anzeige == 'ab');

		// API-Abfrage durchführen und
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$timetable = json_decode($response, true);

		if (!empty($timetable['result'])) {
			// Ergebnisliste der Abfahrts- oder Ankunftszeiten ausgeben
			$times = $timetable['result'][0]['timetable'][$anzeige . '_s'];
	
			if (!empty($times)) {
				echo '<h2>' . ucfirst($anzeige) . 'fahrtszeiten am ' . date('d.m.Y', strtotime($datum)) . ' für ' . $stations['result'][0]['name'] . '</h2>';
				echo '<ul>';
	
				foreach ($times as $time) {
					echo '<li>' . date('H:i', strtotime($time)) . '</li>';
				}
	
					echo '</ul>';
			} else {
				echo '<p>Es wurden keine ' . ($departures ? 'Ab' : 'An') . 'fahrtszeiten gefunden.</p>';
			}
		} else {
			echo '<p>Es konnte kein Fahrplan für den angegebenen Bahnhof gefunden werden.</p>';
		}

	?>

		<table cellspacing="3" cellpadding="8" frame="box" rules="group">
            <thead><tr><th>Stadt</th><th>Stufenloserein -und Ausstieg</th><th>Bundesland</th></tr></thead>
                <tr>
                    <th><?php echo $bahnhof?></th>
                    <th><?php echo $datum?></th>
                    <th><?php echo $anzeige?></th>
                </tr>
                <tr>
                    <td>

                    </td>
                </tr>
        </table>



	</body>
	</html>
