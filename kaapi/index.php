<?php
require_once __DIR__ . '/includes/functions.php';

/* ---------- Handle booking form ---------- */
$errors = [];
$data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $data = [
            'name'   => trim($_POST['name'] ?? ''),
            'email'  => trim($_POST['email'] ?? ''),
            'phone'  => trim($_POST['phone'] ?? ''),
            'date'   => trim($_POST['date'] ?? ''),
            'time'   => trim($_POST['time'] ?? ''),
            'guests' => (int)($_POST['guests'] ?? 0),
            'notes'  => trim($_POST['notes'] ?? ''),
        ];
        if ($data['name'] === '' || mb_strlen($data['name']) > 100) $errors[] = 'Enter your name.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (!preg_match('/^[0-9+\-\s()]{7,30}$/', $data['phone'])) $errors[] = 'Enter a valid phone number.';

        $d = DateTime::createFromFormat('Y-m-d', $data['date']);
        if (!$d || $d->format('Y-m-d') !== $data['date']) $errors[] = 'Choose a date.';
        elseif ($data['date'] < date('Y-m-d')) $errors[] = 'Choose a date today or later.';

        $t = DateTime::createFromFormat('H:i', $data['time']);
        if (!$t || $t->format('H:i') !== $data['time']) $errors[] = 'Choose a time.';
        elseif ($data['time'] < '07:00' || $data['time'] > '18:30') $errors[] = 'We take bookings between 07:00 and 18:30.';

        if ($data['guests'] < 1 || $data['guests'] > 12) $errors[] = 'Guests must be between 1 and 12.';
        if (mb_strlen($data['notes']) > 500) $errors[] = 'Keep notes under 500 characters.';

        if (!$errors) {
            try {
                $st = db()->prepare('INSERT INTO reservations (ref,name,email,phone,reserve_date,reserve_time,guests,notes) VALUES (?,?,?,?,?,?,?,?)');
                $ref = null;
                for ($i = 0; $i < 5 && !$ref; $i++) {
                    $try = make_ref();
                    try {
                        $st->execute([$try, $data['name'], $data['email'], $data['phone'], $data['date'], $data['time'], $data['guests'], $data['notes']]);
                        $ref = $try;
                    } catch (PDOException $dup) {
                        if ($dup->getCode() !== '23000') throw $dup; // retry only on duplicate reference
                    }
                }
                if (!$ref) throw new PDOException('Could not create reference');
                $_SESSION['booked_ref'] = $ref;
                header('Location: index.php#book');
                exit;
            } catch (PDOException $ex) {
                $errors[] = 'We could not save your booking. Please call us instead.';
            }
        }
    }
}
$bookedRef = $_SESSION['booked_ref'] ?? null;
unset($_SESSION['booked_ref']);

/* ---------- Load menu ---------- */
$cats = [];
$dbError = false;
try {
    $rows = db()->query(
        'SELECT c.id AS cid, c.name AS cname, m.name, m.description, m.price
         FROM categories c JOIN menu_items m ON m.category_id = c.id
         WHERE m.is_available = 1 ORDER BY c.sort_order, m.sort_order, m.id'
    )->fetchAll();
    foreach ($rows as $r) {
        $cats[$r['cid']]['name'] = $r['cname'];
        $cats[$r['cid']]['items'][] = $r;
    }
} catch (PDOException $ex) {
    $dbError = true;
}

$places = ['Chikmagalur', 'Coorg', 'Biligiri Hills', 'Sakleshpur', 'Araku Valley', 'Wayanad', 'Shevaroy Hills'];
$ticker = '';
foreach ($places as $p) $ticker .= '<span>' . e($p) . '</span><i>✦</i>';

require __DIR__ . '/includes/header.php';
?>
<div class="hero" id="top"><div class="wrap">
  <div>
    <p class="kn" aria-hidden="true">ಕಾಫಿ</p>
    <h1>Filter kaapi, poured the long way.</h1>
    <p>A Bengaluru coffee house for decoction brewed slow, milk pulled high and steel tumblers that are too hot to hold. Beans come from the hills of Chikmagalur and Coorg.</p>
    <div class="btns"><a class="btn" href="#menu">See the menu</a><a class="btn alt" href="#book">Book a table</a></div>
  </div>
  <?php include __DIR__ . '/includes/hero-art.php'; ?>
</div></div>

<div class="mq" aria-hidden="true"><div><?= str_repeat($ticker, 4) ?></div></div>

<section id="how"><div class="wrap">
  <h2>How a cup gets made</h2>
  <p style="max-width:34em;color:var(--muted)">No machine shortcuts. Every tumbler goes through the same three steps.</p>
  <div class="steps">
    <div class="step"><b>1</b><h3>Decoction</h3><p>Freshly ground beans with a little chicory drip through a steel filter for forty minutes.</p></div>
    <div class="step"><b>2</b><h3>Hot milk</h3><p>Full-cream milk is boiled twice, then poured over the decoction in the tumbler.</p></div>
    <div class="step"><b>3</b><h3>The pull</h3><p>We pour it between tumbler and davara until it froths and cools to drinking temperature.</p></div>
  </div>
</div></section>

<section class="menu" id="menu"><div class="wrap">
  <h2>Menu</h2>
  <?php if ($dbError): ?>
    <p class="msg err">The menu could not be loaded. Check the database settings in config.php.</p>
  <?php elseif (!$cats): ?>
    <p>The menu is being updated. Please check back soon.</p>
  <?php else: ?>
    <div class="tabs" id="tabs">
      <?php foreach ($cats as $id => $c): ?>
        <button type="button" aria-pressed="false" data-k="<?= (int)$id ?>"><?= e($c['name']) ?></button>
      <?php endforeach; ?>
    </div>
    <?php foreach ($cats as $id => $c): ?>
      <ul class="items" data-panel="<?= (int)$id ?>">
        <?php foreach ($c['items'] as $n => $it): ?>
          <li style="animation-delay:<?= $n * 60 ?>ms">
            <div><span class="n"><?= e($it['name']) ?></span><span class="d"><?= e($it['description']) ?></span></div>
            <span class="p"><?= rupees($it['price']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endforeach; ?>
  <?php endif; ?>
</div></section>

<section class="book" id="book"><div class="wrap">
  <div>
    <h2>Book a table</h2>
    <p>Coming with friends, family or a laptop? Tell us the time and we’ll keep a corner by the window for you. We confirm by phone or email.</p>
  </div>
  <div>
    <?php if ($bookedRef): ?><div class="msg ok">Booking received. Your reference is <strong class="ref"><?= e($bookedRef) ?></strong>. Save it, and use it with your phone number to <a href="check.php?ref=<?= e($bookedRef) ?>">check your booking status</a>.</div><?php endif; ?>
    <?php if ($errors): ?><div class="msg err"><ul><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="index.php#book" novalidate>
      <?= csrf_field() ?>
      <label>Name<input name="name" autocomplete="name" maxlength="100" value="<?= e($data['name'] ?? '') ?>"></label>
      <div class="r3">
        <label>Email<input name="email" type="email" value="<?= e($data['email'] ?? '') ?>"></label>
        <label>Phone<input name="phone" type="tel" value="<?= e($data['phone'] ?? '') ?>"></label>
      </div>
      <div class="r3">
        <label>Date<input name="date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($data['date'] ?? '') ?>"></label>
        <label>Time<input name="time" type="time" min="07:00" max="18:30" value="<?= e($data['time'] ?? '') ?>"></label>
        <label>Guests<input name="guests" type="number" min="1" max="12" value="<?= (int)($data['guests'] ?? 2) ?: 2 ?>"></label>
      </div>
      <label>Notes<textarea name="notes" rows="2" maxlength="500" placeholder="Birthday, high chair, allergies..."><?= e($data['notes'] ?? '') ?></textarea></label>
      <button class="btn" type="submit">Request booking</button>
    </form>
  </div>
</div></section>

<?php require __DIR__ . '/includes/footer.php'; ?>
