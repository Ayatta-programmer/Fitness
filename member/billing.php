<?php
require_once __DIR__ . '/../auth/auth_check.php';
checkAuth(['member']);

$memberId = $_SESSION['user_id'];

$invoices = $pdo->prepare("SELECT * FROM invoices WHERE user_id = ? ORDER BY created_at DESC");
$invoices->execute([$memberId]);
$invoices = $invoices->fetchAll();

$totalOwed = 0; $totalPaid = 0;
foreach ($invoices as $i) {
    if ($i['status'] === 'paid') $totalPaid += $i['total'];
    elseif (in_array($i['status'], ['pending','overdue'])) $totalOwed += $i['total'];
}

$user = getCurrentUser($pdo);
$currentPlan = strtolower($user['membership_plan'] ?? 'basic');
$subscriptionOptions = [
    'daily' => ['name' => 'Daily Pass', 'price' => 500, 'desc' => '1 Day Access'],
    'weekly' => ['name' => 'Weekly Pass', 'price' => 1500, 'desc' => '7 Days Access'],
    'monthly' => ['name' => 'Monthly Membership', 'price' => 3000, 'desc' => 'Full Month Access']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Billing - <?= APP_NAME ?></title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="dashboard">
  <?php require_once __DIR__ . '/../includes/member_sidebar.php'; ?>

  <div class="main-content">
    <div class="main-header">
      <div>
        <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
        <h1>My Billing</h1>
        <p>View your invoices and make payments</p>
      </div>
    </div>

    <div class="stats-grid cols-2">
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-value"><?= formatCurrency($totalPaid) ?></div><div class="stat-label">Total Paid</div></div>
          <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-header">
          <div><div class="stat-value"><?= formatCurrency($totalOwed) ?></div><div class="stat-label">Outstanding Balance</div></div>
          <div class="stat-icon orange"><i class="fas fa-exclamation-circle"></i></div>
        </div>
      </div>
    </div>

    <!-- Purchase Pass Section -->
    <div class="card mb-2">
      <div class="card-header"><h3>Purchase Pass or Membership</h3></div>
      <div class="card-body">
        <div class="stack-layout">
          <div class="plan-selector">
            <?php foreach ($subscriptionOptions as $key => $opt): ?>
              <label class="plan-option <?= $currentPlan === $key ? 'selected' : '' ?>" onclick="selectPlan(this, '<?= $key ?>', <?= $opt['price'] ?>, '<?= addslashes($opt['name']) ?>')">
                <input type="radio" name="plan_selection" value="<?= $key ?>" <?= $currentPlan === $key ? 'checked' : '' ?>>
                <div class="plan-info">
                  <span class="plan-title"><?= htmlspecialchars($opt['name']) ?> <?php if($currentPlan === $key) echo '<span class="badge badge-success current-plan-chip">Current</span>'; ?></span>
                  <span class="plan-desc"><?= htmlspecialchars($opt['desc']) ?></span>
                </div>
                <div class="plan-price-tag">KSh <?= number_format($opt['price']) ?></div>
              </label>
            <?php endforeach; ?>
          </div>
          <div class="summary-card billing-summary">
            <div class="summary-panel">
              <h4>Payment Summary</h4>
              <div class="summary-row">
                <span id="summary-name">Select a plan</span>
                <span id="summary-price" class="summary-amount"> - </span>
              </div>
              <hr class="summary-divider">
              <button class="btn mpesa-btn payment-btn full-width" onclick="initiatePlanPurchase()" id="btn-buy-plan">
                <i class="fas fa-credit-card"></i> Pay with M-Pesa
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h3>My Invoices (<?= count($invoices) ?>)</h3></div>
      <div class="card-body no-padding">
        <table class="data-table">
          <thead><tr><th>Invoice #</th><th>Description</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Payment</th><th>Action</th></tr></thead>
          <tbody>
            <?php if (empty($invoices)): ?>
              <tr><td colspan="7" class="text-center empty-value" style="padding:2rem">No invoices yet</td></tr>
            <?php else: ?>
              <?php foreach ($invoices as $i): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($i['invoice_number']) ?></strong></td>
                  <td><?= htmlspecialchars($i['description']) ?></td>
                  <td><strong><?= formatCurrency($i['total']) ?></strong></td>
                  <td><?= date('M j, Y', strtotime($i['due_date'])) ?></td>
                  <td><span class="badge badge-<?= $i['status']==='paid'?'success':($i['status']==='pending'?'warning':($i['status']==='overdue'?'danger':'info')) ?>"><?= ucfirst($i['status']) ?></span></td>
                  <td><?= $i['payment_method'] ? htmlspecialchars($i['payment_method']) : ' - ' ?></td>
                  <td>
                    <?php if (in_array($i['status'], ['pending', 'overdue'])): ?>
                      <button class="btn btn-sm mpesa-btn" onclick="startMpesaPayment(<?= $i['id'] ?>, <?= $i['total'] ?>, '<?= htmlspecialchars($i['invoice_number']) ?>', '<?= htmlspecialchars($i['description']) ?>')">
                        <i class="fas fa-mobile-alt"></i> Pay M-Pesa
                      </button>
                    <?php elseif ($i['status'] === 'paid'): ?>
                      <span class="status-text success"><i class="fas fa-check"></i> Paid</span>
                    <?php else: ?>
                      <span class="status-text muted">Cancelled</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- M-Pesa Payment Modal -->
<div class="modal-overlay" id="mpesaModal">
  <div class="modal payment-modal">
    <div class="modal-header">
      <h3 class="payment-modal-title"><i class="fas fa-mobile-alt"></i> M-Pesa Payment</h3>
      <button class="modal-close" onclick="closeMpesa()">&times;</button>
    </div>
    <div class="modal-body">

      <!-- Step 1: Enter Phone Number -->
      <div id="mpesa-step1" class="payment-step">
        <div class="payment-highlight">
          <div class="eyebrow">Amount to Pay</div>
          <div id="mpesa-amount" class="amount"></div>
          <div id="mpesa-desc" class="description"></div>
        </div>

        <h3>Enter M-Pesa Phone Number</h3>
        <p>Enter the Safaricom number to receive the STK Push prompt</p>

        <div class="phone-input-group">
          <span class="phone-prefix">KE +254</span>
          <input type="tel" id="mpesa-phone" class="form-control" placeholder="7XX XXX XXX" maxlength="10">
        </div>

        <p class="payment-security-note">
          <i class="fas fa-lock"></i> Secured by Safaricom M-Pesa
        </p>

        <button class="btn mpesa-btn payment-btn" onclick="initiateMpesa()">
          <i class="fas fa-paper-plane"></i> Send STK Push
        </button>
      </div>

      <!-- Step 2: Waiting for PIN -->
      <div id="mpesa-step2" class="payment-step" style="display:none">
        <div class="pin-prompt">
          <h4>M-Pesa Payment Request</h4>
          <div class="pin-note">
            Pay <strong id="pin-amount"></strong> to<br><strong>FitPulse Gym</strong>
          </div>
          <div class="pin-dots">
            <div class="pin-dot" id="dot1"></div>
            <div class="pin-dot" id="dot2"></div>
            <div class="pin-dot" id="dot3"></div>
            <div class="pin-dot" id="dot4"></div>
          </div>
          <div class="pin-prompt-text">Enter your M-Pesa PIN</div>
        </div>
        <p class="inline-note">
          <span class="loading-spinner"></span><br><br>
          A payment prompt has been sent to <strong id="mpesa-sent-phone"></strong><br>
          <small>Enter your M-Pesa PIN on your phone. We'll detect it automatically.</small>
        </p>
        <button class="btn btn-ghost payment-btn-secondary" onclick="closeMpesa()">Cancel Payment</button>
      </div>

      <!-- Step 3: Processing -->
      <div id="mpesa-step3" class="payment-step" style="display:none">
        <div class="payment-processing">
          <div class="loading-spinner large"></div>
        </div>
        <h3>Processing Payment...</h3>
        <p>Please wait while we confirm your M-Pesa payment</p>
      </div>

      <!-- Step 4: Success -->
      <div id="mpesa-step4" class="payment-step" style="display:none">
        <div class="payment-icon-circle success"><i class="fas fa-check"></i></div>
        <h3 class="value-strong">Payment Successful!</h3>
        <p>Your payment has been received and recorded</p>

        <div class="receipt-card">
          <div class="receipt-heading">
            <strong>M-Pesa Receipt</strong>
          </div>
          <div class="receipt-row"><span>Receipt No.</span><span id="r-receipt"></span></div>
          <div class="receipt-row"><span>Invoice</span><span id="r-invoice"></span></div>
          <div class="receipt-row"><span>Amount</span><span id="r-amount" class="success-amount"></span></div>
          <div class="receipt-row"><span>Phone</span><span id="r-phone"></span></div>
          <div class="receipt-row"><span>Date</span><span id="r-date"></span></div>
          <div class="receipt-row"><span>Ref</span><span id="r-ref"></span></div>
        </div>

        <button class="btn btn-primary payment-btn" onclick="location.reload()">
          <i class="fas fa-check"></i> Done
        </button>
      </div>

      <!-- Error -->
      <div id="mpesa-error" class="payment-step" style="display:none">
        <div class="payment-icon-circle error">
          <i class="fas fa-times"></i>
        </div>
        <h3 class="status-text warning">Payment Failed</h3>
        <p id="mpesa-error-msg" class="inline-note"></p>
        <button class="btn btn-primary payment-btn" onclick="resetMpesa()">Try Again</button>
      </div>

    </div>
  </div>
</div>

<script src="../js/main.js"></script>
<script>
let currentInvoiceId = null;
let selectedPlan = null;
let planAmount = 0;
let planName = '';
let currentCheckoutRequestId = null;
let pollingInterval = null;

function startMpesaPayment(invoiceId, amount, invoiceNum, desc) {
  currentInvoiceId = invoiceId;
  selectedPlan = null;
  document.getElementById('mpesa-amount').textContent = 'KSh ' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
  document.getElementById('mpesa-desc').textContent = desc + ' (' + invoiceNum + ')';
  document.getElementById('pin-amount').textContent = 'KSh ' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
  showStep(1);
  openModal('mpesaModal');
}

function selectPlan(element, key, price, name) {
  selectedPlan = key;
  planAmount = price;
  planName = name;
  
  document.querySelectorAll('.plan-option').forEach(el => el.classList.remove('selected'));
  element.classList.add('selected');
  
  document.getElementById('summary-name').textContent = name;
  document.getElementById('summary-price').textContent = 'KSh ' + parseFloat(price).toLocaleString(undefined, {minimumFractionDigits: 2});
  
  let btn = document.getElementById('btn-buy-plan');
  btn.disabled = false;
}

function initiatePlanPurchase() {
  if (!selectedPlan) {
    alert("Please select a plan to purchase.");
    return;
  }
  startPlanPurchase(selectedPlan, planAmount, planName);
}

function startPlanPurchase(planKey, amount, desc) {
  selectedPlan = planKey;
  currentInvoiceId = null;
  planAmount = amount;
  planName = desc;
  
  document.getElementById('mpesa-amount').textContent = 'KSh ' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
  document.getElementById('mpesa-desc').textContent = desc;
  document.getElementById('pin-amount').textContent = 'KSh ' + parseFloat(amount).toLocaleString(undefined, {minimumFractionDigits: 2});
  
  showStep(1);
  openModal('mpesaModal');
}

function showStep(n) {
  for (let i = 1; i <= 4; i++) {
    const el = document.getElementById('mpesa-step' + i);
    if (el) el.style.display = i === n ? 'block' : 'none';
  }
  document.getElementById('mpesa-error').style.display = n === 'error' ? 'block' : 'none';
}

function closeMpesa() {
  stopPolling();
  closeModal('mpesaModal');
  setTimeout(() => showStep(1), 300);
}

function resetMpesa() {
  stopPolling();
  showStep(1);
}

function initiateMpesa() {
  let phone = document.getElementById('mpesa-phone').value.trim();
  if (!phone) { alert('Please enter your phone number'); return; }

  // Format phone number
  if (phone.startsWith('07') || phone.startsWith('01')) {
    phone = '254' + phone.substring(1);
  } else if (phone.startsWith('7') || phone.startsWith('1')) {
    phone = '254' + phone;
  }

  const formData = new FormData();
  
  if (selectedPlan) {
    formData.append('action', 'purchase_plan');
    formData.append('plan', selectedPlan);
    formData.append('amount', planAmount);
    formData.append('description', planName);
  } else {
    formData.append('action', 'initiate');
    formData.append('invoice_id', currentInvoiceId);
  }
  
  formData.append('phone', phone);

  // Show STK Push step immediately
  document.getElementById('mpesa-sent-phone').textContent = phone.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
  showStep(2);
  animatePinDots();

  // Send STK Push request
  fetch('../api/mpesa.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Store checkout ID and start polling for payment confirmation
        currentCheckoutRequestId = data.checkout_request_id;
        startPolling();
      } else {
        document.getElementById('mpesa-error-msg').textContent = data.message;
        showStep('error');
      }
    })
    .catch(() => {
      document.getElementById('mpesa-error-msg').textContent = 'Network error. Please check your connection.';
      showStep('error');
    });
}

function animatePinDots() {
  const dots = document.querySelectorAll('.pin-dot');
  dots.forEach(d => { d.classList.remove('filled', 'animating'); });
  let i = 0;
  const interval = setInterval(() => {
    if (i < dots.length) {
      dots[i].classList.add('animating');
      i++;
    } else {
      clearInterval(interval);
    }
  }, 800);
}

// ============================================
// Payment Status Polling
// ============================================
function startPolling() {
  stopPolling();
  let attempts = 0;
  const maxAttempts = 40; // 40 * 3s = 2 minutes max

  pollingInterval = setInterval(() => {
    attempts++;
    
    if (attempts > maxAttempts) {
      stopPolling();
      document.getElementById('mpesa-error-msg').textContent = 'Payment confirmation timed out. If you completed the payment, it will reflect shortly.';
      showStep('error');
      return;
    }

    fetch('../api/mpesa_status.php?checkout_request_id=' + encodeURIComponent(currentCheckoutRequestId))
      .then(r => r.json())
      .then(data => {
        if (!data.success) return;

        if (data.status === 'completed') {
          stopPolling();
          // Show success with receipt
          document.getElementById('r-receipt').textContent = data.receipt || 'N/A';
          document.getElementById('r-invoice').textContent = data.invoice_number || 'N/A';
          document.getElementById('r-amount').textContent = 'KSh ' + parseFloat(data.amount).toLocaleString(undefined, {minimumFractionDigits: 2});
          document.getElementById('r-phone').textContent = data.phone || '';
          document.getElementById('r-date').textContent = data.date || '';
          document.getElementById('r-ref').textContent = currentCheckoutRequestId;
          showStep(4);
        } else if (data.status === 'failed' || data.status === 'cancelled') {
          stopPolling();
          document.getElementById('mpesa-error-msg').textContent = data.message;
          showStep('error');
        }
        // If still 'pending', continue polling
      })
      .catch(() => {
        // Network error during polling  -  continue trying
      });
  }, 3000);
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
}
</script>
</body>
</html>

