<?php
include_once 'config.php';
header("refresh:40");
$timezoneQuery = "SET time_zone = '+03:00'";
mysqli_query($connection, $timezoneQuery);
$userData = [];
$userQuery = "SELECT user_name, price, cryptoamount FROM users WHERE user_id = 1";
$userResult = mysqli_query($connection, $userQuery);

if ($userResult && mysqli_num_rows($userResult) > 0) {
    $userData = mysqli_fetch_assoc($userResult);
}

$walletData = [];
$walletQuery = "SELECT * FROM wallets WHERE user_id = 1";
$walletResult = mysqli_query($connection, $walletQuery);

if ($walletResult && mysqli_num_rows($walletResult) > 0) {
    while ($row = mysqli_fetch_assoc($walletResult)) {
        // Fetch sum of received transactions for today for the current wallet
        $receivedTodayQuery = "SELECT COALESCE(SUM(amount), 0) AS received_today 
                               FROM transactions 
                               WHERE wallet_id = {$row['wallet_id']} 
                               AND transaction_type = 'send' 
                               AND DATE(transaction_date) = CURDATE()";
        $receivedTodayResult = mysqli_query($connection, $receivedTodayQuery);
        $receivedTodayData = mysqli_fetch_assoc($receivedTodayResult);
        $receivedToday = $receivedTodayData['received_today'];

        // Fetch sum of received transactions for the month for the current wallet
        $receivedMonthQuery = "SELECT COALESCE(SUM(amount), 0) AS received_month 
                               FROM transactions 
                               WHERE wallet_id = {$row['wallet_id']} 
                               AND transaction_type = 'send' 
                               AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
        $receivedMonthResult = mysqli_query($connection, $receivedMonthQuery);
        $receivedMonthData = mysqli_fetch_assoc($receivedMonthResult);
        $receivedMonth = $receivedMonthData['received_month'];

        $row['received_today'] = $receivedToday;
        $row['received_month'] = $receivedMonth;

        // Calculate wallet balance
        $receivedAmountQuery = "SELECT COALESCE(SUM(amount), 0) AS received_amount FROM transactions WHERE wallet_id = {$row['wallet_id']} AND transaction_type = 'receive'";
        $receivedResult = mysqli_query($connection, $receivedAmountQuery);
        $receivedData = mysqli_fetch_assoc($receivedResult);

        $sentAmountQuery = "SELECT COALESCE(SUM(amount), 0) AS sent_amount FROM transactions WHERE wallet_id = {$row['wallet_id']} AND transaction_type = 'send'";
        $sentResult = mysqli_query($connection, $sentAmountQuery);
        $sentData = mysqli_fetch_assoc($sentResult);

        $walletBalance = $receivedData['received_amount'] - $sentData['sent_amount'];
        $dailyLimit = 60000  - $receivedToday - $walletBalance ;
        $monthlyLimit = 200000 - $receivedMonth-$walletBalance;

        $row['balance'] = $walletBalance;
        $row['daily_limit'] = $dailyLimit;
        $row['monthly_limit'] = $monthlyLimit;

        $walletData[] = $row;

        $userBalance += $walletBalance;
    }

}

$userBalance += ($userData['cryptoamount'] * $userData['price']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallets Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 20px;
        }
        .navbar {
            background-color: #007bff;
            color: #000000 !important;
            border-radius: 5px;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
        }
        .navbar-text {
            font-size: 18px;
        }
        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 15px 20px;
            font-weight: bold;
        }
        .card-body {
            padding: 20px;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .table th,
        .table td {
            vertical-align: middle;
        }
        .table th {
            background-color: #007bff;
            color: #fff;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 123, 255, 0.1);
        }
        .A1{
            font-size:1.8rem !important;
        }
    </style>
</head>
<body>


    <div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <a class="navbar-brand" href="#">Creative Dashboard</a>
            <div class="navbar-text ml-auto">
                User: <?php echo htmlspecialchars($userData['user_name']); ?> |
               <span class="A1">Balance: <?php echo htmlspecialchars($userBalance); ?> </span> |
                Cryptoamount: <?php echo htmlspecialchars($userData['cryptoamount']); ?>
            </div>
        </nav>

        <div class="row">
    <!-- Update Price Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Update Price</div>
            <div class="card-body">
                <form method="post" action="update.php">
                    <div class="form-group">
                        <label for="new_price">New Price:</label>
                        <input type="number" class="form-control" id="new_price" name="new_price" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update_price">Update Price</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Cryptoamount Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Update Cryptoamount</div>
            <div class="card-body">
                <form method="post" action="update.php">
                    <div class="form-group">
                        <label for="new_cryptoamount">New Cryptoamount:</label>
                        <input type="number" class="form-control" id="new_cryptoamount" name="new_cryptoamount" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update_cryptoamount">Update Cryptoamount</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Wallet Form -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Add Wallet</div>
            <div class="card-body">
                <form method="post" action="update.php">
                    <div class="form-group">
                        <label for="new_wallet_number">New Wallet Number:</label>
                        <input type="text" class="form-control" id="new_wallet_number" name="new_wallet_number" required>
                    </div>
                    <button type="submit" class="btn btn-success" name="add_wallet">Add Wallet</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Sort $walletData array based on balance in descending order
usort($walletData, function($a, $b) {
    return $b['balance'] - $a['balance'];
});
?>

<!-- Wallets Table -->
<div class="card">
    <div class="card-header">Wallets</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Wallet ID</th>
                        <th>Wallet Number</th>
                        <th>Balance</th>
                        <th>Currency</th>
                        <th>Receive today</th>
                        <th>Monthly Limit</th>
                        <th>Send</th>
                        <th>Receive</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($walletData as $wallet): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($wallet['wallet_id']); ?></td>
                        <td><?php echo htmlspecialchars($wallet['wallet_number']); ?></td>
                        <td><?php echo htmlspecialchars($wallet['balance']); ?></td>
                        <td><?php echo htmlspecialchars($wallet['currency']); ?></td>
                        <td><?php echo htmlspecialchars($wallet['daily_limit']); ?></td>
                        <td><?php echo htmlspecialchars($wallet['monthly_limit']); ?></td>
                        <td>
                            <form method="post" action="handle_transaction.php">
                                <input type="hidden" name="wallet_id" value="<?php echo htmlspecialchars($wallet['wallet_id']); ?>">
                                <input type="hidden" name="transaction_type" value="send">
                                <input type="number" class="form-control amount-input" name="amount" placeholder="Enter amount" required>
                                <button type="submit" class="btn btn-primary mt-2" name="submit_transaction">Send</button>
                            </form>
                        </td>
                        <td>
                            <form method="post" action="handle_transaction.php">
                                <input type="hidden" name="wallet_id" value="<?php echo htmlspecialchars($wallet['wallet_id']); ?>">
                                <input type="hidden" name="transaction_type" value="receive">
                                <input type="number" class="form-control amount-input" name="amount" placeholder="Enter amount" required>
                                <button type="submit" class="btn btn-success mt-2" name="submit_transaction">Receive</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
