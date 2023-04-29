<?php
function getFromCache($cacheKey, $cacheExpiration) {
    // Build cache file path from cache key
    $cacheDir = "cache/";
    $cacheFile = $cacheDir . $cacheKey;
    
    $files = glob($cacheDir . "*");
    // Loop through all files
    foreach ($files as $file) {
        // Check if file is older than cache expiration time
        if (time() - filemtime($file) >= 604800) {
            // Delete file
            unlink($file);
        }
    }
    // If cache file exists and is not expired, return cached data
    if (file_exists($cacheFile) && time() - filemtime($cacheFile) < $cacheExpiration) {
        return file_get_contents($cacheFile);
    }

    // Otherwise, return false
    return false;
}

function saveToCache($cacheKey, $data) {
    // Build cache file path from cache key
    $cacheDir = "cache/";
    $cacheFile = $cacheDir . $cacheKey;

    // Save data to cache file
    file_put_contents($cacheFile, $data);
}
//Funktion curlbahn gibt mit den Übergabewerten $url und $accept_header aus was die Bahn API bereitstellt
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
function getYMD($date){
    $date_obj = DateTime::createFromFormat('Y-m-d', $date); // Erstellt ein DateTime-Objekt aus dem String
    $new_date_str = $date_obj->format('ymd'); // Formatiert das DateTime-Objekt in das gewünschte Format
    return $new_date_str;
}
function getviewselect() {
    if (isset($_GET['zugart_fv'])) {
    #    $view1 = $_GET['zugart_fv'];}
        $view1 = "F";} else {$view1 = "DUMMY";}
    if (isset($_GET['zugart_nv'])) {
        $view2 = "N";} else {$view2 = "DUMMY";}
    if (isset($_GET['zugart_metro'])) {
        $view3 = "S";} else {$view3 = "DUMMY";}
    if (isset($_GET['zugart_andere'])) {
        $view4 = "";} else {$view4 = "DUMMY";}
    $data[] = [
            $view1,
            $view2,
            $view3,
            $view4,
    ];
    return $data;
}

function getStationDetails($evaNo, $json) {
    $cacheExpiration = 600;
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/station-data/v2/stations?eva=" . $evaNo;
    // Build cache key from URL
    $cacheKey = "StaDa-". $evaNo;
    // Try to get data from cache
    $cachedData = getFromCache($cacheKey, $cacheExpiration);

    if ($cachedData !== false) {
        $cjson = $cachedData;
    } else {
        $cjson = curlbahn($url, "application/json");
        saveToCache($cacheKey, $cjson);
    }
    $data = json_decode($cjson, true);
    $stationDetail = array_reduce(explode('.', $json), function($arr, $key) {
        return $arr[$key] ?? null;
    }, $data);
    return $stationDetail;
}
function checkElevatorState($evaNo, $gleisnummer) {
    // Bereinige die Gleisnummer und speichere sie in einer Variablen.
    $gleisnummer_bereinigt = intval($gleisnummer);
    
    // Hole die Liste der Einrichtungen im Bahnhof.
    $facilities = json_decode(getFaSta($evaNo), true);
    
    // Überprüfe, ob der Bahnhof existiert.
    if (!(getStationDetails($evaNo, 'errNo')) == '404') {
        // Durchlaufe alle Einrichtungen im Bahnhof.
        foreach($facilities['facilities'] as $facility) {
            // Speichere die Beschreibung und den Zustand der Einrichtung in Variablen.
            $description = $facility['description'] ?? false;
            $state = $facility['state'];
            
            // Überprüfe, ob die Einrichtung ein Aufzug ist.
            if($facility['type'] === 'ELEVATOR') {
                if(preg_match("/Gleis\s{$gleisnummer_bereinigt}(\s|$)/", $description, $matches) && $state === 'ACTIVE') {
                    return true;
                }
                else if(preg_match("/Gleis\s\d+\s?\/\s?{$gleisnummer_bereinigt}(\s|$)/", $description, $matches) && $state === 'ACTIVE') {
                    return true;
                }
                else if(preg_match("/Gleis\s{$gleisnummer_bereinigt}\s?\/\s?\d+(\s|$)/", $description, $matches) && $state === 'ACTIVE') {
                    return true;
                }
            }
        }
    }
    // Gebe false zurück, wenn der Aufzug nicht gefunden wurde.
    return false;
}
#if (isset($_GET['evaNo']) && isset($_GET['date']) && isset($_GET['hour']) && isset($_GET['mode'])) {
#    //Lese Werte aus dem Form
#    $evaNo = filter_input(INPUT_GET, 'evaNo', FILTER_VALIDATE_INT);
#    $date = filter_input(INPUT_GET, 'date', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{4}-\d{2}-\d{2}$/")));
#    $hour = filter_input(INPUT_GET, 'hour', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{2}:\d{2}$/")));
#    $mode = isset($_GET['mode']) && ($_GET['mode'] === 'ar' || $_GET['mode'] === 'dp') ? $_GET['mode'] : 'ar';
#}

function getFaSta($evaNo) {
    $cacheExpiration = 600;
    $stationnumber = getStationDetails($evaNo, 'result.0.number');
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/fasta/v2/stations/" . $stationnumber;

    // Build cache key from URL
    $cacheKey = "FaSta-".$evaNo;

    // Try to get data from cache
    $cachedData = getFromCache($cacheKey, $cacheExpiration);

    if ($cachedData !== false) {
        // Return cached data if available
        return $cachedData;
    }

    // Otherwise, make API request and save response to cache
    $data = curlbahn($url, "application/json");
    saveToCache($cacheKey, $data);

    return $data;
}
function getStationList(){
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/station/*";
    //Caching
    $cacheExpiration = 86400; // 1 Tag, da nicht zu erwarten ist, dass häufig neue Stations hinzu kommen
    // Build cache key from URL
    $cacheKey = "StationList";
    // Try to get data from cache
    $cachedData = getFromCache($cacheKey, $cacheExpiration); 
    if ($cachedData !== false) {
        $sourcexml = $cachedData;
    } else {
        $sourcexml = curlbahn($url,"application/xml");
        saveToCache($cacheKey, $sourcexml);
    }
    // XML-String in ein SimpleXMLElement-Objekt umwandeln
    $xml = new SimpleXMLElement($sourcexml);
    // Erstelle ein leeres Array, um die Daten zu speichern
    $data = [];
    // Fülle das Array mit den Daten aus der Schleife
    foreach ($xml->station as $element) { // Schleife, die jedes Element in $xml->s durchläuft
        $name = $element->attributes()->name;
        $evaNo = $element->attributes()->eva;
         //Array mit Daten befüllen
        $data[] = [
            'name' =>  json_decode(json_encode($name), true),
            'evaNo' => json_decode(json_encode($evaNo), true)
        ];
    } 
        
    return $data;
}

function searchStation($searchTerm) {
    $stations = getStationList();
    $filteredStations = array_filter($stations, function($station) use ($searchTerm) {
        return strpos(strtolower($station['name'][0]), strtolower($searchTerm)) !== false;
    });
    $result = array();
    foreach ($filteredStations as $station) {
        $result[] = array(
            'name' => $station['name'][0],
            'evaNo' => $station['evaNo'][0]
        );
    }
    return json_encode($result, JSON_UNESCAPED_UNICODE);
}



function getTimeTable($evaNo, $date, $hour, $mode){
    $dateNew = getYMD($date);     
    $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/" . $evaNo . "/" . $dateNew . "/" . $hour;
    // Cacheing
    $cacheExpiration = 600;
    // Build cache key from URL
    $cacheKey = "Timetable-". $evaNo . "-" . $dateNew .  "-" . $hour;
    // Try to get data from cache
    $cachedData = getFromCache($cacheKey, $cacheExpiration); 
    if ($cachedData !== false) {
        $sourcexml = $cachedData;
    } else {
        $sourcexml = curlbahn($url,"application/xml");
        saveToCache($cacheKey, $sourcexml);
    }
    foreach(getviewselect() as $views) {
        $view1 = $views[0];
        $view2 = $views[1];
        $view3 = $views[2];
        $view4 = $views[3];
    }
    // XML-String in ein SimpleXMLElement-Objekt umwandeln
    $xml = new SimpleXMLElement($sourcexml);
    // Erstelle ein leeres Array, um die Daten zu speichern
    $data = [];
    // Fülle das Array mit den Daten aus der Schleife
    foreach ($xml->s as $element) { // Schleife, die jedes Element in $xml->s durchläuft
        if (isset($element->$mode)) { // Überprüft, ob das aktuelle Element ein Attribut namens $mode hat
            $ff = $element->tl->attributes()->f; // Liest den Wert des Attributs "f" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $ff zu
            $abfahrtszeit = $element->$mode->attributes()->pt; // Liest den Wert des Attributs "pt" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $abfahrtszeit zu
            $bhf = explode("|", $element->$mode->attributes()->ppth); // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements, splittet ihn anhand des Trennzeichens "|" und weist ihn der Variablen $ziel zu
            $bhf_0 = $bhf[0]; // Nimmt das erste Element des Arrays $ziel und weist es wieder der Variable $bhf_0 zu
            $bhf_end = end($bhf); // Nimmt das letzte Element des Arrays $ziel und weist es wieder der Variable $bhf_end zu
            $hour = substr($abfahrtszeit, 6, 2); // Extrahiert aus dem Wert von $abfahrtszeit die Stundenzahl und weist sie der Variablen $hour zu
            $minute = substr($abfahrtszeit, 8, 2); // Extrahiert aus dem Wert von $abfahrtszeit die Minutenzahl und weist sie der Variablen $minute zu
            $time = $hour . ":" . $minute; // Setzt die Werte von $hour und $minute zu einem Zeitstempel zusammen und weist ihn der Variablen $time zu
            $gleis = $element->$mode->attributes()->pp; //Liest den Wert des Attributs "pp"(gleis) aus
            $category = $element->tl->attributes()->c; // Liest den Wert des Attributs "c" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $category zu
            $linie = $element->tl->attributes()->n; // Liest den Wert des Attributs "n" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $linie zu
            $pfad = $element->$mode->attributes()->ppth; // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $pfad zu
            $pfad = implode(" -> ",$bhf); // Liest den Wert des Attributs "ppth" des Attributs $mode des aktuellen Elements und weist ihn der Variablen $pfad zu
            if (!empty($element->$mode->attributes()->l)) { // Prüft, ob das Attribut $mode des aktuellen Elements ein Attribut "l" hat und ob der Wert nicht leer ist
                $zugn = (!empty($element->ar->attributes()->ppth) and !empty($element->$mode->attributes()->l)) ? $element->ar->attributes()->l : $element->dp->attributes()->l; // Weist der Variablen $zugn den Wert des Attributs "l" des Attributs "ar" des aktuellen Elements zu, wenn vorhanden, andernfalls wird der Wert des Attributs "l" des Attributs "dp" des aktuellen Elements zugewiesen
            } else {
                $zugn = $element->tl->attributes()->n; // Weist der Variablen $zugn den Wert des Attributs "n" des Attributs "tl" des aktuellen Elements zu
            }
            if(checkElevatorState($evaNo, $gleis)){
                $elevator = "TRUE";
            } elseif (!checkElevatorState($evaNo, $gleis)) {
                $elevator = "FALSE";
            }
            // Filtert die Einträge weiter nach gegebenen Kriterien
            if($ff==$view1 or $ff==$view2 or $ff==$view3 or $ff==$view4){
                //Array mit Daten befüllen
                $data[] = [
                    'time' => $time,
                    'gleis' => $gleis,
                    'elevator' => $elevator,
                    'bhf_0' => $bhf_0,
                    'bhf_end' => $bhf_end,
                    'category' => $category . $zugn,
                    'linie' => $category . $linie,
                    'pfad' => $pfad,
                    'filter' => $ff
                ];
            } 
        }
    }
    // Sortiere das Array nach dem 'time'-Schlüssel
    usort($data, function($a, $b) {
        return strcmp($a['time'], $b['time']);
    });
    return $data;
}
?>