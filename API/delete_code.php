<?php
// MySQL database connection settings
$dbHost = 'MYSQL SERVER';
$dbName = 'MYSQL DATABASE';
$dbUsername = 'MYSQL USER';
$dbPassword = 'MYSQL PASSWORD';

// Get the game_id and codename from the query string
$gameId = $_GET['game_id'];
$codename = $_GET['codename'];

// Create a PDO connection to the database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Delete the record from the codes table based on game_id and codename
$stmt = $pdo->prepare("DELETE FROM codes WHERE game_id = :gameId AND code = :codename");
$stmt->bindParam(':gameId', $gameId);
$stmt->bindParam(':codename', $codename);
$result = $stmt->execute();

if ($result) {
    $response = array('message' => 'Record deleted successfully');
} else {
    $response = array('error' => 'Failed to delete the record');
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);

// Close the database connection
$pdo = null;
?>
