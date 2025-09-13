<?php
include './fetch_accounts.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOZRIAH Accounts</title>
    <link rel="icon" type="image/x-icon" href="../assets/products/logo.png">
    <link rel="stylesheet" href="../styles/accounts.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
</head>


<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="logo">
            <img src="../assets/products/logo.png" alt="Logo">
            <h1 class="sidebar-text">Bozriah</h1>
            <button id="toggleBtn">☰</button>
        </div>

        <!-- nav linksd -->
        <a href="./index.php" class="sidebar-link active" style="margin-top: 45px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <!-- Orders -->
        <a href="./orders.php" class="sidebar-link">
            <svg xmlns=" http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M9 6h6m-3-3h3a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h3" />
            </svg>
            <span class="sidebar-text">Orders</span>
        </a>

        <!-- Inventory -->
        <a href="inventory.php" class="sidebar-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10l1.5-4.5a2 2 0 011.9-1.5h11.2a2 2 0 011.9 1.5L21 10m-2 0H5m14 0v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7m5 0V5h4v5m-4 0h4" />
            </svg>
            <span class="sidebar-text">Items Available</span>
        </a>

        <!-- Accounts -->
        <a href="./accounts.php" class="sidebar-link" style=" background: #f48734;color: white;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M6 20c0-4 3-6 6-6s6 2 6 6"></path>
            </svg>
            <span class="sidebar-text">Accounts</span>
        </a>

        <!-- History -->
        <a href="./history.php" class="sidebar-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-6.219-8.731m-.281-1.269V4m0-4h4m-4 0h-4" />
            </svg>
            <span class="sidebar-text">History</span>
        </a>

        <!-- Sales Report -->
        <a href="./sales-report.php" class="sidebar-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3v18h18M9 17v-6m4 6v-4m4 4v-8" />
            </svg>
            <span class="sidebar-text">Sales Report</span>
        </a>

        <!-- User Profile -->
        <div class="user-profile" id="userProfile">
            <img src="../assets/products/default.webp" alt="Profile Picture" class="profile-img">
            <div class="user-info">
                <p class="user-name">
                    <?php echo htmlspecialchars($_SESSION['user']['FirstName'] . ' ' . $_SESSION['user']['LastName']); ?>
                </p>
                <p class="user-role">
                    <?php echo htmlspecialchars($_SESSION['user']['Role']); ?>
                </p>
            </div>
        </div>

        <!-- Custom Logout Context Menu -->
        <div id="logoutMenu" class="logout-menu">
            <ul>
                <li onclick="logoutUser()">Logout</li>
            </ul>
        </div>

    </div>

    <div class="main-content">
        <div class="card">
            <h2>Edit Account Details</h2>
            <?php if (isset($_SESSION['alert'])): ?>
                <div class="alert <?php echo $_SESSION['alert']['type']; ?>">
                    <?php echo $_SESSION['alert']['message']; ?>
                </div>
                <?php unset($_SESSION['alert']);
                ?>
            <?php endif; ?>

            <form action="update_account.php" method="post">

                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <p><?php echo htmlspecialchars($user['FirstName'] ?? ''); ?></p>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <p><?php echo htmlspecialchars($user['LastName'] ?? ''); ?></p>
                </div>

                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <p><?php echo htmlspecialchars($user['EmailAddress'] ?? ''); ?></p>
                </div>

                <div class="form-group">
                    <label for="user_id">User ID:</label>
                    <p><?php echo htmlspecialchars($user['UserID'] ?? ''); ?></p>
                </div>

                <label style="margin-top: 20px;" for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" placeholder="Enter current password">

                <label style="margin-top: 20px;" for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" placeholder="Enter new password (leave blank to keep current)">

                <button class="submitBTN" type="submit">Save Changes</button>
            </form>


        </div>
    </div>




    <script src="../js/logout.js"></script>
    <script src="../js/sidebar.js"></script>


</body>

</html>