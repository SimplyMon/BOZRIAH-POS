<?php
include '../config/app_config.php';
session_start();
include '../config/dbconn.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

error_log("Received POST data: " . print_r($_POST, true) . "\n", 0, "php_errors.log");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // user logged in
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['UserID'])) {
        echo json_encode(["status" => "error", "message" => "User not logged in."]);
        exit;
    }

    $cashierID = $_SESSION['user']['UserID'];
    $osehSeqNo = $_POST['osehSeqNo'];
    $grandtotal = $_POST['grandtotal'];
    // default grandtotal
    $originalGrandtotal = $_POST['originalGrandtotal'] ?? $grandtotal;
    $customerChange = isset($_POST['customerChange']) ? floatval($_POST['customerChange']) : 0;
    $paymentsJSON = $_POST['payments'] ?? '[]';
    $payments = json_decode($paymentsJSON, true);
    error_log("Decoded payments array: " . print_r($payments, true) . "\n", 0, "php_errors.log");

    // discount details
    $discCode = $_POST['DiscCode'] ?? null;
    $discPercent = $_POST['Percentage'] ?? null;
    $discountAmount = $_POST['Discountamount'] ?? 0;


    // if ($discPercent !== null) {
    //     $discPercent = str_replace('%', '', $discPercent);
    //     $discPercent = is_numeric($discPercent) ? floatval($discPercent) : null;
    // }

    // TEST
    function normalizeNull($val)
    {
        return (is_string($val) && strtolower(trim($val)) === 'null') ? null : $val;
    }

    $discCode = normalizeNull($_POST['DiscCode'] ?? null);
    // $discPercent = normalizeNull($_POST['Percentage'] ?? null);

    if ($discPercent !== null) {
        $discPercent = str_replace('%', '', $discPercent);
        $discPercent = is_numeric($discPercent) ? floatval($discPercent) : 0;
    }
    // TEST

    $discountAmount = is_numeric($discountAmount) ? floatval($discountAmount) : 0;

    // VAT Calculation
    $vatableAmount = round($grandtotal / 1.12, 2);
    $vatAmount = round(($grandtotal * 0.12) / 1.12, 2);

    // VAT ORIGINAL TOTAL
    // $vatableAmount = round($originalGrandtotal / 1.12, 2);
    // $vatAmount = round(($originalGrandtotal * 0.12) / 1.12, 2);

    if ($payments === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["status" => "error", "message" => "Invalid payment data received."]);
        exit;
    }

    $totalAmountPaid = 0;
    $totalGCAmount = 0;
    $totalOtherTenderAmount = 0;
    $totalCardPayment = 0;
    $cashPayment = 0;

    foreach ($payments as $payment) {
        $amount = floatval($payment['amount']);
        $totalAmountPaid += $amount;
        if ($payment['method'] === "GCASH") {
            $totalGCAmount += $amount;
        } elseif ($payment['method'] === "CARD") {
            $totalCardPayment += $amount;
        } elseif (in_array($payment['method'], ["PAYPAL", "MAYA"])) {
            $totalOtherTenderAmount += $amount;
        } elseif ($payment['method'] === "CASH") {
            $cashPayment += $amount;
        }
    }

    if (round($totalAmountPaid, 2) < round($grandtotal, 2)) {
        echo json_encode(["status" => "error", "message" => "Insufficient payment. Total payment must be greater than or equal to the grand total."]);
        exit;
    }

    try {
        // Start transaction
        $conn->beginTransaction();

        // 1. Fetch the last decrypted InvoiceNo from TRAH
        $lastInvoiceQuery = "SELECT TOP 1 dbo.d1(InvoiceNo) AS DecryptedInvoiceNo FROM TRAH ORDER BY dbo.d1(InvoiceNo) DESC";
        $stmt = $conn->prepare($lastInvoiceQuery);
        $stmt->execute();
        $lastInvoiceRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $lastValue = 0;

        if ($lastInvoiceRow && $lastInvoiceRow['DecryptedInvoiceNo']) {
            preg_match('/(\d{10})$/', $lastInvoiceRow['DecryptedInvoiceNo'], $matches);
            if (isset($matches[1])) {
                $lastValue = (int) $matches[1];
            }
        }

        // Generate the new InvoiceNo
        $newInvoiceNo = "CI-00000-" . str_pad($lastValue + 1, 10, "0", STR_PAD_LEFT);
        $posSeriesNo = str_replace("CI-00000-", "", $newInvoiceNo);

        // 2. Get the OSNumber from OSEH using the given SeqNo
        $osehQuery = "SELECT OSNumber, isTakeout FROM OSEH WHERE SeqNo = ?";
        $stmt = $conn->prepare($osehQuery);
        $stmt->execute([$osehSeqNo]);
        $osehRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$osehRow) {
            echo json_encode(["status" => "error", "message" => "OSNumber not found for SeqNo: $osehSeqNo"]);
            $conn->rollBack();
            exit;
        }

        $osNumber = $osehRow['OSNumber'];
        $isTakeout = (int) $osehRow['isTakeout'];

        // Get the current date and time
        $currentDateTime = date('Y-m-d H:i:s');

        $dineType = ($isTakeout == 1) ? "O" : "I";
        $goodsOrService = ($isTakeout == 1) ? "G" : "S";

        // Insert into TRAH
        $insertQuery = "INSERT INTO TRAH (
            InvoiceNo, InvoiceDate, BranchCode, TerminalNo, SONumber, CustomerID, ShowRemarksInPrint, CashierID, VATType, Status,
            TransactionType, GrossAmount, DiscountSetting, LastMod, LastModDt, Currency, CurrencyRate, EntryDate,
            FinalInvoiceAmount, GrossAmount2, ATCRate, EWTAmount,
            With2307, Amount2307, With2306, Amount2306, Code2306Rate, ZeroRatedAmount, ExemptAmount, VATableAmount,
            VATAmount, DiscCode, DiscPercent, DiscountAmount, CashierName, POSSeriesNo, DineType, isDateChanged, HasSCDiscount,
            DTInitialDeposit, GoodsOrService, NoOfCust, SODSeqNo, isMultipleDiscount, isTemporary, ServiceCharge,
            ServiceChargeRate, VATAdjustment
        ) VALUES (
            dbo.e1(:InvoiceNo), :InvoiceDate, :BranchCode, :TerminalNo, :SONumber, :CustomerID, :ShowRemarksInPrint, :CashierID, :VATType, :Status,
            :TransactionType, :GrossAmount, :DiscountSetting, :LastMod, :LastModDt, :Currency, :CurrencyRate, :EntryDate,
            :FinalInvoiceAmount, :GrossAmount2, :ATCRate, :EWTAmount,
            :With2307, :Amount2307, :With2306, :Amount2306, :Code2306Rate, :ZeroRatedAmount, :ExemptAmount, :VATableAmount,
            :VATAmount, :DiscCode, :DiscPercent, :DiscountAmount, :CashierName, :POSSeriesNo, :DineType, :isDateChanged, :HasSCDiscount,
            :DTInitialDeposit, :GoodsOrService, :NoOfCust, :SODSeqNo, :isMultipleDiscount, :isTemporary, :ServiceCharge,
            :ServiceChargeRate, :VATAdjustment
        )";

        $stmtTrah = $conn->prepare($insertQuery);
        $stmtTrah->execute([
            ':InvoiceNo' => $newInvoiceNo,
            ':InvoiceDate' => $currentDateTime,
            ':BranchCode' => $branchCode,
            ':TerminalNo' => $terminalNo,
            ':SONumber' => $osNumber,
            ':CustomerID' => '00000001',
            ':ShowRemarksInPrint' => 0,
            ':CashierID' => $cashierID,
            ':VATType' => 0,
            ':Status' => 1,
            ':TransactionType' => 1,
            ':GrossAmount' => $originalGrandtotal,
            ':DiscountSetting' => 0,
            ':LastMod' => -1,
            ':LastModDt' => $currentDateTime,
            ':Currency' => "PHP",
            ':CurrencyRate' => 1,
            ':EntryDate' => $currentDateTime,
            ':FinalInvoiceAmount' => $grandtotal,
            ':GrossAmount2' => $originalGrandtotal,
            ':ATCRate' => 0,
            ':EWTAmount' => 0,
            ':With2307' => 0,
            ':Amount2307' => 0,
            ':With2306' => 0,
            ':Amount2306' => 0,
            ':Code2306Rate' => 0,
            ':ZeroRatedAmount' => 0,
            ':ExemptAmount' => 0,
            ':VATableAmount' => $vatableAmount,
            ':VATAmount' => $vatAmount,
            ':DiscCode' => $discCode,
            ':DiscPercent' => $discPercent,
            ':DiscountAmount' => $discountAmount,
            ':CashierName' => $cashierID,
            ':POSSeriesNo' => $posSeriesNo,
            ':DineType' => $dineType,
            ':isDateChanged' => 0,
            ':HasSCDiscount' => 0,
            ':DTInitialDeposit' => 0,
            ':GoodsOrService' => $goodsOrService,
            ':NoOfCust' => 1,
            ':SODSeqNo' => 0,
            ':isMultipleDiscount' => 0,
            ':isTemporary' => 0,
            ':ServiceCharge' => 0,
            ':ServiceChargeRate' => 0,
            ':VATAdjustment' => 0
        ]);

        // last inserted TRAH SeqNo
        $trahSeqNo = $conn->lastInsertId();

        // 3. Insert into TRAD (order details)
        $tradInsertQuery = "INSERT INTO TRAD (
            InvoiceSeqNo, ItemID, Description, Quantity, UOM, Amount, isPriceOverride, LineAmount,
            DiscountAmount, UnitCost, WarehouseID, ItemReferenceSeqNo, Vattype, Status, isVoid, DiscPercent,
            Currency, CurrencyRate, isFree, PRMDSeqno, PromoSeries, ApplyTaxExemption, Servings, BOSeqNo,
            OSEDSeqNo, isExchange
        )
        SELECT
            :TrahSeqNo, OSED.ItemID,
            (SELECT dbo.d1(i.ItemDesc) FROM ITEM i WHERE dbo.d1(i.ItemID) = dbo.d1(OSED.ItemID)),
            OSED.Quantity,
            (SELECT i.DefaultUOM FROM ITEM i WHERE dbo.d1(i.ItemID) = dbo.d1(OSED.ItemID)),
            OSED.Amount, 0, OSED.Amount, 0, OSED.Amount, '000000001', 0, 0, 0, 0, 0,
            'PHP', 1, 0, 0, 0, 0,
            (SELECT i.Servings FROM ITEM i WHERE dbo.d1(i.ItemID) = dbo.d1(OSED.ItemID)),
            0, OSED.OSEDSeqNo, 0
        FROM OSED
        WHERE OSED.OSEHSeqNo = :OSEHSeqNo";

        $stmtTrad = $conn->prepare($tradInsertQuery);
        $stmtTrad->execute([
            ':TrahSeqNo' => $trahSeqNo,
            ':OSEHSeqNo' => $osehSeqNo
        ]);

        // Insert into TRAD2 (transaction details)
        $trad2InsertQuery = "INSERT INTO TRAD2 (
            InvoiceSeqNo, AmountDue, Status, AmountPayment, AmountChange,
            TotalBalance, TotalGCAmount, TotalCardPayment,
            TotalOtherTenderAmount, TotalCustCreditPayment, EntryDate
        ) VALUES (
            :TrahSeqNo,
            :AmountDue, 1, :AmountPayment, :AmountChange,
            0, :TotalGCAmount, :TotalCardPayment, :TotalOtherTenderAmount, 0, :EntryDate
        )";

        $stmtTrad2 = $conn->prepare($trad2InsertQuery);
        $stmtTrad2->execute([
            ':TrahSeqNo' => $trahSeqNo,
            ':AmountDue' => $grandtotal,
            ':AmountPayment' => $cashPayment,
            ':AmountChange' => $customerChange,
            ':TotalGCAmount' => $totalGCAmount,
            ':TotalCardPayment' => $totalCardPayment,
            ':TotalOtherTenderAmount' => $totalOtherTenderAmount,
            ':EntryDate' => $currentDateTime
        ]);
        $trad2SeqNo = $conn->lastInsertId();
        error_log("Inserted into TRAD2 with SeqNo: " . $trad2SeqNo . " - AmountPaid: " . $cashPayment);

        // Insert into TRADT (GCASH, PayPal, Maya)
        foreach ($payments as $payment) {
            if ($payment['method'] === "GCASH" || $payment['method'] === "PAYPAL" || $payment['method'] === "MAYA") {
                $tradtInsertQuery = "INSERT INTO TRADT (
                    InvoiceSeqNo, TenderType, Amount, TRAD2Seqno, MobileNumber, ReferenceNo
                ) VALUES (
                    :TrahSeqNo,
                    :TenderType,
                    :Amount,
                    :TRAD2Seqno,
                    :MobileNumber,
                    :ReferenceNo
                )";

                $stmtTradt = $conn->prepare($tradtInsertQuery);
                $stmtTradt->execute([
                    ':TrahSeqNo' => $trahSeqNo,
                    ':TenderType' => ($payment['method'] === "GCASH") ? 'º¶´Æ»' : $payment['method'],
                    ':Amount' => $payment['amount'],
                    ':TRAD2Seqno' => $trad2SeqNo,
                    ':MobileNumber' => $payment['mobileNumber'] ?? NULL,
                    ':ReferenceNo' => $payment['referenceNo'] ?? NULL
                ]);
                error_log("Inserted into TRADT for method: " . $payment['method']);
            }

            // Insert into TRADC (CARD)
            if ($payment['method'] === "CARD") {
                $tradcInsertQuery = "INSERT INTO TRADC (
                    InvoiceSeqNo, CardNo, CardType, CardHolder, ExpDate, ApprovalNo, Amount
                ) VALUES (
                    :TrahSeqNo,
                    dbo.e1(:CardNo),
                    :CardType,
                    NULL,
                    :ExpDate,
                    NULL,
                    :Amount
                )";

                $stmtTradc = $conn->prepare($tradcInsertQuery);
                $stmtTradc->execute([
                    ':TrahSeqNo' => $trahSeqNo,
                    ':CardNo' => $payment['cardNumber'] ?? '',
                    ':CardType' => $payment['cardType'] ?? '',
                    ':ExpDate' => $payment['cardExpDate'] ?? '',
                    ':Amount' => $payment['amount']
                ]);
                error_log("Inserted into TRADC");
            }
        }

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Payment confirmed.", "InvoiceNo" => $newInvoiceNo]);
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
