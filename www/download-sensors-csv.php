<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");
    
    
    $CSVTemplateFile = "SELECT DISTINCT us.SensorUniqueName as SensorUniqueName, s.SensorLabel as SensorLabel
                        FROM UNITSENSORS us
                        LEFT JOIN SENSORS s
                        ON s.SensorUniqueName = us.SensorUniqueName
                        ORDER BY SensorUniqueName ASC";
    $CSVData = PDO_FetchAll($CSVTemplateFile);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=sensor-template.csv');

    $csvFile = fopen('php://output', 'w');

    //Add the headers
    fputcsv($csvFile, array('SensorUniqueName', 'SensorLabel'));  
    
    foreach($CSVData as $row) {
        
        fputcsv($csvFile, $row);  
    }  

    fclose($csvFile);

?>