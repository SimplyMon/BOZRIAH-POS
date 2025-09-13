<?php

include '../config/app_config.php';
include '../config/dbconn.php';

if (!isset($_GET['invoiceNo'])) {
    echo "Error: Invoice Number not provided.";
    exit;
}
$invoiceNo = $_GET['invoiceNo'];

try {
    // Fetch data from TRAH
    $trahQuery = "SELECT
                        dbo.d1(InvoiceNo) AS InvoiceNo,
                        InvoiceDate,
                        BranchCode,
                        TerminalNo,
                        CashierID,
                        dbo.d1(DiscCode) AS DiscCode,
                        DiscPercent,
                        FinalInvoiceAmount,
                        VATableAmount,
                        VATAmount,
                        SONumber,
                        GrossAmount
                    FROM TRAH
                    WHERE dbo.d1(InvoiceNo) = ?";
    $stmtTrah = $conn->prepare($trahQuery);
    $stmtTrah->execute([$invoiceNo]);
    $trahData = $stmtTrah->fetch(PDO::FETCH_ASSOC);

    if (!$trahData) {
        echo "Error: Invoice details not found.";
        exit;
    }

    // Fetch order details from TRAD
    $tradQuery = "SELECT
                        dbo.d1(Description) AS ItemDesc,
                        Quantity,
                        Amount
                    FROM TRAD
                    WHERE InvoiceSeqNo = (SELECT TOP 1 SeqNo FROM TRAH WHERE dbo.d1(InvoiceNo) = ?)";
    $stmtTrad = $conn->prepare($tradQuery);
    $stmtTrad->execute([$invoiceNo]);
    $tradItems = $stmtTrad->fetchAll(PDO::FETCH_ASSOC);

    // Fetch payment details from TRAD2
    $trad2Query = "SELECT
                        AmountPayment,
                        TotalGCAmount,
                        TotalCardPayment,
                        TotalOtherTenderAmount,
                        AmountChange
                    FROM TRAD2
                    WHERE InvoiceSeqNo = (SELECT TOP 1 SeqNo FROM TRAH WHERE dbo.d1(InvoiceNo) = ?)";
    $stmtTrad2 = $conn->prepare($trad2Query);
    $stmtTrad2->execute([$invoiceNo]);
    $trad2Data = $stmtTrad2->fetch(PDO::FETCH_ASSOC);

    // Fetch isTakeout ib OSEH   OSNumber in TRAH
    $osehQuery = "SELECT TOP 1 isTakeout FROM OSEH WHERE OSNumber = ?";
    $stmtOseh = $conn->prepare($osehQuery);
    $stmtOseh->execute([$trahData['SONumber']]);
    $osehData = $stmtOseh->fetch(PDO::FETCH_ASSOC);
    $takeoutStatus = ($osehData && $osehData['isTakeout'] == 1) ? 'TAKE-OUT' : 'DINE-IN';

    // Calculate Discount Amount
    $discountAmount = 0;
    if ($trahData['DiscPercent'] > 0) {
        $discountAmount = $trahData['GrossAmount'] * ($trahData['DiscPercent'] / 100);
    }

    // Calculate Total
    $totalAmount = $trahData['FinalInvoiceAmount'];

    // Calculate Change
    $changeAmount = $trad2Data['AmountChange'];

    // Calculate Total Payment Received
    $totalPaymentReceived = ($trad2Data['AmountPayment'] + $trad2Data['TotalGCAmount'] + $trad2Data['TotalCardPayment'] + $trad2Data['TotalOtherTenderAmount']) - $changeAmount;

?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Receipt</title>
        <style>
            body {
                font-family: consolas, monospace;
                font-size: 18px;
                width: 30ch;
                margin: 0 auto;
            }

            body,
            div,
            span {
                line-height: 1.05;
                margin: 0;
                padding: 0;
            }

            .center {
                text-align: center;
            }

            .left {
                text-align: left;
            }

            .right {
                text-align: right;
            }

            .bold {
                font-weight: bold;
            }

            .underline {
                text-decoration: underline;
            }

            .item-row {
                display: flex;
                justify-content: space-between;
            }

            .item-desc {
                flex: 1;
            }

            .item-price {
                width: 6ch;
                text-align: right;
            }

            .totals-row,
            .breakdown-row,
            .payment-row {
                display: flex;
                justify-content: space-between;
            }

            .footer {
                margin-top: 8px;
                text-align: center;
                line-height: 1.2;
            }


            .separator {
                text-align: center;
            }

            .separator:before {
                content: "------------------------------";
                display: block;
            }
        </style>
    </head>

    <body>
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
                Cashier: <?php echo $trahData['CashierID']; ?><br>
                MIN: <?php echo $min; ?><br>
                Serial: <?php echo $serial; ?>
            </span>
        </div>
        <div class="separator"></div>
        <div class="center bold">
            <span>
                *** REPRINT ***<br>
                *** Cash Invoice ***
            </span>
        </div>
        <div class="separator"></div>

        <div class="left">
            <span>
                CI No.: <?php echo $trahData['InvoiceNo']; ?><br>
                CI Date: <?php echo date('m/d/Y (D)', strtotime($trahData['InvoiceDate'])); ?>
            </span>
        </div>
        <div class="separator"></div>
        <div class="center">
            <span>
                REPRINTED ON<br>
                <?php echo date('m/d/Y (D)'); ?><br>
                BY <?php echo $trahData['CashierID']; ?>
            </span>
        </div>

        <div class="separator"></div>

        <div class="center bold">
            <span>*** <?php echo $takeoutStatus; ?> ***</span>
        </div>

        <div class="separator"></div>

        <?php foreach ($tradItems as $item): ?>
            <div class="item-row">
                <div class="item-desc"><?php echo number_format($item['Quantity'], 0); ?> SET <?php echo $item['ItemDesc']; ?></div>
                <div class="item-price"><?php echo number_format($item['Amount'], 2); ?></div>
            </div>
        <?php endforeach; ?>
        <div class="separator"></div>
        <div class="totals-row bold">
            <div>Gross Sales</div>
            <div><?php echo number_format($trahData['GrossAmount'], 2); ?></div>
        </div>

        <?php if ($trahData['DiscPercent'] > 0 || !empty($trahData['DiscCode'])): ?>
            <div class="totals-row">
                <div>Discount <?php echo $trahData['DiscCode']; ?> <?php echo $trahData['DiscPercent'] ? number_format($trahData['DiscPercent'], 2) . '%' : ''; ?></div>
                <div><?php echo number_format($discountAmount, 2); ?></div>
            </div>
        <?php endif; ?>

        <div class="totals-row bold">
            <div>Total</div>
            <div><?php echo number_format($totalAmount, 2); ?></div>
        </div>
        <div class="separator"></div>

        <div class="center bold">
            Amount Breakdown
        </div>

        <div class="left">
            <div class="breakdown-row"><span>VATable Sales</span><span><?php echo number_format($trahData['VATableAmount'], 2); ?></span></div>
            <div class="breakdown-row"><span>VAT Amount</span><span><?php echo number_format($trahData['VATAmount'], 2); ?></span></div>
            <div class="breakdown-row"><span>VAT Exempt Sales</span><span>0.00</span></div>
            <div class="breakdown-row"><span>Zero-Rated Sales</span><span>0.00</span></div>
            <div class="separator"></div>
            <div class="breakdown-row bold"><span>Total Amount</span><span><?php echo number_format($totalAmount, 2); ?></span></div>
            <div class="separator"></div>
        </div>

        <div class="left">
            <div class="payment-row"><span>Cash Tendered</span><span><?php echo number_format($totalPaymentReceived + $changeAmount, 2); ?></span></div>
            <div class="payment-row"><span>Change</span><span><?php echo number_format($changeAmount, 2); ?></span></div>
            <div class="separator"></div>
            <div class="payment-row bold"><span>Total Payment Received</span><span><?php echo number_format($totalPaymentReceived, 2); ?></span></div>
            <div class="separator"></div>
        </div>

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
    </body>

    </html>
<?php

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
function formatCurrency($amount)
{
    return number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sales Report Receipt</title>
    <style>
        body {
            font-family: consolas, monospace;
            font-size: 16px;
            width: 30ch;
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }

        .item-row,
        .totals-row,
        .breakdown-row,
        .payment-row {
            display: flex;
            justify-content: space-between;
        }

        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 12px;
            line-height: 1.2;
        }
    </style>
</head>

<body>

    <div class="center bold">
        SALES REPORT<br>
        <?php echo $formattedDate; ?><br>
        Shift: <?php echo $shiftStartTime . ' - ' . $shiftEndTime; ?><br>
        Invoice Range: <?php echo $firstInvoice . ' - ' . $lastInvoice; ?><br>
    </div>

    <div class="separator"></div>

    <div class="totals-row bold">
        <div>Gross Sales</div>
        <div><?php echo formatCurrency($salesSummary['TotalGrossSales'] ?? 0); ?></div>
    </div>
    <div class="totals-row bold">
        <div>Total Discounts</div>
        <div><?php echo formatCurrency($salesSummary['TotalDiscounts'] ?? 0); ?></div>
    </div>
    <div class="totals-row bold">
        <div>VATable Amount</div>
        <div><?php echo formatCurrency($salesSummary['TotalVATableAmount'] ?? 0); ?></div>
    </div>
    <div class="totals-row bold">
        <div>VAT Amount</div>
        <div><?php echo formatCurrency($salesSummary['TotalVATAmount'] ?? 0); ?></div>
    </div>

    <div class="separator"></div>

    <div class="bold">Discount Breakdown</div>
    <?php if (!empty($discountBreakdown)): ?>
        <?php foreach ($discountBreakdown as $discount): ?>
            <div class="item-row">
                <div><?php echo htmlspecialchars($discount['DiscountCode']); ?> x<?php echo $discount['DiscountQuantity']; ?></div>
                <div><?php echo formatCurrency($discount['TotalDiscountAmount']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="center">No discounts applied.</div>
    <?php endif; ?>

    <div class="separator"></div>

    <div class="bold">Payment Breakdown</div>
    <div class="payment-row">
        <div>Cash</div>
        <div><?php echo formatCurrency($paymentBreakdown['TotalCashPayment'] ?? 0); ?></div>
    </div>
    <div class="payment-row">
        <div>Card</div>
        <div><?php echo formatCurrency($paymentBreakdown['TotalCardPayment'] ?? 0); ?></div>
    </div>
    <div class="payment-row">
        <div>Gift Card</div>
        <div><?php echo formatCurrency($paymentBreakdown['TotalGCAmount'] ?? 0); ?></div>
    </div>
    <div class="payment-row">
        <div>Other Tender</div>
        <div><?php echo formatCurrency($paymentBreakdown['TotalOtherTenderAmount'] ?? 0); ?></div>
    </div>
    <div class="payment-row">
        <div>Change</div>
        <div><?php echo formatCurrency($paymentBreakdown['TotalChange'] ?? 0); ?></div>
    </div>

    <div class="separator"></div>

    <div class="center footer">
        Powered by Your POS System<br>
        Thank you!
    </div>

</body>

</html>