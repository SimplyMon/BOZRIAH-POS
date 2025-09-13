<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}

include '../config/dbconn.php';
include '../config/app_config.php';

$cashierID = $_SESSION['user']['UserID'];
$cashierFname = $_SESSION['user']['FirstName'];
$cashierLname = $_SESSION['user']['LastName'];

$selectedDate = isset($_GET['report_date']) ? $_GET['report_date'] : date("Y-m-d");

$shiftStartTime = "";
$shiftEndTime = "";
$formattedDate = "";
$firstInvoice = "";
$lastInvoice = "";
$salesSummary = [];
$discountBreakdown = [];
$paymentBreakdown = [];
$paymentDetails = [];

try {
    $stmt = $conn->prepare("SELECT
                                MIN([InvoiceDate]) AS ShiftStart
                                ,MAX([InvoiceDate]) AS ShiftEnd
                                ,SUM([GrossAmount]) AS TotalGrossSales
                                ,SUM([DiscountAmount]) AS TotalDiscounts
                                ,SUM([VATableAmount]) AS TotalVATableAmount
                                ,SUM([VATAmount]) AS TotalVATAmount
                                ,MIN(dbo.d1([InvoiceNo])) AS FirstInvoiceNo
                                ,MAX(dbo.d1([InvoiceNo])) AS LastInvoiceNo
                            FROM [POSDB].[dbo].[TRAH]
                            WHERE [CashierID] = :cashierID AND CAST([InvoiceDate] AS DATE) = :selectedDate");

    $stmt->bindParam(':cashierID', $cashierID);
    $stmt->bindParam(':selectedDate', $selectedDate);
    $stmt->execute();
    $salesSummary = $stmt->fetch(PDO::FETCH_ASSOC);

    $shiftStartTime = isset($salesSummary['ShiftStart']) ? date("g:i a", strtotime($salesSummary['ShiftStart'])) : '';
    $shiftEndTime = isset($salesSummary['ShiftEnd']) ? date("g:i a", strtotime($salesSummary['ShiftEnd'])) : '';
    $formattedDate = isset($salesSummary['ShiftStart']) ? date("F j, Y", strtotime($salesSummary['ShiftStart'])) : '';
    $firstInvoice = isset($salesSummary['FirstInvoiceNo']) ? $salesSummary['FirstInvoiceNo'] : '';
    $lastInvoice = isset($salesSummary['LastInvoiceNo']) ? $salesSummary['LastInvoiceNo'] : '';

    $stmtDiscount = $conn->prepare("SELECT dbo.d1([DiscCode]) AS DiscountCode, COUNT([SeqNo]) AS DiscountQuantity, SUM([DiscountAmount]) AS TotalDiscountAmount
                                    FROM [POSDB].[dbo].[TRAH]
                                    WHERE [CashierID] = :cashierID AND CAST([InvoiceDate] AS DATE) = :selectedDate AND [DiscountAmount] > 0
                                    GROUP BY dbo.d1([DiscCode])
                                    ORDER BY dbo.d1([DiscCode])");

    $stmtDiscount->bindParam(':cashierID', $cashierID);
    $stmtDiscount->bindParam(':selectedDate', $selectedDate);
    $stmtDiscount->execute();
    $discountBreakdown = $stmtDiscount->fetchAll(PDO::FETCH_ASSOC);

    // t2 TRAD2, t1  TRAH
    $stmtPaymentDetails = $conn->prepare("SELECT
                                            t2.[InvoiceSeqNo],
                                            t2.[AmountPayment] AS CashPayment,
                                            t2.[TotalCardPayment] AS CardPayment,
                                            t2.[TotalGCAmount] AS GCAmount,
                                            t2.[TotalOtherTenderAmount] AS OtherTender,
                                            t2.[AmountChange] AS ChangeAmount
                                        FROM [POSDB].[dbo].[TRAD2] t2
                                        INNER JOIN [POSDB].[dbo].[TRAH] t1 ON t2.[InvoiceSeqNo] = t1.[SeqNo]
                                        WHERE t1.[CashierID] = :cashierID AND CAST(t1.[InvoiceDate] AS DATE) = :selectedDate
                                        ORDER BY t2.[InvoiceSeqNo]");

    $stmtPaymentDetails->bindParam(':cashierID', $cashierID);
    $stmtPaymentDetails->bindParam(':selectedDate', $selectedDate);
    $stmtPaymentDetails->execute();
    $paymentDetails = $stmtPaymentDetails->fetchAll(PDO::FETCH_ASSOC);

    $totalCashPayment = 0;
    $totalCardPayment = 0;
    $totalGCAmount = 0;
    $totalOtherTenderAmount = 0;
    $totalChange = 0;

    foreach ($paymentDetails as $detail) {
        $totalCashPayment += $detail['CashPayment'];
        $totalCardPayment += $detail['CardPayment'];
        $totalGCAmount += $detail['GCAmount'];
        $totalOtherTenderAmount += $detail['OtherTender'];
        $totalChange += $detail['ChangeAmount'];
    }

    $paymentBreakdown = [
        'TotalCashPayment' => $totalCashPayment,
        'TotalCardPayment' => $totalCardPayment,
        'TotalGCAmount' => $totalGCAmount,
        'TotalOtherTenderAmount' => $totalOtherTenderAmount,
        'TotalChange' => $totalChange,
    ];
} catch (PDOException $e) {
    echo "Error fetching data: " . $e->getMessage();
    $salesSummary = [];
    $discountBreakdown = [];
    $paymentBreakdown = [];
    $paymentDetails = [];
}
// pota
