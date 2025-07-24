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
$rID=0;
$regions= fetchAllRows($conn, "SELECT * FROM regions WHERE regionID>? ", "i", $rID);

$previousForms= fetchAllRows($conn, "SELECT * FROM quality_inspections WHERE inspectorID = ?", "i", $offID);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Officer Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="off5.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const links = document.querySelectorAll(".sidebar ul li a");
            const sections = document.querySelectorAll(".section");

            links.forEach(link => {
                link.addEventListener("click", function (e) {
                    e.preventDefault();
                    const target = this.getAttribute("data-target");

                    sections.forEach(section => {
                        section.style.display = "none";
                    });

                    document.getElementById(target).style.display = "block";
                });
            });

            document.getElementById("profile-section").style.display = "block"; // Default section
        });
    </script>
</head>
<body>

<div class="sidebar">
    <div class="logo">
    <img src="images/firm_logo1.png" alt="Logo">
        <h2><i class="fa-solid fa-leaf"></i> Quality Officer</h2>
    </div>
    <ul>
        <li><a href="#" data-target="profile-section"><i class="fa-solid fa-user"></i> Profile</a></li>
        <li><a href="#" data-target="new-form-section"><i class="fa-solid fa-tractor"></i> New Form</a></li>
        <li><a href="#" data-target="previous-inspections-section"><i class="fa-solid fa-tree"></i> Previous Forms</a></li>
        <li><a href="#" data-target="analytics-section"><i class="fa-solid fa-chart-bar"></i> Analytics</a></li>
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>

    <!-- Profile Section -->
    <section id="profile-section" class="section">
        <h3>Your Profile</h3>
        <div class="profile-container">
            <img src="images/f2.jpg" alt="Profile Picture" class="profile-pic">
            <table class="profile-table">
                <tr><th>ID:</th><td><?php echo htmlspecialchars($officerDetails['offID']); ?></td></tr>
                <tr><th>Name:</th><td><?php echo htmlspecialchars($officerDetails['Fname']) . " " . htmlspecialchars($officerDetails['Lname']); ?></td></tr>
                <tr><th>Designation:</th><td>Quality Control Officer</td></tr>
                <tr><th>Email:</th><td><?php echo htmlspecialchars($officerDetails['email']); ?></td></tr>
                <tr><th>Phone:</th><td><?php echo htmlspecialchars($officerDetails['phone_no']); ?></td></tr>
                <tr><th>Age:</th><td><?php echo htmlspecialchars($officerDetails['age']); ?></td></tr>
                <tr><th>Sex:</th><td><?php echo htmlspecialchars($officerDetails['sex']); ?></td></tr>
            </table>
        </div>
    </section>

    <!-- New Form Section -->
    <section id="new-form-section" class="section" style="display: none;">
        <h2>Fertilizer Quality Inspection Form</h2>
        <form method="POST" action="">
            <label>Date:</label>
            <input type="date" name="date" required><br>

            <label>Inspector's Name:</label>
            <input type="text" name="inspector_name" required><br>

            <label>Region:</label>
            <select name="region_id" required>
                <?php
                foreach ($regions as $region) {
                    echo "<option value='" . $region['regionID'] . "'>" . $region['regionName'] . "</option>";
                }
                ?>
            </select><br>

            <label>Supplier/Distributor:</label>
            <input type="text" name="supplier" required><br>

            <h3>Physical Checks</h3>
            <label>Packaging Condition:</label>
            <select name="packaging">
                <option value="Good">Good</option>
                <option value="Damaged">Damaged</option>
                <option value="Poor Labeling">Poor Labeling</option>
            </select><br>

            <label>Weight Check:</label>
            <select name="weight_check">
                <option value="Correct">Correct</option>
                <option value="Underweight">Underweight</option>
                <option value="Overweight">Overweight</option>
            </select><br>

            <h3>Compliance & Action</h3>
            <label>Batch Matches Supplier Records?</label>
            <select name="batch_match">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select><br>

            <label>Approve for Distribution?</label>
            <select name="approval">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
                <option value="Further Testing Needed">Further Testing Needed</option>
            </select><br>

            <label>Comments:</label>
            <textarea name="comments"></textarea><br>

            <input type="submit" value="Submit">
        </form>
    </section>

    <!-- Previous Inspections Section -->
    <section id="previous-inspections-section" class="section" style="display: none;">
        <h2>Previous Inspections</h2>
        <table border="1">
            <tr>
                <th>Inspection ID</th>
                <th>Date</th>
                <th>Inspector</th>
                <th>Region</th>
                <th>Supplier</th>
                <th>Approval</th>
                <th>Comments</th>
            </tr>
            <?php 
            foreach ($previousForms as $inspection) { ?>
                <tr>
                    <td><?php echo $inspection['inspectionID']; ?></td>
                    <td><?php echo $inspection['inspection_date']; ?></td>
                    <td><?php echo $inspection['inspectorID']; ?></td>
                    <td>
                        <?php 
                        $region_name = getSingleValue($conn, "SELECT regionName FROM regions WHERE regionID = ?", "i", $inspection['regionID']);
                        echo $region_name;
                        ?>
                    </td>
                    <td><?php echo $inspection['supplier']; ?></td>
                    <td><?php echo $inspection['approval_status']; ?></td>
                    <td><?php echo $inspection['comments']; ?></td>
                </tr>
            <?php } ?>
        </table>
    </section>

    <!-- Analytics Section -->
    <section id="analytics-section" class="section" style="display: none;">
        <h2>Analytics (To be implemented)</h2>
    </section>
</div>

</body>
</html>
