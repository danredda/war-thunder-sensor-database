<html data-bs-theme="dark">
    <title>RWR Information - Home</title>
    <?php
    include "./_head.php";
    $versions = PDO_FetchRow("SELECT * FROM VERSIONDATA");
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/gszabi99/War-Thunder-Datamine/commits/HEAD");
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RWR-Application');

        $apiResponse = curl_exec($ch);
        curl_close($ch);
        // var_dump($apiResponse);

        $latestCommit = json_decode($apiResponse);

        $latestVersion = $latestCommit->commit->message;
        $unitsMissingQuery = "SELECT COUNT(DISTINCT us.UnitUniqueName) as MissingUnits
                                FROM UNITSENSORS us
                                LEFT JOIN UNITS un
                                ON un.UnitUniqueName = us.UnitUniqueName
                                WHERE un.UnitLabel IS NULL";
        $unitsMissing = PDO_FetchRow($unitsMissingQuery);

        $sensorsMissingQuery = "SELECT COUNT(DISTINCT us.UnitUniqueName) as MissingSensors
                                FROM UNITSENSORS us
                                LEFT JOIN SENSORS s
                                ON s.SensorUniqueName = us.SensorUniqueName
                                WHERE s.SensorLabel IS NULL";
        $sensorsMissing = PDO_FetchRow($sensorsMissingQuery);
    ?>
    <div class="container">
        <div class="row">
            <h2>Latest version in Gszabi's Github: <?php echo $latestVersion ?></h2>
            <h2>Current Database Versions</h2>
            <p>Please note: Updating the sensor database will download roughly 6MB of data. Updating the UnitSensors will download roughly 300MB of data, as it needs to retrieve all the flightmodel, tankmodel, and ship blks from Gszabi's github repo. Unless there have been changes to the units in the game, or to the sensors on certain units, you do not need to update this to the latest version.</p>
            <div class="col">
                <table class="table table-bordered text-center">
                    <thead>
                        <th scope="col" class="col-2">RADAR</th>
                        <th scope="col" class="col-2">MLWS</th>
                        <th scope="col" class="col-2">LWS</th>
                        <th scope="col" class="col-2">RWR</th>
                        <th scope="col" class="col-4">UNITSENSORS</th>
                    </thead>
                    <tbody>
                        <tr scope="row">
                        <?php 
                            echo '<td>'.$versions['RADAR'].'</td>';
                            echo '<td>'.$versions['MLWS'].'</td>';
                            echo '<td>'.$versions['LWS'].'</td>';
                            echo '<td>'.$versions['RWR'].'</td>';
                            echo '<td>'.$versions['UNITSENSORS'].'</td>';
                        ?>
                        </tr>
                        <tr scope="row">
                            <td colspan='4'>
                                <?php
                                    if ($latestVersion != $versions['RADAR'] || $latestVersion != $versions['RWR'] || $latestVersion != $versions['MLWS'] || $latestVersion != $versions['LWS']) {
                                        echo "<button class='btn btn-secondary' id='update-sensors' value='$latestVersion' >Update Sensor Datasets</button>";
                                    }
                                ?>
                                <div id="sensor-spinner" class="d-none flex-column align-items-center justify-content-center">
                                    <div class="row">
                                        <div class="spinner-border text-light" role="status"></div>
                                    </div>
                                    <div class="row">
                                        <strong>Collecting & processing data...</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php
                                    if ($latestVersion != $versions['UNITSENSORS'] && $latestVersion == $versions['RWR'] && $latestVersion == $versions['RADAR'] && $latestVersion == $versions['MLWS'] && $latestVersion == $versions['LWS']) {
                                        echo "<button class='btn btn-secondary' id='update-unitsensors' value='$latestVersion'>Update Unit Sensor Dataset</button>";
                                    } else if ($latestVersion != $versions['UNITSENSORS']) {
                                        echo "Please update the Sensors first - that is used to determine what is and is not an RWR, MLWS, or radar in the Unit Files.";
                                    }
                                ?>
                                <div id="unitsensor-spinner" class="d-none flex-column align-items-center justify-content-center">
                                    <div class="row">
                                        <div class="spinner-border text-light" role="status"></div>
                                    </div>
                                    <div class="row">
                                        <strong>Collecting & processing data...</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h2>Update Unit/Sensor CSV data (Used for display names)</h2>
            <div class="col">
                <table class="table table-bordered text-center">
                    <thead>
                        <th scope="col" class="col-6">SENSORS CSV</th>
                        <th scope="col" class="col-6">UNITS CSV</th>
                    </thead>
                    <tbody>
                        <!-- <tr scope="row"> -->
                        <?php 
                            // echo '<td>'.$versions['UNITCSV'].'</td>';
                            // echo '<td>'.$versions['SENSORCSV'].'</td>';
                        ?>
                        <!-- </tr> -->
                        <tr scope="row">
                            <?php
                                echo '<td>Currently missing labels for ' . $sensorsMissing['MissingSensors'] . ' used sensors</td>';
                                echo '<td>Currently missing labels for ' . $unitsMissing['MissingUnits'] . ' units with sensors</td>';
                            ?>
                        </tr>
                        <tr scope="row">
                        <td>
                            <p>Downloading used templates will only include sensors currently used by an in-game unit. If you would like to update unused sensors as well, please download the full template</p>
                            <div class="btn-group" role="group" aria-label="Sensor Download">
                                <button class='btn btn-secondary' id='download-sensors'>Download Used Sensor Template</button>
                                <button class='btn btn-secondary' id='download-sensors-full'>Download Full Sensor Template</button>
                            </div>
                        </td>
                            <td><button class='btn btn-secondary' id='download-units'>Download Unit Template</button></td>
                        </tr>
                        <tr scope="row">
                            <td>
                                <form id="sensor-csv-formdata" action="./update-sensorcsv.php" method="POST">
                                    <div class="form-group">
                                        <label for="sensorCsv">Select sensor CSV to upload</label>
                                        <input type="file" class="form-control" name="sensorCsv" id="sensorCsv" aria-describedby="sensorHelp" placeholder="Select File">
                                        <div id="validationSensor" class="d-none"></div>
                                        <small id="sensorHelp" class="form-text text-muted">The file will only be uploaded to your local instance of the application.</small>
                                    </div>
                                    <button type="submit" class="btn btn-secondary" id="submit-sensor-update">Submit</button>
                                </form>
                                <div id="sensor-update-spinner" class="d-none flex-column align-items-center justify-content-center">
                                    <div class="row">
                                        <div class="spinner-border text-light" role="status"></div>
                                    </div>
                                    <div class="row">
                                        <strong>Uploading & processing data...</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <form id="unit-csv-formdata" action="./update-unitcsv.php" method="POST">
                                    <div class="form-group">
                                        <label for="unitsCsv">Select units CSV to upload</label>
                                        <input type="file" class="form-control" name="unitsCsv" id="unitsCsv" aria-describedby="unitsHelp" placeholder="Select File">
                                        <div id="validationUnits" class="d-none"></div>
                                        <small id="unitsHelp" class="form-text text-muted">The file will only be uploaded to your local instance of the application.</small>
                                    </div>
                                    <button type="submit" class="btn btn-secondary" id="submit-unit-update">Submit</button>
                                </form>
                                <div id="unit-update-spinner" class="d-none flex-column align-items-center justify-content-center">
                                    <div class="row">
                                        <div class="spinner-border text-light" role="status"></div>
                                    </div>
                                    <div class="row">
                                        <strong>Uploading & processing data...</strong>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function(){
            $('#update-sensors').click(function(){
                $('#sensor-spinner').toggleClass('d-none').toggleClass('d-flex');
                $('#update-sensors').toggleClass('d-none');
                var clickBtnValue = $(this).val();
                var ajaxurl = 'sensor-update.php',
                data =  {'versionNumber': clickBtnValue};
                $.post(ajaxurl, data, function (response) {
                    $('#sensor-spinner').toggleClass('d-none').toggleClass('d-flex');
                    $('#update-sensors').toggleClass('d-none');
                    window.location.reload();
                });
            });
            $('#update-unitsensors').click(function(){
                $('#unitsensor-spinner').toggleClass('d-none').toggleClass('d-flex');
                $('#update-unitsensors').toggleClass('d-none');
                var clickBtnValue = $(this).val();
                var ajaxurl = 'unitsensor-update.php',
                data =  {'versionNumber': clickBtnValue};
                $.post(ajaxurl, data, function (response) {
                    $('#unitsensor-spinner').toggleClass('d-none').toggleClass('d-flex');
                    $('#update-unitsensors').toggleClass('d-none');
                    window.location.reload();
                });
            });
            $('#download-units').click(function(){
                window.location = './download-units-csv.php';
            });
            $('#download-sensors').click(function(){
                window.location = './download-sensors-csv.php';
            });
            $('#download-sensors-full').click(function(){
                window.location = './download-sensors-full-csv.php';
            });
            $("#unit-csv-formdata").submit(function(e){
                e.preventDefault();
                $('#unitsCsv').removeClass('is-invalid');
                $('#unitsCsv').removeClass('is-valid');
                $('#validationUnits').addClass('d-none').removeClass('invalid-feedback').removeClass('valid-feedback');
                var action = $(this).attr("action");
                $('#unit-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                $('#submit-unit-update').toggleClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: action,
                    data:  new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {
                        console.log(response);
                        $('#unit-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                        $('#submit-unit-update').toggleClass('d-none');
                        $('#unitsCsv').toggleClass('is-valid');
                        $('#validationUnits').removeClass('d-none').addClass('valid-feedback');
                        setTimeout(function(){
                            window.location.reload();
                        }, 2000);
                    },
                    error: function (response) {
                        $('#unit-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                        $('#submit-unit-update').toggleClass('d-none');
                        $('#unitsCsv').toggleClass('is-invalid');
                        $('#validationUnits').removeClass('d-none').addClass('invalid-feedback');
                        $('#validationUnits').text(response.responseJSON.message);
                    }
                });
            });
            $("#sensor-csv-formdata").submit(function(e){
                e.preventDefault();
                $('#sensorCsv').removeClass('is-invalid');
                $('#sensorCsv').removeClass('is-valid');
                $('#validationSensor').addClass('d-none').removeClass('invalid-feedback').removeClass('valid-feedback');
                var action = $(this).attr("action");
                $('#sensor-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                $('#submit-sensor-update').toggleClass('d-none');
                $.ajax({
                    type: 'POST',
                    url: action,
                    data:  new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function (response) {
                        console.log(response);
                        $('#sensor-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                        $('#submit-sensor-update').toggleClass('d-none');
                        $('#sensorCsv').toggleClass('is-valid');
                        $('#validationSensor').removeClass('d-none').addClass('valid-feedback');
                        setTimeout(function(){
                            window.location.reload();
                        }, 2000);
                    },
                    error: function (response) {
                        $('#sensor-update-spinner').toggleClass('d-none').toggleClass('d-flex');
                        $('#submit-sensor-update').toggleClass('d-none');
                        $('#sensorCsv').toggleClass('is-invalid');
                        $('#validationSensor').removeClass('d-none').addClass('invalid-feedback');
                        $('#validationSensor').text(response.responseJSON.message);
                    }
                });
            });
        });


    </script>

</html>