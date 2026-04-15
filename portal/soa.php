<?php
$active_portal = 'soa';
require_once 'includes/auth.php';

$active_sy = $conn->query("SELECT * FROM school_years WHERE is_active=1 LIMIT 1")->fetch_assoc();
$sy_id     = $active_sy['id'] ?? 0;
$student   = $conn->query("SELECT s.*, g.name as grade FROM students s LEFT JOIN grade_levels g ON s.grade_level_id=g.id WHERE s.id=$student_id")->fetch_assoc();

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $payment_id = intval($_POST['payment_id']);
  if (!empty($_FILES['proof']['tmp_name'])) {
    $allowed = ['image/jpeg','image/png','image/webp','application/pdf'];
    if (!in_array($_FILES['proof']['type'], $allowed)) {
      $error = "Only JPG, PNG, WEBP, or PDF allowed.";
    } else {
      $fname = 'proof_' . uniqid() . '_' . basename($_FILES['proof']['name']);
      move_uploaded_file($_FILES['proof']['tmp_name'], "../pages/uploads/" . $fname);
      $conn->query("UPDATE payments SET proof_file='$fname' WHERE id=$payment_id AND student_id=$student_id");
      $success = "Proof of payment uploaded.";
    }
  }
}

$fees_payments = $conn->query("
  SELECT f.name as fee_name, f.amount,
         p.id as payment_id, p.amount_paid, p.balance, p.status,
         p.paid_at, p.or_number, p.payment_method, p.proof_file
  FROM fees f
  LEFT JOIN payments p ON p.fee_id=f.id AND p.student_id=$student_id
  WHERE f.grade_level_id = {$student['grade_level_id']} AND f.school_year_id = $sy_id
  ORDER BY f.name
")->fetch_all(MYSQLI_ASSOC);

$total_fees = array_sum(array_column($fees_payments, 'amount'));
$total_paid = array_sum(array_column($fees_payments, 'amount_paid'));
$total_bal  = array_sum(array_column($fees_payments, 'balance'));
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
              <a href="../pages/uploads/<?= htmlspecialchars($fp['proof_file']) ?>" target="_blank" class="portal-btn-view">View</a>
            <?php elseif (!empty($fp['payment_id'])): ?>
              <form method="POST" action="soa.php" enctype="multipart/form-data" style="display:inline-flex;gap:4px;align-items:center;">
                <input type="hidden" name="payment_id" value="<?= $fp['payment_id'] ?>">
                <input type="file" name="proof" accept="image/*,.pdf" class="portal-file-input" required/>
                <button type="submit" class="portal-btn-upload"><i class="bi bi-upload"></i></button>
              </form>
            <?php else: ?>
              <span class="td-muted">—</span>
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
