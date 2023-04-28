<?php
require 'backend.php';

header('Content-Type: application/json');

$searchTerm = (isset($_GET['query'])) ? $_GET['query'] : "";
#$searchTerm = $_GET['term'];

$stations = searchStation($searchTerm);

echo $stations;
