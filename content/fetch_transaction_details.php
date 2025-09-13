<?php
include '../config/dbconn.php';

if (!isset($_GET['seqNo'])) {
    die("Invalid request.");
}

$seqNo = $_GET['seqNo'];

try {
    //  TRAD details
    $sql_trad = "SELECT ItemID, Description, Quantity, Amount FROM [POSDB].[dbo].[TRAD] WHERE InvoiceSeqNo = ?";
    $stmt_trad = $conn->prepare($sql_trad);
    $stmt_trad->execute([$seqNo]);
    $trad_results = $stmt_trad->fetchAll(PDO::FETCH_ASSOC);

    //  TRAD2 details
    $sql_trad2 = "SELECT AmountDue, Status, AmountPayment, AmountChange, TotalGCAmount,TotalOtherTenderAmount, TotalCardPayment, EntryDate FROM [POSDB].[dbo].[TRAD2] WHERE InvoiceSeqNo = ?";
    $stmt_trad2 = $conn->prepare($sql_trad2);
    $stmt_trad2->execute([$seqNo]);
    $trad2 = $stmt_trad2->fetch(PDO::FETCH_ASSOC);

    // TRAH details
    // TRAH details
    $sql_trah = "SELECT dbo.d1([DiscCode]) as DiscCode, [DiscPercent], [FinalInvoiceAmount] FROM [POSDB].[dbo].[TRAH] WHERE SeqNo = ?";
    $stmt_trah = $conn->prepare($sql_trah);
    $stmt_trah->execute([$seqNo]);
    $trah = $stmt_trah->fetch(PDO::FETCH_ASSOC);


    if (!$trad_results) {
        echo "<p>No transaction details found.</p>";
        exit;
    }

    // TRAD  order details
    echo "<h3>Purchased Items</h3>";
    echo "<div style='max-height: 250px; overflow-y: auto; border: 1px solid #ccc;'>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%;'>";
    echo "<tr>
    <th>Description</th>
    <th style='text-align: center;'>Quantity</th>
    <th>Amount</th>
  </tr>";
    foreach ($trad_results as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
        echo "<td style='text-align: center;'>" . number_format($row['Quantity']) . "</td>";
        echo "<td>" . number_format($row['Amount'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";


    //  TRAD2  payment details 
    if ($trad2) {
        echo '<div class="payment-section">';
        echo "<h3>Payment Details</h3>";
        $statusText = ($trad2['Status'] == 1) ? '<span style="color: green; font-weight: bold;">Paid</span>' : '<span style="color: red; font-weight: bold;">Unpaid</span>';

        echo '<div class="payment-details"><span>Status:</span> <span class="payment-amount">' . $statusText . '</span></div>';
        echo '<div class="payment-details"><span>Date:</span> <span class="payment-amount">' . date('M d, Y', strtotime($trad2['EntryDate'])) . '</span></div>';

        // DISCOUNT STATUS
        // DISCOUNT STATUS
        if ($trah && !empty($trah['DiscCode'])) {
            $discCode = htmlspecialchars($trah['DiscCode']);
            if (!is_null($trah['DiscPercent']) && $trah['DiscPercent'] > 0) {
                $discCode .= ' (' . rtrim(rtrim(number_format($trah['DiscPercent'], 2), '0'), '.') . '%)';
            }
            echo '<div class="payment-details"><span>Discount:</span> <span class="payment-amount">' . $discCode . '</span></div>';

            if (isset($trah['FinalInvoiceAmount'])) {
                $finalGrandTotal = $trah['FinalInvoiceAmount'];
                echo '<div class="payment-details"><span>Discounted GrandTotal:</span> <span class="payment-amount">' . number_format($finalGrandTotal, 2) . '</span></div>';
            }
        }

        // AMOUNT PAID
        $totalAmountPaid = $trad2['AmountPayment'] + $trad2['TotalGCAmount'] + $trad2['TotalOtherTenderAmount'] + $trad2['TotalCardPayment'];
        echo '<div class="payment-details"><span>Amount Paid:</span> <span class="payment-amount">' . number_format($totalAmountPaid, 2) . '</span></div>';


        if ($trad2['AmountPayment'] > 0) {
            echo '<div class="payment-details"><span>Payment Method:</span><span style= "font-weight: bold;">Cash:  <span class="payment-amount">' . number_format($trad2['AmountPayment'], 2) . '</span></span></div>';
        }
        if ($trad2['TotalGCAmount'] > 0) {
            echo '<div class="payment-details"><span>Payment Method:</span> <span style= "font-weight: bold;">GCash: <span class="payment-amount">' . number_format($trad2['TotalGCAmount'], 2) . '</span></span></div>';
        }

        if ($trad2['TotalCardPayment'] > 0) {
            echo '<div class="payment-details"><span>Payment Method:</span><span style= "font-weight: bold;">Card: <span class="payment-amount">' . number_format($trad2['TotalCardPayment'], 2) . '</span></span></div>';
        }

        if ($trad2['TotalOtherTenderAmount'] > 0) {
            echo '<div class="payment-details"><span>Payment Method:</span><span style= "font-weight: bold;">Maya/PayPal: <span class="payment-amount">' . number_format($trad2['TotalOtherTenderAmount'], 2) . '</span></span></div>';
        }

        echo '<div class="payment-details"><span>Change:</span> <span class="payment-amount">' . number_format($trad2['AmountChange'], 2) . '</span></div>';
        // echo '<div class="payment-details"><span>Amount Due:</span> <span class="payment-amount">' . number_format($trad2['AmountDue'], 2) . '</span></div>';
        $computedAmountDue = $trad2['AmountDue'];
        if ($totalAmountPaid >= $trad2['AmountDue']) {
            $computedAmountDue = 0.00;
        }
        echo '<div class="payment-details"><span>Amount Due:</span> <span class="payment-amount">' . number_format($computedAmountDue, 2) . '</span></div>';
        echo '</div>';
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
