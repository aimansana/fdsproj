<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: OfficerLogin.php");
    exit();
}

include 'connection.php';
include 'functions.php';

$username = $_SESSION['username'];
$officer = fetchSingleRow($conn, "SELECT offID FROM officer_login WHERE username = ?", "s", $username);
if (!$officer) die("Officer not found.");

$offID = $officer['offID'];
$officerDetails = fetchSingleRow($conn, "SELECT * FROM officers WHERE offID = ?", "i", $offID);

// Fetch data
//$Payments = fetchAllRows($conn, "SELECT p.paymentID,p.requestID, p.amount, p.payment_status,p.payment_date,f.farmerID FROM request_payments p JOIN fertilizer_requests f ON p.requestID = f.requestID ", []);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subsidy Payment Officer Dashboard</title>
    <link rel="stylesheet" href="off4.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="header">
                <h2><i class="fas fa-credit-card"></i> Subsidy Payment Officer</h2>
            </div>
            <ul>
                <li><a href="#profile"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="#pending-payments"><i class="fas fa-hand-holding-usd"></i> Pending Payments</a></li>
                <li><a href="#farmer-details"><i class="fas fa-user-check"></i> Farmer Details</a></li>
                <li><a href="#previous-payments"><i class="fas fa-history"></i> Previous Payments</a></li>
                <li><a href="#settings"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Subsidy Payment Officer Dashboard</h1>

            <div id="profile" class="section">
                <h2>Profile</h2>
                <table class="table table-bordered">
                    <tr><th>ID:</th><td><?= htmlspecialchars($officerDetails['offID']) ?></td></tr>
                    <tr><th>Name:</th><td><?= htmlspecialchars($officerDetails['Fname'] . " " . $officerDetails['Lname']) ?></td></tr>
                    <tr><th>Designation:</th><td>Subsidy Payment Officer</td></tr>
                    <tr><th>Email:</th><td><?= htmlspecialchars($officerDetails['email']) ?></td></tr>
                    <tr><th>Phone:</th><td><?= htmlspecialchars($officerDetails['phone_no']) ?></td></tr>
                    <tr><th>Age:</th><td><?= htmlspecialchars($officerDetails['age']) ?></td></tr>
                    <tr><th>Sex:</th><td><?= htmlspecialchars($officerDetails['sex']) ?></td></tr>
                </table>
            </div>

            <div id="pending-payments" class="section">
                <h2>Pending Payments</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr><th>Farmer Name</th><th>Amount</th><th>Reason</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPayments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['Fname'] . " " . $payment['Lname']) ?></td>
                            <td>$<?= htmlspecialchars($payment['amount']) ?></td>
                            <td><?= htmlspecialchars($payment['reason']) ?></td>
                            <td><button class="btn btn-success">Process</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="farmer-details" class="section">
                <h2>Farmer Details</h2>
                <table class="table table-bordered">
                    <thead><tr><th>Farmer Name</th><th>Farm Location</th><th>Eligibility</th></tr></thead>
                    <tbody>
                        <?php foreach ($farmerDetails as $farmer): ?>
                        <tr>
                            <td><?= htmlspecialchars($farmer['Fname'] . " " . $farmer['Lname']) ?></td>
                            <td><?= htmlspecialchars($farmer['location']) ?></td>
                            <td><?= htmlspecialchars($farmer['category']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="off4.js"></script>
</body>
</html>
