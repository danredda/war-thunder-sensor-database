<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["sensorCsv"]["name"]);
    $uploadOk = 1;
    $errorMessage = '';
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if($fileType != "csv") {
        $errorMessage = "Sorry, only CSV files are allowed.";
        $uploadOk = 0;
    }
    // $result = move_uploaded_file($_FILES["sensorCsv"]["tmp_name"], $target_file);
    // if () {
    //     $errorMessage = 'An unknown error has occurred processing the file';
    //     $uploadOk = 0;
    // }

    // Read the CSV
    $rows = file($_FILES["sensorCsv"]["tmp_name"]);
    $cols = str_getcsv(array_shift($rows));
    $result = array_map(function($v) use($cols) {
                            return (object)array_combine($cols, str_getcsv($v));
                        }, $rows);
                        
    // DELETE the Unit Data
    PDO_Execute("DELETE FROM SENSORS");
    

    if(sizeof($result) > 0 && $uploadOk = 1) {
        // Write to the DB
        $SensorValuesArray = array();
        foreach($result as $Sensor) {
            $Sensor->SensorLabel = str_replace("'", "''", $Sensor->SensorLabel);
            $SensorValuesArray[] = "('".str_replace('"', '\"', implode("','",(array) $Sensor))."')";
        }
        $sensorValues = implode(",", $SensorValuesArray);
        // var_dump($sensorValues);
        $sensorInsert = "INSERT INTO SENSORS (SensorUniqueName, SensorLabel)
                            VALUES $sensorValues";

        PDO_Execute($sensorInsert);

        $row = PDO_FetchRow("SELECT UNITSENSORS FROM VERSIONDATA");
        $versionNumber = $row['UNITSENSORS'];
        // GET UNITSENSORS Version
        PDO_Execute("UPDATE VERSIONDATA SET SENSORCSV='$versionNumber'");
    }





    
    if ($uploadOk == 0) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => "ERROR: $errorMessage")));
    }

    echo json_encode(array('message' => "Success: Sensors have been updated successfully"));
?>