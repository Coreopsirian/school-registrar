<?php
require_once __DIR__ . '/mailer.php';

function notifyEnrollmentReceived($parent_email, $parent_name, $student_name, $ref_number, $plain_password = null) {
  $subject = "Enrollment Application Received - $ref_number";

  $portal_url = getenv('APP_URL') ?: 'http://localhost/school-registrar/portal/login.php';

  // Login credentials block — only shown for new accounts
  $credentials_block = '';
  if ($plain_password) {
    $credentials_block = "
    <div style='background:#eef0f8;border-radius:8px;padding:16px 18px;margin:20px 0;'>
      <div style='font-size:12px;font-weight:700;color:#494C8A;text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;'>Your Parent Portal Login</div>
      <table style='font-size:13px;color:#374151;width:100%;'>
        <tr><td style='padding:4px 0;font-weight:600;width:120px;'>Portal URL:</td><td><a href='$portal_url' style='color:#494C8A;'>$portal_url</a></td></tr>
        <tr><td style='padding:4px 0;font-weight:600;'>Email:</td><td>$parent_email</td></tr>
        <tr><td style='padding:4px 0;font-weight:600;'>Password:</td><td><code style='background:#f3f4f6;padding:2px 8px;border-radius:4px;font-size:13px;font-weight:700;'>$plain_password</code></td></tr>
      </table>
      <div style='margin-top:10px;font-size:12px;color:#d97706;'>
        ⚠️ Please save your password. You can change it anytime from the portal.
      </div>
    </div>
    <p style='text-align:center;'>
      <a href='$portal_url' style='display:inline-block;padding:12px 28px;background:#494C8A;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;'>Login to Parent Portal</a>
    </p>";
  } else {
    $credentials_block = "
    <p>Log in to the <a href='$portal_url' style='color:#494C8A;font-weight:600;'>Parent Portal</a> using your existing email and password.</p>";
  }

  $body = "
    <p>Dear <strong>" . htmlspecialchars($parent_name) . "</strong>,</p>
    <p>We have received the enrollment application for <strong>" . htmlspecialchars($student_name) . "</strong>.</p>
    <div style='background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:14px 18px;margin:16px 0;'>
      <div style='font-size:12px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:.06em;'>Reference Number</div>
      <div style='font-size:24px;font-weight:800;color:#494C8A;'>$ref_number</div>
    </div>
    <p>Our registrar will review your application within <strong>2–3 school days</strong>. You will receive another email once your application has been processed.</p>
    <p>In the meantime, please prepare the following documents:</p>
    <ul>
      <li>PSA Birth Certificate (original or certified true copy)</li>
      <li>Form 138 / Report Card from previous school</li>
      <li>Good Moral Certificate</li>
      <li>2x2 ID Photo (2 pieces, white background)</li>
    </ul>
    $credentials_block
    <p>Thank you for choosing COJ Catholic Progressive School.</p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

function notifyEnrollmentApproved($parent_email, $parent_name, $student_name, $grade) {
  $subject = "Enrollment Approved — $student_name";
  $body = "
    <p>Dear <strong>$parent_name</strong>,</p>
    <p>We are pleased to inform you that the enrollment of <strong>$student_name</strong> for <strong>$grade</strong> has been <span style='color:#16a34a;font-weight:700;'>APPROVED</span>.</p>
    <p>Please log in to the Parent Portal to:</p>
    <ul>
      <li>View your Statement of Account</li>
      <li>Upload any remaining required documents</li>
      <li>Submit proof of payment</li>
    </ul>
    <p><a href='http://localhost/school-registrar/portal/login.php' style='display:inline-block;padding:10px 24px;background:#494C8A;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;'>Go to Parent Portal</a></p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

function notifyDocumentVerified($parent_email, $parent_name, $student_name, $doc_name) {
  $subject = "Document Verified — $doc_name";
  $body = "
    <p>Dear <strong>$parent_name</strong>,</p>
    <p>The following document for <strong>$student_name</strong> has been <span style='color:#16a34a;font-weight:700;'>VERIFIED</span> by our registrar:</p>
    <p style='background:#f0fdf4;border-left:4px solid #16a34a;padding:12px 16px;border-radius:6px;font-weight:600;'>$doc_name</p>
    <p>Log in to the Parent Portal to check the status of your remaining documents.</p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

function notifyDocumentRejected($parent_email, $parent_name, $student_name, $doc_name) {
  $subject = "Document Needs Resubmission — $doc_name";
  $body = "
    <p>Dear <strong>$parent_name</strong>,</p>
    <p>The following document for <strong>$student_name</strong> requires resubmission:</p>
    <p style='background:#fef2f2;border-left:4px solid #dc2626;padding:12px 16px;border-radius:6px;font-weight:600;'>$doc_name</p>
    <p>Please upload a clearer or correct copy through the Parent Portal.</p>
    <p>If you have questions, please contact the registrar's office directly.</p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

function notifyPaymentReceived($parent_email, $parent_name, $student_name, $amount, $or_number) {
  $subject = "Payment Received — OR# $or_number";
  $body = "
    <p>Dear <strong>$parent_name</strong>,</p>
    <p>We have recorded a payment for <strong>$student_name</strong>:</p>
    <table style='width:100%;border-collapse:collapse;font-size:13px;margin:16px 0;'>
      <tr><td style='padding:8px 12px;background:#f9fafb;border:1px solid #e5e7eb;font-weight:600;'>Amount Paid</td><td style='padding:8px 12px;border:1px solid #e5e7eb;'>₱" . number_format($amount, 2) . "</td></tr>
      <tr><td style='padding:8px 12px;background:#f9fafb;border:1px solid #e5e7eb;font-weight:600;'>OR Number</td><td style='padding:8px 12px;border:1px solid #e5e7eb;'>$or_number</td></tr>
    </table>
    <p>View your full Statement of Account in the Parent Portal.</p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

/**
 * Congratulations email sent when enrollment status is set to 'enrolled'.
 */
function notifyEnrollmentConfirmed($parent_email, $parent_name, $student_name, $grade, $sy_label) {
  $portal_url = getenv('APP_URL') ?: 'http://localhost/school-registrar/portal/login.php';
  $subject = "🎉 Enrollment Confirmed — " . $student_name;
  $body = "
    <div style='text-align:center;padding:8px 0 24px;'>
      <div style='font-size:48px;'>🎉</div>
      <h2 style='color:#494C8A;margin:8px 0 4px;font-size:22px;'>Congratulations!</h2>
      <p style='color:#6b7280;font-size:14px;margin:0;'>Enrollment has been officially confirmed.</p>
    </div>
    <p>Dear <strong>" . htmlspecialchars($parent_name) . "</strong>,</p>
    <p>We are delighted to inform you that <strong>" . htmlspecialchars($student_name) . "</strong> is now officially enrolled for <strong>" . htmlspecialchars($grade) . "</strong> — SY <strong>" . htmlspecialchars($sy_label) . "</strong>.</p>
    <div style='background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:18px 20px;margin:20px 0;'>
      <div style='font-size:13px;font-weight:700;color:#166534;margin-bottom:10px;'>✅ What's next?</div>
      <ul style='margin:0;padding-left:18px;font-size:13px;color:#374151;line-height:2;'>
        <li>Log in to the Parent Portal to view your <strong>Statement of Account</strong></li>
        <li>Select your <strong>Payment Scheme</strong> (Annual, Semi-Annual, Quarterly, or Monthly)</li>
        <li>Upload your <strong>Proof of Payment</strong> once you've settled the downpayment</li>
        <li>Ensure all required <strong>documents</strong> are submitted and verified</li>
      </ul>
    </div>
    <p style='text-align:center;'>
      <a href='$portal_url' style='display:inline-block;padding:13px 32px;background:#494C8A;color:#fff;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;'>Go to Parent Portal</a>
    </p>
    <p style='font-size:13px;color:#6b7280;margin-top:24px;'>If you have any questions, please don't hesitate to contact the registrar's office. We look forward to a wonderful school year with your child!</p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}

/**
 * Generic notification email — sent when admin manually sends a notification to a parent.
 */
function notifyParentCustom($parent_email, $parent_name, $student_name, $title, $message) {
  $portal_url = getenv('APP_URL') ?: 'http://localhost/school-registrar/portal/login.php';
  $subject = $title;
  $body = "
    <p>Dear <strong>" . htmlspecialchars($parent_name) . "</strong>,</p>
    <p>You have a new message from the school regarding <strong>" . htmlspecialchars($student_name) . "</strong>:</p>
    <div style='background:#f8f9ff;border-left:4px solid #494C8A;border-radius:6px;padding:16px 18px;margin:16px 0;font-size:14px;color:#374151;line-height:1.7;'>
      " . nl2br(htmlspecialchars($message)) . "
    </div>
    <p style='text-align:center;'>
      <a href='$portal_url' style='display:inline-block;padding:11px 28px;background:#494C8A;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;'>View in Parent Portal</a>
    </p>
  ";
  return sendEnrollmentEmail($parent_email, $parent_name, $subject, $body);
}
