<?php
require_once __DIR__ . '/includes/functions.php';

$ref = strtoupper(trim($_POST['ref'] ?? ($_GET['ref'] ?? '')));
$phone = trim($_POST['phone'] ?? '');
$booking = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['chk'] = ($_SESSION['chk'] ?? 0) + 1;
    if (!csrf_ok()) {
        $error = 'Your session expired. Please try again.';
    } elseif ($_SESSION['chk'] > 10) {
        $error = 'Too many attempts. Please call us instead.';
    } elseif ($ref === '' || $phone === '') {
        $error = 'Enter your booking reference and phone number.';
    } else {
        try {
            $st = db()->prepare('SELECT ref, name, phone, reserve_date, reserve_time, guests, status FROM reservations WHERE ref = ?');
            $st->execute([$ref]);
            $row = $st->fetch();
            if ($row && strlen(phone_digits($phone)) >= 7 && phone_digits($row['phone']) === phone_digits($phone)) {
                $booking = $row;
                $_SESSION['chk'] = 0;
            } else {
                // same message either way, so nobody can probe for valid references
                $error = 'We could not find a booking with that reference and phone number.';
            }
        } catch (PDOException $ex) {
            $error = 'Something went wrong. Please try again shortly.';
        }
    }
}

$messages = [
    'pending'   => 'We have your request and will confirm by phone or email soon.',
    'confirmed' => 'Your table is confirmed. We will see you then.',
    'cancelled' => 'This booking was cancelled. Please call us or make a new booking.',
];

require __DIR__ . '/includes/header.php';
?>
<section class="book check"><div class="wrap">
  <h2>Check your booking</h2>
  <p>Enter the reference you got when you booked, and the phone number you used.</p>

  <?php if ($error): ?><div class="msg err"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="check.php" novalidate>
    <?= csrf_field() ?>
    <label>Booking reference<input name="ref" placeholder="KK-AB12CD" maxlength="12" value="<?= e($ref) ?>" autocapitalize="characters"></label>
    <label>Phone number<input name="phone" type="tel" value="<?= e($phone) ?>"></label>
    <button class="btn" type="submit">Check status</button>
  </form>

  <?php if ($booking): ?>
    <div class="card">
      <span class="badge <?= e($booking['status']) ?>"><?= e(ucfirst($booking['status'])) ?></span>
      <h3><?= e($booking['name']) ?></h3>
      <p><?= e($messages[$booking['status']] ?? '') ?></p>
      <dl>
        <dt>Reference</dt><dd class="ref"><?= e($booking['ref']) ?></dd>
        <dt>Date</dt><dd><?= e(date('D, j M Y', strtotime($booking['reserve_date']))) ?></dd>
        <dt>Time</dt><dd><?= e(date('g:i A', strtotime($booking['reserve_time']))) ?></dd>
        <dt>Guests</dt><dd><?= (int)$booking['guests'] ?></dd>
      </dl>
    </div>
  <?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
