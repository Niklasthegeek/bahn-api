<?php require 'backend.php'; ?>
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
        </div>
        <div class="col-md-9">
            <table class="table">
              <tbody>
                <tr>
                  <td>EvaNo:</td>
                  <td><?php echo "test"; ?></td>
                </tr>
                <tr>
                  <td>Name:</td>
                  <td><?php echo "test"; ?></td>
                </tr>
                <tr>
                  <td>Bundesland:</td>
                  <td><?php echo "test"; ?></td>
                </tr>
              </tbody>
            </table>        
        </div>
        </div>

        <br>
        <div id="timetable-results"><br>
        <?php
        if (isset($_GET['evaNo']) && isset($_GET['date']) && isset($_GET['hour'])) {
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
