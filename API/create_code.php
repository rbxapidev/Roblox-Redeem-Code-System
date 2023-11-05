<?php
// Set the path to the log file
$logFilePath = "api_debug.log";

// Function to log information to a file
function logToDebugFile($message) {
    global $logFilePath;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFilePath, "$timestamp - $message\n", FILE_APPEND);
}

// MySQL database connection settings
$dbHost = 'MYSQL SERVER';
$dbName = 'MYSQL DATABASE';
$dbUsername = 'MYSQL USERNAME';
$dbPassword = 'MYSQL PASSWORD';

// Get the POST data from the Lua script
$postData = file_get_contents("php://input");
logToDebugFile("Received POST data: $postData"); // Log the received POST data
$data = json_decode($postData, true);

// Check if the required fields are present in the POST data
if (isset($data['game_id'], $data['code'], $data['value'], $data['redeem_amount'])) {
    logToDebugFile("Required fields present in POST data"); // Log the presence of required fields

    // Create a PDO connection to the database
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Enable error handling
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Prepare an SQL statement to insert a new record
    $stmt = $pdo->prepare("INSERT INTO codes (game_id, code, value, redeem_amount) VALUES (:game_id, :code, :value, :redeem_amount)");
    $stmt->bindParam(':game_id', $data['game_id']);
    $stmt->bindParam(':code', $data['code']);
    $stmt->bindParam(':value', $data['value']);
    $stmt->bindParam(':redeem_amount', $data['redeem_amount']);

    // Execute the SQL statement
    try {
        $stmt->execute();
        logToDebugFile("Code created successfully"); // Log success
        $response = array('success' => 'Code created successfully');
    } catch (PDOException $e) {
        logToDebugFile("Failed to create code: " . $e->getMessage()); // Log failure with error message
        $response = array('error' => 'Failed to create code');
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the database connection
    $pdo = null;
} else {
    logToDebugFile("Bad Request - Missing Required Fields"); // Log missing required fields
    // Required fields are missing in the POST data
    header('HTTP/1.1 400 Bad Request');
    echo "Bad Request - Missing Required Fields";
}
?>
