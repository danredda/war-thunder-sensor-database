<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");
    
    
    $CSVTemplateFile = "SELECT DISTINCT us.UnitUniqueName as UnitUniqueName, un.UnitLabel as UnitLabel
                        FROM UNITSENSORS us
                        LEFT JOIN UNITS un
                        ON un.UnitUniqueName = us.UnitUniqueName
                        ORDER BY UnitUniqueName ASC";
    $CSVData = PDO_FetchAll($CSVTemplateFile);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=units-template.csv');

    $csvFile = fopen('php://output', 'w');

    //Add the headers
    fputcsv($csvFile, array('UnitUniqueName', 'UnitLabel'));  
    
    foreach($CSVData as $row) {
        
        fputcsv($csvFile, $row);  
    }  

    fclose($csvFile);

?>