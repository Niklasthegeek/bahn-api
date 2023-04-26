<?php

//Funktion curlbahn gibt mit den Übergabewerten $url und $accept_header aus was die Bahn API bereitstellt
/**
 * Summary of curlbahn
 * @param mixed $url
 * @param mixed $accept_header
 * @return bool|string
 */
function curlbahn($url, $accept_header) { 
    $filename = "secrets.txt";
    $file = fopen($filename, "r");
    $apiKey = explode('=', trim(fgets($file)))[1];
    $clientID = explode('=', trim(fgets($file)))[1];
    fclose($file);
    #echo "API-Key: " . $apiKey;    
    #echo "ClientID: " . $clientID;    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "DB-Api-Key: " . $apiKey,
            "DB-Client-Id: " . $clientID,
            "accept: ". $accept_header
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    return $response;
}
// Funktion getStationDetails gibt gewünschte Werte aus der station Data API der DB aus
/**
 * Summary of getStationDetails
 * @param mixed $evaNo
 * @param mixed $json
 * @return mixed
 */
function getStationDetails($evaNo, $json) {
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations?eva=" . $evaNo;
    $data = json_decode(curlbahn($url, "application/json"), true);
    $stationDetail = array_reduce(explode('.', $json), function($arr, $key) {
        return $arr[$key] ?? null;
    }, $data);
    return $stationDetail;
}
#$url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/8010224/230424/15";
#echo curlbahn($url,"application/xml");
if (isset($_GET['evaNo']) && isset($_GET['date']) && isset($_GET['hour']) && isset($_GET['mode'])) {
    //Lese Werte aus dem Form
    $evaNo = filter_input(INPUT_GET, 'evaNo', FILTER_VALIDATE_INT);
    $date = filter_input(INPUT_GET, 'date', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{4}-\d{2}-\d{2}$/")));
    $hour = filter_input(INPUT_GET, 'hour', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{2}:\d{2}$/")));
    $mode = isset($_GET['mode']) && ($_GET['mode'] === 'ar' || $_GET['mode'] === 'dp') ? $_GET['mode'] : 'ar';
}

//Funktion gibt zurück ob auf einem Gleis der Aufzug geht oder nicht
/**
 * Summary of getGleisInfos
 * @param mixed $evaNo
 * @param mixed $gleis
 * @return string
 */
#function getGleisInfos($evaNo, $gleis) {
#    $stationNumber = getStationDetails($evaNo, 'result.0.number');
#    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/facilities?stationnumber=" . $stationNumber;
#  // Extrahiere Gleisnummer aus der Variable $gleis
#  preg_match('/(\d+)/', $gleis, $matches);
#  $gleisNummer = !empty($matches) ? $matches[0] : null;
#
#  // Durchlaufe das Array von Elevator-Daten und suche nach dem passenden Gleis
#  $data = json_decode(curlbahn($url, "application/json"), true);
#  foreach ($data as $elevator) {
#     # // Extrahiere Gleisnummer aus der Beschreibung
#     # preg_match('/Gleis\s*(\d+)/', $elevator['description'], $matches);
#     # $elevatorGleisNummer = !empty($matches) ? $matches[1] : null;
#     # // Wenn die Gleisnummern übereinstimmen und der Status "ACTIVE" ist, gib TRUE zurück
#     # if ($elevatorGleisNummer == $gleisNummer && $elevator['state'] == "ACTIVE") {
#     #     return "  " . $gleisNummer . "Elevator is active";
#     # }
#     #preg_match('/zu Gleis\s*(\d+(?:\/\d+)?)/', $elevator['description'], $matches);
#     #$elevatorGleisNummer = isset($matches[1]) ? str_replace('/', '-', $matches[1]) : null;
#     preg_match('/zu Gleis\s*(\d+(?:\/\d+)?)/', $elevator['description'], $matches);
#     $elevatorGleisNummer = str_replace('/', '-', $matches[1]);
#     $elevatorGleisNummer = (int)explode('-', $elevatorGleisNummer)[0];
#     if (($elevatorGleisNummer == $gleisNummer || strpos($elevator['description'], "Gleis " . $gleisNummer . "/") !== false || strpos($elevator['description'], "Gleis " . $gleisNummer . " ") !== false) && $elevator['state'] == "ACTIVE") {
#        return "Gleis " . $gleisNummer . " elevator is active";
#     }
#    
#
#  }
#
#  // Wenn keine passenden Elevator-Daten gefunden wurden, gib FALSE zurück
#  return "  " . $gleisNummer . " Elevator not active";
#}
#function getGleisInfos($evaNo, $gleis) {
#    $stationNumber = getStationDetails($evaNo, 'result.0.number');
#    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/facilities?stationnumber=" . $stationNumber;
#
#    // Extrahiere Gleisnummer aus der Variable $gleis
#    preg_match('/(\d+)/', $gleis, $matches);
#    $gleisNummer = !empty($matches) ? $matches[0] : null;
#
#    // Durchlaufe das Array von Elevator-Daten und suche nach dem passenden Gleis
#    $data = json_decode(curlbahn($url, "application/json"), true);
#    foreach ($data as $elevator) {
#        // Extrahiere Gleisnummern aus der Beschreibung
#        preg_match_all('/Gleis\s*(\d+(?:\/\d+)?)/', $elevator['description'], $matches);
#        $elevatorGleisNummern = !empty($matches[1]) ? $matches[1] : array();
#        
#        // Wenn die gesuchte Gleisnummer in den Elevator-Gleisnummern enthalten ist und der Status "ACTIVE" ist, gib TRUE zurück
#        if (in_array($gleisNummer, $elevatorGleisNummern) && $elevator['state'] == "ACTIVE") {
#            return "Gleis " . $gleisNummer . " elevator is active + " . $elevator['description'];
#        }
#    }
#
#    // Wenn keine passenden Elevator-Daten gefunden wurden, gib FALSE zurück
#    return "Gleis " . $gleisNummer . " elevator not active + " . $elevator['description'];
#}
#function getGleisInfos($evaNo, $gleis) {
#    $stationNumber = getStationDetails($evaNo, 'result.0.number');
#    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/facilities?stationnumber=" . $stationNumber;#

#    // Extrahiere Gleisnummer aus der Variable $gleis
#    preg_match('/(\d+)/', $gleis, $matches);
#    $gleisNummer = !empty($matches) ? $matches[0] : null;#

#    // Durchlaufe das Array von Elevator-Daten und suche nach dem passenden Gleis
#    $data = json_decode(curlbahn($url, "application/json"), true);
#    $elevatorGleisNummern = array();
#    foreach ($data as $elevator) {
#        preg_match('/zu Gleis\s*(\d+(?:\/\d+)?)/', $elevator['description'], $matches);
#        if (!empty($matches[1])) {
#            $elevatorGleisNummern[] = str_replace('/', '-', $matches[1]);
#        }
#        if (in_array($gleisNummer, $elevatorGleisNummern) && $elevator['state'] == "ACTIVE") {
#            return "Gleis " . $gleisNummer . " elevator is active";
#        }
#    }#

#    // Wenn keine passenden Elevator-Daten gefunden wurden, gib FALSE zurück
#    return "Gleis " . $gleisNummer . " elevator not active";
#}

#function getGleisInfos($evaNo, $gleis) {
#    $stationNumber = getStationDetails($evaNo, 'result.0.number');
#    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/facilities?stationnumber=" . $stationNumber;
#    // Extrahiere Gleisnummer aus der Variable $gleis
#    preg_match('/(\d+)/', $gleis, $matches);
#    $gleisNummer = !empty($matches) ? $matches[0] : null; 
#    $elevators = json_decode(curlbahn($url, "application/json"), true);
#
#    foreach ($elevators as $elevator) {
#        $matches = array();
#        if (preg_match('/Gleis\s+(\d+)/', $elevator['description'], $matches)) {
#            if ($matches[1] == $gleisNummer) {
#                if ($elevator['state'] == 'ACTIVE') {
#                    return 'Gleis ' . $gleisNummer . ' elevator is active';
#                } else {
#                    return 'Gleis ' . $gleisNummer . ' elevator not active';
#                }
#            }
#        }
#    }
#
#    return 'Elevator not found for Gleis ' . $gleisNummer;
#}
function getGleisInfos($evaNo, $gleis) {
    $stationNumber = getStationDetails($evaNo, 'result.0.number');
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/facilities?stationnumber=" . $stationNumber;
    // Extrahiere Gleisnummer aus der Variable $gleis
    preg_match('/(\d+)/', $gleis, $matches);
    $gleisNummer = !empty($matches) ? $matches[0] : null; 
    
    
    
    #$gleisNummern = array();
  
    // Decodieren des JSON Strings in ein Array von Objekten
    $objArray = json_decode(curlbahn($url, "application/json"));
  
    // Durchlaufen der Objekte
    foreach ($objArray as $obj) {
        // Prüfen, ob die Beschreibung die Gleisnummer enthält
        if (strpos($obj->description, "Gleis " . $gleisNummer) !== false) {
            // Extrahieren der Gleisnummern aus der Beschreibung
            preg_match_all('!\d+!', $obj->description, $matches);
            $gleisNummern[] = $matches[0][0];
            // Wenn die Beschreibung zwei Gleisnummern enthält, die zweite Gleisnummer extrahieren
            if (is_array($matches[0]) && count($matches[0]) > 1) {
                $gleisNummern[] = $matches[0][1];
            } else {
                $gleisNummern[] = "";
            }
        } elseif (strpos($obj->description, "Gleis " . $gleisNummer) !== false) {
        
        }
    }
  
    return $gleisNummern;
  }


/**
 * Summary of getTimeTable
 * @param mixed $evaNo
 * @param mixed $date
 * @param mixed $hour
 * @param mixed $mode
 * @return array
 */
function getTimeTable($evaNo, $date, $hour, $mode){
$url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/" . $evaNo . "/" . $date . "/" . $hour;
// XML-String in ein SimpleXMLElement-Objekt umwandeln
$xml = new SimpleXMLElement(curlbahn($url,"application/xml"));
// Erstelle ein leeres Array, um die Daten zu speichern
$data = [];
// Fülle das Array mit den Daten aus der Schleife
foreach ($xml->s as $element) { // Schleife, die jedes Element in $xml->s durchläuft
    if (isset($element->$mode)) { // Überprüft, ob das aktuelle Element ein Attribut namens $mode hat
        $abfahrtszeit = $element->$mode->attributes()->pt; // Liest den Wert des Attributs "pt" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $abfahrtszeit zu
        $bhf = explode("|", $element->$mode->attributes()->ppth); // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements, splittet ihn anhand des Trennzeichens "|" und weist ihn der Variablen $ziel zu
        $bhf_0 = $bhf[0]; // Nimmt das erste Element des Arrays $ziel und weist es wieder der Variable $bhf_0 zu
        $bhf_end = end($bhf); // Nimmt das letzte Element des Arrays $ziel und weist es wieder der Variable $bhf_end zu
        $hour = substr($abfahrtszeit, 6, 2); // Extrahiert aus dem Wert von $abfahrtszeit die Stundenzahl und weist sie der Variablen $hour zu
        $minute = substr($abfahrtszeit, 8, 2); // Extrahiert aus dem Wert von $abfahrtszeit die Minutenzahl und weist sie der Variablen $minute zu
        $time = $hour . ":" . $minute; // Setzt die Werte von $hour und $minute zu einem Zeitstempel zusammen und weist ihn der Variablen $time zu
        $gleis = $element->$mode->attributes()->pp; //Liest den Wert des Attributs "pp"(gleis) aus

        $elevator = getGleisInfos($evaNo, $gleis)[0];
        $elevator2 = getGleisInfos($evaNo, $gleis)[1];

        $category = $element->tl->attributes()->c; // Liest den Wert des Attributs "c" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $category zu
        $linie = $element->tl->attributes()->n; // Liest den Wert des Attributs "n" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $linie zu
        $ff = $element->tl->attributes()->f; // Liest den Wert des Attributs "f" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $ff zu
        $pfad = $element->$mode->attributes()->ppth; // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $pfad zu
        $pfad = implode(" -> ",$bhf); // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $pfad zu
        if (!empty($element->$mode->attributes()->l)) { // Prüft, ob das Attribut $mode des aktuellen Elements ein Attribut "l" hat und ob der Wert nicht leer ist
            $zugn = (!empty($element->ar->attributes()->ppth) and !empty($element->$mode->attributes()->l)) ? $element->ar->attributes()->l : $element->dp->attributes()->l; // Weist der Variablen $zugn den Wert des Attributs "l" des Attributs "ar" des aktuellen Elements zu, wenn vorhanden, andernfalls wird der Wert des Attributs "l" des Attributs "dp" des aktuellen Elements zugewiesen
        } else {
            $zugn = $element->tl->attributes()->n; // Weist der Variablen $zugn den Wert des Attributs "n" des Attributs "tl" des aktuellen Elements zu
        }
        $data[] = [
            'time' => $time,
            'gleis' => $gleis,
            'bhf_0' => $bhf_0,
            'bhf_end' => $bhf_end,
            'category' => $category . $zugn,
            'linie' => $category . $linie,
            'pfad' => $pfad . "####Test->" . $elevator . "####" . $elevator2,
            'filter' => $ff
        ];
    }
}
// Sortiere das Array nach dem 'time'-Schlüssel
usort($data, function($a, $b) {
    return strcmp($a['time'], $b['time']);
});
return $data;
}
?>