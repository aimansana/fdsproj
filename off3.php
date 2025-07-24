<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: OfficerLogin.php");
    exit();
}

include 'connection.php';
include 'functions.php';

$username = $_SESSION['username'];

// Fetch officer's ID
$officer = fetchSingleRow($conn, "SELECT offID FROM officer_login WHERE username = ?", "s", $username);
$offID = $officer['offID'];

// Fetch officer's details
$officerDetails = fetchSingleRow($conn, "SELECT * FROM officers WHERE offID = ?", "i", $offID);

// Fetch Junior Officers and their stock
$juniorOfficers = fetchAllRows($conn, "
    SELECT o.offID, o.regionID, COALESCE(fs.total_stock, 0) AS total_stock
    FROM officers o
    LEFT JOIN fertilizer_stock fs ON o.offID = fs.officerID
    WHERE o.supervisorID = ?
", "i", $offID);

// Set total stock available
$totalstock = 5000;
$allocatedstock = 0;

if (isset($_POST['stockbtn'])) {
    $allocations = $_POST['allocations']; // Get input as associative array
    $total_allocated = array_sum($allocations); // Sum of all inputs

    if ($total_allocated <= $totalstock) {
        foreach ($allocations as $juniorID => $amount) {
            if ($amount > 0) {
                // Store allocation in the fertilizer_allocation table
                $query = "INSERT INTO fertilizer_allocation (officerID, amount_allocated, allocated_by) VALUES (?, ?, ?)";
                executeQuery($conn, $query, "iii", $juniorID, $amount, $offID);

                // Check if junior officer already has a stock record
                $existingStock = fetchSingleRow($conn, "SELECT total_stock FROM fertilizer_stock WHERE officerID = ?", "i", $juniorID);

                if ($existingStock) {
                    // Update existing stock
                    $updateQuery = "UPDATE fertilizer_stock SET total_stock = total_stock + ? WHERE officerID = ?";
                    executeQuery($conn, $updateQuery, "di", $amount, $juniorID);
                } else {
                    // Insert new stock record
                    $insertQuery = "INSERT INTO fertilizer_stock (officerID, total_stock) VALUES (?, ?)";
                    executeQuery($conn, $insertQuery, "id", $juniorID, $amount);
                }

                $allocatedstock += $amount;
            }
        }

        echo "<script>alert('Stock allocated successfully!');</script>";
    } else {
        echo "<script>alert('Total allocated stock exceeds available stock!');</script>";
    }
}

//previous allocations
$previousAllocations =fetchAllRows($conn,"SELECT * FROM fertilizer_allocation WHERE allocated_by = ?", "i", $offID);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senior Officer Dashboard</title>
    <link rel="stylesheet" href="cssfiles/off1.css"> <!-- Linking the CSS -->
    <script src="off2.js" defer></script> <!-- Linking the JavaScript -->
    
</head>
<body>
    <div class="sidebar">
    <div class="logo">
        
    <img src="images/firm_logo1.png" alt="Logo" style="width: 100px; height: auto; max-height: 80px;">
        <h2>Senior Dashboard</h2>
    </div>
        <ul>
        
        <li><a href="#" class="active" data-section="profile">profile</a></li>
        <li><a href="#" data-section="stock-overview">stock overview</a></li>
        <li><a href="#" data-section="stock-allocation">stock allocation</a></li>
        <li><a href="#" data-section="previous-allocations">previous allocation</a></li>
        <li><a href="#" data-section="analytics">Analytics</a></li>
        <li><a href="logout.php" data-section="profile"> Logout</a></li>
    </ul>
    </div>
    <div class="logout-btn">
        <a href="#">Logout</a>
    
    </div>
    
    <div class="main-content">
        <header>
            <h1>Senior Officer Dashboard</h1>
        </header>
        
        <div id="profile" class="section active">
            <h2>Profile</h2>
            <h3>Your Profile</h3>
            <table class="profile-table">
                <tr><th>ID:</th><td><?= htmlspecialchars($officerDetails['offID']) ?></td></tr>
                <tr><th>Name:</th><td><?= htmlspecialchars($officerDetails['Fname'] . " " . $officerDetails['Lname']); ?></td></tr>
                <tr><th>Designation:</th><td>Senior Officer</td></tr>
                <tr><th>Email:</th><td><?= htmlspecialchars($officerDetails['email']); ?></td></tr>
                <tr><th>Phone:</th><td><?= htmlspecialchars($officerDetails['phone_no']); ?></td></tr>
                <tr><th>Age:</th><td><?= htmlspecialchars($officerDetails['age']); ?></td></tr>
                <tr><th>Sex:</th><td><?= htmlspecialchars($officerDetails['sex']); ?></td></tr>
            </table>
        </div>

        <div id="stock-overview" class="section">
            <h2>Stock Overview</h2>
            <h3>Junior Officers and their Stock</h3>
            <table border="1">
                <tr>
                    <th>#</th>
                    <th>Junior Officer</th>
                    <th>Region ID</th>
                    <th>Total Stock</th>
                </tr>
                <?php $index = 1; foreach ($juniorOfficers as $officer): ?>
                    <tr>
                        <td><?= $index++ ?></td>
                        <td><?= $officer['offID'] ?></td>
                        <td><?= $officer['regionID'] ?: 'N/A' ?></td>
                        <td><?= $officer['total_stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="stock-allocation" class="section">
            <h2>Stock Allocation</h2>
            <p>Total Stock: <?= $totalstock ?></p>
            <form method="post">
                <table border="1">
                    <tr>
                        <th>#</th>
                        <th>Junior Officer</th>
                        <th>Stock Allocated</th>
                    </tr>
                    <?php $index = 1; foreach ($juniorOfficers as $officer): ?>
                        <tr>
                            <td><?= $index++ ?></td>
                            <td><?= $officer['offID'] ?></td>
                            <td><input type="number" name="allocations[<?= $officer['offID'] ?>]" min="0" required></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <button type="submit" name="stockbtn">Allocate Stock</button>
            </form>
        </div>

        <div id="previous-allocations" class="section">
            <h3>Previous Allocations</h3>
            <table border="1">
                <tr>
                    <th>#</th>
                    <th>Junior Officer</th>
                    <th>Amount Allocated</th>
                    <th>Date Allocated</th>
                </tr>
                <?php $index = 1; foreach ($previousAllocations as $allocation): ?>
                    <tr>
                        <td><?= $index++ ?></td>
                        <td><?= $allocation['officerID'] ?></td>
                        <td><?= $allocation['amount_allocated'] ?></td>
                        <td><?= $allocation['allocation_date'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div id="logout" class="section">
            <h2>Logout</h2>
            <p>You have been logged out.</p>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const links = document.querySelectorAll(".sidebar ul li a");
    const sections = document.querySelectorAll(".section");

    function showSection(sectionId) {
        sections.forEach(section => section.style.display = "none");
        let targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.style.display = "block";
        }
    }

    links.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();

            // Remove active class from all links
            links.forEach(l => l.classList.remove("active"));

            // Get section ID from href attribute
            let sectionId = this.getAttribute("href").substring(1);
            showSection(sectionId);

            // Add active class to the clicked link
            this.classList.add("active");
        });
    });

    // Show the first section by default
    if (sections.length > 0) {
        showSection(sections[0].id);
        links[0].classList.add("active");
    }

    // Logout button click event
    const logoutButton = document.querySelector('a[href="logout.php"]');
    if (logoutButton) {
        logoutButton.addEventListener("click", function (e) {
            e.preventDefault();
            window.location.href = "logout.php";
        });
    }

    
</script>
<script src="jsfiles/off2.js"></script> 

</body>
</html>