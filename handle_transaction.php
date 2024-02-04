<?php
// Include configuration file
include_once 'config.php';

// Check if data is received
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_transaction'])) {
    // Retrieve data from POST request
    $walletId = $_POST['wallet_id'];
    $transactionType = $_POST['transaction_type'];
    $amount = $_POST['amount'];
    $transactionDate = date('Y-m-d'); // Current date

    // Insert transaction into the database
    $insertTransactionSql = "INSERT INTO transactions (wallet_id, transaction_type, amount, transaction_date) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $insertTransactionSql);

    // Check if the statement was prepared successfully
    if ($stmt) {
        // Bind parameters and execute the statement
        mysqli_stmt_bind_param($stmt, "isds", $walletId, $transactionType, $amount, $transactionDate);
        $success = mysqli_stmt_execute($stmt);

        // Check if the transaction was successful
        if ($success) {
            // Redirect back to the dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle failure
            echo 'Error performing transaction.';
        }

        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        // Handle statement preparation failure
        echo 'Error preparing statement.';
    }
} else {
    // Redirect to an error page or handle the situation accordingly
    echo "Error: Transaction data not received.";
}
?>
