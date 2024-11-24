<?php
    include "./_pdo.php";
    $rwr_db = "./radar_rwr_database.db";
    PDO_Connect("sqlite:$rwr_db");

    if (isset($_POST['versionNumber'])) {
        $versionNumber = $_POST["versionNumber"];
    }

    $sensorURL = "https://api.dm.wt.flareflo.dev/files/aces.vromfs.bin/gamedata/sensors?version=$versionNumber";


    $sensorZip = './data_raw/sensors.zip';

    $sensors = file_get_contents($sensorURL);
    file_put_contents($sensorZip, $sensors);
    $zip = new ZipArchive;
    $res = $zip->open($sensorZip);
    if ($res === TRUE) {
        $zip->extractTo('./data_raw/sensors/');
        $zip->close();
    }
    unlink($sensorZip);

    $UIThreats = array(
        "hud/rwr_track"                => "TRACK",
        "hud/rwr_launch"               => "LAUNCH",
        "hud/rwr_threat_pulse"         => "PULSE",
        "hud/rwr_threat_mprf"          => "MPRF",
        "hud/rwr_threat_hprf"          => "HPRF",
        "hud/rwr_threat_pd"            => "PD",
        "hud/rwr_threat_cw"            => "CW",
        "hud/rwr_threat_cw_pd"         => "CW/PD",
        "hud/rwr_threat_tws"           => "TWS",
        "hud/rwr_threat_ai"            => "AI",
        "hud/rwr_threat_attacker"      => "ATK",
        "hud/rwr_threat_ai_pd"         => "AI PD",
        "hud/rwr_threat_ai_ro"         => "AI R/O",
        "hud/rwr_threat_aaa_ai"        => "AAA/AI",
        "hud/rwr_threat_aaa"           => "AAA",
        "hud/rwr_threat_sam"           => "SAM",
        "hud/rwr_threat_naval"         => "NVL",
        "hud/rwr_threat_air_defence"   => "A/D",
        "hud/rwr_threat_ai_track"      => "AI TRK",
        "hud/rwr_threat_aaa_track"     => "AAA TRK",
        "hud/rwr_threat_sam_track"     => "SAM TRK",
        "hud/rwr_threat_sam_launch"    => "SAM LNC",
        "hud/rwr_threat_low"           => "LO",
        "hud/rwr_threat_mid"           => "MED",
        "hud/rwr_threat_high"          => "HI",
        "hud/rwr_threat_sam_low"       => "SAM LO",
        "hud/rwr_threat_sam_mid"       => "SAM MID",
        "hud/rwr_threat_sam_high"      => "SAM HI",
        "hud/rwr_threat_aaa_low"       => "AAA LO",
        "hud/rwr_threat_aaa_ai_mid"    => "AAA/AI",
        "hud/rwr_threat_ai_high"       => "AI HI",
        "hud/rwr_threat_nike_hercules" => "N-H",
        "hud/rwr_threat_hawk"          => "HAWK",
        "hud/rwr_threat_sa_75"         => "SA75",
        "hud/rwr_threat_s_75"          => "S75",
        "hud/rwr_threat_s_125"         => "S125"
    );

    $BandMapping = array('A', 'B','C','D','E','F','G','H','I','J','K','L','M');
    
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

    // Define the structure classes
    class RWR {
        public $Name;
        public $RWRUniqueName;
        public $BandA;
        public $BandB;
        public $BandC;
        public $BandD;
        public $BandE;
        public $BandF;
        public $BandG;
        public $BandH;
        public $BandI;
        public $BandJ;
        public $BandK;
        public $BandL;
        public $BandM;
        public $HasIFF;
        public $HasRangefinder;
        public $HasTargetTrack;
        public $NumTargetTrack;
        public $DetectTrack;
        public $DetectLaunch;
        public $Power;
        public $Range;
        public $NewTargetTime;
        public $TargetHoldTime;
        public $SignalHoldTime;
        public $RangeFinderMin;
        public $RangeFinderMax;
    }

    class RWR_RECEIVER {
        public $RWRUniqueName;
        public $Azimuth;
        public $HorizontalWidth;
        public $Elevation;
        public $VerticalWidth;
        public $AngleFind;
        public $Indicate;
    }

    class RWR_GROUP {
        public $RWRUniqueName;
        public $GroupName;
        public $IsDirectionGenericGroup;
        public $IsPresenceGenericGroup;
        public $DirectionLabel;
        public $PresenceLabel;
        public $RadarName;
        public $DetectLaunch;
        public $Launch;
        public $Track;
        public $Search;
        public $Priority;
    }

    class MLWS {
        public $Name;
        public $MLWSUniqueName;
        public $Range;
        public $BandA;
        public $BandB;
        public $BandC;
        public $AutomaticFlares;
        public $FlareSeriesInterval;
        public $FlareInterval;
        public $NumberFlares;
        public $ClosureRateMin;
        public $AngularRateMax;
        public $SignalHoldTime;
    }

    class MLWS_RECEIVER {
        public $MLWSUniqueName;
        public $Azimuth;
        public $HorizontalWidth;
        public $Elevation;
        public $VerticalWidth;
        public $AngleFind;
    }

    class RADAR_MODES {
        public $RadarUniqueName;
        public $Transiver;
        public $Signal;
    }

    class RADAR_SIGNAL {
        public $RadarUniqueName;
        public $SignalUniqueName;
        public $DynamicRange;
        public $AircraftAsTarget;
        public $GroundAsTarget;
        public $IFF;
        public $RangeFinder;
        public $DopplerSpeedFinder;
        public $AnglesFinder;
        public $GroundClutter;
        public $Track;
        public $MinimumRange;
        public $MaximumRange;
        public $DistanceWidth;
        public $AbsDopplerSpeed;
        public $MainBeamDopplerSpeed;
        public $AngularAccuracy;
        public $DistanceAccuracy;
        public $ZeroDopplerNotchWidth;
        public $MainBeamNotchwidth;
        public $ShowBScope;
        public $ShowCScope;
        public $DopplerSpeedMin;
        public $DopplerSpeedMax;
        public $DopplerSpeedSignalWidthMin;
        public $DopplerSpeedWidth;
    }

    class RADAR_TRANSIVER {
        public $RadarUniqueName;
        public $TransiverUniqueName;
        public $Band;
        public $Power;
        public $PulsePower;
        public $PulseWidth;
        public $PRF;
        public $SideLobesAttenuation;
        public $RCS;
        public $Range;
        public $Range1;
        public $RangeMax;
        public $MultipathEffect;
        public $AntennaAzimuthAngleHalfSens;
        public $AntennaAzumuthSideLobeSens;
        public $AntennaElevationAngleHalfSens;
        public $AntennaElevationSideLobeSens;
        public $VisibilityType;
    }

    class RADAR_TRANSMITTER {
        public $RadarUniqueName;
        public $Power;
        public $Band;
        public $AntennaHalfSens;
        public $AntennaSideLobeSens;
        public $TransmitterType;
    }


    // DELETE ALL EXISTING SENSOR DATA
    PDO_Execute("DELETE FROM MLWS_RECEIVERS");
    PDO_Execute("DELETE FROM MLWS");
    PDO_Execute("DELETE FROM RWR_RECEIVERS");
    PDO_Execute("DELETE FROM RWR_GROUPS");
    PDO_Execute("DELETE FROM RWR");
    PDO_Execute("DELETE FROM RADAR");
    PDO_Execute("DELETE FROM RADAR_TRANSIVERS");
    PDO_Execute("DELETE FROM RADAR_TRANSMITTER");
    PDO_Execute("DELETE FROM RADAR_SIGNALS");
    PDO_Execute("DELETE FROM RADAR_MODES");

    
    // Now get the new files
    $SensorPath = './data_raw/sensors';
    $Files = scanAllDir($SensorPath);
    foreach($Files as $sensorFile) {
        // $sensorFile = 'swd_bow_21.blk';
        $sensorJson = json_decode(file_get_contents("$SensorPath/$sensorFile"));
        $sensorUniqueName = str_replace("naval".DIRECTORY_SEPARATOR, "", str_replace(".blk", "", $sensorFile));
        if (isset($sensorJson->type)) {
            switch ($sensorJson->type) {
                case 'rwr':
                    processRWR($sensorJson, $sensorUniqueName);
                    break;
                case 'radar':
                    processRadar($sensorJson, $sensorUniqueName);
                    break;
                case 'mlws':
                    processMLWS($sensorJson, $sensorUniqueName);
                    break;
                default:
                    // NOT configured, skip
            }
        }
    }
    removeDir($SensorPath);

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

    function processRadar($sensorObject, $uniqueName) {
        global $BandMapping;
        $modeList = computeRadarModes($sensorObject, $uniqueName);
        $signalList = array();
        $transiverList = array();
        $band = null;

        if (isset($sensorObject->signals)) {
            $signals = $sensorObject->signals;
            foreach($signals as $key=>$value) {
                $signalData = defineRadarSignal($value, $key, $uniqueName);
                $signalList[] = $signalData;
            }
            if (sizeof($signalList) == 1 && $signalList[0]->Track != 1 && isset($sensorObject->scanPatterns->track->track)) {
                $signalList[0]->Track = checkAndConvertBoolean($sensorObject->scanPatterns->track, 'track');
            }
        }
        if (isset($sensorObject->transivers)) {
            $transivers = $sensorObject->transivers;
            foreach($transivers as $key=>$value) {
                $transiverData = defineRadarTransiver($value, $key, $uniqueName);
                $transiverList[] = $transiverData;
            }
        }
        // get the band of radar - NO radar has a radar transiver in a different band.
        foreach ($transiverList as $transiver) {
            if ($transiver->VisibilityType == 'radar') {
                $band = $transiver->Band;
                break;
            }
        }
        $transmitterList = array();

        // Get any transmitters that might exist.
        if (isset($sensorObject->illuminationTransmitter)) {
            // Illumination transmitter = CW Air radar.
            $transmitter = $sensorObject->illuminationTransmitter;
            $transmitterDefinition = new RADAR_TRANSMITTER();
            $transmitterDefinition->RadarUniqueName = $uniqueName;
            $transmitterDefinition->Power = checkAndConvertFloat($transmitter, 'power', true);
            // if there is NOT a band configured on the transmitter, use the radar transiver band.
            $transmitterDefinition->Band = $band;
            if (isset($transmitter->band)) {
                $transmitterDefinition->Band = $transmitter->band < 0 ? '' : $BandMapping[$transmitter->band];
            }
            $transmitterDefinition->AntennaHalfSens = checkAndConvertFloat($transmitter->antenna, 'angleHalfSens', true);
            $transmitterDefinition->AntennaSideLobeSens = checkAndConvertFloat($transmitter->antenna, 'sideLobesSensitivity', true);
            $transmitterDefinition->TransmitterType = 'Illumination';
            $transmitterList[] = $transmitterDefinition;
        } else if (isset($sensorObject->transmitters) && isset($sensorObject->transmitters->rc)) {
            //radio control transmitter for SAMs, can be picked up even if radar is not picked up.
            $transmitter = $sensorObject->transmitters->rc;
            $transmitterDefinition = new RADAR_TRANSMITTER();
            $transmitterDefinition->RadarUniqueName = $uniqueName;
            $transmitterDefinition->Power = checkAndConvertFloat($transmitter, 'power', true);
            // if there is NOT a band configured on the transmitter, use the radar transiver band.
            $transmitterDefinition->Band = $band;
            if (isset($transmitter->band)) {
                $transmitterDefinition->Band = $transmitter->band < 0 ? '' : $BandMapping[$transmitter->band];
            }
            $transmitterDefinition->AntennaHalfSens = checkAndConvertFloat($transmitter->antenna, 'angleHalfSens', true);
            $transmitterDefinition->AntennaSideLobeSens = checkAndConvertFloat($transmitter->antenna, 'sideLobesSensitivity', true);
            $transmitterDefinition->TransmitterType = 'RadioControl';
            $transmitterList[] = $transmitterDefinition;
        }


        if (isset($sensorObject->name)) {
            insertRadarData($sensorObject->name, $uniqueName, $modeList, $signalList, $transiverList, $transmitterList);
        } else {
            insertRadarData($uniqueName, $uniqueName, $modeList, $signalList, $transiverList, $transmitterList);
        }
    }

    function defineRadarTransiver($transiver, $transiverKey, $uniqueName) {
        global $BandMapping;
        $transiverDefinition = new RADAR_TRANSIVER();
        $transiverDefinition->RadarUniqueName = $uniqueName;
        $transiverDefinition->TransiverUniqueName = $transiverKey;
        $transiverDefinition->Band = $transiver->band < 0 ? '' : $BandMapping[$transiver->band];
        $transiverDefinition->Power = checkAndConvertFloat($transiver, 'power', true);
        $transiverDefinition->PulsePower = checkAndConvertFloat($transiver, 'pulsePower', true);
        $transiverDefinition->PulseWidth = checkAndConvertFloat($transiver, 'pulseWidth', true);
        $transiverDefinition->PRF = checkAndConvertFloat($transiver, 'prf', true);
        $transiverDefinition->SideLobesAttenuation = checkAndConvertFloat($transiver, 'sideLobesAttenuation', true);
        $transiverDefinition->RCS = checkAndConvertFloat($transiver, 'rcs', true);
        $transiverDefinition->Range = checkAndConvertFloat($transiver, 'range', true);
        $transiverDefinition->Range1 = checkAndConvertFloat($transiver, 'range1', true);
        $transiverDefinition->RangeMax = checkAndConvertFloat($transiver, 'rangeMax', true);
        $transiverDefinition->MultipathEffect = isset($transiver->MultipathEffect) ? json_encode($transiver->MultipathEffect) : '';
        $antenna = $transiver->antenna;
        $transiverDefinition->AntennaAzimuthAngleHalfSens = checkAndConvertFloat($antenna, 'angleHalfSens', true);
        $transiverDefinition->AntennaAzumuthSideLobeSens = checkAndConvertFloat($antenna, 'sideLobesSensitivity', true);
        $transiverDefinition->AntennaElevationAngleHalfSens = checkAndConvertFloat($antenna, 'angleHalfSens', true);
        $transiverDefinition->AntennaElevationSideLobeSens = checkAndConvertFloat($antenna, 'sideLobesSensitivity', true);
        if (isset($antenna->azimuth)) {
            $transiverDefinition->AntennaAzimuthAngleHalfSens = checkAndConvertFloat($antenna->azimuth, 'angleHalfSens', true);
            $transiverDefinition->AntennaAzumuthSideLobeSens = checkAndConvertFloat($antenna->azimuth, 'sideLobesSensitivity', true);    
        }
        if (isset($antenna->elevation)) {
            $transiverDefinition->AntennaAzimuthAngleHalfSens = checkAndConvertFloat($antenna->elevation, 'angleHalfSens', true);
            $transiverDefinition->AntennaAzumuthSideLobeSens = checkAndConvertFloat($antenna->elevation, 'sideLobesSensitivity', true);  
        }
        $transiverDefinition->VisibilityType = isset($transiver->visibilityType) ? $transiver->visibilityType : 'radar';
        return $transiverDefinition;
    }

    function defineRadarSignal($signal, $signalKey, $uniqueRadarName) {
        $signalDefinition = new RADAR_SIGNAL();
        $signalDefinition->RadarUniqueName = $uniqueRadarName;
        $signalDefinition->SignalUniqueName = $signalKey;
        $signalDefinition->DynamicRange = isset($signal->dynamicRange) ? json_encode($signal->dynamicRange) : '';
        $signalDefinition->AircraftAsTarget = checkAndConvertBoolean($signal, 'aircraftAsTarget', true);
        $signalDefinition->GroundAsTarget = checkAndConvertBoolean($signal, 'groundVehiclesAsTarget', true);
        $signalDefinition->IFF = checkAndConvertBoolean($signal, 'friendFoeId');
        $signalDefinition->RangeFinder = checkAndConvertBoolean($signal, 'rangeFinder');
        $signalDefinition->DopplerSpeedFinder = checkAndConvertBoolean($signal, 'dopplerSpeedFinder');
        $signalDefinition->AnglesFinder = checkAndConvertBoolean($signal, 'anglesFinder');
        $signalDefinition->GroundClutter = checkAndConvertBoolean($signal, 'groundClutter');
        $signalDefinition->Track = checkAndConvertBoolean($signal, 'track');
        if (isset($signal->distance->presents) && $signal->distance->presents == true) {
            $signalDefinition->MinimumRange = checkAndConvertFloat($signal->distance, 'minValue', true);
            $signalDefinition->MaximumRange = checkAndConvertFloat($signal->distance, 'maxValue', true);
            $signalDefinition->DistanceWidth = checkAndConvertFloat($signal->distance, 'width', true);
        }
        $signalDefinition->AbsDopplerSpeed = checkAndConvertBoolean($signal, 'absDopplerSpeed');
        $signalDefinition->MainBeamDopplerSpeed = checkAndConvertBoolean($signal, 'mainBeamDopplerSpeed');
        $signalDefinition->AngularAccuracy = checkAndConvertFloat($signal, 'angularAccuracy', true);
        $signalDefinition->DistanceAccuracy = checkAndConvertFloat($signal, 'distanceAccuracy', true);
        $signalDefinition->ZeroDopplerNotchWidth = checkAndConvertFloat($signal, 'zeroDopplerNotchWidth', true);
        $signalDefinition->MainBeamNotchwidth = checkAndConvertFloat($signal, 'mainBeamNotchWidth', true);
        $signalDefinition->ShowBScope = checkAndConvertBoolean($signal, 'showBScope', true);
        $signalDefinition->ShowCScope = checkAndConvertBoolean($signal, 'showCScope', true);
        if (isset($signal->dopplerSpeed->presents) && $signal->dopplerSpeed->presents == true) {
            $signalDefinition->DopplerSpeedMin = checkAndConvertFloat($signal->dopplerSpeed, 'minValue', true);
            $signalDefinition->DopplerSpeedMax = checkAndConvertFloat($signal->dopplerSpeed, 'maxValue', true);
            $signalDefinition->DopplerSpeedSignalWidthMin = checkAndConvertFloat($signal->dopplerSpeed, 'signalWidthMin', true);
            $signalDefinition->DopplerSpeedWidth = checkAndConvertFloat($signal->dopplerSpeed, 'width', true);
        }
        return $signalDefinition;
    }

    function computeRadarModes($object, $uniqueName) {
        $fsms = $object->fsms;
        $sensorLinks = array();
        $finalLinks = array();
        $fullStates = new stdClass();
        // $main = $fsms->main;
        // $actionTemplates = $main->actionsTemplates;
        // $transitions = $main->transitions;
        foreach($fsms as $key=>$fsm) {
            $fsmkeys = get_object_vars($fsm);
            if (array_key_exists('transitions', $fsmkeys)) {
                $transitions = $fsm->transitions;
                if (array_key_exists('actionsTemplates', $fsmkeys)) {
                    $actionTemplates = $fsm->actionsTemplates;
                    foreach($transitions as $transitionKey=>$transition) {
                        if (is_array($transition)) {
                            $transitions->$transitionKey = mergeActionTemplates($transition[0], $actionTemplates, get_object_vars($actionTemplates));
                        } else {
                            $transitions->$transitionKey = mergeActionTemplates($transition, $actionTemplates, get_object_vars($actionTemplates));
                        }
                    }
                    $sensorLinks = array_merge($sensorLinks, parseNodesForSignalTransiver($actionTemplates, $uniqueName));
                }

                $result = computeFullStateData($transitions, $fsms);
                $fullStates->$key = $result;
                // $sensorLinks = array_merge($sensorLinks, getActiveModesFromFullStateData($result));
            }
            $sensorLinks = array_merge($sensorLinks, parseNodesForSignalTransiver($fullStates, $uniqueName));
                
        }
        $finalLinks = array_values(array_unique($sensorLinks, SORT_REGULAR));
        return $finalLinks;
    }

    function getActiveModesFromFullStateData($stateData) {
        $sensorLinks = array();
        foreach($stateData as $modeKey=>$modeData) {
            $objectKeys = get_object_vars($modeData);
            if (!array_key_exists('actions', $objectKeys)) {
                continue;
            }
            $actions = $modeData->actions;
            $actionKeys = get_object_vars($actions);
            if (!array_key_exists('setEnabled', $actionKeys)) {
                continue;
            }
            if ($actions->setEnabled->value == true) {
                // ACTIVE mode. Lets check for the other keys
                if(array_key_exists('setTransiver', $actionKeys) && array_key_exists('setSignal', $actionKeys)) {
                    // WOOHOO WE HAVE SOMETHING
                    $radarMode = new RADAR_MODES();
                    $radarMode->Transiver = $actions->setTransiver->transiver;
                    $radarMode->Signal = $actions->setSignal->signal;
                    // $radarMode->ModeName = 'DEFAULT';
                    // if (array_key_exists('setModeName', $actionKeys)) {
                    //     $radarMode->ModeName = $actions->setModeName->name;
                    // }
                    $sensorLinks[] = $radarMode;
                }
            }
        }
        return $sensorLinks;
    }

    function computeFullStateData($transitions, $fsms) {
        $fullStates = new stdClass();
        $fsmskeys = get_object_vars($fsms);
        foreach($transitions as $key=>$value) {
            $objectKeys = get_object_vars($value);
            // CHECK for doCustomActionTemplate.
            // IF set, get ACTION from: $fsms->($action->setFsmActive(active:true)->fsm)
            // Will be in relevant 

            if (array_key_exists('stateFrom', $objectKeys)) {
                $from = $value->stateFrom;
                if (!is_array($from)) {
                    if (array_key_exists($from, $fsmskeys)) {
                        $previousStateFrom = $fsms->$from;
                        $previousStateKeys = get_object_vars($previousStateFrom);
                        if (array_key_exists('transition', $previousStateKeys)) {
                            $previousStateActions = $fsms->$from->transition;
                            $value = mergeStateData($previousStateActions, $value);
                        } else {
                            $fsms->$from = new stdClass();
                            $fsms->$from->transition = $value;
                            $fsmskeys = get_object_vars($fsms);
                        }
                    } else {
                        $fsms->$from = new stdClass();
                        $fsms->$from->transition = $value;
                        $fsmskeys = get_object_vars($fsms);
                    }   
                }
            }

            if (array_key_exists('stateTo', $objectKeys)) {
                $to = $value->stateTo;
                if (array_key_exists($to, $fsmskeys)) {
                    $fsms->$to->transition = $value;
                } else {
                    $fsms->$to = new stdClass();
                    $fsms->$to->transition = $value;
                    $fsmskeys = get_object_vars($fsms);
                }
            }
            $fullStates->$key = $value;
        }
        // echo json_encode($fullStates);
        return $fullStates;
    }

    function mergeStateData($previousState, $currentState) {
        if (!is_array($currentState)) {
            $currentStateKeys = get_object_vars($currentState);
        } else {
            $currentStateKeys = array_keys($currentState);
        }
        foreach($previousState as $key=>$value) {
            if (is_object($value)) {
                if (array_key_exists($key, $currentStateKeys)) {
                    $currentState->$key = mergeStateData($value, $currentState->$key);
                } else {
                    $currentState->$key = $value;
                }
            } else if (!array_key_exists($key, $currentStateKeys) && gettype($value) == gettype($currentState)) {
                $currentState->$key = $value;
            }
        }
        return $currentState;
    }

    function mergeActionTemplates($object, $actionTemplates, $actionTemplateKeys) {
        // var_dump($object);
        foreach($object as $key=>$value) {
            if(is_object($value)) {
                if(empty((array)json_decode(json_encode($value))) && array_key_exists($key, $actionTemplateKeys)) {
                    //Value at key is an EMPTY object, and likely an actionTemplate
                    $object = (object) array_merge((array) $object, (array) $actionTemplates->$key);
                    unset($object->$key);
                } else {
                    $object->$key = mergeActionTemplates($value, $actionTemplates, $actionTemplateKeys);
                }
            }
        }
        return $object;
    }

    function parseNodesForSignalTransiver($object, $uniqueName) {
        $validModes = array();
        foreach($object as $key=>$value) {
            if(is_object($value)) {
                $objectKeys = get_object_vars($value);
                if (array_key_exists('setTransiver', $objectKeys) && array_key_exists('setSignal', $objectKeys)) {
                    $radarMode = new RADAR_MODES();
                    $radarMode->RadarUniqueName = $uniqueName;
                    $radarMode->Transiver = $value->setTransiver->transiver;
                    $radarMode->Signal = $value->setSignal->signal;
                    // $radarMode->ModeName = 'DEFAULT';
                    $validModes[] = $radarMode;
                } else if(is_object($value)) {
                    $continueChecking = parseNodesForSignalTransiver($value, $uniqueName);
                    $validModes = array_merge($validModes, $continueChecking);
                }
            }
        }
        return $validModes;
    }

    function processMLWS($sensorObject, $uniqueName) {

        // get the core mlws definition
        $mlwsData = defineMLWS($sensorObject, $uniqueName);

        // Get the receiver data
        $receiverList = array();
        if (isset($sensorObject->receivers->receiver)) {
            $receivers = $sensorObject->receivers->receiver;
            if (is_array($receivers)) {
                foreach($receivers as $receiver) {
                    $receiverData = processMLWSReceivers($receiver, $uniqueName);
                    $receiverList[] = $receiverData;
                }
            } else {
                $receiverList[] = processMLWSReceivers($receivers, $uniqueName);
            }
        }
        
        insertMLWSData($mlwsData, $receiverList);
    }

    function processRWR($sensorObject, $uniqueName) {

        // Get the core RWR definition
        $rwrData = defineRWR($sensorObject, $uniqueName);

        // Get the receiver data
        $receiverList = array();
        if (isset($sensorObject->receivers->receiver)) {
            $receivers = $sensorObject->receivers->receiver;
            if (is_array($receivers)) {
                foreach($receivers as $receiver) {
                    $receiverData = processRWRReceivers($receiver, $uniqueName);
                    $receiverList[] = $receiverData;
                }
            } else {
                $receiverList[] = processRWRReceivers($receivers, $uniqueName);
            }
        }

        $groupList = array();
        // Process the groups
        if (isset($sensorObject->groups->group)) {
            $groups = $sensorObject->groups->group;
            $targetDirectionGroups = isset($sensorObject->targetsDirectionGroups) ? $sensorObject->targetsDirectionGroups->targetsDirectionGroup : array();
            $targetPresenceGroups = isset($sensorObject->targetsPresenceGroups) ? $sensorObject->targetsPresenceGroups->targetsPresenceGroup : array();

            if (is_array($groups)) {
                foreach($groups as $group) {
                    $groupData = processRWRGroups($group, $targetDirectionGroups, $targetPresenceGroups, $uniqueName);
                    $groupList = array_merge($groupList, $groupData);
                }
            }
            
        }

        insertRWRData($rwrData, $receiverList, $groupList);
    }

    function defineMLWS($sensorObject, $uniqueName) {
        $mlwsName = $sensorObject->name;
        if (isset($sensorObject->{'override:name'})) {
            $mlwsName = $sensorObject->{'override:name'};
        }

        $mlwsDefinition = new MLWS();
        $mlwsDefinition->Name = $mlwsName;
        $mlwsDefinition->MLWSUniqueName = $uniqueName;
        $mlwsDefinition->Range = checkAndConvertFloat($sensorObject, 'range');
        $mlwsDefinition->BandA = checkAndConvertBoolean($sensorObject, 'band0');
        $mlwsDefinition->BandB = checkAndConvertBoolean($sensorObject, 'band1');
        $mlwsDefinition->BandC = checkAndConvertBoolean($sensorObject, 'band2');
        $mlwsDefinition->AutomaticFlares = checkAndConvertBoolean($sensorObject, 'automaticFlares');
        $mlwsDefinition->FlareSeriesInterval = checkAndConvertFloat($sensorObject, 'flaresSeriesInterval', true);
        $mlwsDefinition->FlareInterval = checkAndConvertFloat($sensorObject, 'flaresInterval', true);
        $mlwsDefinition->NumberFlares = checkAndConvertInteger($sensorObject, 'trackedTargetsMax', true);
        $mlwsDefinition->ClosureRateMin = checkAndConvertFloat($sensorObject, 'closureRateMin', true);
        $mlwsDefinition->AngularRateMax = checkAndConvertFloat($sensorObject, 'angularRateMax', true);;
        $mlwsDefinition->SignalHoldTime = checkAndConvertFloat($sensorObject, 'signalHoldTime', true);;
        return $mlwsDefinition;
    }

    function processMLWSReceivers($receiverData, $uniqueSensorName) {
        $receiverDefinition = new MLWS_RECEIVER();
        $receiverDefinition->MLWSUniqueName = $uniqueSensorName;
        $receiverDefinition->Azimuth = checkAndConvertFloat($receiverData, 'azimuth');
        $receiverDefinition->HorizontalWidth = checkAndConvertFloat($receiverData, 'azimuthWidth');
        $receiverDefinition->Elevation = checkAndConvertFloat($receiverData, 'elevation');
        $receiverDefinition->VerticalWidth = checkAndConvertFloat($receiverData, 'elevationWidth');
        $receiverDefinition->AngleFind = checkAndConvertBoolean($receiverData, 'angleFinder');
        return $receiverDefinition;
    }

    function defineRWR($sensorObject, $uniqueName) {
        $rwrName = $sensorObject->name;
        if (isset($sensorObject->{'override:name'})) {
            $rwrName = $sensorObject->{'override:name'};
        }

        $rwrDefinition = new RWR();
        $rwrDefinition->Name = $rwrName;
        $rwrDefinition->RWRUniqueName = $uniqueName;
        $rwrDefinition->BandA = checkAndConvertBoolean($sensorObject, 'band0');
        $rwrDefinition->BandB = checkAndConvertBoolean($sensorObject, 'band1');
        $rwrDefinition->BandC = checkAndConvertBoolean($sensorObject, 'band2');
        $rwrDefinition->BandD = checkAndConvertBoolean($sensorObject, 'band3');
        $rwrDefinition->BandE = checkAndConvertBoolean($sensorObject, 'band4');
        $rwrDefinition->BandF = checkAndConvertBoolean($sensorObject, 'band5');
        $rwrDefinition->BandG = checkAndConvertBoolean($sensorObject, 'band6');
        $rwrDefinition->BandH = checkAndConvertBoolean($sensorObject, 'band7');
        $rwrDefinition->BandI = checkAndConvertBoolean($sensorObject, 'band8');
        $rwrDefinition->BandJ = checkAndConvertBoolean($sensorObject, 'band9');
        $rwrDefinition->BandK = checkAndConvertBoolean($sensorObject, 'band10');
        $rwrDefinition->BandL = checkAndConvertBoolean($sensorObject, 'band11');
        $rwrDefinition->BandM = checkAndConvertBoolean($sensorObject, 'band12');
        $rwrDefinition->HasIFF = checkAndConvertBoolean($sensorObject, 'friendFoeId');
        $rwrDefinition->HasRangefinder = checkAndConvertBoolean($sensorObject, 'targetRangeFinder');
        if ($rwrDefinition->HasRangefinder == 1) {
            if(isset($sensorObject->targetRange)) {
                $ranges = $sensorObject->targetRange;
                sort($ranges, SORT_NUMERIC);
                $rwrDefinition->RangeFinderMin = $ranges[0];
                $rwrDefinition->RangeFinderMax = $ranges[1];
            }
        }
        $rwrDefinition->HasTargetTrack = checkAndConvertBoolean($sensorObject, 'targetTracking');
        $rwrDefinition->NumTargetTrack = checkAndConvertInteger($sensorObject, 'trackedTargetsMax', true);
        $rwrDefinition->DetectTrack = checkAndConvertBoolean($sensorObject, 'detectTracking');
        $rwrDefinition->DetectLaunch = checkAndConvertBoolean($sensorObject, 'detectLaunch');
        $rwrDefinition->Power = checkAndConvertFloat($sensorObject, 'power');
        $rwrDefinition->Range = checkAndConvertFloat($sensorObject, 'range');
        $rwrDefinition->NewTargetTime = checkAndConvertFloat($sensorObject, 'newTargetHoldTime', true);
        $rwrDefinition->TargetHoldTime = checkAndConvertFloat($sensorObject, 'targetHoldTime', true);
        $rwrDefinition->SignalHoldTime = checkAndConvertFloat($sensorObject, 'signalHoldTime', true);
        return $rwrDefinition;
    }

    function processRWRReceivers($receiverData, $uniqueSensorName) {
        $receiverDefinition = new RWR_RECEIVER();
        $receiverDefinition->RWRUniqueName = $uniqueSensorName;
        $receiverDefinition->Azimuth = checkAndConvertFloat($receiverData, 'azimuth');
        $receiverDefinition->HorizontalWidth = checkAndConvertFloat($receiverData, 'azimuthWidth');
        $receiverDefinition->Elevation = checkAndConvertFloat($receiverData, 'elevation');
        $receiverDefinition->VerticalWidth = checkAndConvertFloat($receiverData, 'elevationWidth');
        $receiverDefinition->AngleFind = checkAndConvertBoolean($receiverData, 'angleFinder');
        $receiverDefinition->Indicate = isset($receiverData->indicate) ? ($receiverData->indicate ? 1: 0) : 1;
        return $receiverDefinition;
    }

    function processRWRGroups($group, $directionGroups, $presenceGroups, $uniqueSensorName) {
        $groupReturn = array();
        if (isset($group->type)) {
            $groupDefinition = new RWR_GROUP();
            $groupDefinition->RWRUniqueName = $uniqueSensorName;
            $groupDefinition->GroupName = $group->name;

            // Check the size of direction groups. If not set, skip direction labels
            if (sizeof($directionGroups) > 0) {
                $directionGroupLabelData = getGroupLabel($group->name, $directionGroups, $uniqueSensorName);
                $groupDefinition->IsDirectionGenericGroup = $directionGroupLabelData->IsGeneric;
                $groupDefinition->DirectionLabel = $directionGroupLabelData->LabelValue;
            }
            // Check the size of presence groups. If not set, skip presence labels
            if (sizeof($presenceGroups) > 0) {
                $presenceGroupLabelData = getGroupLabel($group->name, $presenceGroups, $uniqueSensorName);
                $groupDefinition->IsPresenceGenericGroup = $presenceGroupLabelData->IsGeneric;
                $groupDefinition->PresenceLabel = $presenceGroupLabelData->LabelValue;
            }
            $groupDefinition->DetectLaunch = checkAndConvertBoolean($group, 'detectLaunch', true);
            $groupDefinition->Launch = checkAndConvertBoolean($group, 'launch', true);
            $groupDefinition->Track = checkAndConvertBoolean($group, 'track', true);
            $groupDefinition->Search = checkAndConvertBoolean($group, 'search', true);
            $groupDefinition->Priority = checkAndConvertBoolean($group, 'priority');
            if (is_array($group->type)) {
                // Array of types - add each radar as its own entry
                foreach($group->type as $radar) {
                    $groupDefinition->RadarName = $radar;
                    $groupReturn[] = clone $groupDefinition;
                }
            } else {
                $groupDefinition->RadarName = $group->type;
                $groupReturn[] = $groupDefinition;
            }
            // var_dump($group->type, is_array($group->type));
        }
        return $groupReturn;
    }

    function getGroupLabel($groupName, $labelTable, $rwrName) {
        global $UIThreats;
        //return associative array containing the label, and true/false for generic. These will be overridden if a value is returned
        $arrayToReturn = new stdClass();
        $arrayToReturn->LabelValue = "?";
        $arrayToReturn->IsGeneric = 1;
        foreach($labelTable as $row) {
            if (isset($row->group)) {
                $groups = $row->group;
                if (is_array($groups)) {
                    foreach($groups as $group) {
                        if ($group == $groupName) {
                            $labelText = $row->text;
                            $arrayToReturn->IsGeneric = 0;
                            $arrayToReturn->LabelValue = $labelText;
            
                            // is it generic? If so, grab the generic label value and then set generic
                            if (array_key_exists($labelText, $UIThreats)) {
                                $arrayToReturn->LabelValue = $UIThreats[$labelText];
                                $arrayToReturn->IsGeneric = 1;
                            }
                        }
                    }
                } else if ($groups == $groupName) {
                    // Key was found, lets continue
                    $labelText = $row->text;
                    $arrayToReturn->IsGeneric = 0;
                    $arrayToReturn->LabelValue = $labelText;

                    // is it generic? If so, grab the generic label value and then set generic
                    if (array_key_exists($labelText, $UIThreats)) {
                        $arrayToReturn->LabelValue = $UIThreats[$labelText];
                        $arrayToReturn->IsGeneric = 1;
                    }
                }
            }
        }

        return $arrayToReturn;
    }

    // this function is required because gaijin is big dumb, and has mutltiple entries for the same groups for some inexplicable reason that ALSO contain the same Radar. Identifies unique using a combination of RWR, Group, and Radar - merging any dupes
    function deDupeGroups($groups) {
        $groupObject = new stdClass();
        foreach($groups as $group) {
            $combinedName = $group->RWRUniqueName . $group->GroupName . $group->RadarName;
            if (isset($groupObject->$combinedName)) {
                $prevgroup = $groupObject->$combinedName;
                $merged = (object) array_merge((array) $prevgroup, (array) $group);
                $groupObject->$combinedName = $merged;
            } else {
                $groupObject->$combinedName = $group;
            }
        }
        return array_values((array) $groupObject);
    }

    function insertRWRData($rwr, $receivers, $groups) {
        global $versionNumber;

        $rwrValues = "('".implode("','",(array) $rwr)."')";
        $rwrInsert = "INSERT INTO RWR (Name, RWRUniqueName, BandA, BandB, BandC, BandD, BandE, BandF, BandG, BandH, BandI, BandJ, BandK, BandL, BandM, HasIFF, HasRangefinder, HasTargetTrack, NumTargetTrack, DetectTrack, DetectLaunch, Power, Range, NewTargetTime, TargetHoldTime, SignalHoldTime, RangeFinderRangeMin, RangeFinderRangeMax)
                        VALUES $rwrValues";
        PDO_Execute($rwrInsert);

        if (sizeof($receivers) > 0) {
            $receiverValuesArray = array();
            foreach($receivers as $receiver) {
                $receiverValuesArray[] = "('".implode("','",(array) $receiver)."')";
            }
            $receiverValues = implode(",", $receiverValuesArray);
            $receiverInsert = "INSERT INTO RWR_RECEIVERS (RWRUniqueFileName, Azimuth, HorizontalWidth, Elevation, VerticalWidth, AngleFind, Indicate)
                                VALUES $receiverValues";
            PDO_Execute($receiverInsert);
        }

        if (sizeof($groups) > 0) {
            $groupValuesArray = array();
            foreach(deDupeGroups(array_unique($groups, SORT_REGULAR)) as $group) {
                $groupValuesArray[] = "('".implode("','",(array) $group)."')";
            }
            $groupValues = implode(",", $groupValuesArray);
            $groupInsert = "INSERT INTO RWR_GROUPS (RWRUniqueFileName, GroupName, IsDirectionGenericGroup, IsPresenceGenericGroup, DirectionLabel, PresenceLabel, RadarName, DetectLaunch, Launch, Track, Search, Priority)
                                VALUES $groupValues";
            PDO_Execute($groupInsert);
        }

        // SET RWR Version to versionNumber
        PDO_Execute("UPDATE VERSIONDATA SET RWR='$versionNumber'");

    }

    function insertMLWSData($mlws, $receivers) {
        global $versionNumber;

        $mlwsValues = "('".implode("','",(array) $mlws)."')";
        $mlwsInsert = "INSERT INTO MLWS (Name, MLWSUniqueName, Range, BandA, BandB, BandC, AutomaticFlares, FlareSeriesInterval, FlareInterval, NumberFlares, ClosureRateMin, AngularRateMax, SignalHoldTime)
                        VALUES $mlwsValues";
        PDO_Execute($mlwsInsert);

        if (sizeof($receivers) > 0) {
            $receiverValuesArray = array();
            foreach($receivers as $receiver) {
                $receiverValuesArray[] = "('".implode("','",(array) $receiver)."')";
            }
            $receiverValues = implode(",", $receiverValuesArray);
            $receiverInsert = "INSERT INTO MLWS_RECEIVERS (MLWSUniqueFileName, Azimuth, HorizontalWidth, Elevation, VerticalWidth, AngleFind)
                                VALUES $receiverValues";
            PDO_Execute($receiverInsert);
        }

        // SET MLWS Version to versionNumber
        PDO_Execute("UPDATE VERSIONDATA SET MLWS='$versionNumber'");

    }

    function insertRadarData($radarName, $radarUniqueName, $modes, $signals, $transivers, $transmitters) {
        global $versionNumber;

        $RadarInsert = "INSERT INTO RADAR (Name, RadarUniqueName) VALUES ('$radarName', '$radarUniqueName')";
        PDO_EXECUTE($RadarInsert);

        
        if (sizeof($transmitters) > 0) {
            $transmitterValuesArray = array();
            foreach($transmitters as $transmitter) {
                $transmitterValuesArray[] = "('".implode("','",(array) $transmitter)."')";
            }
            $transmitterValues = implode(",", $transmitterValuesArray);
            $transmitterInsert = "INSERT INTO RADAR_TRANSMITTER (RadarUniqueName, Power, Band, AntennaHalfSens, AntennaSideLobeSens, TransmitterType)
                            VALUES $transmitterValues";
            PDO_EXECUTE($transmitterInsert);
        }

        if (sizeof($modes) > 0) {
            $modeValuesArray = array();
            foreach($modes as $mode) {
                $modeValuesArray[] = "('".implode("','",(array) $mode)."')";
            }
            $modeValues = implode(",", $modeValuesArray);
            $modeInsert = "INSERT INTO RADAR_MODES (RadarUniqueName, TransiverUniqueName, SignalUniqueName)
                            VALUES $modeValues";
            PDO_EXECUTE($modeInsert);
        }

        if (sizeof($signals) > 0) {
            $signalValuesArray = array();
            foreach($signals as $signal) {
                $signalValuesArray[] = "('".implode("','",(array) $signal)."')";
            }
            $signalValues = implode(",", $signalValuesArray);
            $signalInsert = "INSERT INTO RADAR_SIGNALS (RadarUniqueName, SignalUniqueName, DynamicRange, AircraftAsTarget, GroundAsTarget, IFF, RangeFinder, DopplerSpeedFinder, AnglesFinder, GroundClutter, Track, MinimumRange, MaximumRange, DistanceWidth, AbsDopplerSpeed, MainBeamDopplerSpeed, AngularAccuracy, DistanceAccuracy, ZeroDopplerNotchWidth, MainBeamNotchwidth, ShowBScope, ShowCScope, DopplerSpeedMin, DopplerSpeedMax, DopplerSpeedSignalWidthMin, DopplerSpeedWidth)
                                VALUES $signalValues";
            PDO_EXECUTE($signalInsert);
        }

        if (sizeof($transivers) > 0) {
            $transiverValuesArray = array();
            foreach($transivers as $transiver) {
                $transiverValuesArray[] = "('".implode("','",(array) $transiver)."')";
            }
            $transiverValues = implode(",", $transiverValuesArray);
            $transiverInsert = "INSERT INTO RADAR_TRANSIVERS (RadarUniqueName, TransiverUniqueName, Band, Power, PulsePower, PulseWidth, PRF, SideLobesAttenuation, RCS, Range, Range1, RangeMax, MultipathEffect, AntennaAzimuthAngleHalfSens, AntennaAzumuthSideLobeSens, AntennaElevationAngleHalfSens, AntennaElevationSideLobeSens, VisibilityType)
                                VALUES $transiverValues";
            PDO_EXECUTE($transiverInsert);
        }

        // SET RADAR Version to versionNumber
        PDO_Execute("UPDATE VERSIONDATA SET RADAR='$versionNumber'");

    }

    function checkAndConvertBoolean($object, $key, $returnNull = false) {
        return isset($object->$key) && $object->$key ? 1: ($returnNull ? null : 0);
    }

    function checkAndConvertInteger($object, $key, $returnNull = false) {
        return isset($object->$key) ? $object->$key: ($returnNull ? null : 0);
    }

    function checkAndConvertFloat($object, $key, $returnNull = false) {
        return isset($object->$key) ? $object->$key: ($returnNull ? null : 0.0);
    }
?>