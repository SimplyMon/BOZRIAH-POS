<?php
include './fetch_order_history.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOZRIAH History</title>
    <link rel="stylesheet" href="../styles/history.css">
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="icon" type="image/x-icon" href="../assets/products/logo.png">
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
        <a href="./accounts.php" class="sidebar-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"></circle>
                <path d="M6 20c0-4 3-6 6-6s6 2 6 6"></path>
            </svg>
            <span class="sidebar-text">Accounts</span>
        </a>

        <!-- History -->
        <a href="./history.php" class="sidebar-link" style=" background: #f48734;color: white;">
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
        <div class="search-container">
            <h2>Transaction History</h2>

            <!-- Search Bar -->
            <div class="search-wrapper">
                <input type="text" id="invoiceSearch" placeholder="Search Invoice Number..." onkeyup="filterInvoices()">
                <span class="clear-btn" onclick="clearSearch()">✖</span>
            </div>
        </div>

        <div class="history-container">
            <table id="transactionTable">
                <thead>
                    <tr>
                        <th>Invoice Number</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Amount</th>
                        <th>VATable</th>
                        <th>VAT</th>
                        <th>Dine</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (!empty($transactions)) : ?>
                        <?php foreach ($transactions as $transaction) : ?>
                            <tr onclick='openTransactionModal(<?php echo json_encode($transaction['InvoiceNo']); ?>, <?php echo json_encode($transaction['SeqNo']); ?>)'>
                                <td><?php echo htmlspecialchars($transaction['InvoiceNo']); ?></td>
                                <td><?php echo date('M. d, Y', strtotime($transaction['InvoiceDate'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['CashierID']); ?></td>
                                <td><?php echo number_format($transaction['FinalInvoiceAmount'], 2); ?></td>
                                <td><?php echo number_format($transaction['VATableAmount'], 2); ?></td>
                                <td><?php echo number_format($transaction['VATAmount'], 2); ?></td>
                                <td>
                                    <?php echo ($transaction['DineType'] == 'I') ? 'Dine In' : 'Take Out'; ?>
                                </td>
                                <td>
                                    <?php
                                    echo ($transaction['DiscPercent'] > 0) ? 'Discounted' : 'Regular';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="11">No transaction history available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="no-orders-message" style="display: none; margin-top: 10px;">No orders found...</div>
        </div>

        <!-- Modal -->
        <div id="transactionModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeTransactionModal()">&times;</span>
                <h2>Transaction Details</h2>
                <div id="modalContent">
                </div>
            </div>
        </div>

    </div>


    <script src="../js/logout.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/history.js"></script>

</body>

</html>