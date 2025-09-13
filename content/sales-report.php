<?php
include './fetch_sales_report.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOZRIAH Sales Report</title>
    <link rel="stylesheet" href="../styles/sidebar.css">
    <link rel="icon" type="image/x-icon" href="../assets/products/logo.png">
    <link rel="stylesheet" href="../styles/sales.css">
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
        <a href="./history.php" class="sidebar-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-6.219-8.731m-.281-1.269V4m0-4h4m-4 0h-4" />
            </svg>
            <span class="sidebar-text">History</span>
        </a>

        <!-- Sales Report -->
        <a href="./sales-report.php" class="sidebar-link" style=" background: #f48734;color: white;">
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

    <!-- main -->
    <div class="main-content">
        <div class="card">
            <?php if (!empty($salesSummary)): ?>
                <div>
                    <div class="sales-header">
                        <h2>Sales Report</h2>
                        <form method="get">
                            <input type="date" name="report_date" value="<?php echo $selectedDate; ?>">
                            <button type="submit">View Report</button>
                        </form>
                    </div>
                    <hr>
                    <div class="summary-item">
                        <p><span class="label">Cashier:</span>
                            <span class="value"><?php echo !empty($cashierID) ? htmlspecialchars($cashierID) : 'N/A'; ?></span>
                        </p>
                        <p><span class="label">Shift:</span> <span class="value"><?php echo !empty($shiftStartTime) ? htmlspecialchars($shiftStartTime) : 'N/A'; ?> – <?php echo !empty($shiftEndTime) ? htmlspecialchars($shiftEndTime) : 'N/A'; ?></span></p>
                        <p><span class="label">Terminal:</span> <span class="value"><?php echo $terminalNo; ?></span></p>
                        <p><span class="label">Date:</span> <span class="value"><?php echo date("F j, Y", strtotime($selectedDate)); ?></span></p>
                    </div>
                    <hr>
                    <h3>Transactions Summary</h3>
                    <div class="summary-item">
                        <span class="label">Beg Invoice:</span>
                        <span class="value"><?php echo !empty($firstInvoice) ? $firstInvoice : 'N/A'; ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">End Invoice:</span>
                        <span class="value"><?php echo !empty($lastInvoice) ? $lastInvoice : 'N/A'; ?></span>
                    </div>

                    <hr>

                    <h3>Transactions Amount</h3>
                    <div class="summary-item">
                        <span class="label">Gross Sales:</span>
                        <span class="value"><?php echo number_format($salesSummary['TotalGrossSales'], 2); ?></span>
                    </div>

                    <hr>
                    <h3>Discount Summary</h3>
                    <?php if (!empty($discountBreakdown)): ?>
                        <?php foreach ($discountBreakdown as $discount): ?>
                            <div class="discount-item">
                                <span class="label"><?php echo $discount['DiscountCode']; ?> x<?php echo $discount['DiscountQuantity']; ?>:</span>
                                <span class="value"><?php echo number_format($discount['TotalDiscountAmount'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        <div class="summary-item total">
                            <span class="label">Total Discounts:</span>
                            <span class="value"><?php echo number_format($salesSummary['TotalDiscounts'], 2); ?></span>
                        </div>
                    <?php else: ?>
                        <p>No discounts applied.</p>
                    <?php endif; ?>
                    <hr>

                    <div class="summary-item total">
                        <span class="label">Total Amount:</span>
                        <span class="value"><?php echo number_format(($paymentBreakdown['TotalCashPayment'] ?? 0) + ($paymentBreakdown['TotalCardPayment'] ?? 0) + ($paymentBreakdown['TotalGCAmount'] ?? 0) + ($paymentBreakdown['TotalOtherTenderAmount'] ?? 0) - ($paymentBreakdown['TotalChange'] ?? 0), 2); ?></span>
                    </div>
                    <hr>
                    <h3>Amount Breakdown</h3>
                    <div class="summary-item">
                        <span class="label">VATable Sales:</span>
                        <span class="value"><?php echo number_format($salesSummary['TotalVATableAmount'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">VAT Amount:</span>
                        <span class="value"><?php echo number_format($salesSummary['TotalVATAmount'], 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Exempt Sales:</span>
                        <span class="value">0.00</span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Zero-rated Sales:</span>
                        <span class="value">0.00</span>
                    </div>

                    <hr>
                    <h3>Transaction Reconciliation</h3>
                    <div class="summary-item total">
                        <span class="label">Total Amount per CI:</span>
                        <span class="value"><?php echo number_format($salesSummary['TotalGrossSales'], 2); ?></span>
                    </div>
                    <hr>

                    <h3>Payments Breakdown</h3>
                    <div class="payment-item">
                        <span class="label">Cash Payment:</span>
                        <span class="value"><?php echo number_format($paymentBreakdown['TotalCashPayment'] ?? 0, 2); ?></span>
                    </div>
                    <div class="payment-item">
                        <span class="label">Card Payment:</span>
                        <span class="value"><?php echo number_format($paymentBreakdown['TotalCardPayment'] ?? 0, 2); ?></span>
                    </div>
                    <div class="payment-item">
                        <span class="label">GCash:</span>
                        <span class="value"><?php echo number_format($paymentBreakdown['TotalGCAmount'] ?? 0, 2); ?></span>
                    </div>
                    <div class="payment-item">
                        <span class="label">Other Tender:</span>
                        <span class="value"><?php echo number_format($paymentBreakdown['TotalOtherTenderAmount'] ?? 0, 2); ?></span>
                    </div>
                    <hr>
                    <div class="payment-item total">
                        <span class="label">Change:</span>
                        <span class="value">-<?php echo number_format($paymentBreakdown['TotalChange'] ?? 0, 2); ?></span>
                    </div>
                    <hr style="border-top: 2px solid #000;">
                    <div class="summary-item total">
                        <span class="label">TOTAL Sales:</span>
                        <span class="value"><?php echo number_format(($paymentBreakdown['TotalCashPayment'] ?? 0) + ($paymentBreakdown['TotalCardPayment'] ?? 0) + ($paymentBreakdown['TotalGCAmount'] ?? 0) + ($paymentBreakdown['TotalOtherTenderAmount'] ?? 0) - ($paymentBreakdown['TotalChange'] ?? 0), 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span class="label">Short/Over:</span>
                        <span class="value">0.00</span>
                    </div>
                    <hr>
                    <div class="btn-group">
                        <button onclick="printReceipt()">Print Report</button>
                    </div>
                </div>
            <?php else: ?>
                <p>No sales transactions found for the selected date.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/logout.js"></script>
    <script src="../js/sidebar.js"></script>
    <script src="../js/salesReport.js"></script>

</body>

</html>