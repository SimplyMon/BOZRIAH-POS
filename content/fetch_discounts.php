<?php
include '../config/dbconn.php';

try {
    $stmt = $conn->query("
        SELECT
            SeqNo,
            dbo.e1(DiscCode) AS DiscCode,
            dbo.d1(DiscDesc) AS DiscDesc,
            CASE
                WHEN Type = '¤' THEN CAST(Percentage AS VARCHAR(10))
                WHEN Type = '¥' THEN CAST(Percentage AS VARCHAR(10)) + '%'
                WHEN Type = '¦' THEN 'OPEN AMOUNT'
                WHEN Type = '§' THEN 'OPEN PERCENT'
            END AS Percentage,
            MaxDiscount,
            CASE
                WHEN IsSoloPDiscount = 1 THEN 'Solo Parent Discount'
                WHEN isSCDiscount = 1 THEN 'Senior Citizen Discount'
                WHEN isNSTMDisc = 1 THEN 'National Athlete Discount'
                WHEN isPWDDiscount = 1 THEN 'PWD Discount'
                ELSE 'Regular Discount'
            END AS DiscType
        FROM [POSDB].[dbo].[DISC]
        WHERE [DiscountType] = '£'
        AND [DateFrom] IS NULL
        ORDER BY [SeqNo] DESC
    ");
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($discounts);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
