<?php
session_start();
include 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset(); // Clear all session variables
    session_destroy(); // Destroy the session
    header('Location: login.php'); // Redirect to login page
    exit();
}

// Fetch data from database where status is 'verified'
$sql = "SELECT id, accountno, district, branch, Name, Email_id, MobileNo, Address, Occupation, lockerType, nominee_name, relation_with_nominee, id_proof, id_proof_file, paymentMode, lockerCharges, processingFees, gstAmount, totalAmount, booking_date, status FROM locker_bookings WHERE status = 'verified'";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Error fetching records: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locker Bookings - Admin Panel</title>
    <style>
        /* General styles from admin.php */
        body {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f4f7fc;
            color: #666;
            font-family: "Montserrat", sans-serif;
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: auto 1fr;
            grid-template-areas:
                "header"
                "main";
            height: 100vh;
        }

        .header {
            grid-area: header;
            background-color: #0adcd1;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 30px 10px;
        }

        .header h2 {
            margin: 0;
            padding-left: 80px;
            font-size: 30px;
            font-weight: bolder;
        }

        .logout-btn {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #e53935;
        }

        .main-content {
            grid-area: main;
            display: grid;
            grid-template-columns: 300px 1fr; /* Sidebar 300px wide */
            grid-template-areas: "sidebar main";
            height: 100%;
            overflow: auto;
        }

        #sidebar {
            grid-area: sidebar;
            background-color: #434447;
            color: #9799ab;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }

        .sidebar-list {
            list-style: none;
            padding: 2px;
        }

        .sidebar-list-item {
            padding: 15px 0;
        }

        .sidebar-list-item a {
            text-decoration: none;
            color: #f0f0f0;
        }

        .sidebar-list-item a:hover {
            color: #d7780c;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0adcd1;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .image-cell img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <h2>Admin Dashboard</h2>
        </div>
        <a href="admin.php?action=logout" class="logout-btn">Logout</a>
    </header>
    <!-- End Header -->

    <!-- Main content grid (Sidebar + Main area) -->
    <div class="main-content">
        <!-- Sidebar -->
        <aside id="sidebar">
            <ul class="sidebar-list">
                <li class="sidebar-list-item"><a href="admin.php">Dashboard</a></li>
                <li class="sidebar-list-item"><a href="formverification.php">Booking Form Notifications</a></li>
                <li class="sidebar-list-item"><a href="admin.php">Add Sub-Admin</a></li>
            </ul>
        </aside>
        <!-- End Sidebar -->

        <!-- Main Content -->
        <main id="main-content">
            <div class="table-container">
                <h3>Locker Bookings</h3>
                <table>
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>Account No</th>
                            <th>District</th>
                            <th>Branch</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile No</th>
                            <th>Address</th>
                            <th>Occupation</th>
                            <th>Locker Type</th>
                            <th>Nominee Name</th>
                            <th>Relation with Nominee</th>
                            <th>ID Proof</th>
                            <th>ID Proof File</th>
                            <th>Payment Mode</th>
                            <th>Locker Charges</th>
                            <th>Processing Fees</th>
                            <th>GST Amount</th>
                            <th>Total Amount</th>
                            <th>Booking Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <!-- <td><?php echo htmlspecialchars($row['id']); ?></td> -->
                                    <td><?php echo htmlspecialchars($row['accountno']); ?></td>
                                    <td><?php echo htmlspecialchars($row['district']); ?></td>
                                    <td><?php echo htmlspecialchars($row['branch']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Email_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['MobileNo']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Occupation']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lockerType']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nominee_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['relation_with_nominee']); ?></td>
                                    <td><?php echo htmlspecialchars($row['id_proof']); ?></td>
                                    <td class="image-cell"><img src="uploads/<?php echo htmlspecialchars($row['id_proof_file']); ?>" alt="ID Proof"></td>
                                    <td><?php echo htmlspecialchars($row['paymentMode']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lockerCharges']); ?></td>
                                    <td><?php echo htmlspecialchars($row['processingFees']); ?></td>
                                    <td><?php echo htmlspecialchars($row['gstAmount']); ?></td>
                                    <td><?php echo htmlspecialchars($row['totalAmount']); ?></td>
                                    <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="20">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
        <!-- End Main Content -->
    </div>

    <?php
    // Close connection
    $conn->close();
    ?>
</body>
</html>
