<?php
$versions = PDO_FetchRow("SELECT * FROM VERSIONDATA");


?>
<div class="fixed-bottom">
    <div class="container">
        <div class="row">
            <h2>Version Information</h2>
            <p>This is the current version of the game where the files have been loaded from. If you wish to update the database with new files, please go to the <a href="./update-database.php">update database</a> page.</p>
            <div class="col">
                <table class="table table-bordered text-center">
                    <thead>
                        <th scope="col" class="col-3">RADAR</th>
                        <th scope="col" class="col-3">MLWS</th>
                        <th scope="col" class="col-3">RWR</th>
                        <th scope="col" class="col-3">UNITSENSORS</th>
                    </thead>
                    <tbody>
                        <tr scope="row">
                        <?php 
                            echo '<td>'.$versions['RADAR'].'</td>';
                            echo '<td>'.$versions['MLWS'].'</td>';
                            echo '<td>'.$versions['RWR'].'</td>';
                            echo '<td>'.$versions['UNITSENSORS'].'</td>';
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>