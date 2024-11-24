<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");
    
    
    $CSVTemplateFile = "SELECT DISTINCT ifnull(ifnull(rw.RWRUniqueName, rd.RadarUniqueName), ml.MLWSUniqueName) as SensorUniqueName, s.SensorLabel as SensorLabel
                        FROM RWR rw
                        FULL JOIN Radar rd ON rd.RadarUniqueName = rw.RWRUniqueName
                        FULL JOIN MLWS ml ON ml.MLWSUniqueName = rw.RWRUniqueName
                        LEFT JOIN Sensors s ON s.SensorUniqueName = rw.RWRUniqueName OR s.SensorUniqueName = rd.RadarUniqueName OR s.SensorUniqueName = ml.MLWSUniqueName
                        ORDER BY SensorUniqueName ASC";
    $CSVData = PDO_FetchAll($CSVTemplateFile);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=sensor-full-template.csv');

    $csvFile = fopen('php://output', 'w');

    //Add the headers
    fputcsv($csvFile, array('SensorUniqueName', 'SensorLabel'));  
    
    foreach($CSVData as $row) {
        
        fputcsv($csvFile, $row);  
    }  

    fclose($csvFile);

?>