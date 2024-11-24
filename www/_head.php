<style type="text/css">@import url("style.css");</style>
<?php
// error_reporting(1);
include "./_pdo.php";
$rwr_db = "./radar_rwr_database.db";
PDO_Connect("sqlite:$rwr_db");
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<nav class="navbar navbar-expand-lg">
    <div class="container">
        <h1 class="navbar-brand">Sensor Database</h1>
        <a class="nav-item nav-link" href="./index.php">Home</a>
    </div>
</nav>