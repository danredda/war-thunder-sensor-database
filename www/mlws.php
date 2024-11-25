<?php
    $selectedMLWS = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedMLWS = $_POST["selectedMLWS"];
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

    $sectors =  PDO_FetchAll("SELECT * FROM MLWS_RECEIVERS WHERE MLWSUniqueFileName = :name", array("name"=>$selectedMLWS));
    $MLWS = PDO_FetchRow("SELECT * FROM MLWS WHERE MLWSUniqueName = :name", array("name"=>$selectedMLWS));
    $MLWSName = PDO_FetchRow("SELECT ifnull(ifnull(SensorLabel, Name), MLWSUniqueName) as SensorLabel, MLWSUniqueName FROM MLWS LEFT JOIN SENSORS ON MLWSUniqueName = SensorUniqueName WHERE MLWSUniqueName = :name", array("name"=>$selectedMLWS));
    $PageTitle = $MLWSName['SensorLabel']." (".$MLWSName['MLWSUniqueName'].")";

    ?>
    <title>MLWS(MAW) Information - <?php echo $PageTitle; ?></title>

    <div class="container-fluid">
        <?php echo "<h2>".$PageTitle."</h2>";?>
        <ul class="nav nav-tabs" id="mlws-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab" aria-controls="basic" aria-selected="true">Basic Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="threed-tab" data-bs-toggle="tab" data-bs-target="#threed" type="button" role="tab" aria-controls="threed" aria-selected="false">MLWS 3D Viewer</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="receiver-tab" data-bs-toggle="tab" data-bs-target="#receiverlist" type="button" role="tab" aria-controls="receiverlist" aria-selected="false">Receiver List</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active" id="basic" role="tabpanel" aria-labelledby="basic-tab">
                <div class="row pt-3">
                    <div class="col-6">
                        <h3>MLWS Basic Information</h3>
                        <table class="table table-bordered">
                            <tr>
                                <th>Range (m)</th>
                                <td><?php echo $MLWS['Range']; ?></td>
                            </tr>
                            <tr>
                                <th>Automatic Flare Slaving Possible?</th>
                                <td><?php echo intToYesNo($MLWS['AutomaticFlares']); ?></td>
                            </tr>
                            <tr>
                                <th>Flare Series Interval (s)</th>
                                <td><?php echo $MLWS['FlareSeriesInterval']; ?></td>
                            </tr>
                            <tr>
                                <th>Flare Interval (s)</th>
                                <td><?php echo $MLWS['FlareInterval']; ?></td>
                            </tr>
                            <tr>
                                <th>Number of Flares per series</th>
                                <td><?php echo $MLWS['NumberFlares']; ?></td>
                            </tr>
                            <tr>
                                <th>Minimum Closure Rate (m/s)</th>
                                <td><?php echo $MLWS['ClosureRateMin']; ?></td>
                            </tr>
                            <tr>
                                <th>Maximum Angular Rate (&deg;/s)</th>
                                <td><?php echo $MLWS['AngularRateMax']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <h3>MLWS Receivers</h3>
                        <p class="pb-2"> 
                            <span style="color:green">Green</span> = MLWS detects the missile approach vector<br />
                            <span style="color:red">Red</span> = MLWS does not detect the missile approach vector<br />
                            <span style="color:white">White/Transparent</span> = No indicated MLWS coverage
                        </p>
                        <div class="row pt-2">
                            <div class="col-6">
                                <h4>Top</h4>
                                <svg id="mlws-top" width="300" height="300" xmlns="http://www.w3.org/2000/svg" version="1.1">
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
                                            $start = $sector['Azimuth'] - ($sector['HorizontalWidth']/2);
                                            $end = $sector['Azimuth'] + ($sector['HorizontalWidth']/2);

                                            $startVertical = $sector['Elevation'] - ($sector['VerticalWidth']/2);
                                            $endVertical = $sector['Elevation'] + ($sector['VerticalWidth']/2);
                                            
                                            $verticalPlusMinus = $sector['VerticalWidth']/2;
                                            if (($end-$start) < 0 || $end > 270 || $start < 90) {
                                                // FRONT SECTOR (closest to nose).
                                                $maxVerticalFront = max($maxVerticalFront, $verticalPlusMinus);
                                                echo "<path d=\"". describeArc(150, 150, 110, convertLeft($startVertical), convertLeft($endVertical)) ."\"  fill=\"none\" stroke=\"".$strokeColour."\" stroke-width=\"1\" />";
                                            }
                                            if ($end > 90 || $start < 270) {
                                                $maxVerticalRear = max($maxVerticalRear, $verticalPlusMinus);
                                                // REAR SECTOR (closest to nose).
                                                echo "<path d=\"". describeArc(150, 150, 110, convertRight($startVertical), convertRight($endVertical)) ."\"  fill=\"none\" stroke=\"".$strokeColour."\" stroke-width=\"1\" />";
                                            }
                                        }
                                        if ($maxVerticalFront > 0) {
                                            echo "<text x=\"70\" y=\"145\" class=\"small\" stroke=\"white\">+/- $maxVerticalFront&deg;</text>";
                                        }
                                        if ($maxVerticalRear > 0) {
                                            echo "<text x=\"180\" y=\"145\" class=\"small\" stroke=\"white\">+/- $maxVerticalRear&deg;</text>";
                                        }
                                    ?>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row pt-3">
                    <div class="col">
                        <h3>Units with this MLWS:</h3>
                        <?php
                            $unitsWithMLWS = PDO_FetchAll("SELECT ifnull(UNITS.UnitLabel, UNITSENSORS.UnitUniqueName) as UnitLabel FROM UNITSENSORS
                                                            LEFT JOIN UNITS
                                                            ON UNITS.UnitUniqueName = UNITSENSORS.UnitUniqueName
                                                            WHERE UNITSENSORS.SensorUniqueName = :name
                                                            ORDER BY UnitLabel ASC", array("name"=>$selectedMLWS));
                        ?>
                        <ul>
                            <?php
                                foreach($unitsWithMLWS as $unit) {
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
                            <span style="color:green">Green</span> = MLWS detects the missile approach vector<br />
                            <span style="color:red">Red</span> = MLWS does not detect the missile approach vector<br />
                            <span style="color:white">White/Transparent</span> = No indicated MLWS coverage
                        </p>
                    </fieldset>
                    <div class="btn-group" role="group" aria-label="3D Viewer Controls">
                        <button id="show3DMLWS" class="btn btn-secondary">Toggle 3d MLWS</button>
                    </div>
                </div>
                <div class="pt-4 pb-4 d-flex justify-content-center" id="sensor-viewer"></div>
            </div>
            <div class="tab-pane" id="receiverlist" role="tabpanel" aria-labelledby="receiver-tab">
                <div class="row pt-3">
                    <h3>Full Receiver Sector List</h3>
                    <table class="table table-bordered">
                        <thead style="position: sticky;top: 0" >
                            <tr>
                                <th>Azimuth (0 = Nose, Negative = Left, Positive = Right)</th>
                                <th>Horizontal Width (Centred on Azimuth)</th>
                                <th>Elevation (0 = Horizon, Positive = Up, Negative = Down)</th>
                                <th>Elevation Width (Centred on Elevation)</th>
                                <th>Signal Angle Finder (No = Sector MLWS)</th>
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

                $horizontalSectors = 12;
                $verticalSectors = 8;

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
                        // Add additional lines to make sectors more visible.
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

        function load3dMLWS() {
            init();
            animate();
            threedLoaded = true;
        }

        function triggerVisibilityNoIndicate() {
            camera.layers.toggle(2);
        }

        var show3d = document.getElementById('show3DMLWS');
        show3d.addEventListener('click', function() {
            console.log('clicked');
            if (threedLoaded) {
                $('#togglelayer').toggleClass('d-none');
                dispose();
            } else {
                load3dMLWS();
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

        var triggerTabList = [].slice.call(document.querySelectorAll('#mlws-tabs a'))
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