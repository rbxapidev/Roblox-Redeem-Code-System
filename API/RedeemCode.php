<?php
// MySQL database connection settings
$dbHost = 'MYSQL SERVER IP ADDRESS';
$dbName = 'MYSQL DATABASE NAME';
$dbUsername = 'MYSQL USERNAME';
$dbPassword = 'MYSQL PASSWORD';

// Get the POST data from the Lua script
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_GET['game_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Invalid request";
    exit;
}

if (isset($data['code']) && isset($data['userId'])) {
    // Get the code and user ID from the POST data
    $code = $data['code'];
    $userId = $data['userId'];

    // Create a PDO connection to the database
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Check if the user has already redeemed this code
    $stmtCheckRedeemed = $pdo->prepare("SELECT COUNT(*) FROM redeem_log WHERE code = :code AND player = :userId");
    $stmtCheckRedeemed->bindParam(':code', $code);
    $stmtCheckRedeemed->bindParam(':userId', $userId);
    $stmtCheckRedeemed->execute();

    $hasRedeemed = $stmtCheckRedeemed->fetchColumn();

    if ($hasRedeemed > 0) {
        // The user has already redeemed this code
        $response = array('status' => 'already_redeemed');
    } else {
        // Proceed with checking and updating the code as before
        $stmt = $pdo->prepare("SELECT value, redeem_amount FROM codes WHERE code = :code");
        $stmt->bindParam(':code', $code);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $value = $result['value'];
            $redeemAmount = $result['redeem_amount'];

            if ($redeemAmount > 0) {
                $redeemAmount--;
                $updateStmt = $pdo->prepare("UPDATE codes SET redeem_amount = :redeemAmount WHERE code = :code");
                $updateStmt->bindParam(':redeemAmount', $redeemAmount);
                $updateStmt->bindParam(':code', $code);
                $updateStmt->execute();
                $response = array('status' => 'valid', 'value' => $value);
            } else {
                $deleteStmt = $pdo->prepare("DELETE FROM codes WHERE code = :code");
                $deleteStmt->bindParam(':code', $code);
                $deleteStmt->execute();
                $response = array('status' => 'valid', 'value' => $value);
            }
        } else {
            $response = array('status' => 'invalid');
        }

        // Log the redemption in the redeem_log table
        $logStmt = $pdo->prepare("INSERT INTO redeem_log (player, code) VALUES (:userId, :code)");
        $logStmt->bindParam(':userId', $userId);
        $logStmt->bindParam(':code', $code);
        $logStmt->execute();
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);

    // Close the database connection
    $pdo = null;
} else {
    // Invalid request
    header('HTTP/1.1 400 Bad Request');
    echo "Invalid request";
}
?>
