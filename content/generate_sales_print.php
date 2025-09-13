<?php
include './fetch_sales_report.php';
?>
<style media="print">
    body {
        font-family: consolas, monospace;
        font-size: 13px;
    }

    .center {
        text-align: center;
    }

    .left {
        text-align: left;
    }

    .bold {
        font-weight: bold;
    }

    .separator {
        border-top: 1px dashed #000;
        margin: 10px 0;
    }

    .summary-item,
    .payment-item,
    .discount-item {
        display: flex;
        justify-content: space-between;
        margin: 2px 0;
    }

    .label {
        flex: 1.9;
    }

    .value {
        flex: 0.1;
        text-align: right;
    }
</style>

<div class="center header-info bold">
    <span>
        RAS ULTIMATE ENTERPRISE OPC<br>
        Unit 106 AIC Burgundy<br>
        Tower Ortigas Center
    </span>
</div>

<div class="center">
    <span>
        VAT Reg TIN: <?php echo $tin; ?><br>
        <span>ADB Ave. Cor. Sapphire Road</span>
    </span>
</div>

<div class="separator"></div>

<div class="left">
    <span>
        Branch: <?php echo $branchCode; ?><br>
        Terminal: <?php echo $terminalNo; ?><br>
        Cashier: <?php echo htmlspecialchars($cashierID); ?><br>
        MIN: <?php echo $min; ?><br>
        Serial: <?php echo $serial; ?><br>
        Gen. by: <?php echo htmlspecialchars(substr($cashierFname, 0, 8) . ' ' . substr($cashierLname, 0, 8)); ?><br>
        Rpt. Date:<?php echo date("m/d/Y (D)", strtotime($selectedDate)); ?><br>
    </span>
</div>

<div class="separator"></div>

<div class="center bold">
    <span>
        *** Transaction Summary ***<br>
    </span>
</div>

<div class="separator"></div>

<div class="left">
    <span>
        Beg CI: <?php echo $firstInvoice ?><br>
        End CI: <?php echo $lastInvoice ?>
    </span>
</div>

<div class="separator"></div>
<div class="center bold">
    <span>
        *** Transaction Amount ***<br>
    </span>
</div>
<div class="summary-item total">
    <span class="label">Gross Amount per CI:</span>
    <span class="value"><?php echo number_format($salesSummary['TotalGrossSales'], 2); ?></span>
</div>

<div class="separator"></div>
<div class="center bold">
    <span>
        *** Discount Summary ***<br>
    </span>
</div>

<div class="left">
    <span>
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
    </span>
</div>

<div class="separator"></div>

<div class="summary-item total">
    <span class="label">Total Amount:</span>
    <span class="value"><?php echo number_format(($paymentBreakdown['TotalCashPayment'] ?? 0) + ($paymentBreakdown['TotalCardPayment'] ?? 0) + ($paymentBreakdown['TotalGCAmount'] ?? 0) + ($paymentBreakdown['TotalOtherTenderAmount'] ?? 0) - ($paymentBreakdown['TotalChange'] ?? 0), 2); ?></span>
</div>

<div class="separator"></div>

<div class="center bold">
    <span>
        *** Amount Breakdown ***<br>
    </span>
</div>

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

<div class="separator"></div>

<div class="center bold">
    <span>
        Transaction Reconciliation <br>
    </span>
</div>

<div class="summary-item total">
    <span class="label">Total Amount per CI:</span>
    <span class="value"><?php echo number_format($salesSummary['TotalGrossSales'], 2); ?></span>
</div>

<div class="separator"></div>

<div class="center bold">
    <span>
        *** Payments Breakdown ***<br>
    </span>
</div>

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
<br>
<br>
<div class="center footer">
    <span>
        eTAXPOINT ePOS System Version 8.8<br>
        Provided By:<br>
        eTaxpoint Software Solutions Corp<br>
        Unit 2724 Corinthian Exec. Regency<br>
        Ortigas Center Pasig City<br>
        VAT Reg TIN: <?php echo $tin; ?><br>
        Accreditation No.: AC-111-222222-333333<br>
        Date Issued: 03/08/2024<br>
        PTU No.: 000-000-000000-000<br>
        Date Issued: 03/08/2024<br>
        === END ===
    </span>
</div>