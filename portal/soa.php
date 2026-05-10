<?php
$active_portal = 'soa';
require_once 'includes/auth.php';

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;
$student   = $conn->query("SELECT s.*, g.name as grade FROM students s LEFT JOIN grade_levels g ON s.grade_level_id=g.id WHERE s.id=$student_id")->fetch_assoc();

// Guard: no linked student
if (!$student) {
  echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <link rel="stylesheet" href="../css/portal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    </head><body>';
  include('includes/nav.php');
  echo '<div class="portal-container" style="text-align:center;padding:60px 20px;">
    <i class="bi bi-exclamation-circle" style="font-size:48px;color:#d97706;"></i>
    <h3 style="margin-top:16px;">No Student Linked</h3>
    <p style="color:#6b7280;font-size:14px;">Your account has no enrolled student linked yet.<br>
    Please wait for the registrar to process your enrollment application.</p>
    <a href="dashboard.php" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#494C8A;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
      Back to Dashboard
    </a>
  </div></body></html>';
  exit();
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fee_id     = intval($_POST['fee_id'] ?? 0);
  $payment_id = intval($_POST['payment_id'] ?? 0);

  if (!empty($_FILES['proof']['tmp_name'])) {
    $allowed = ['image/jpeg','image/png','image/webp','application/pdf'];
    if (!in_array($_FILES['proof']['type'], $allowed)) {
      $error = "Only JPG, PNG, WEBP, or PDF allowed.";
    } elseif ($_FILES['proof']['size'] > 5 * 1024 * 1024) {
      $error = "File must be under 5MB.";
    } else {
      $fname = 'proof_' . uniqid() . '_' . basename($_FILES['proof']['name']);
      move_uploaded_file($_FILES['proof']['tmp_name'], "../pages/uploads/" . $fname);

      if ($payment_id) {
        // Update existing payment record
        $conn->query("UPDATE payments SET proof_file='$fname' WHERE id=$payment_id AND student_id=$student_id");
      } elseif ($fee_id) {
        // No payment record yet — create one with unpaid status and attach proof
        $fee_row = $conn->query("SELECT amount FROM fees WHERE id=$fee_id")->fetch_assoc();
        $fee_amt = $fee_row['amount'] ?? 0;
        $stmt_ins = $conn->prepare("INSERT INTO payments (student_id, fee_id, amount_paid, balance, status, proof_file) VALUES (?,?,0,?,?,?)");
        $status_val = 'unpaid';
        $stmt_ins->bind_param("iidss", $student_id, $fee_id, $fee_amt, $status_val, $fname);
        $stmt_ins->execute();
      }
      $success = "Proof of payment uploaded. The finance staff will verify it shortly.";
    }
  }
}

$fees_payments = $conn->query("
  SELECT f.id as fee_id, f.name as fee_name, f.amount, f.fee_type,
         p.id as payment_id, p.amount_paid, p.balance, p.status,
         p.paid_at, p.or_number, p.payment_method, p.proof_file,
         p.payment_plan, p.surcharge
  FROM fees f
  LEFT JOIN payments p ON p.fee_id=f.id AND p.student_id=$student_id
  WHERE f.grade_level_id = {$student['grade_level_id']} AND f.school_year_id = $sy_id
  ORDER BY f.name
")->fetch_all(MYSQLI_ASSOC);

$total_fees = array_sum(array_column($fees_payments, 'amount'));
$total_paid = array_sum(array_column($fees_payments, 'amount_paid'));
$total_bal  = array_sum(array_column($fees_payments, 'balance'));

// Auto-apply SPED fee if student is flagged
if (!empty($student['is_sped'])) {
  $sped_fee = $conn->query("
    SELECT f.* FROM fees f
    WHERE f.fee_type = 'sped' AND f.school_year_id = $sy_id
    AND f.grade_level_id = {$student['grade_level_id']}
    LIMIT 1
  ")->fetch_assoc();

  if ($sped_fee) {
    // Check if already in payments
    $already = false;
    foreach ($fees_payments as $fp) {
      if ($fp['fee_name'] === $sped_fee['name']) { $already = true; break; }
    }
    if (!$already) {
      $fees_payments[] = [
        'fee_name' => $sped_fee['name'] . ' (SPED)',
        'amount'   => $sped_fee['amount'],
        'amount_paid' => 0,
        'balance'  => $sped_fee['amount'],
        'status'   => 'unpaid',
        'paid_at'  => null,
        'or_number' => null,
        'payment_method' => null,
        'payment_plan' => null,
        'surcharge' => 0,
        'proof_file' => null,
        'payment_id' => null,
      ];
      $total_fees += $sped_fee['amount'];
      $total_bal  += $sped_fee['amount'];
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Statement of Account — Parent Portal</title>
  <link rel="icon" type="image/x-icon" href="../images/COJ.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/portal.css">
</head>
<body>
<?php include('includes/nav.php'); ?>
<div class="portal-container">
  <div class="portal-page-header">
    <h2>Statement of Account</h2>
    <p><?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?> · SY <?= htmlspecialchars($active_sy['label'] ?? '') ?></p>
  </div>

  <?php if ($success): ?><div class="portal-success-msg"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="portal-error-msg"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <!-- SOA Summary -->
  <div class="soa-summary">
    <div class="soa-sum-item">
      <div class="soa-sum-label">Total Fees</div>
      <div class="soa-sum-val">₱<?= number_format($total_fees, 2) ?></div>
    </div>
    <div class="soa-sum-item soa-paid">
      <div class="soa-sum-label">Total Paid</div>
      <div class="soa-sum-val">₱<?= number_format($total_paid, 2) ?></div>
    </div>
    <div class="soa-sum-item soa-balance">
      <div class="soa-sum-label">Balance</div>
      <div class="soa-sum-val">₱<?= number_format($total_bal, 2) ?></div>
    </div>
  </div>

  <!-- Payment Instructions -->
  <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:16px 20px;margin-bottom:20px;">
    <div style="font-size:13px;font-weight:700;color:#166534;margin-bottom:8px;"><i class="bi bi-bank"></i> Payment Instructions</div>
    <div style="font-size:13px;color:#374151;line-height:1.8;">
      <strong>GCash:</strong> 09XX-XXX-XXXX (COJ Catholic Progressive School)<br>
      <strong>Bank Transfer:</strong> BDO Savings — Account Name: COJ Catholic Progressive School · Account No: 1234-5678-90<br>
      <strong>Cash:</strong> Pay at the Finance Office, present this SOA as reference.
    </div>
    <div style="font-size:12px;color:#6b7280;margin-top:8px;">After payment, upload your receipt/screenshot below next to the corresponding fee.</div>
  </div>

  <!-- Discounts applied -->
  <?php
  $discounts = $conn->query("
    SELECT d.type, d.percentage, d.fixed_amount, d.label
    FROM discounts d
    WHERE d.student_id = $student_id AND d.school_year_id = $sy_id
  ")->fetch_all(MYSQLI_ASSOC);
  if (!empty($discounts)):
  ?>
  <div style="background:#eef0f8;border-radius:10px;padding:14px 18px;margin-bottom:16px;">
    <div style="font-size:13px;font-weight:700;color:#494C8A;margin-bottom:8px;"><i class="bi bi-percent"></i> Discounts / Scholarships Applied</div>
    <?php foreach ($discounts as $d): ?>
    <div style="display:flex;justify-content:space-between;font-size:13px;padding:4px 0;border-bottom:1px solid #e0e4f0;">
      <span><?= htmlspecialchars($d['label'] ?: ucfirst($d['type'])) ?></span>
      <span style="color:#16a34a;font-weight:700;">
        <?= $d['fixed_amount'] ? '-₱'.number_format($d['fixed_amount'],2) : '-'.$d['percentage'].'%' ?>
      </span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Fee breakdown -->
  <div class="soa-table-card">
    <table class="soa-table">
      <thead>
        <tr><th>Fee</th><th>Amount</th><th>Paid</th><th>Balance</th><th>Status</th><th>Method</th><th>OR #</th><th>Proof</th></tr>
      </thead>
      <tbody>
        <?php foreach ($fees_payments as $fp): ?>
        <tr>
          <td style="font-weight:600;"><?= htmlspecialchars($fp['fee_name']) ?></td>
          <td>₱<?= number_format($fp['amount'], 2) ?></td>
          <td style="color:var(--portal-success);font-weight:600;">₱<?= number_format($fp['amount_paid'] ?? 0, 2) ?></td>
          <td style="color:<?= ($fp['balance'] ?? 0) > 0 ? 'var(--portal-danger)' : 'var(--portal-success)' ?>;font-weight:600;">
            ₱<?= number_format($fp['balance'] ?? $fp['amount'], 2) ?>
          </td>
          <td>
            <?php $st = $fp['status'] ?? 'unpaid'; ?>
            <span class="soa-badge soa-<?= $st ?>"><?= ucfirst($st) ?></span>
          </td>
          <td class="td-muted"><?= $fp['payment_method'] ? ucfirst(str_replace('_',' ',$fp['payment_method'])) : '—' ?></td>
          <td class="td-muted"><?= htmlspecialchars($fp['or_number'] ?? '—') ?></td>
          <td>
            <?php if (!empty($fp['proof_file'])): ?>
              <a href="../pages/uploads/<?= htmlspecialchars($fp['proof_file']) ?>" target="_blank" class="portal-btn-view"><i class="bi bi-eye-fill"></i> View</a>
              <!-- Allow re-upload -->
              <form method="POST" action="soa.php" enctype="multipart/form-data" style="display:inline-flex;gap:4px;align-items:center;margin-top:4px;">
                <input type="hidden" name="fee_id" value="<?= $fp['fee_id'] ?? 0 ?>">
                <input type="hidden" name="payment_id" value="<?= $fp['payment_id'] ?? 0 ?>">
                <input type="file" name="proof" accept="image/*,.pdf" class="portal-file-input" required/>
                <button type="submit" class="portal-btn-upload" title="Re-upload proof"><i class="bi bi-arrow-repeat"></i></button>
              </form>
            <?php else: ?>
              <form method="POST" action="soa.php" enctype="multipart/form-data" style="display:inline-flex;gap:4px;align-items:center;">
                <input type="hidden" name="fee_id" value="<?= $fp['fee_id'] ?? 0 ?>">
                <input type="hidden" name="payment_id" value="<?= $fp['payment_id'] ?? 0 ?>">
                <input type="file" name="proof" accept="image/*,.pdf" class="portal-file-input" required/>
                <button type="submit" class="portal-btn-upload"><i class="bi bi-upload"></i> Upload</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($fees_payments)): ?>
        <tr><td colspan="8" style="text-align:center;padding:32px;color:#6b7280;">No fee records found for this school year.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="portal-req-note" style="margin-top:20px;">
    <i class="bi bi-info-circle-fill"></i>
    To upload proof of payment, click the upload button next to the fee. Accepted: JPG, PNG, PDF.
    Contact the finance office for payment concerns.
  </div>
</div>
</body>
</html>
