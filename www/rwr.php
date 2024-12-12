<?php
    $selectedRWR = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedRWR = $_POST["selectedRWR"];
    }

    function polarToCartesian($centerX, $centerY, $radius, $angleInDegrees) {
        $angleInRadians = ($angleInDegrees - 90) * pi() / 180.0;
        return array(
            "x" => $centerX + ($radius * cos($angleInRadians)),
            "y" => $centerY + ($radius * sin($angleInRadians)),
        );
    }
    
    function describeArc($x, $y, $radius, $startAngle, $endAngle){
        $start = polarToCartesian($x, $y, $radius, $startAngle);
        $end = polarToCartesian($x, $y, $radius, $endAngle);
        $arcSweep = $endAngle - $startAngle <= 180 ? "0" : "1";
    
        return "M $x $y L $start[x] $start[y] A $radius $radius 0 $arcSweep 1 $end[x] $end[y] z";
    }

    function intToYesNo($intvalue) {
        if ($intvalue == 1) {
            return '<span class="yes-green">Yes</span>';
        }
        return '<span class="no-red">No</span>';
    }
    
    function convertLeft($convertValue) {
        return $convertValue + 270;
    }

    function convertRight($convertValue) {
        return $convertValue + 90;
    }

    function convertAbs($value) {
        return $value < 0 ? 360 - abs($value) : $value;
    }

    function restructureUnits($groupUnitsArray) {
        $returnArr = array();
        foreach($groupUnitsArray as $unit) {
            $returnArr[$unit['UnitType']][] = $unit['UnitLabel'];
        }
        return $returnArr;
    }

?>

<html data-bs-theme="dark">
    <?php
    include "./_head.php";

    $sectors =  PDO_FetchAll("SELECT * FROM RWR_RECEIVERS WHERE RWRUniqueFileName = :name AND Indicate = 1", array("name"=>$selectedRWR));
    $noIndicateSector = PDO_FetchAll("SELECT * FROM RWR_RECEIVERS WHERE RWRUniqueFileName = :name AND Indicate = 0", array("name"=>$selectedRWR));
    $RWR = PDO_FetchRow("SELECT * FROM RWR WHERE RWRUniqueName = :name", array("name"=>$selectedRWR));
    $RWRName = PDO_FetchRow("SELECT ifnull(ifnull(SensorLabel, Name), RWRUniqueName) as SensorLabel, RWRUniqueName FROM RWR LEFT JOIN SENSORS ON RWRUniqueName = SensorUniqueName WHERE RWRUniqueName = :name", array("name"=>$selectedRWR));
    // var_dump($RWR);
    $PageTitle = $RWRName['SensorLabel']." (".$RWRName['RWRUniqueName'].")";

    ?>
    <title>RWR Information - <?php echo $PageTitle; ?></title>

    <div class="container-fluid">
        <?php echo "<h2>".$PageTitle."</h2>";?>
        <ul class="nav nav-tabs" id="rwr-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="threed-tab" data-bs-toggle="tab" data-bs-target="#threed" type="button" role="tab" aria-controls="threed" aria-selected="false">RWR 3D Viewer</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="threat-tab" data-bs-toggle="tab" data-bs-target="#threat" type="button" role="tab" aria-controls="threat" aria-selected="false">Threat Identification</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="receiver-tab" data-bs-toggle="tab" data-bs-target="#receiverlist" type="button" role="tab" aria-controls="receiverlist" aria-selected="false">Receiver List</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="basic" role="tabpanel" aria-labelledby="basic-tab">

                <div class="row pt-3">
                    <div class="col-6">
                        <h3>RWR Band Detection</h3>
                        <table class="table table-bordered">
                            <tr>
                                <th>Band</th>
                                <th>Can RWR Detect?</th>
                            </tr>
                            <tr>
                                <th>Band C</th>
                                <td><?php echo intToYesNo($RWR['BandC']); ?></td>
                            </tr>
                            <tr>
                                <th>Band D</th>
                                <td><?php echo intToYesNo($RWR['BandD']); ?></td>
                            </tr>
                            <tr>
                                <th>Band E</th>
                                <td><?php echo intToYesNo($RWR['BandE']); ?></td>
                            </tr>
                            <tr>
                                <th>Band F</th>
                                <td><?php echo intToYesNo($RWR['BandF']); ?></td>
                            </tr>
                            <tr>
                                <th>Band G</th>
                                <td><?php echo intToYesNo($RWR['BandG']); ?></td>
                            </tr>
                            <tr>
                                <th>Band H</th>
                                <td><?php echo intToYesNo($RWR['BandH']); ?></td>
                            </tr>
                            <tr>
                                <th>Band I</th>
                                <td><?php echo intToYesNo($RWR['BandI']); ?></td>
                            </tr>
                            <tr>
                                <th>Band J</th>
                                <td><?php echo intToYesNo($RWR['BandJ']); ?></td>
                            </tr>
                            <tr>
                                <th>Band K</th>
                                <td><?php echo intToYesNo($RWR['BandK']); ?></td>
                            </tr>
                            <tr>
                                <th>Band L</th>
                                <td><?php echo intToYesNo($RWR['BandL']); ?></td>
                            </tr>
                            <tr>
                                <th>Band M</th>
                                <td><?php echo intToYesNo($RWR['BandM']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <h3>RWR Receivers</h3>
                        <p class="pb-2"> 
                            <span style="color:green">Green</span> = Digital RWR, detects the signal angle<br />
                            <span style="color:red">Red</span> = Sector RWR, no angle detection<br />
                            <span style="color:white">White/Transparent</span> = No indicated RWR coverage<br />
                            <span style="color:teal">Teal</span> = RWR detection sets off alarm only. Red/Green take priority
                        </p>
                        <div class="row pt-2">
                            <div class="col-6">
                                <h4>Top</h4>
                                <svg id="rwr-top" width="300" height="300" xmlns="http://www.w3.org/2000/svg" version="1.1">
                                    <!-- <circle cx="150" cy="150" r="30" fill="transparent" stroke="white" stroke-width="1"/> -->
                                    <circle cx="150" cy="150" r="110" fill="transparent" stroke="white" stroke-width="1"/>
                                    <text x="50%" y="5%" dominant-baseline="middle" text-anchor="middle" fill="white">Front</text>
                                    <text x="50%" y="95%" dominant-baseline="middle" text-anchor="middle" fill="white">Rear</text>
                                    <text x="50%" y="-5%" dominant-baseline="middle" text-anchor="middle" fill="white" transform="rotate(90)">Left</text>
                                    <text x="50%" y="-95%" dominant-baseline="middle" text-anchor="middle" fill="white" transform="rotate(90)">Right</text>

                                    <?php
                                    // Create paths for each sector
                                    foreach ($sectors as $key=>$sector) {
                                        $strokeColour = 'red';
                                        if ($sector['AngleFind'] == 1) {
                                            $strokeColour = 'green';
                                        }
                                        $start = $sector['Azimuth'] - ($sector['HorizontalWidth']/2);
                                        $end = $sector['Azimuth'] + ($sector['HorizontalWidth']/2);
                                        echo "<path id=\"sector-".$key."\" d=\"". describeArc(150, 150, 110, $start, $end) ."\"  fill=\"none\" stroke=\"".$strokeColour."\" stroke-width=\"1\" />";
                                    }

                                    foreach ($noIndicateSector as $key=>$sector) {
                                        $start = $sector['Azimuth'] - ($sector['HorizontalWidth']/2);
                                        $end = $sector['Azimuth'] + ($sector['HorizontalWidth']/2);

                                        $startVertical = $sector['Elevation'] - ($sector['VerticalWidth']/2);
                                        $endVertical = $sector['Elevation'] + ($sector['VerticalWidth']/2);

                                        if (abs($endVertical - $startVertical) == 180 && ($endVertical == 0 || $startVertical == 0)) {
                                            echo "<circle id=\"noindicate-$key\" cx=\"150\" cy=\"150\" r=\"120\" fill=\"transparent\" stroke=\"teal\" stroke-width=\"1\"/>";
                                        } else {
                                            echo "<path id=\"noindicate-".$key."\" d=\"". describeArc(150, 150, 120, $start, $end) ."\"  fill=\"none\" stroke=\"teal\" stroke-width=\"1\" />";
                                        }
                                    }


                                    ?>
                                </svg>
                            </div>
                            <div class="col-6">
                                <h4>Side</h4>
                                <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg" version="1.1">
                                    <circle cx="150" cy="150" r="110" fill="transparent" stroke="white" stroke-width="1"/>
                                    <text x="50%" y="-5%" dominant-baseline="middle" text-anchor="middle" fill="white" transform="rotate(90)">Front</text>
                                    <text x="50%" y="-95%" dominant-baseline="middle" text-anchor="middle" fill="white" transform="rotate(90)">Rear</text>
                                    <text x="50%" y="5%" dominant-baseline="middle" text-anchor="middle" fill="white">Top</text>
                                    <text x="50%" y="95%" dominant-baseline="middle" text-anchor="middle" fill="white">Bottom</text>
                                    <?php
                                        $maxVerticalFront = 0;
                                        $maxVerticalRear = 0;
                                        foreach ($sectors as $key=>$sector) {
                                            $strokeColour = 'red';
                                            if ($sector['AngleFind'] == 1) {
                                                $strokeColour = 'green';
                                            }
                                            $azimuth = convertAbs($sector['Azimuth']);
                                            $start = $azimuth - ($sector['HorizontalWidth']/2);
                                            $end = $azimuth + ($sector['HorizontalWidth']/2);

                                            $elevation = convertAbs($sector['Elevation']);
                                            $startVertical = $elevation - ($sector['VerticalWidth']/2);
                                            $endVertical = $elevation + ($sector['VerticalWidth']/2);
                                            
                                            $verticalPlusMinus = $sector['VerticalWidth']/2;
                                            if (($end-$start) < 0 || $end > 270 || $start < 90) {
                                                // FRONT SECTOR (closest to nose). Some RWRs have multiple sectors here, but unlikely they differ
                                                $maxVerticalFront = max($maxVerticalFront, $verticalPlusMinus);
                                                echo "<path d=\"". describeArc(150, 150, 110, convertLeft($startVertical), convertLeft($endVertical)) ."\"  fill=\"none\" stroke=\"".$strokeColour."\" stroke-width=\"1\" />";
                                            }
                                            if ($end > 90 || $start < 270) {
                                                $maxVerticalRear = max($maxVerticalRear, $verticalPlusMinus);
                                                // REAR SECTOR (closest to nose). Some RWRs have multiple sectors here, but unlikely they differ
                                                echo "<path d=\"". describeArc(150, 150, 110, convertRight($startVertical), convertRight($endVertical)) ."\"  fill=\"none\" stroke=\"".$strokeColour."\" stroke-width=\"1\" />";
                                            }
                                        }
                                        if ($maxVerticalFront > 0) {
                                            echo "<text x=\"85\" y=\"145\" class=\"small\" stroke=\"white\">&pm; $maxVerticalFront&deg;</text>";
                                        }
                                        if ($maxVerticalRear > 0) {
                                            echo "<text x=\"180\" y=\"145\" class=\"small\" stroke=\"white\">&pm; $maxVerticalRear&deg;</text>";
                                        }

                                        foreach ($noIndicateSector as $sector) {
                                            $elevation = convertAbs($sector['Elevation']);
                                            $startVertical = $elevation - ($sector['VerticalWidth']/2);
                                            $endVertical = $elevation + ($sector['VerticalWidth']/2);
                                            echo "<path d=\"". describeArc(150, 150, 120, convertLeft($startVertical), convertLeft($endVertical)) ."\"  fill=\"none\" stroke=\"teal\" stroke-width=\"1\" />";
                                        }
                                    ?>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h3>Additional RWR Parameters</h3>
                        <table class="table table-bordered">
                            <thead>
                                <th class="col-2">IFF</th>
                                <th class="col-2">Rangefinding</th>
                                <th class="col-2">Rangefinding Ranges (m)</th>
                                <th class="col-2">Target Tracking</th>
                                <th class="col-2">Number of Targets Tracked</th>
                                <th class="col-2">Range (m)</th>
                            </thead>
                            <tbody>
                                <tr>
                                <?php 
                                    echo '<td>'.intToYesNo($RWR['HasIFF']).'</td>';
                                    echo '<td>'.intToYesNo($RWR['HasRangefinder']).'</td>';
                                    echo '<td>'.$RWR['RangeFinderRangeMin'].'-'.$RWR['RangeFinderRangeMax'].'</td>';
                                    echo '<td>'.intToYesNo($RWR['HasTargetTrack']).'</td>';
                                    echo '<td>'.$RWR['NumTargetTrack'].'</td>';
                                    echo '<td>'.$RWR['Range'].'</td>';
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h3>Units with this RWR:</h3>
                        <?php
                            $unitsWithRWR = PDO_FetchAll("SELECT ifnull(UNITS.UnitLabel, UNITSENSORS.UnitUniqueName) as UnitLabel
                                                            FROM UNITSENSORS
                                                            LEFT JOIN UNITS
                                                            ON UNITS.UnitUniqueName = UNITSENSORS.UnitUniqueName
                                                            WHERE UNITSENSORS.SensorUniqueName = :name
                                                            ORDER BY UnitLabel ASC", array("name"=>$selectedRWR));
                        ?>
                        <ul>
                            <?php
                                foreach($unitsWithRWR as $unit) {
                                    echo "<li>".$unit['UnitLabel']."</li>";
                                }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="threed" role="tabpanel" aria-labelledby="threed-tab">
                <div class="container">
                    <fieldset class="form-group border p-2">
                        <legend class="w-auto">Colour Indicators</legend>
                        <p>
                            <span style="color:green">Green</span> = Digital RWR, detects the signal angle<br />
                            <span style="color:red">Red</span> = Sector RWR, no angle detection<br />
                            <span style="color:white">White/Transparent</span> = No indicated RWR coverage<br />
                            <span style="color:teal">Teal</span> = RWR detection sets off alarm only. Red/Green take priority
                        </p>
                    </fieldset>
                    <div class="btn-group" role="group" aria-label="3D Viewer Controls">
                        <button id="show3DRWR" class="btn btn-secondary">Toggle 3d RWR</button>

                        <?php 
                        if (sizeof($noIndicateSector) > 0) {
                            echo '<button id="togglelayer" class="d-none btn btn-secondary">Toggle non-indicated layer</button>';
                        }
                            
                        ?>
                    </div>
                </div>
                <div class="pt-4 pb-4 d-flex justify-content-center" id="sensor-viewer"></div>
            </div>
            <div class="tab-pane" id="threat" role="tabpanel" aria-labelledby="threat-tab">
                <?php
                // Threat data
                $threats = PDO_FetchAll("SELECT GRP.GroupName, ifnull(ifnull(S.SensorLabel, R.Name), R.RadarUniqueName) as RadarName, US.UnitUniqueName, ifnull(U.UnitLabel, US.UnitUniqueName) as UnitLabel, RM.RadarUniqueName, RT.Band as RadarBand,
                                max(RS.Track) as RadarTrack,  GRP.IsDirectionGenericGroup, GRP.IsPresenceGenericGroup, GRP.DirectionLabel, GRP.PresenceLabel, RTRN.Band as TransmitBand, RTRN.TransmitterType,
                                GRP.DetectLaunch, GRP.Launch, GRP.Track, GRP.Search, GRP.Priority, US.UnitType
                                FROM RWR_GROUPS as GRP
                                JOIN Radar R ON GRP.RadarName = R.Name
                                JOIN UNITSENSORS as US ON US.SensorUniqueName = R.RadarUniqueName
                                JOIN UNITS as U ON U.UnitUniqueName = US.UnitUniqueName
                                JOIN SENSORS as S ON S.SensorUniqueName = R.RadarUniqueName
                                LEFT JOIN RADAR_TRANSMITTER as RTRN ON RTRN.RadarUniqueName = R.RadarUniqueName
                                JOIN RADAR_MODES as RM ON RM.RadarUniqueName = S.SensorUniqueName
                                JOIN RADAR_TRANSIVERS as RT ON RT.RadarUniqueName = RM.RadarUniqueName
                                AND RT.TransiverUniqueName = RM.TransiverUniqueName
                                JOIN RADAR_SIGNALS as RS ON RS.RadarUniqueName = RM.RadarUniqueName
                                AND RS.SignalUniqueName = RM.SignalUniqueName
                                WHERE GRP.RWRUniqueFileName = :name
                                AND RS.AircraftAsTarget <> 0
                                AND RT.VisibilityType = 'radar'
                                GROUP BY GRP.GroupName, US.UnitUniqueName, RT.Band, RS.RadarUniqueName
                                ORDER BY  GRP.IsDirectionGenericGroup, GRP.GroupName, RadarTrack, TransmitBand ASC", array("name"=>$selectedRWR));

                $rwrThreats = array();
                $rwrGroup = array();
                $groupUnits = array();
                $groupRadars = array();
                $trackedRadars = array();
                $directionLabelsList = array();
                $presenceLabelsList = array();
                $trackedUnitRadar = array();
                foreach ($threats as $threat) {
                    $combinedUnitRadar = $threat['UnitUniqueName'].$threat['RadarUniqueName'];
                    if(($threat['IsDirectionGenericGroup'] == 1) && in_array($combinedUnitRadar, $trackedUnitRadar)) {
                        //Threat has already been added to another category, move on
                        continue;
                    }
                    $track = $RWR['DetectTrack'] == 1 ? intToYesNo($threat['RadarTrack']) : ($threat['Track'] == 1 ? intToYesNo($threat['RadarTrack']) : intToYesNo(0));

                    $transmitBandAlpha = $threat['TransmitBand'];
                    if ($transmitBandAlpha != null) {
                        $TransmitBand = "Band$transmitBandAlpha";
                        $launch = $RWR['DetectLaunch'] == 1 ? (intToYesNo($RWR[$TransmitBand])) : ($threat['DetectLaunch'] == 1 ? (intToYesNo($RWR[$TransmitBand])) : intToYesNo(0));
                    } else {
                        $launch = intToYesNo(0);
                    }
                    
                    if ($threat['Launch'] == 1 && $launch == intToYesNo(0)) {
                        // if the threat has "launchmode" configured (ie: CW illum), but doesn't actually transmit the signal, don't include.
                        // For things like APG-66 (F-16A) in the Groups that doesn't actually have any way to CW guide
                        continue;
                    }
                    if (array_key_exists('GroupName', $rwrGroup) && (
                            $rwrGroup['GroupName'] != $threat['GroupName'] || 
                            ($rwrGroup['GroupName'] == $threat['GroupName'] && $rwrGroup['DetectTracking'] != $track) || 
                            ($rwrGroup['GroupName'] == $threat['GroupName'] && $rwrGroup['DetectLaunch'] != $launch))) {
                        // Group has changed. Add to Array and reset.
                        // SORT the Units
                        $groupUnits = array_values(array_unique($groupUnits, SORT_REGULAR));
                        array_multisort(array_column($groupUnits, 'UnitType'), SORT_ASC,
                                        array_column($groupUnits, 'UnitLabel'), SORT_ASC,
                                        $groupUnits);
                        $rwrGroup['UnitList'] = restructureUnits($groupUnits);
                        $rwrGroup['RadarList'] = array_values(array_unique($groupRadars));
                        $rwrThreats[] = $rwrGroup;
                        $rwrGroup = array();
                        $groupUnits = array();
                        $groupRadars = array();
                    }
                    $unique = $threat['UnitLabel'];
                    //Check the radar band. If not valid, next
                    if (array_key_exists('RadarBand', $threat)) {
                        $BandAlpha = $threat['RadarBand'];
                        $RadarBand = "Band$BandAlpha";
                        if ($RWR[$RadarBand] == 1) {
                            // Band is valid for detection, continue
                            $rwrGroup["GroupName"] = $threat['GroupName'];
                            $rwrGroup["IsDirectionGenericGroup"] = $threat['IsDirectionGenericGroup'];
                            $rwrGroup["IsPresenceGenericGroup"] = $threat['IsPresenceGenericGroup'];
                            $rwrGroup["DirectionLabel"] = $threat['DirectionLabel'];
                            $directionLabelsList[] = $threat['DirectionLabel'];
                            $rwrGroup["PresenceLabel"] = $threat['PresenceLabel'];
                            $presenceLabelsList[] = $threat['PresenceLabel'];
                            $rwrGroup["DetectTracking"] = $track;
                            $rwrGroup["DetectLaunch"] = $launch;
                            $UntData = array();
                            $UntData['UnitLabel'] = $threat['UnitLabel'];
                            $UntData['UnitType'] = $threat['UnitType'];
                            $groupUnits[] = $UntData;
                            $groupRadars[] = $threat['RadarName'];
                            $trackedRadars[] = $threat['RadarUniqueName'];
                            $trackedUnitRadar[] = $combinedUnitRadar;
                        }
                    }
                }
                if (array_key_exists('GroupName', $rwrGroup)) {
                    // Add last group to Array
                    $groupUnits = array_values(array_unique($groupUnits, SORT_REGULAR));
                    array_multisort(array_column($groupUnits, 'UnitType'), SORT_ASC,
                                    array_column($groupUnits, 'UnitLabel'), SORT_ASC,
                                    $groupUnits);
                    $rwrGroup['UnitList'] = restructureUnits($groupUnits);
                    $rwrGroup['RadarList'] = array_values(array_unique($groupRadars));
                    $rwrThreats[] = $rwrGroup;
                    $rwrGroup = array();
                    $groupUnits = array();
                    $groupRadars = array();
                }
                
                // Get all untracked radars. These will NOT have a direction or presence label, but will still trigger the RWR pings/locks
                $untrackedQuery = "SELECT ifnull(ifnull(S.SensorLabel, R.Name), R.RadarUniqueName) as RadarName, US.UnitUniqueName, ifnull(U.UnitLabel, US.UnitUniqueName) as UnitLabel, RM.RadarUniqueName, RT.Band as RadarBand,
                                    max(RS.Track) as RadarTrack, RTRN.Band as TransmitBand, RTRN.TransmitterType, US.UnitType
                                    FROM Radar R
                                    JOIN UNITSENSORS as US ON US.SensorUniqueName = R.RadarUniqueName
                                    JOIN UNITS as U ON U.UnitUniqueName = US.UnitUniqueName
                                    JOIN SENSORS as S ON S.SensorUniqueName = R.RadarUniqueName
                                    LEFT JOIN RADAR_TRANSMITTER as RTRN ON RTRN.RadarUniqueName = R.RadarUniqueName
                                    JOIN RADAR_MODES as RM ON RM.RadarUniqueName = S.SensorUniqueName
                                    JOIN RADAR_TRANSIVERS as RT ON RT.RadarUniqueName = RM.RadarUniqueName
                                    AND RT.TransiverUniqueName = RM.TransiverUniqueName
                                    JOIN RADAR_SIGNALS as RS ON RS.RadarUniqueName = RM.RadarUniqueName
                                    AND RS.SignalUniqueName = RM.SignalUniqueName
                                    WHERE R.RadarUniqueName NOT  IN (\"".implode('","', array_values(array_unique($trackedRadars)))."\")
                                    AND RS.AircraftAsTarget <> 0
                                    AND RT.VisibilityType = 'radar'
                                    GROUP BY RS.RadarUniqueName, US.UnitUniqueName, RT.Band
                                    ORDER BY RadarTrack, TransmitBand ASC";
                $untracked = PDO_FetchAll($untrackedQuery);

                foreach ($untracked as $unknown) {
                    $track = $RWR['DetectTrack'] == 1 ? intToYesNo($unknown['RadarTrack']) : intToYesNo(0);
                    
                    $transmitBandAlpha = $unknown['TransmitBand'];
                    if ($transmitBandAlpha != null) {
                        $TransmitBand = "Band$transmitBandAlpha";
                        $launch = $RWR['DetectLaunch'] == 1 ? intToYesNo($RWR[$TransmitBand]) : intToYesNo(0);
                    } else {
                        $launch = intToYesNo(0);
                    }
                    // check if launch or tracking detect value has changed for unidentified radar
                    if (array_key_exists('GroupName', $rwrGroup) && ($rwrGroup['DetectTracking'] != $track ||  $rwrGroup['DetectLaunch'] != $launch)) {
                        // Detection has changed. Add to Array and reset.
                        $groupUnits = array_values(array_unique($groupUnits, SORT_REGULAR));
                        array_multisort(array_column($groupUnits, 'UnitType'), SORT_ASC,
                                        array_column($groupUnits, 'UnitLabel'), SORT_ASC,
                                        $groupUnits);
                        $rwrGroup['UnitList'] = restructureUnits($groupUnits);
                        $rwrGroup['RadarList'] = array_values(array_unique($groupRadars));
                        $rwrThreats[] = $rwrGroup;
                        $rwrGroup = array();
                        $groupUnits = array();
                        $groupRadars = array();
                    }

                    $rwrGroup['GroupName'] = "Unknown";
                    $rwrGroup["DirectionLabel"] = "?";
                    $rwrGroup["PresenceLabel"] = "";
                    $rwrGroup['DetectTracking'] = $track;
                    $rwrGroup['DetectLaunch'] = $launch;
                        //Check the radar band. If not valid, next
                    if (array_key_exists('RadarBand', $unknown)) {
                        $BandAlpha = $unknown['RadarBand'];
                        $RadarBand = "Band$BandAlpha";
                        if ($RWR[$RadarBand] == 1) {
                            // Band is valid for detection, continue
                            $UntData = array();
                            $UntData['UnitLabel'] = $unknown['UnitLabel'];
                            $UntData['UnitType'] = $unknown['UnitType'];
                            $groupUnits[] = $UntData;
                            $groupRadars[] = $unknown['RadarName'];
                        }
                    }
                }

                if (sizeof($groupUnits) > 0) {
                    // Add unknown radars
                    $groupUnits = array_values(array_unique($groupUnits, SORT_REGULAR));
                    array_multisort(array_column($groupUnits, 'UnitType'), SORT_ASC,
                                    array_column($groupUnits, 'UnitLabel'), SORT_ASC,
                                    $groupUnits);
                    $rwrGroup['UnitList'] = restructureUnits($groupUnits);
                    $rwrGroup['RadarList'] = array_values(array_unique($groupRadars));
                    $rwrThreats[] = $rwrGroup;
                }



                ?>
                <table class="table table-bordered">
                    <thead style="position: sticky;top: 0" >
                        <tr>
                            <th>Group Name</th>
                            <th>Radars</th>
                            <?php
                                $uniqueDirectionLabels = array_unique($directionLabelsList);
                                $showDirectionLabels = sizeof($uniqueDirectionLabels)> 0 && $uniqueDirectionLabels[0] != '' ;
                                echo ($showDirectionLabels ? '<th>Direction Label</th>' : '');

                                $uniquePresenceLabels = array_unique($presenceLabelsList);
                                $showPresenceLabels = sizeof($uniquePresenceLabels)> 0 && $uniquePresenceLabels[0] != '';
                                echo ($showPresenceLabels ? '<th>Presence Label</th>' : '');
                            ?>
                            <th>Detect Tracking</th>
                            <th>Detect Launch</th>
                            <th>Units - Note: Some Units have multiple radars, and may appear multiple times</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($rwrThreats as $rwrThreat) {
                                $units = $rwrThreat['UnitList'];
                                $radars = $rwrThreat['RadarList'];
                                echo "<tr>";
                                echo "<th>".$rwrThreat['GroupName']."</th>";
                                echo "<td>".implode(', ', $radars)."</td>";
                                echo ($showDirectionLabels ? "<td>".$rwrThreat['DirectionLabel']."</td>" : '');
                                echo ($showPresenceLabels ? "<td>".$rwrThreat['PresenceLabel']."</td>" : '');
                                echo "<th>".$rwrThreat['DetectTracking']."</th>";
                                echo "<th>".$rwrThreat['DetectLaunch']."</th>";
                                echo "<td>";
                                foreach($units as $unittype=>$unitlist) {
                                    echo "<p><strong>";
                                    if ($unittype == 'AIR') {
                                        echo "Air: ";
                                    } else if ($unittype == 'GRND') {
                                        echo "Ground: ";
                                    } else if ($unittype == 'NVL') {
                                        echo "Naval: ";
                                    }
                                    echo "</strong>";
                                    echo implode(', ', $unitlist);
                                    echo "</p>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="tab-pane" id="receiverlist" role="tabpanel" aria-labelledby="receiver-tab">
                <div class="row pt-3">
                    <h3>Full Receiver Sector List</h3>
                    <table class="table table-bordered">
                        <thead style="position: sticky;top: 0" >
                            <tr>
                                <th class="col-2">Azimuth (0 = Nose, Negative = Left, Positive = Right, 180/-180 = Rear)</th>
                                <th class="col-2">Horizontal Width (Centred on Azimuth)</th>
                                <th class="col-2">Elevation (0 = Horizon, Positive = Up, Negative = Down)</th>
                                <th class="col-2">Elevation Width (Centred on Elevation)</th>
                                <th class="col-2">Signal Angle Finder (No = Sector RWR)</th>
                                <th class="col-2">Indicator (Shows on RWR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($sectors as $key=>$sector) {
                                    echo "<tr>";
                                    echo "<td>".$sector['Azimuth']."</td>";
                                    echo "<td>".$sector['HorizontalWidth']."</td>";
                                    echo "<td>".$sector['Elevation']."</td>";
                                    echo "<td>".$sector['VerticalWidth']."</td>";
                                    echo "<td>".intToYesNo($sector['AngleFind'])."</td>";
                                    echo "<td><span class=\"yes-green\">Yes</span></td>";
                                    echo "</tr>";
                                }
                                foreach ($noIndicateSector as $key=>$sector) {
                                    echo "<tr>";
                                    echo "<td>".$sector['Azimuth']."</td>";
                                    echo "<td>".$sector['HorizontalWidth']."</td>";
                                    echo "<td>".$sector['Elevation']."</td>";
                                    echo "<td>".$sector['VerticalWidth']."</td>";
                                    echo "<td>".intToYesNo($sector['AngleFind'] )."</td>";
                                    echo "<td><span class=\"no-red\">No</span></td>";
                                    echo "</tr>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="importmap">
        {
            "imports": {
            "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.js",
            "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.170.0/examples/jsm/"
            }
        }
    </script>
    <script type="module">
        import * as THREE from 'three';
        import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
        import { OBJLoader } from 'three/addons/loaders/OBJLoader.js';

        let renderer, scene, camera, controls, requestAnimationId;

        function init() {
            const width = window.innerWidth * 0.9, height = window.innerHeight * 0.70;

            // Renderer
            renderer = new THREE.WebGLRenderer( { antialias: true,  alpha: true  } );
            renderer.setSize( width, height );
            renderer.setPixelRatio( window.devicePixelRatio );
            document.getElementById('sensor-viewer').appendChild( renderer.domElement );
            
            // Scene
            scene = new THREE.Scene();

            // Camera
            camera = new THREE.PerspectiveCamera( 75, width / height, 1, 10000 );
            camera.position.set( 15, 15, 15 );
            camera.layers.enable(1);
            camera.layers.enable(2);

            // Controls
            controls = new OrbitControls( camera, renderer.domElement );
            
            // Axes
            // scene.add( new THREE.AxesHelper( 20 ) );

            // Load the plane :)
            const loader = new OBJLoader();
            loader.load(
                'jet.obj',
                function ( object ) {
                    object.scale.setScalar(0.003);
                    object.rotation.x = -(Math.PI / 2)
                    object.rotation.z = -(Math.PI / 2)
                    object.position.x = 0.15
                    object.position.y = -0.25
                    object.children[0].material = new THREE.MeshBasicMaterial({ color: 0xAA00AA} );
                    
                    scene.add(object)
                }
            );
            
            <?php
                function convertHorizontal($degrees) {
                    return deg2rad($degrees);
                }

                function convertVerticalStart($degrees) {
                    return ((90-$degrees)/180)*pi();
                }

                function convertVerticalLen($degrees) {
                    return ($degrees/180)*pi();
                }

                $horizontalSectors = 8;
                $verticalSectors = 8;
                if (sizeof($sectors) < 4) {
                    $horizontalSectors = 16;
                }

                foreach ($sectors as $key=>$sector) {
                    $colour = '0xFF0000';
                    if ($sector['AngleFind'] == 1) {
                        $colour = '0x00FF00';
                    }
                    $start = $sector['Azimuth'] - ($sector['HorizontalWidth']/2);
                    $end = $sector['Azimuth'] + ($sector['HorizontalWidth']/2);

                    $startVertical = $sector['Elevation'] - ($sector['VerticalWidth']/2);
                    $endVertical = $sector['Elevation'] + ($sector['VerticalWidth']/2);
                    
                    $startH = convertHorizontal($start);
                    $lengthH = convertHorizontal(abs($sector['HorizontalWidth']));

                    $startV = convertVerticalStart(abs($startVertical));
                    $lengthV = convertVerticalLen(abs($sector['VerticalWidth']));

                    echo "
                        const material$key = new THREE.MeshBasicMaterial({ color: $colour} );
                        const geometryinner$key = new THREE.SphereGeometry( 3, $horizontalSectors, $verticalSectors, $startH, $lengthH, $startV, $lengthV );
                        const geometryouter$key = new THREE.SphereGeometry( 10, $horizontalSectors, $verticalSectors, $startH, $lengthH, $startV, $lengthV );

                        const maxPos$key = geometryouter$key.attributes.position.array;
                        const minPos$key = geometryinner$key.attributes.position.array;
                        const boundarypoints$key = [
                            new THREE.Vector3().fromArray(maxPos$key, 0), //TopRight
                            new THREE.Vector3().fromArray(minPos$key, 0),
                            new THREE.Vector3().fromArray(maxPos$key, $horizontalSectors*3), //TopLeft
                            new THREE.Vector3().fromArray(minPos$key, $horizontalSectors*3),
                            new THREE.Vector3().fromArray(maxPos$key, maxPos$key.length -($horizontalSectors+1)*3), //Bottom Right
                            new THREE.Vector3().fromArray(minPos$key, minPos$key.length -($horizontalSectors+1)*3),
                            new THREE.Vector3().fromArray(maxPos$key, maxPos$key.length - 3), //Bottom left
                            new THREE.Vector3().fromArray(minPos$key, minPos$key.length - 3),
                        ];
                        const boundarylineGeometry$key = new THREE.BufferGeometry().setFromPoints(boundarypoints$key);
                        scene.add( new THREE.LineSegments( boundarylineGeometry$key, new THREE.LineBasicMaterial({ color: $colour }) ) );


                        const meshinner$key = new THREE.Mesh( geometryinner$key, material$key );
                        const meshouter$key = new THREE.Mesh( geometryouter$key, material$key );
                        scene.add(meshinner$key);
                        scene.add(meshouter$key);
                        meshinner$key.material.wireframe = true;
                        meshouter$key.material.wireframe = true;
                        
                        meshinner$key.layers.set(1);
                        meshouter$key.layers.set(1);
                        
                        ";
                        // Add additional lines to make sector RWR more visible.
                        echo "
                        const additionalpoints$key = [";
                        for ($i=1; $i<$horizontalSectors+1; $i++) {
                            echo "
                                new THREE.Vector3().fromArray(maxPos$key, $i*3),
                                new THREE.Vector3().fromArray(minPos$key, $i*3),
                                new THREE.Vector3().fromArray(maxPos$key, maxPos$key.length -($i+1)*3),
                                new THREE.Vector3().fromArray(minPos$key, minPos$key.length -($i+1)*3),
                            ";
                        }
                        echo "
                        ];
                        const additionalGeometry$key = new THREE.BufferGeometry().setFromPoints(additionalpoints$key);
                        const linesegments$key = new THREE.LineSegments( additionalGeometry$key, new THREE.LineBasicMaterial({ color: $colour }) );
                        scene.add(linesegments$key);

                        linesegments$key.layers.set(1);
                        
                        ";
                }
                
                foreach ($noIndicateSector as $key=>$sector) {
                    $colour = '0x008080';
                    $start = $sector['Azimuth'] - ($sector['HorizontalWidth']/2);
                    $end = $sector['Azimuth'] + ($sector['HorizontalWidth']/2);

                    $startVertical = $sector['Elevation'] - ($sector['VerticalWidth']/2);
                    $endVertical = $sector['Elevation'] + ($sector['VerticalWidth']/2);
                    
                    $startH = convertHorizontal($start);
                    $lengthH = convertHorizontal(abs($sector['HorizontalWidth']));

                    $startV = convertVerticalStart(abs($startVertical));
                    $lengthV = convertVerticalLen(abs($sector['VerticalWidth']));

                    echo "
                        const indmaterial$key = new THREE.MeshBasicMaterial({ color: $colour} );
                        const indgeometryinner$key = new THREE.SphereGeometry( 3, 20, 20, $startH, $lengthH, $startV, $lengthV );
                        const indgeometryouter$key = new THREE.SphereGeometry( 11, 20, 20, $startH, $lengthH, $startV, $lengthV );

                        const indmaxPos$key = indgeometryouter$key.attributes.position.array;
                        const indminPos$key = indgeometryinner$key.attributes.position.array;
                        const indpoints$key = [
                            new THREE.Vector3().fromArray(indmaxPos$key, 0), //TopRight
                            new THREE.Vector3().fromArray(indminPos$key, 0),
                            new THREE.Vector3().fromArray(indmaxPos$key, $horizontalSectors*3), //TopLeft
                            new THREE.Vector3().fromArray(indminPos$key, $horizontalSectors*3),
                            new THREE.Vector3().fromArray(indmaxPos$key, indmaxPos$key.length - ($horizontalSectors+1)*3), //Bottom Right
                            new THREE.Vector3().fromArray(indminPos$key, indminPos$key.length - ($horizontalSectors+1)*3),
                            new THREE.Vector3().fromArray(indmaxPos$key, indmaxPos$key.length - 3), //Bottom left
                            new THREE.Vector3().fromArray(indminPos$key, indminPos$key.length - 3),
                        ];
                        const indlineGeometry$key = new THREE.BufferGeometry().setFromPoints(indpoints$key);
                        const indlinesegments$key = new THREE.LineSegments( indlineGeometry$key, new THREE.LineBasicMaterial({ color: $colour, linewidth: 3}) );
                        scene.add(indlinesegments$key);


                        const indmeshinner$key = new THREE.Mesh( indgeometryinner$key, indmaterial$key );
                        const indmeshouter$key = new THREE.Mesh( indgeometryouter$key, indmaterial$key );
                        scene.add(indmeshinner$key);
                        scene.add(indmeshouter$key);
                        indmeshinner$key.material.wireframe = true;
                        indmeshouter$key.material.wireframe = true;
                        
                        indmeshinner$key.layers.set(3);
                        indmeshouter$key.layers.set(2);

                        indlinesegments$key.layers.set(2);
                        ";
                }

            ?>
        }
        
        function animate() {

            requestAnimationId = requestAnimationFrame( animate );
            
            //controls.update();

            renderer.render( scene, camera );

        }
        function sceneTraverse (obj, fn) {

            if (!obj) return

                fn(obj)

            if (obj.children && obj.children.length > 0) {
                obj.children.forEach(o => {
                    sceneTraverse(o, fn)
                })
            }
        }

        function stopRender () {
            if (requestAnimationId) {
                cancelAnimationFrame(requestAnimationId)
            }
            requestAnimationId = undefined
        }
        function dispose () {
            // dispose geometries and materials in scene
            sceneTraverse(scene, o => {

                if (o.geometry) {
                    o.geometry.dispose()					                 
                }

                if (o.material) {
                    if (o.material.length) {
                        for (let i = 0; i < o.material.length; ++i) {
                            o.material[i].dispose()							
                        }
                    }
                    else {
                        o.material.dispose()						
                    }
                }
            })	


            renderer && renderer.renderLists.dispose()
            renderer && renderer.dispose() 
            stopRender()

            renderer.domElement.parentElement.removeChild(renderer.domElement)			

            scene = null
            camera = null
            controls = null
            renderer = null  
            threedLoaded = false;
            }

        function load3dRWR() {
            init();
            animate();
            threedLoaded = true;
        }

        function triggerVisibilityNoIndicate() {
            camera.layers.toggle(2);
        }

        var show3d = document.getElementById('show3DRWR');
        show3d.addEventListener('click', function() {
            console.log('clicked');
            if (threedLoaded) {
                $('#togglelayer').toggleClass('d-none');
                dispose();
            } else {
                load3dRWR();
                $('#togglelayer').toggleClass('d-none');
            }
        })

        let threedLoaded = false;
        // register toggle event
        var toggleButton = document.getElementById('togglelayer');
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                triggerVisibilityNoIndicate();
            })
        }

        var triggerTabList = [].slice.call(document.querySelectorAll('#rwr-tabs a'))
            triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })

        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            if (threedLoaded) {
                dispose();
            }
        })
    </script>
</html>