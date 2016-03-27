<?php


// Reset Calc table

function resetCalcTable() {
    $servername = "localhost";
    $username = "sport";
    $password = "mot2passe";
    $dbname = "sport";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $conn->query("TRUNCATE Calc");
	

    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

}

// Compute:
// DTP, DtpPerMin

function computeDtpPerMin() {
    $servername = "localhost";
    $username = "sport";
    $password = "mot2passe";
    $dbname = "sport";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $conn->query("SELECT * FROM gps_data");
        $result->setFetchMode(PDO::FETCH_OBJ);
        while( $row = $result->fetch() ) {
            // Debug
            echo $row->Athlete. "|" . $row->Duration . "\n";
            // Compute
            $Athlete = $row->Athlete;
            $DTP =  $row->DistanceZone1 + $row->DistanceZone2 + $row->DistanceZone3 + $row->DistanceZone4 + $row->DistanceZone5;
            $DtpPerMin = $DTP / $row->Duration;

            // prepare sql and bind parameters
            $stmt = $conn->prepare("INSERT INTO Calc (Athlete, DTP, DtpPerMin) VALUES (:Athlete, :DTP, :DtpPerMin)");
            $stmt->bindParam(':Athlete', $Athlete);
            $stmt->bindParam(':DTP', $DTP);
            $stmt->bindParam(':DtpPerMin', $DtpPerMin);

            $stmt->execute();



        }
        $result->closeCursor();
	

    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;
}

//computeDtpPerMin();

//resetCalcTable();
?> 
