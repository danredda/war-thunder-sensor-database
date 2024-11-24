<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");

    if (isset($_POST['versionNumber'])) {
        $versionNumber = $_POST["versionNumber"];
    }

    // Get all sensor unique names.
    $sensorUniqueNameQuery = "SELECT RWRUniqueName AS SensorUniqueName, \"RWR\" AS SensorType FROM RWR
                                UNION 
                                SELECT RadarUniqueName AS SensorUniqueName, \"RADAR\" AS SensorType FROM RADAR
                                UNION
                                SELECT MLWSUniqueName AS SensorUniqueName, \"MLWS\" AS SensorType FROM MLWS";
    $FullSensorData = PDO_FetchAll($sensorUniqueNameQuery);

    $flightmodelURL = "https://api.dm.wt.flareflo.dev/files/aces.vromfs.bin/gamedata/flightmodels?version=$versionNumber";
    $tankmodelURL = "https://api.dm.wt.flareflo.dev/files/aces.vromfs.bin/gamedata/units/tankmodels?version=$versionNumber";
    $shipsURL = "https://api.dm.wt.flareflo.dev/files/aces.vromfs.bin/gamedata/units/ships?version=$versionNumber";

    $fmZip = './data_raw/flightmodels.zip';
    $tmZip = './data_raw/tankmodels.zip';
    $smZip = './data_raw/ships.zip';

    $FMPath = './data_raw/flightmodels';
    $TMPath = './data_raw/tankmodels';
    $SMPath = './data_raw/ships';

    getAndUnzipToFolderAndRemoveZip($flightmodelURL, $fmZip, 'flightmodels');
    getAndUnzipToFolderAndRemoveZip($tankmodelURL, $tmZip, 'tankmodels');
    getAndUnzipToFolderAndRemoveZip($shipsURL, $smZip, 'ships');

    removeExtraFiles('flightmodels');
    removeExtraFiles('tankmodels');
    removeExtraFiles('ships');

    PDO_Execute("DELETE FROM UNITSENSORS");

    $UnitSensorList = array();
    // Parse the flightModels
    foreach(scanAllDir($FMPath) as $fmFile) {
        $uniqueName = str_replace(".blk", "", $fmFile);

        $fmJson = json_decode(file_get_contents("$FMPath/$fmFile"));
        $sensorsList = findSensorsInJson($fmJson);
        foreach($sensorsList as $sensor) {
            $searchResult = checkIfSensorIsCaptured($FullSensorData, $sensor);
            if ($searchResult != false) {
                $UnitSensor = new UNITSENSOR();
                $UnitSensor->SensorType = $searchResult;
                $UnitSensor->SensorUniqueName = $sensor;
                $UnitSensor->UnitUniqueName = $uniqueName;
                $UnitSensor->UnitType = 'AIR';
                $unitClass = $fmJson->type;
                if (is_array($fmJson->type)) {
                    $unitClass = $fmJson->type[0];
                }
                $UnitSensor->UnitClass = $unitClass;
                $UnitSensorList[] = $UnitSensor;
            }
        }
    }
    removeDir($FMPath);
    // Parse the tankModels
    foreach(scanAllDir($TMPath) as $tmFile) {
        $uniqueName = str_replace(".blk", "", $tmFile);

        $tmJson = json_decode(file_get_contents("$TMPath/$tmFile"));
        $sensorsList = findSensorsInJson($tmJson);
        foreach($sensorsList as $sensor) {
            $searchResult = checkIfSensorIsCaptured($FullSensorData, $sensor);
            if ($searchResult != false) {
                $UnitSensor = new UNITSENSOR();
                $UnitSensor->SensorType = $searchResult;
                $UnitSensor->SensorUniqueName = $sensor;
                $UnitSensor->UnitUniqueName = $uniqueName;
                $UnitSensor->UnitType = 'GRND';
                $unitClass = $tmJson->type;
                if (is_array($tmJson->type)) {
                    $unitClass = $tmJson->type[0];
                }
                $UnitSensor->UnitClass = $unitClass;
                $UnitSensorList[] = $UnitSensor;
            }
        }
    }
    removeDir($TMPath);
    // Parse the ships
    foreach(scanAllDir($SMPath) as $smFile) {
        $uniqueName = str_replace(".blk", "", $smFile);

        $smJson = json_decode(file_get_contents("$SMPath/$smFile"));
        $sensorsList = findSensorsInJson($smJson);
        foreach($sensorsList as $sensor) {
            $searchResult = checkIfSensorIsCaptured($FullSensorData, $sensor);
            if ($searchResult != false) {
                $UnitSensor = new UNITSENSOR();
                $UnitSensor->SensorType = $searchResult;
                $UnitSensor->SensorUniqueName = $sensor;
                $UnitSensor->UnitUniqueName = $uniqueName;
                $UnitSensor->UnitType = 'NVL';
                $unitClass = $smJson->type;
                if (is_array($smJson->type)) {
                    $unitClass = $smJson->type[0];
                }
                $UnitSensor->UnitClass = $unitClass;
                $UnitSensorList[] = $UnitSensor;
            }
        }
    }
    removeDir($SMPath);

    if(sizeof($UnitSensorList) > 0) {
        $FinalSensorList = array_values(array_unique($UnitSensorList, SORT_REGULAR));
        // Write to the DB
        $unitSensorValuesArray = array();
        foreach($FinalSensorList as $UnitSensor) {
            $unitSensorValuesArray[] = "('".implode("','",(array) $UnitSensor)."')";
        }
        $unitSensorValues = implode(",", $unitSensorValuesArray);
        $unitSensorInsert = "INSERT INTO UNITSENSORS (SensorType, SensorUniqueName, UnitUniqueName, UnitType, UnitClass)
                            VALUES $unitSensorValues";
        PDO_Execute($unitSensorInsert);

        // SET UNITSENSORS Version to versionNumber
        PDO_Execute("UPDATE VERSIONDATA SET UNITSENSORS='$versionNumber'");
    }

    class UNITSENSOR {
        public $SensorType;
        public $SensorUniqueName;
        public $UnitUniqueName;
        public $UnitType;
        public $UnitClass;
    }

    function checkIfSensorIsCaptured($objectArray, $sensor) {
        foreach ((array) $objectArray as $element) {
            if ($element['SensorUniqueName'] == $sensor) {
                return $element['SensorType'];
            }
        }
        return false;
    }

    function findSensorsInJson($jsonDef) {
        $sensorList = array();
        foreach($jsonDef as $value) {
            if(is_object($value)) {
                // var_dump($value);
                $sensorList = array_merge($sensorList, findSensorsInJson($value));
            } else if(is_array($value)) {
                foreach($value as $val) {
                    if (is_object($val)) {
                        $sensorList = array_merge($sensorList, findSensorsInJson($val));
                    } else if (is_string($val) && str_contains($val, "/sensors/")) {
                        $start = strpos($val, "/sensors/")+9;
                        $sensorList[] = substr(str_replace("naval/", "",$val),$start,-4);
                    }
                }
            } else if (is_string($value) && str_contains($value, "/sensors/")) {
                $start = strpos($value, "/sensors/")+9;
                $sensorList[] = substr(str_replace("naval/", "",$value),$start,-4);
            }
        }
        return $sensorList;
    }

    function removeExtraFiles($folderName) {
        // Zips also include some extra files we don't care about.
        foreach (scandir("./data_raw/$folderName/") as $filename) {
            $filePath = "./data_raw/$folderName" . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filePath) and $filename != "." && $filename != "..") {
                removeDir($filePath);
            }
        }
    }

    function getAndUnzipToFolderAndRemoveZip($url, $zipPath, $finalPath) {
        $contents = file_get_contents($url);
        file_put_contents($zipPath, $contents);
        $zip = new ZipArchive;
        $res = $zip->open($zipPath);
        if ($res === TRUE) {
            $zip->extractTo("./data_raw/$finalPath/");
            $zip->close();
        }
        unlink($zipPath);
    }

    function scanAllDir($dir) {
        $result = [];
        foreach(scandir($dir) as $filename) {
          if ($filename[0] === '.') continue;
          $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
          if (is_dir($filePath)) {
            foreach (scanAllDir($filePath) as $childFilename) {
              $result[] = $filename . DIRECTORY_SEPARATOR . $childFilename;
            }
          } else {
            $result[] = $filename;
          }
        }
        return $result;
    }

    function removeDir(string $dir): void {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
                     RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($dir);
    }

?>