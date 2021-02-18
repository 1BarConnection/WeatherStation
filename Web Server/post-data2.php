<?php
$servername = "localhost";

// REPLACE with your Database name
$dbname = "oxxxxxxxx_weather";
// REPLACE with Database user
$username = "oxxxxxxxx_admin_weather";
// REPLACE with Database user password
$password = "xxxxxxxxxxxxxxxxxxxxxxxx";

// Keep this API Key value to be compatible with the ESP32 code provided in the project page. 
// If you change this value, the ESP32 sketch needs to match
$api_key_value = "00f2d801-371a-22fb-44d8-8b1e-006e056cba00";

$api_key = $value1 = $value2 = $value3 = $value4 = $value5 = $value6 = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $api_key = test_input($_POST["api_key"]);
    if($api_key == $api_key_value) {
        $value1 = test_input($_POST["value1"]);
        $value2 = test_input($_POST["value2"]);
        $value3 = test_input($_POST["value3"]);
		$value4 = test_input($_POST["value4"]);
		$value5 = test_input($_POST["value5"]);
		$value6 = test_input($_POST["value6"]);
        
        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection is not established: " . $conn->connect_error);
        } 
        
        $sql = "INSERT INTO Sensor (value1, value2, value3, value4, value5, value6)
        VALUES ('" . $value1 . "', '" . $value2 . "', '" . $value3 . "', '" . $value4 . "', '" . $value5 . "', '" . $value6 . "')";
        
        if ($conn->query($sql) === TRUE) {
            echo "New record is succesfully made";
        } 
        else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    
        $conn->close();
    }
    else {
        echo "Wrong API key is provided";
    }

}
else {
    echo "No data published with HTTP POST";
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}