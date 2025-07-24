<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: FarmerLogin.php");
    exit();
}

// Include the database connection
include 'connection.php';

//include functions
include 'functions.php';

// Get the logged-in farmer's ID
$username = $_SESSION['username'];


// Fetch farmer's ID from login table
$farmer= fetchSingleRow($conn, "SELECT FarmerID FROM farmer_login WHERE username = ?", "s", $username);
$farmerID = $farmer['FarmerID']??null;

// Fetch farmer's personal details
$farmerDetails=fetchSingleRow($conn,"SELECT fname, lname, phone_no, age, sex,category,adhar FROM farmers WHERE farmerID = ?","i",$farmerID);

// Fetch land details
$land_details =fetchAllRows($conn," SELECT * FROM farmer_land WHERE farmerID = ?", "i", $farmerID);

// Fetch fertilizer request history
$requests = fetchAllRows($conn, "
    SELECT fr.requestID, fr.landID, f.fertName, fr.quantityRequested, fr.requestDate, 
           fr.status, COALESCE(rp.payment_status, 'Not Available') AS payment_status, 
           COALESCE(rp.amount, 0) AS amount
    FROM fertilizer_requests fr
    JOIN fertilizers f ON fr.fertID = f.fertID
    LEFT JOIN request_payments rp ON fr.requestID = rp.requestID
    WHERE fr.farmerID = ?
    ORDER BY fr.requestDate DESC
", "i", $farmerID);


$off=fetchSingleRow($conn,"SELECT registeredBy FROM farmers WHERE farmerID=?","i",$farmerID);
$FoffID=$off['registeredBy'];
$supervisor = fetchSingleRow($conn, "SELECT supervisorID FROM officers WHERE offID = ?", "i", $FoffID);
$JoffID = $supervisor['supervisorID'];

//fetch all fertilizers
$fertilizers = fetchAllRows($conn, "SELECT fertID, fertName FROM fertilizers WHERE fertID > ?","i",0);


//apply request
if (isset($_POST['submitrequest'])) {
    $landID = $_POST['landID'];
    $fertID = $_POST['fertID'];
    $quantity = $_POST['quantity'];

    // Validate Land ID (Check if land belongs to the farmer)
    $landExists = fetchSingleRow($conn, "SELECT * FROM farmer_land WHERE farmerID = ? AND landID = ?", "ii", $farmerID, $landID);
    $landIDexists=$landExists['landID']??null;
    if($landIDexists==null){
        echo "<script>alert('Invalid Land ID');</script>";
        exit();
    }
    // Validate Fertilizer ID (Check if fertilizer exists)
    $fertExists = fetchSingleRow($conn, "SELECT * FROM fertilizers WHERE fertID = ?", "i", $fertID);
    $fertIDexists=$fertExists['fertID']??null;
    if($fertIDexists==null){
        echo "<script>alert('Invalid Fertilizer ID');</script>";
        exit();
    }  
    if ($landExists && $fertExists) {
        // Insert the fertilizer request if both exist       
        $result = executeQuery($conn,"INSERT INTO fertilizer_requests (farmerID, landID, fertID, quantityRequested,registeredBy,reviewedBy ,requestDate) VALUES (?, ?, ?,?,?, ?, NOW())", "iiiiii", $farmerID, $landID, $fertID, $quantity,$FoffID,$JoffID);
        if ($result) {
            echo "<script>alert('Request submitted successfully!');
        window.location.href = 'FarmerDashboard.php';
        </script>";
           exit();
        }
        else {
            echo "<script>alert('Error in submitting request. Try again!');</script>";
        }
    }
}

// Pay button
if (isset($_POST['paybtn'])) {
    $requestID = $_POST['requestID'];
    $amount = $_POST['amount'];

    // Update the request_payments table with payment details
    $result = executeQuery($conn, "UPDATE request_payments SET payment_status = 'Completed' WHERE requestID = ?", "i", $requestID);

    if ($result) {
        echo "<script>alert('Payment submitted successfully!'); window.location.href='FarmerDashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error in submitting payment. Try again!');</script>";
    }
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard</title>
    <link rel="stylesheet" href="cssfiles/FarmerDashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="images/firm_logo1.png" alt="Profile Picture">
            <h2>Farmer Panel</h2>
        </div>
        <ul class="nav-links">
            <li><a href="#" onclick="showSection('profile')"><i class="fas fa-user"></i>Profile</a></li>
            <li><a href="#" onclick="showSection('land')"><i class="fas fa-map-marked-alt"></i>  Land Details</a></li>
            <li><a href="#" onclick="showSection('requests')"><i class="fas fa-tasks"></i>  Requests</a></li>
            <li><a href="#" onclick="showSection('apply-request')"><i class="fas fa-file-signature"></i>  Apply Request</a></li>
            <li><a href="#" onclick="showSection('payment')"><i class="fas fa-money-bill-wave"></i>  Payment</a></li>
            <li><a href="#" onclick="showSection('support')"><i class="fas fa-headset"></i>  Support</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>  Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>Dashboard</h1>
        </header>

        <!-- Profile Section -->
        <section id="profile" class="section active">
            <h2><i class="fas fa-user"></i> Profile</h2>
            <img src="images/farmer1.jpg" class="profile-pic" alt="Profile Picture">
            <p><i class="fas fa-user"></i>
            <strong>Farmer ID:</strong> <?php echo $farmerID; ?></p>
            <p><i class="fas fa-user"></i>
            <strong>Name:</strong>  <?php echo $farmerDetails['fname'] . " ". $farmerDetails['lname']; ?></p>
            <p><i class="fas fa-phone"></i> 
            <strong>Phone:</strong> <?php echo $farmerDetails['phone_no']; ?></p>
            <p><i class="fas fa-venus-mars"></i> 
            <strong>Sex:</strong> <?php echo $farmerDetails['sex']; ?></p>
            <p><i class="fas fa-venus-mars"></i> 
            <strong>Age:</strong> <?php echo $farmerDetails['age']; ?></p>
            <p><i class="fas fa-users"></i> 
            <strong>Category:</strong> <?php echo $farmerDetails['category']; ?></p>
            <p><i class="fas fa-id-card"></i> 
            <strong>Aadhaar No:</strong><?php echo $farmerDetails['adhar']; ?></p>
        </section>
        

        <!-- Land Details Section -->
        <section id="land" class="section">
            <h2>Land Details</h2>
            <?php if (empty($land_details)): ?>
                    <p>No land records found.</p>
            <?php else: ?>
                
            <table>
                <tr>
                    <th>Land ID</th>
                    <th>Location</th>
                    <th>Size </th>
                    <th>Soil Type</th>
                </tr>
                <?php foreach ($land_details as $land): ?>
                <tr>
                    <td><?php echo htmlspecialchars($land['landID']); ?></td>
                    <td><?php echo htmlspecialchars($land['landLocation']); ?></td>
                    <td><?php echo htmlspecialchars($land['landSize']); ?></td>
                    <td><?php echo htmlspecialchars($land['soilType']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </section>

        <!-- Requests Section -->
        <section id="requests" class="section">
        <h2>APPROVED REQUESTS<h2>
                    <?php if (!empty($requests)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request ID</th>
                                <th>Land ID</th>
                                <th>Fertilizer Name</th>
                                <th>Quantity Requested</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            <th>Payment status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1;
                             foreach ($requests as $req): ?>
                                <?php if ($req['status'] == 'Approved'): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td><?php echo htmlspecialchars($req['requestID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['landID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['fertName']); ?></td>
                                        <td><?php echo htmlspecialchars($req['quantityRequested']); ?></td>
                                        <td><?php echo htmlspecialchars($req['requestDate']); ?></td>
                                        <td><?php echo htmlspecialchars($req['status']); ?></td>
                                        <td><?php echo htmlspecialchars($req['payment_status']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    <hr>
            <h2>ALL Requests</h2>
            <?php if (!empty($requests)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Request ID</th>
                            <th>Land ID</th>
                            <th>Fertilizer Name</th>
                            <th>Quantity Requested</th>
                            <th>Request Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1;
                         foreach ($requests as $req): ?>
                            <tr>
                                <td><?= $index++ ?></td>
                                <td><?php echo htmlspecialchars($req['requestID']); ?></td>
                                <td><?php echo htmlspecialchars($req['landID']); ?></td>
                                <td><?php echo htmlspecialchars($req['fertName']); ?></td>
                                <td><?php echo htmlspecialchars($req['quantityRequested']); ?></td>
                                <td><?php echo htmlspecialchars($req['requestDate']); ?></td>
                                <td><?php echo htmlspecialchars($req['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="no-data">No fertilizer requests found.</p>
                <?php endif; ?>

                <h2>PENDING REQUESTS</h2>
                <?php if (!empty($requests)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request ID</th>
                                <th>Land ID</th>
                                <th>Fertilizer Name</th>
                                <th>Quantity Requested</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1;
                             foreach ($requests as $req): ?>
                                <?php if ($req['status'] == 'Pending'): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td><?php echo htmlspecialchars($req['requestID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['landID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['fertName']); ?></td>
                                        <td><?php echo htmlspecialchars($req['quantityRequested']); ?></td>
                                        <td><?php echo htmlspecialchars($req['requestDate']); ?></td>
                                        <td><?php echo htmlspecialchars($req['status']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    <hr>

                    <h2>Rejected REQUESTS</h2>
                <?php if (!empty($requests)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request ID</th>
                                <th>Land ID</th>
                                <th>Fertilizer Name</th>
                                <th>Quantity Requested</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 1;
                             foreach ($requests as $req): ?>
                                <?php if ($req['status'] == 'Rejected'): ?>
                                    <tr>
                                        <td><?= $index++ ?></td>
                                        <td><?php echo htmlspecialchars($req['requestID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['landID']); ?></td>
                                        <td><?php echo htmlspecialchars($req['fertName']); ?></td>
                                        <td><?php echo htmlspecialchars($req['quantityRequested']); ?></td>
                                        <td><?php echo htmlspecialchars($req['requestDate']); ?></td>
                                        <td><?php echo htmlspecialchars($req['status']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                                </section>
                                <section id=payment class="section">
    <!-- Pending Payments Section -->
     <hr>
                    <h2 >PENDING PAYMENTS</h2>
                    <?php if (!empty($requests)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Request ID</th>
                                    <th>Land ID</th>
                                    <th>Fertilizer Name</th>
                                    <th>Quantity Requested</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Payment status</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $index = 1;
                                 foreach ($requests as $req): ?>
                                    <?php if ( $req['status'] =='Approved' && $req['payment_status'] == 'Pending'): ?>
                                        <tr>
                                            <td><?= $index++ ?></td>
                                            <td><?php echo htmlspecialchars($req['requestID']); ?></td>
                                            <td><?php echo htmlspecialchars($req['landID']); ?></td>
                                            <td><?php echo htmlspecialchars($req['fertName']); ?></td>
                                            <td><?php echo htmlspecialchars($req['quantityRequested']); ?></td>
                                            <td><?php echo htmlspecialchars($req['requestDate']); ?></td>
                                            <td><?php echo htmlspecialchars($req['status']); ?></td>
                                            <td><?php echo htmlspecialchars($req['payment_status']); ?></td>
                                            <td><?php echo htmlspecialchars($req['amount']); ?></td>
                                            <td>
                                            <form method="post">
                                                <input type="hidden" name="requestID" value="<?php echo $req['requestID']; ?>">
                                                <button onclick="openModal()>Pay</button>
                                                
                                                <div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Payment Form</h2>
        <form method="post" action="">
            <input type="hidden" name="requestID" value="<?php echo $req['requestID']; ?>">
            <button type="submit" name="paybtn">Submit Payment</button>
        </form>
    </div>
</div>
                                            </form>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>

                        


        </section>

        <!-- Apply Request Section -->
        <section id="apply-request" class="section">
            <h2>Apply for Fertilizer</h2>
            <form method="POST" action="">
                <label>Land ID:</label>
                <input type="text" name="landID" required placeholder="Enter Land ID">
                <label>Fertilizer : </label>
                <select name="fertID" required>
                    <?php foreach ($fertilizers as $fertilizer) : ?>
                        <option value="<?php echo $fertilizer['fertID']; ?>">
                            <?php echo $fertilizer['fertID'] . " - " . $fertilizer['fertName']; ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>
            
                <label>Quantity (kg):</label>
                <input type="number" name="quantity" required placeholder="Enter Quantity">
                <button type="submit" name="submitrequest" class="btn">Apply</button>
            </form>
        </section>

        <!-- Support Section -->
        <section id="support" class="section">
            <h2>Support</h2>
            <p>Contact us at <strong>support@agriculture.gov</strong></p>
        </section>
    </div>

    <script src="jsfiles/FarmerDashboard.js"></script>
               
</body>
</html>
