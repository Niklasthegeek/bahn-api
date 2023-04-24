<!DOCTYPE html>
<html>
<head>
    <title>Timetables</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <h1>Timetables</h1>
        <form method="GET">
            <div class="form-group">
                <label for="evaNo">EvaNo:</label>
                <input type="text" class="form-control" id="evaNo" name="evaNo" value="<?php echo isset($_GET['evaNo']) ? $_GET['evaNo'] : ''; ?>"
                    placeholder="Enter EvaNo">
            </div>
            <div class="form-group">
                <label for="date">Datum:</label>
                <input type="text" class="form-control" id="date" name="date"
                    value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>">
            </div>
            <div class="form-group">
                <label for="hour">Stunde:</label>
                <input type="text" class="form-control" id="hour" name="hour"
                    value="<?php echo isset($_GET['hour']) ? $_GET['hour'] : ''; ?>">
            </div>
            <div class="form-check-inline">
                <input type="radio" class="form-check-input" id="mode-ar" name="mode" value="ar" <?php if (isset($_GET['mode'])){if ($_GET['mode'] == 'ar')
                    echo "checked";} else echo "checked"; ?>>
                <label class="form-check-label" for="mode-ar">Ankünfte</label>
            </div>
            <div class="form-check-inline">
                <input type="radio" class="form-check-input" id="mode-dp" name="mode" value="dp" <?php if (isset($_GET['mode'])){if ($_GET['mode'] == 'dp')
                    echo "checked";}; ?>>
                <label class="form-check-label" for="mode-dp">Abfahrten</label>
            </div>
            <br><br>
            <button type="submit" class="btn btn-primary">Get Timetable</button>
        </form>
        <br>
        <div id="timetable-results"><br>
        <?php
        if (isset($_GET['evaNo']) && isset($_GET['date']) && isset($_GET['hour'])) {
            $evaNo = $_GET['evaNo'];
            $date = $_GET['date'];
            $hour = $_GET['hour'];
            $mode = $_GET['mode'];

            #$url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/8010224/230424/15";
            $url = "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/plan/" . $evaNo . "/" . $date . "/" . $hour;


            $curl = curl_init();

            curl_setopt_array($curl, [
                #  CURLOPT_URL => "https://apis.deutschebahn.com/db-api-marketplace/apis/timetables/v1/station/*",  # URL  für ausgabe aller Stations
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "DB-Api-Key: b8ffe6595cfe1ab0af130dc199e87627",
                    "DB-Client-Id: a389731540cd5f94f5d06d8afce71687",
                    "accept: application/xml"
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            #echo "Respone:\n" . $response . "End-Response\n";
            #echo "end";#
            #$mode = "ar";
            if ($mode == "ar") {
                $th_1 = "Startbahnhof";
                $th_2 = "Letzter Halt";
            } else {
                $th_1 = "Nächster Halt";
                $th_2 = "Zielbahnhof";
            }
            // XML-String in ein SimpleXMLElement-Objekt umwandeln
            $xml = new SimpleXMLElement($response);
            // Erstelle ein leeres Array, um die Daten zu speichern
            $data = [];
            // Fülle das Array mit den Daten aus der SchleifeX
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
                    $category = $element->tl->attributes()->c; // Liest den Wert des Attributs "c" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $category zu
                    $linie = $element->tl->attributes()->n; // Liest den Wert des Attributs "n" des Attributs "tl" des aktuellen Elements und weist ihn der Variablen $linie zu
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
                        'pfad' => $pfad
                    ];
                }
            }
            // Sortiere das Array nach dem 'time'-Schlüssel
            usort($data, function($a, $b) {
                return strcmp($a['time'], $b['time']);
            });
        #}
        ?>

        <table class="table table-bordered">
          <thead>
            <tr>
              <th>Zeit</th>
              <th>Gleis</th>
              <th><?php echo $th_1; ?></th>
              <th><?php echo $th_2; ?></th>
              <th>Zugnummer</th>
              <th>ZugID</th>
              <th>Über</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($data as $row) { ?>
              <tr>
                <td><?php echo $row['time']; ?> Uhr</td>
                <td><?php echo $row['gleis']; ?></td>
                <td><?php echo $row['bhf_0']; ?></td>
                <td><?php echo $row['bhf_end']; ?></td>
                <td><?php echo $row['category']; ?></td>
                <td><?php echo $row['linie']; ?></td>
                <td><?php echo $row['pfad']; ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
        <?php
        }
        ?>
    </div>
</body>

</html>