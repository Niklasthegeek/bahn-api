<?php 
//Backend laden
require 'backend.php';
date_default_timezone_set('Europe/Berlin');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bahn-Infotafel</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Aktuelles Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
    <!-- Leaflet Openstreetmap -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.7.1/dist/leaflet.css" />
    <script type="module">
      import Autocomplete from "./autocomplete.min.js";
      const opts = {
        //onSelectItem: console.log,
        maximumItems: "10",
        fullWidth: true	
      };
      new Autocomplete(document.getElementById("bahnhof"), opts);
    </script>
    <script>
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
        document.documentElement.setAttribute('data-bs-theme', 'light');
      } else {
        document.documentElement.setAttribute('data-bs-theme', 'dark');
      }
    </script>
</head>
<body>
    <div class="container-fluid">
        <h1>Bahn-Infotafel</h1>
        <div class="row">
        <div class="col-md-3">
        <form method="GET">
            <div class="form-group col-12">
                <label for="bahnhof">Bahnhof:</label>
                <input
                  type="text"
                  class="form-control"
                  id="bahnhof"
                  name="bahnhof"
                  value="<?php echo isset($_GET['bahnhof']) ? $_GET['bahnhof'] : ''; ?>"
                  data-server="searchStation.php"
                  data-live-server="true"
                  data-value-field="evaNo"
                  data-label-field="name"
                  placeholder="Bahnhof eingeben"
                  required
                />
            </div>
            <div class="form-group col-12">
                <label for="date">Datum:</label>
                <input type="date" class="form-control" id="date" name="date"
                       value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group col-12">
                <label for="hour">Stunde:</label>
                <input type="text" class="form-control" id="hour" name="hour"
                    value="<?php echo isset($_GET['hour']) ? $_GET['hour'] : ''; ?>" required>
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
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="zugart_fv" name="zugart_fv" value="yes" <?php if (isset($_GET['zugart_fv'])){if ($_GET['zugart_fv'] == 'yes'){echo "checked";}}  ?>>
                <label class="form-check-label" for="zugart_fv">
                    Fernverkehr
                </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="zugart_nv" name="zugart_nv" value="yes" <?php if (isset($_GET['zugart_nv'])){if ($_GET['zugart_nv'] == 'yes'){echo "checked";}}  ?>>
                <label class="form-check-label" for="zugart_nv">
                    Nahverkehr
                </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="zugart_metro" name="zugart_metro" value="yes" <?php if (isset($_GET['zugart_metro'])){if ($_GET['zugart_metro'] == 'yes'){echo "checked";}}  ?>>
                <label class="form-check-label" for="zugart_metro">
                    Metro
                </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="zugart_andere" name="zugart_andere" value="yes" <?php if (isset($_GET['zugart_andere'])){if ($_GET['zugart_andere'] == 'yes'){echo "checked";}}  ?>>
                <label class="form-check-label" for="zugart_andere">
                    Andere
                </label>
            </div>
            <br><br>
            <button type="submit" class="btn btn-primary">Get Timetable</button>
        </form>
        </div>
        <div class="col-md-5">
        <?php
        
        if (isset($_GET['bahnhof']) && isset($_GET['date']) && isset($_GET['hour'])&& isset($_GET['mode'])) {
            //Lese Werte aus dem Form
            $date = filter_input(INPUT_GET, 'date', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{4}-\d{2}-\d{2}$/")));
            $mode = isset($_GET['mode']) && ($_GET['mode'] === 'ar' || $_GET['mode'] === 'dp') ? $_GET['mode'] : 'ar';
            $evaNo = (searchStation($_GET['bahnhof']))==[] ?  null : json_decode(searchStation($_GET['bahnhof']), true)[0]['evaNo'];
            $hour = filter_input(INPUT_GET, 'hour', FILTER_VALIDATE_INT);
            //Auswahl der Tabellenüberschrift
            if ($mode == "ar") {
                $th_1 = "Startbahnhof";
                $th_2 = "Letzter Halt";
            } else {
                $th_1 = "Nächster Halt";
                $th_2 = "Zielbahnhof";
            }
        ?>
        <div >
            <table class="table">
              <tbody>
                <tr>
                  <td>StationNumber:</td>
                  <td><?php //Wenn evaNo gesetzt und getStationDetails($evaNo, 'result.0.name') nicht leer ist gebe getStationDetails($evaNo, 'result.0.name') aus, sonst gebe 'Information nicht verfügbar' aus
                  echo isset($evaNo)&&!empty(getStationDetails($evaNo, 'result.0.number')) ? getStationDetails($evaNo, 'result.0.number') : 'Information nicht verfügbar';?>
                </tr>
                <tr>
                  <td>Name:</td>
                  <td><?php echo isset($evaNo)&&!empty(getStationDetails($evaNo, 'result.0.name')) ? getStationDetails($evaNo, 'result.0.name') : 'Information nicht verfügbar'; ?></td>
                </tr>
                <tr>
                  <td>Aufgabenträger:</td>
                  <td><?php echo isset($evaNo)&&!empty(getStationDetails($evaNo, 'result.0.aufgabentraeger.name')) ? getStationDetails($evaNo, 'result.0.aufgabentraeger.name') : 'Information nicht verfügbar'; ?></td>
                </tr>
              </tbody>
            </table>        
        </div>
        </div>
        <div id="mapid" class="col-md-4">
        <script>
        // OpenStreetMap-Karte erstellen
        var mymap = L.map('mapid').setView([<?php echo getStationDetails($evaNo, 'result.0.evaNumbers.0.geographicCoordinates.coordinates.1') ?>, <?php echo getStationDetails($evaNo, 'result.0.evaNumbers.0.geographicCoordinates.coordinates.0') ?>], 13);
        // Tile-Layer von OpenStreetMap hinzufügen
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
                '<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
            id: 'mapbox/streets-v11',
            tileSize: 512,
            zoomOffset: -1
        }).addTo(mymap);        
        // Marker hinzufügen
        L.marker([<?php echo getStationDetails($evaNo, 'result.0.evaNumbers.0.geographicCoordinates.coordinates.1') ?>, <?php echo getStationDetails($evaNo, 'result.0.evaNumbers.0.geographicCoordinates.coordinates.0') ?>]).addTo(mymap);
        </script>
        </div>
        </div>
        <br>
        <div id="timetable-results"><br>

        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Zeit</th>
              <th>Gleis</th>
              <th>Aufzug</th>
              <th><?php echo $th_1; ?></th>
              <th><?php echo $th_2; ?></th>
              <th>Zugnummer</th>
              <th>ZugID</th>
              <th>Über</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (getTimeTable($evaNo, $date, $hour, $mode) as $row) { ?>
              <tr <?php if ($row['filter'] == "F") { ?>style="background-color: #ffcccc; color: #333;" <?php } elseif ($row['filter'] == "N") { ?>style="background-color: #e6ffe6; color: #333;"<?php } elseif ($row['filter'] == "S") { ?>style="background-color: #ccebff; color: #333;"<?php } ?>>
                <td><?php echo $row['time']; ?> Uhr</td>
                <td><?php echo $row['gleis']; ?></td>
                <td><?php if ($row['elevator'] == "TRUE") { ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-square-fill" viewBox="0 0 16 16">
                      <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                      <path d="M10.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.235.235 0 0 1 .02-.022z"/>
                    </svg>
                  <?php } elseif($row['elevator'] == "FALSE"){ ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-square" viewBox="0 0 16 16">
                      <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                      <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                    </svg>
                  <?php } ?></td>
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
