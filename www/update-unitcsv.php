<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["unitsCsv"]["name"]);
    $uploadOk = 1;
    $errorMessage = '';
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if($fileType != "csv") {
        $errorMessage = "Sorry, only CSV files are allowed.";
        $uploadOk = 0;
    }

    // Read the CSV
    $rows = file($_FILES["unitsCsv"]["tmp_name"]);
    $cols = str_getcsv(array_shift($rows));
    $result = array_map(function($v) use($cols) {
                            return (object)array_combine($cols, str_getcsv($v));
                        }, $rows);
                        
    // DELETE the Unit Data
    PDO_Execute("DELETE FROM UNITS");
    

    if(sizeof($result) > 0 && $uploadOk = 1) {
        // Write to the DB
        $unitValuesArray = array();
        foreach($result as $Unit) {
            $Unit->UnitLabel = str_replace("'", "''", $Unit->UnitLabel);
            $unitValuesArray[] = "('".str_replace('"', '\"', implode("','",(array) $Unit))."')";
        }
        $unitValues = implode(",", $unitValuesArray);
        $unitInsert = "INSERT INTO UNITS (UnitUniqueName, UnitLabel)
                            VALUES $unitValues";
        // var_dump($unitValues);
        PDO_Execute($unitInsert);

        $row = PDO_FetchRow("SELECT UNITSENSORS FROM VERSIONDATA");
        $versionNumber = $row['UNITSENSORS'];
        // GET UNITSENSORS Version
        PDO_Execute("UPDATE VERSIONDATA SET UNITCSV='$versionNumber'");
    }





    
    if ($uploadOk == 0) {
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => "ERROR: $errorMessage")));
    }

    echo json_encode(array('message' => "Success: Units have been updated successfully"));
?>