<?php
// Do not forget to 'Auto-Index' the 'PerfMax' table


function convertDate($date) {
    $newDate = explode('/', $date);
    return $newDate[2] . "-" . $newDate[1] . "-" . $newDate[0];
}

// Reset Calc table

function resetPerfMaxTable() {
    $servername = "localhost";
    $username = "sport";
    $password = "mot2passe";
    $dbname = "sport";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $result = $conn->query("TRUNCATE PerfMax");
	

    }
    catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    $conn = null;

}



function  computeMaxDtp() {
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
        
        $perfMax = array();

        // Compute max values
        while( $row = $result->fetch() ) {
            // Debug
            //echo $row->Athlete. "|" . $row->Duration . "\n";
            // Compute
            $Athlete  = $row->Athlete;
            $MaxDtp   = $row->DistanceZone1 + $row->DistanceZone2 + $row->DistanceZone3 + $row->DistanceZone4 + $row->DistanceZone5;
            $MaxDtp23 = $row->DistanceZone5; 
            $MaxDtpPerMin = $MaxDtp / $row->Duration;
            $MaxVmax = $row->MaxSpeed;
            $DateEvent = $row->DateEvent;

            // Get the array value
            if(in_array($Athlete, $perfMax)) {
                $arrayMaxDtp =  $perfMax[$Athlete]['MaxDtp'];
                $arrayMaxDtp23 = $perfMax[$Athlete]['MaxDtp23'];
                $arrayMaxDtpPerMin = $perfMax[$Athlete]['MaxDtpPerMin'];
                $arrayMaxVmax = $perfMax[$Athlete]['MaxVmax'];
            } else {
                // By default, 0
                $arrayMaxDtp = $perfMax[$Athlete]['MaxDtp'] = 0;
                $arrayMaxDtp23 = $perfMax[$Athlete]['MaxDtp23'] = 0;
                $arrayMaxDtpPerMin = $perfMax[$Athlete]['MaxDtpPerMin'] = 0;
                $arrayMaxVmax = $perfMax[$Athlete]['MaxVmax'] = 0;
                $perfMax[$Athlete]['Date_MaxDtp'] =  $perfMax[$Athlete]['Date_MaxDtp23'] = $perfMax[$Athlete]['Date_MaxDtpPerMin'] = "1970-01-01";
            }

            // Test if data from database is greater than the array value
            if($MaxDtp > $arrayMaxDtp ) {
                 $perfMax[$Athlete]['MaxDtp'] = $MaxDtp;
                 $perfMax[$Athlete]['Date_MaxDtp'] = convertDate($DateEvent);
            }

            if($MaxDtp23 > $arrayMaxDtp23 ) {
                 $perfMax[$Athlete]['MaxDtp23'] = $MaxDtp23;
                 $perfMax[$Athlete]['Date_MaxDtp23'] = convertDate($DateEvent);
            }

            if($MaxDtpPerMin > $arrayMaxDtpPerMin ) {
                $perfMax[$Athlete]['MaxDtpPerMin'] = $MaxDtpPerMin;
                $perfMax[$Athlete]['Date_MaxDtpPerMin'] = convertDate($DateEvent);
            }

            if($MaxVmax > $arrayMaxVmax ) {
                $perfMax[$Athlete]['MaxVmax'] = $MaxVmax;
                $perfMax[$Athlete]['Date_MaxVmax'] = convertDate($DateEvent);
            }



        }
        $result->closeCursor();

        
        // Insert max value in PerfMax table
        foreach ($perfMax as $athlete => $skill) {
                echo $athlete . " " . $skill['MaxDtp'] . " " . $skill['MaxDtp23'] . " " . $skill['MaxDtpPerMin'] . " " .  $skill['MaxVmax'] . " ";
                echo $skill['Date_MaxDtp'] . " " . $skill['Date_MaxDtp23'] . " " . $skill['Date_MaxDtpPerMin'] . " " .  $skill['Date_MaxVmax'];
                
                // prepare sql and bind parameters
                $stmt = $conn->prepare("INSERT INTO PerfMax (PlayerName,  MaxDtp,  MaxDtp23,  MaxDtppermin,  MaxVmax,  Date_MaxDtp,  Date_MaxDtp23,  Date_MaxDtppermin,  Date_MaxVmax) 
                                        VALUES             (:PlayerName, :MaxDtp, :MaxDtp23, :MaxDtppermin, :MaxVmax, :Date_MaxDtp, :Date_MaxDtp23, :Date_MaxDtppermin, :Date_MaxVmax)");
                $stmt->bindParam(':PlayerName', $athlete);
                $stmt->bindParam(':MaxDtp', $skill['MaxDtp'] );
                $stmt->bindParam(':MaxDtp23', $skill['MaxDtp23'] );
                $stmt->bindParam(':MaxDtppermin',  $skill['MaxDtpPerMin']);
                $stmt->bindParam(':MaxVmax', $skill['MaxVmax'] );

                $stmt->bindParam(':Date_MaxDtp', $skill['Date_MaxDtp'] );
                $stmt->bindParam(':Date_MaxDtp23', $skill['Date_MaxDtp23'] );
                $stmt->bindParam(':Date_MaxDtppermin',  $skill['Date_MaxDtpPerMin']);
                $stmt->bindParam(':Date_MaxVmax', $skill['Date_MaxVmax'] );

                $stmt->execute();
                
                echo "\n";
        }
        
	
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $conn = null;
}

resetPerfMaxTable();
computeMaxDtp();
?> 
