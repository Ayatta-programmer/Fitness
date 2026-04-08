<?php
// ============================================
// FitPulse - M-Pesa Daraja API Integration
// Safaricom STK Push (Lipa Na M-Pesa Online)
// ============================================

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';

// ============================================
// Helper: Get OAuth Access Token from Daraja
// ============================================
function getMpesaAccessToken() {
    $url = MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';
    $credentials = base64_encode(MPESA_CONSUMER_KEY . ':' . MPESA_CONSUMER_SECRET);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $credentials],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("M-Pesa OAuth cURL error: $error");
        return null;
    }

    $data = json_decode($response, true);
    if ($httpCode === 200 && isset($data['access_token'])) {
        return $data['access_token'];
    }

    error_log("M-Pesa OAuth failed: HTTP $httpCode - " . $response);
    return null;
}

// ============================================
// Helper: Initiate STK Push via Daraja API
// ============================================
function initiateSTKPush($phone, $amount, $accountRef, $description) {
    $accessToken = getMpesaAccessToken();
    if (!$accessToken) {
        return ['success' => false, 'message' => 'Failed to authenticate with M-Pesa. Check API credentials.'];
    }

    $timestamp = date('YmdHis');
    $password = base64_encode(MPESA_SHORTCODE . MPESA_PASSKEY . $timestamp);

    $url = MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';

    $payload = [
        'BusinessShortCode' => MPESA_SHORTCODE,
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => 'CustomerPayBillOnline',
        'Amount'            => (int) ceil($amount),
        'PartyA'            => $phone,
        'PartyB'            => MPESA_SHORTCODE,
        'PhoneNumber'       => $phone,
        'CallBackURL'       => MPESA_CALLBACK_URL,
        'AccountReference'  => $accountRef,
        'TransactionDesc'   => $description,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log("M-Pesa STK Push cURL error: $error");
        return ['success' => false, 'message' => 'Connection to M-Pesa failed. Please try again.'];
    }

    $data = json_decode($response, true);

    if ($httpCode === 200 && isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
        return [
            'success' => true,
            'data' => $data,
            'CheckoutRequestID' => $data['CheckoutRequestID'],
            'MerchantRequestID' => $data['MerchantRequestID'],
        ];
    }

    error_log("M-Pesa STK Push failed: HTTP $httpCode - " . $response);
    $errorMsg = $data['errorMessage'] ?? $data['ResponseDescription'] ?? 'STK Push request failed. Please try again.';
    return ['success' => false, 'message' => $errorMsg];
}

// ============================================
// Action: Initiate Invoice Payment
// ============================================
if ($action === 'initiate') {
    $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
    $phone = sanitize($_POST['phone'] ?? '');

    // Validate phone
    if (!preg_match('/^(254|0)\d{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number. Use format 254XXXXXXXXX or 0XXXXXXXXX']);
        exit;
    }

    // Format phone to 254 format
    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    }

    // Validate invoice
    $stmt = $pdo->prepare("SELECT i.*, u.full_name FROM invoices i JOIN users u ON i.user_id = u.id WHERE i.id = ? AND i.user_id = ?");
    $stmt->execute([$invoiceId, $_SESSION['user_id']]);
    $invoice = $stmt->fetch();

    if (!$invoice) {
        echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        exit;
    }

    if ($invoice['status'] === 'paid') {
        echo json_encode(['success' => false, 'message' => 'This invoice is already paid']);
        exit;
    }

    // Initiate STK Push
    $result = initiateSTKPush(
        $phone,
        $invoice['total'],
        $invoice['invoice_number'],
        'Payment for ' . $invoice['description']
    );

    if (!$result['success']) {
        echo json_encode($result);
        exit;
    }

    // Store transaction in database
    $stmt = $pdo->prepare('INSERT INTO mpesa_transactions (user_id, invoice_id, checkout_request_id, merchant_request_id, phone, amount, transaction_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_SESSION['user_id'],
        $invoiceId,
        $result['CheckoutRequestID'],
        $result['MerchantRequestID'],
        $phone,
        $invoice['total'],
        'invoice',
        'pending'
    ]);

    echo json_encode([
        'success' => true,
        'message' => "STK Push sent to $phone. Please enter your M-Pesa PIN on your phone.",
        'checkout_request_id' => $result['CheckoutRequestID'],
        'amount' => $invoice['total'],
        'phone' => $phone
    ]);
    exit;
}

// ============================================
// Action: Initiate Plan Purchase
// ============================================
if ($action === 'purchase_plan') {
    $plan = sanitize($_POST['plan'] ?? '');
    $amount = (float) ($_POST['amount'] ?? 0);
    $desc = sanitize($_POST['description'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (!preg_match('/^(254|0)\d{9}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
        exit;
    }

    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    }

    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid amount']);
        exit;
    }

    // Auto-generate invoice
    $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare('INSERT INTO invoices (user_id, invoice_number, description, amount, tax, total, due_date, status) VALUES (?,?,?,?,?,?,?,?)');
    $stmt->execute([$_SESSION['user_id'], $invoiceNumber, $desc, $amount, 0, $amount, date('Y-m-d'), 'pending']);
    $newInvoiceId = $pdo->lastInsertId('invoices_id_seq');

    // Initiate STK Push
    $result = initiateSTKPush($phone, $amount, $invoiceNumber, $desc);

    if (!$result['success']) {
        echo json_encode($result);
        exit;
    }

    // Store transaction
    $stmt = $pdo->prepare('INSERT INTO mpesa_transactions (user_id, invoice_id, checkout_request_id, merchant_request_id, phone, amount, transaction_type, plan_name, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_SESSION['user_id'],
        $newInvoiceId,
        $result['CheckoutRequestID'],
        $result['MerchantRequestID'],
        $phone,
        $amount,
        'plan_purchase',
        $plan,
        'pending'
    ]);

    echo json_encode([
        'success' => true,
        'message' => "STK Push sent to $phone. Please enter your M-Pesa PIN.",
        'checkout_request_id' => $result['CheckoutRequestID'],
        'amount' => $amount,
        'phone' => $phone
    ]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
