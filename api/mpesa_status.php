<?php
// ============================================
// FitPulse - M-Pesa Payment Status Check
// Frontend polls this to check payment result
// ============================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$checkoutRequestId = $_GET['checkout_request_id'] ?? '';

if (empty($checkoutRequestId)) {
    echo json_encode(['success' => false, 'message' => 'Missing checkout request ID']);
    exit;
}

// Look up the transaction
$stmt = $pdo->prepare('SELECT mt.*, i.invoice_number FROM mpesa_transactions mt LEFT JOIN invoices i ON mt.invoice_id = i.id WHERE mt.checkout_request_id = ? AND mt.user_id = ?');
$stmt->execute([$checkoutRequestId, $_SESSION['user_id']]);
$transaction = $stmt->fetch();

if (!$transaction) {
    echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    exit;
}

$response = [
    'success' => true,
    'status' => $transaction['status'],
    'checkout_request_id' => $transaction['checkout_request_id'],
];

if ($transaction['status'] === 'completed') {
    $response['receipt'] = $transaction['mpesa_receipt'];
    $response['amount'] = $transaction['amount'];
    $response['phone'] = $transaction['phone'];
    $response['invoice_number'] = $transaction['invoice_number'];
    $response['date'] = date('M j, Y g:i A', strtotime($transaction['updated_at']));
    $response['message'] = 'Payment received successfully!';
} elseif ($transaction['status'] === 'failed') {
    $response['message'] = $transaction['result_desc'] ?: 'Payment failed. Please try again.';
} elseif ($transaction['status'] === 'cancelled') {
    $response['message'] = 'Payment was cancelled. Please try again if needed.';
} else {
    $response['message'] = 'Payment is being processed...';
}

echo json_encode($response);
