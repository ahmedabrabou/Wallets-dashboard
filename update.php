<?php
include_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["update_price"])) {
        // Handle updating the price
        $newPrice = $_POST["new_price"];
        $updatePriceQuery = "UPDATE users SET price = $newPrice WHERE user_id = 1";
        $result = mysqli_query($connection, $updatePriceQuery);
        if ($result) {
            // Redirect to dashboard.php after successful update
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle error
            echo "Error updating price: " . mysqli_error($connection);
        }
    } elseif (isset($_POST["update_cryptoamount"])) {
        // Handle updating the cryptoamount
        $newCryptoamount = $_POST["new_cryptoamount"];
        $updateCryptoamountQuery = "UPDATE users SET cryptoamount = $newCryptoamount WHERE user_id = 1";
        $result = mysqli_query($connection, $updateCryptoamountQuery);
        if ($result) {
            // Redirect to dashboard.php after successful update
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle error
            echo "Error updating cryptoamount: " . mysqli_error($connection);
        }
    } elseif (isset($_POST["add_wallet"])) {
        // Handle adding a new wallet
        $newWalletNumber = $_POST["new_wallet_number"];
        // Perform your SQL insert query for adding a new wallet
        $insertWalletQuery = "INSERT INTO wallets (user_id, wallet_number) VALUES (1, '$newWalletNumber')";
        $result = mysqli_query($connection, $insertWalletQuery);
        if ($result) {
            // Redirect to dashboard.php after successful update
            header("Location: dashboard.php");
            exit();
        } else {
            // Handle error
            echo "Error adding wallet: " . mysqli_error($connection);
        }
    } else {
        // Handle other cases, such as invalid form submissions
        echo "Invalid form submission.";
    }
}
?>
