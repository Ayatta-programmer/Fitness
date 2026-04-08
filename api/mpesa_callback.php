<?php
// ============================================
// FitPulse - M-Pesa Daraja Callback Handler
// Receives payment confirmations from Safaricom
// ============================================

require_once __DIR__ . '/../config/database.php';

// Log all callbacks for debugging
$callbackData = file_get_contents('php://input');
error_log("M-Pesa Callback received: " . $callbackData);

// Always respond with success to Safaricom immediately
header('Content-Type: application/json');

// Parse the callback JSON
$data = json_decode($callbackData, true);

if (!$data || !isset($data['Body']['stkCallback'])) {
    error_log("M-Pesa Callback: Invalid payload structure");
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit;
}

$callback = $data['Body']['stkCallback'];
$merchantRequestID = $callback['MerchantRequestID'] ?? '';
$checkoutRequestID = $callback['CheckoutRequestID'] ?? '';
$resultCode = $callback['ResultCode'] ?? -1;
$resultDesc = $callback['ResultDesc'] ?? '';

// Find the transaction
$stmt = $pdo->prepare('SELECT * FROM mpesa_transactions WHERE checkout_request_id = ?');
$stmt->execute([$checkoutRequestID]);
$transaction = $stmt->fetch();

if (!$transaction) {
    error_log("M-Pesa Callback: Transaction not found for CheckoutRequestID: $checkoutRequestID");
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    exit;
}

// Payment was successful
if ($resultCode == 0) {
    // Extract callback metadata
    $mpesaReceipt = '';
    $amount = 0;
    $phone = '';
    $transactionDate = '';

    if (isset($callback['CallbackMetadata']['Item'])) {
        foreach ($callback['CallbackMetadata']['Item'] as $item) {
            switch ($item['Name']) {
                case 'MpesaReceiptNumber':
                    $mpesaReceipt = $item['Value'] ?? '';
                    break;
                case 'Amount':
                    $amount = $item['Value'] ?? 0;
                    break;
                case 'PhoneNumber':
                    $phone = $item['Value'] ?? '';
                    break;
                case 'TransactionDate':
                    $transactionDate = $item['Value'] ?? '';
                    break;
            }
        }
    }

    // Update transaction to completed
    $stmt = $pdo->prepare('UPDATE mpesa_transactions SET status = ?, mpesa_receipt = ?, result_code = ?, result_desc = ?, updated_at = CURRENT_TIMESTAMP WHERE checkout_request_id = ?');
    $stmt->execute(['completed', $mpesaReceipt, $resultCode, $resultDesc, $checkoutRequestID]);

    // Update the invoice to paid
    if ($transaction['invoice_id']) {
        $stmt = $pdo->prepare("UPDATE invoices SET status = 'paid', paid_date = CURRENT_DATE, payment_method = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute(["M-Pesa ($mpesaReceipt)", $transaction['invoice_id']]);
    }

    // If plan purchase, update user's membership plan
    if ($transaction['transaction_type'] === 'plan_purchase' && $transaction['plan_name']) {
        $stmt = $pdo->prepare("UPDATE users SET membership_plan = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$transaction['plan_name'], $transaction['user_id']]);
    }

    error_log("M-Pesa Callback: Payment successful - Receipt: $mpesaReceipt, Amount: $amount, Phone: $phone");
} else {
    // Payment failed or was cancelled
    $status = ($resultCode == 1032) ? 'cancelled' : 'failed';

    $stmt = $pdo->prepare('UPDATE mpesa_transactions SET status = ?, result_code = ?, result_desc = ?, updated_at = CURRENT_TIMESTAMP WHERE checkout_request_id = ?');
    $stmt->execute([$status, $resultCode, $resultDesc, $checkoutRequestID]);

    // If it was a plan purchase, cancel the auto-generated invoice
    if ($transaction['transaction_type'] === 'plan_purchase' && $transaction['invoice_id']) {
        $stmt = $pdo->prepare("UPDATE invoices SET status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'pending'");
        $stmt->execute([$transaction['invoice_id']]);
    }

    error_log("M-Pesa Callback: Payment failed - Code: $resultCode, Desc: $resultDesc");
}

// Respond to Safaricom
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
