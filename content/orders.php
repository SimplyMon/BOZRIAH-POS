<?php
include './fetch_orders.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOZRIAH Orders</title>
    <link rel="stylesheet" href="../styles/orders.css">
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
        <a href="./orders.php" class="sidebar-link" style=" background: #f48734;color: white;">
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

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header Container -->
        <div class="header-container">
            <div style="display: flex; align-items: center;">
                <h1>Order Summary</h1>

                <!-- Refresh Button -->
                <button id="refreshBtn" class="refresh-button">Get Orders</button>
            </div>

            <!-- Search Bar -->
            <div class="search-wrapper">
                <input type="text" id="invoiceSearch" placeholder="Search Order Number..." onkeyup="filterInvoices()">
                <span class="clear-btn" onclick="clearSearch()" style="display: none;">✖</span>
            </div>
        </div>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Date</th>
                    <th>Order Type</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <?php if (count($rows) > 0): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr class="order-row" data-seqno="<?= $row['SeqNo'] ?>">
                            <td><?= $row['OSNumber'] ?></td>
                            <td><?= $row['OSDate'] ?></td>
                            <td><?= $row['isTakeout'] == 1 ? 'Take-Out' : 'Dine-In' ?></td>
                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Order Details Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close-btn">&times;</span>
            <h2>Order Details</h2>
            <div class="table-container" style="max-height: 265px">
                <table>
                    <thead>
                        <tr>
                            <th class="left-align">Product Name</th>
                            <th class="center-align">Quantity</th>
                            <th class="right-align">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody id="orderDetails"></tbody>
                </table>
            </div>

            <!-- Grand Total & VAT Breakdown -->
            <div id="orderSummary">
                <div class="summary-header">
                    <p></p>
                    <h3>Grand Total: <span id="grandTotal">0.00</span></h3>
                </div>

                <table>
                    <tr>
                        <td class="right-align">Vatable Amount</td>
                        <td class="right-align"><span id="vatableAmount">0.00</span></td>
                    </tr>
                    <tr>
                        <td class="right-align">VAT Amount (12%)</td>
                        <td class="right-align"><span id="vatAmount">0.00</span></td>
                    </tr>
                    <tr>
                        <td class="right-align"><strong>Total</strong></td>
                        <td class="right-align"><strong><span id="totalAmount">0.00</span></strong></td>
                    </tr>
                </table>
                <div class="summary-button-container">
                    <button id="applyDiscountBtn" class="discount-btn">Apply Discount</button>
                    <button id="confirmBtn" class="summary-button">Receive Payment</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DISCOUNTS MODAL -->
    <div id="discountModal" class="modal">
        <div class="modal-content" style="max-height: 100vh;">
            <span class="close-discount-btn">&times;</span>
            <h2>Apply Discount</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Percentage</th>
                            <th>Max DISC</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="discountOptions"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dark Background Overlay -->
    <div id="paymentOverlay" class="payment-overlay"></div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <span class="close-payment-btn">&times;</span>
            <h2>Receive Payment</h2>
            <label>Payment Method:</label>
            <div id="paymentButtons" class="payment-buttons">
                <button type="button" class="payment-method-btn" data-method="CASH">Cash</button>
                <button type="button" class="payment-method-btn" data-method="CARD">Card</button>
                <button type="button" class="payment-method-btn" data-method="GCASH">GCash</button>
                <button type="button" id="otherPaymentBtn">Others</button>
            </div>

            <div id="otherPaymentMethods" style="display: none;">
                <label>Other Payment Methods:</label>
                <button type="button" class="payment-method-btn" data-method="PAYPAL">PayPal</button>
                <button type="button" class="payment-method-btn" data-method="MAYA">Maya</button>
            </div>

            <input style="display: none !important;" type="number" id="grandTotalPayment" readonly>

            <label>Grand Total:</label>
            <input type="number" id="grandTotal1" readonly>



            <input type="hidden" id="paymentMethod">

            <!-- GCASH  -->
            <div id="gcashFields" style="display: none;">
                <label>GCash Mobile Number:</label>
                <select id="gcashMobileNumber">
                    <option value="">Select GCash Account</option>
                </select>

                <label>GCash Reference No.:</label>
                <input type="text" id="gcashReferenceNo" placeholder="Enter Reference Number">
            </div>

            <!-- CARD  -->
            <div id="cardFields" style="display: none;">
                <label>Card Type:</label>
                <select id="cardType">
                    <option value="">Select Card Type</option>
                </select>

                <label>Card Number:</label>
                <input type="text" id="cardNumber" placeholder="XXXXX-XXXXX" maxlength="11">

                <label>Expiration Date:</label>
                <input type="text" id="cardExpDate" placeholder="YYYY-MM-DD" maxlength="10">
            </div>

            <div id="paymentFields" style="display: none;">
                <p style="font-size: 14px; color: gray;">PayPal & Maya payments do not require extra details.</p>
            </div>

            <label>Customer Payment:</label>
            <input style="font-weight: bold;" type="number" id="customerPayment" min="0" step="0.01" required>

            <label>Remaining Balance:</label>
            <input type="number" id="remainingBalance" readonly>

            <label>Change:</label>
            <input type="number" id="changeAmount" readonly>

            <button id="confirmPaymentBtn">Confirm Payment</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="modal">
        <div class="modal-content">
            <span class="close-error-btn">&times;</span>
            <p id="errorMessage">Insufficient Payment</p>
        </div>
    </div>

    <div id="successModal" class="success-modal">
        <div class="success-modal-content">
            <img src="../assets/products/payment-success.png" alt="">
            <button style="font-size: 20px;" id="closeSuccessModal">OK</button>
        </div>
    </div>

    <script src="../js/discountModal.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/orderDetails.js"></script>
    <script src="../js/logout.js"></script>
    <script src="../js/fetchGcashAccounts.js"></script>
    <script src="../js/fetchCardTypes.js"></script>
    <script src="../js/cardFormatting.js"></script>
    <script src="../js/orderSearch.js"></script>


</body>

</html>