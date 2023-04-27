<?php 
//Backend laden
require 'backend.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bahn-Infotafel</title>
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
        <h1>Bahn-Infotafel</h1>
        <div class="row">
        <div class="col-md-3">
        <form method="GET">
            <div class="form-group">
                <label for="evaNo">EvaNo:</label>
                <input type="text" class="form-control" id="evaNo" name="evaNo" value="<?php echo isset($_GET['evaNo']) ? $_GET['evaNo'] : ''; ?>"
                    placeholder="Enter EvaNo">
            </div>
            <div class="form-group">
                <label for="date">Datum:</label>
                <input type="date" class="form-control" id="date" name="date"
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
        </div>
        <div class="col-md-9">
            <table class="table">
              <tbody>
                <tr>
                  <td>StationNumber:</td>
                  <td><?php echo isset($evaNo) ? getStationDetails($evaNo, 'result.0.number') : ''; ?></td>
                </tr>
                <tr>
                  <td>Name:</td>
                  <td><?php echo isset($evaNo) ? getStationDetails($evaNo, 'result.0.name') : ''; ?></td>
                </tr>
                <tr>
                  <td>Aufgabenträger:</td>
                  <td><?php echo isset($evaNo) ? getStationDetails($evaNo, 'result.0.aufgabentraeger.name') : ''; ?></td>
                </tr>
              </tbody>
            </table>        
        </div>
        </div>

        <br>
        <div id="timetable-results"><br>
        <?php
        
        if (isset($_GET['evaNo']) && isset($_GET['date']) && isset($_GET['hour'])&& isset($_GET['mode'])) {
            //Lese Werte aus dem Form
            $evaNo = filter_input(INPUT_GET, 'evaNo', FILTER_VALIDATE_INT);
            #$date = filter_input(INPUT_GET, 'date', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{4}-\d{2}-\d{2}$/")));
            #$hour = filter_input(INPUT_GET, 'hour', FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^\d{2}:\d{2}$/")));
            $mode = isset($_GET['mode']) && ($_GET['mode'] === 'ar' || $_GET['mode'] === 'dp') ? $_GET['mode'] : 'ar';
            #$evaNo = $_GET['evaNo'];
            $date = $_GET['date'];
            $hour = $_GET['hour'];
            #$mode = $_GET['mode'];
            //Auswahl der Tabellenüberschrift
            if ($mode == "ar") {
                $th_1 = "Startbahnhof";
                $th_2 = "Letzter Halt";
            } else {
                $th_1 = "Nächster Halt";
                $th_2 = "Zielbahnhof";
            }
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
            <?php foreach (getTimeTable($evaNo, $date, $hour, $mode) as $row) { ?>
              <tr <?php if ($row['filter'] == "F") { ?>style="background-color: #ffcccc;"<?php } elseif ($row['filter'] == "N") { ?>style="background-color: #e6ffe6;"<?php } elseif ($row['filter'] == "S") { ?>style="background-color: #ccebff;"<?php }
              ?>>
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
