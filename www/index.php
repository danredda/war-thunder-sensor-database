<html data-bs-theme="dark">
    <title>RWR Information - Home</title>
    <?php
    include "./_head.php";

    // $units = PDO_FetchAll("SELECT UnitUniqueName, UnitLabel FROM UNITS WHERE UnitUniqueName IN (SELECT UnitUniqueName FROM UNITSENSORS) ORDER BY UnitLabel ASC");
    $rwrs = PDO_FetchAll("SELECT ifnull(ifnull(s.SensorLabel, r.Name),r.RWRUniqueName) as SensorLabel, r.RWRUniqueName FROM RWR r LEFT JOIN SENSORS s ON r.RWRUniqueName = s.SensorUniqueName ORDER BY SensorLabel ASC");
    $mlws = PDO_FetchAll("SELECT ifnull(ifnull(s.SensorLabel, maw.Name),maw.MLWSUniqueName) as SensorLabel, maw.MLWSUniqueName FROM MLWS maw LEFT JOIN SENSORS s ON maw.MLWSUniqueName = s.SensorUniqueName ORDER BY SensorLabel ASC");
    ?>

    <div class="container">
        <div class="row">
            <div class="col">
                <p>This tool will display all the sensor information for a given sensor/unit, including detailed threat information. To begin, select a unit or a specific RWR below.</p>
            </div>
        </div>

        <div class="row">
            <div class="col-6">
                <form class="form-group border rounded p-2" action="./unit.php" method="POST">
                    <h3 class="w-auto">Select Unit</h3>
                    <div class="form-floating">
                        <select name='selectedUnit' class="form-select" id="SelectUnit" required disabled>
                            <option value="" selected>Open to select by Unit (COMING SOON)</option>
                            <?php 
                                // foreach ($units as $row){
                                //     echo '<option value="' . $row['UnitUniqueName'] . '">' . $row['UnitLabel'] . '</option>';
                                // }
                            ?>
                        </select>
                        <label for="SelectUnit">Select a specific Unit:</label>
                    </div>
                    <div class="text-center pt-3">
                        <button type="submit" class="btn btn-secondary" disabled>Submit</button>
                    </div>
                </form>
            </div>
            <div class="col-6">
                <form class="form-group border rounded p-2" action="./rwr.php" method="POST">
                    <h3 class="w-auto">Select RWR</h3>
                    <div class="form-floating">
                        <select name='selectedRWR' class="form-select" id="SelectRWR" required>
                            <option value="" selected>Open to select by RWR</option>
                            <?php 
                                foreach ($rwrs as $row){
                                    echo '<option value="' . $row['RWRUniqueName'] . '">' . $row['SensorLabel'] . ' (' . $row['RWRUniqueName'] . ') </option>';
                                }
                            ?>
                        </select>
                        <label for="SelectRWR">Select a specific RWR:</label>
                    </div>
                    <div class="text-center pt-3">
                        <button type="submit" class="btn btn-secondary">Submit</button>
                    </div>
                </form>
            </div>
            <div class="col-6">
                <form class="form-group border rounded p-2" action="./mlws.php" method="POST">
                    <h3 class="w-auto">Select MLWS (MAW)</h3>
                    <div class="form-floating">
                        <select name='selectedMLWS' class="form-select" id="SelectMLWS" required>
                            <option value="" selected>Open to select by MLWS</option>
                            <?php 
                                foreach ($mlws as $row){
                                    echo '<option value="' . $row['MLWSUniqueName'] . '">' . $row['SensorLabel'] . ' (' . $row['MLWSUniqueName'] . ') </option>';
                                }
                            ?>
                        </select>
                        <label for="SelectMLWS">Select a specific MLWS:</label>
                    </div>
                    <div class="text-center pt-3">
                        <button type="submit" class="btn btn-secondary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php

    include './_version.php';
    ?>
</html>