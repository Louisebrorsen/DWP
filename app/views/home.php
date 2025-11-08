<?php
require_once __DIR__ . '/../../includes/movies.php';
$movies = movies_all(8); // hent de 8 nyeste film
?>
<main>
  <section class="hero">
    <div class="container hero__wrap">
      <div>
        <h1>Oplev magien på det store lærred</h1>
        <p>Book billetter til de nyeste blockbusters og tidløse klassikere. En simpel og hurtig oplevelse – på alle enheder.</p>
        <div class="hero__actions">
          <a class="btn btn--primary" href="#today">Se dagens forestillinger</a>
          <a class="btn btn--ghost" href="#showing">Udforsk film</a>
        </div>
      </div>
      <div class="hero__poster" aria-hidden="true"></div>
    </div>
  </section>

  <section id="showing">
    <div class="container">
      <div class="section__head">
        <div>
          <h2 class="section__title">I biografen nu</h2>
          <p class="section__sub">Aktuelle titler du kan se i denne uge</p>
        </div>
        <a class="btn btn--ghost" href="#">Alle film</a>
      </div>
      <div class="grid">
        <?php foreach ($movies as $m): ?>
        <article class="card">
          <?php if (!empty($m['poster_url'])): ?>
            <img class="card__media" src="<?= url($m['poster_url']) ?>" alt="Plakat for <?= e($m['title']) ?>">
          <?php else: ?>
            <div class="card__media" aria-hidden="true"></div>
          <?php endif; ?>

          <div class="card__body">
            <span class="badge">
              <?= e($m['duration_min']) ?> min · <?= e($m['age_limit']) ?>+
            </span>
            <div class="title"><?= e($m['title']) ?></div>
            <div class="meta">
              <?= !empty($m['released']) ? e(date('d.m.Y', strtotime($m['released']))) : 'Ukendt premieredato' ?>
            </div>
          </div>

          <div class="card__actions">
            <a class="btn btn--primary" href="#today">Billetter</a>
            <a class="btn btn--ghost" href="#">Detaljer</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
  </section>

  <section id="today">
    <div class="container">
      <div class="section__head">
        <div>
          <h2 class="section__title">Dagens forestillinger</h2>
          <p class="section__sub">Vælg tidspunkt og reserver pladser</p>
        </div>
        <a class="btn btn--ghost" href="#">Hele ugeplanen</a>
      </div>

      <div class="list" role="list">
        <?php foreach ([
          ['title' => 'Stellar Odyssey',  'meta' => 'Sal 1 · Rækkevalg · 2D', 'price' => 'DKK 110', 'time' => '16:20'],
          ['title' => 'City of Echoes',   'meta' => 'Sal 2 · Atmos · 2D',     'price' => 'DKK 120', 'time' => '18:45'],
          ['title' => 'The Little Comet', 'meta' => 'Sal 3 · Dansk tale',     'price' => 'DKK 95',  'time' => '19:10'],
        ] as $s): ?>
        <div class="row" role="listitem">
          <div>
            <div class="title"><?= e($s['title']) ?></div>
            <div class="meta"><?= e($s['meta']) ?></div>
          </div>
          <div class="row__format"><?= e($s['price']) ?></div>
          <div class="row__time"><?= e($s['time']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="cta">
        <div>
          <div class="title">Klar til at booke?</div>
          <div class="muted small">Vælg en forestilling ovenfor og fortsæt til sædevalg.</div>
        </div>
        <a class="btn btn--primary" href="#">Gå til booking</a>
      </div>
    </div>
  </section>

  <section id="coming">
    <div class="container">
      <div class="section__head">
        <div>
          <h2 class="section__title">Kommende film</h2>
          <p class="section__sub">Forpremierer og næste måneds highlights</p>
        </div>
        <a class="btn btn--ghost" href="#">Se kalender</a>
      </div>

      <div class="grid">
        <?php foreach ([
          ['badge' => 'Premiere · 15/11', 'title' => 'Neon Boulevard', 'meta' => 'Krimi · Engelsk'],
          ['badge' => 'Premiere · 29/11', 'title' => 'Aurora Falls',   'meta' => 'Eventyr · Norsk'],
        ] as $c): ?>
        <article class="card">
          <div class="card__media" role="img" aria-label="Kommer snart"></div>
          <div class="card__body">
            <span class="badge"><?= e($c['badge']) ?></span>
            <div class="title"><?= e($c['title']) ?></div>
            <div class="meta"><?= e($c['meta']) ?></div>
          </div>
          <div class="card__actions">
            <a class="btn btn--ghost" href="#">Påmind mig</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="contact">
    
  <div class="contact__container">
    <h1>Kontakt os</h1>
    <p class="contact__intro">Har du spørgsmål til vores forestillinger, billetter eller arrangementer? Udfyld formularen nedenfor, så vender vi tilbage hurtigst muligt.</p>

    <form action="/./includes/contact_actions.php" method="POST" class="contact__form">
      <?= csrf_input() ?>

      <div class="form__group">
        <label for="name">Navn</label>
        <input type="text" id="name" name="name" placeholder="Dit fulde navn" required>
      </div>

      <div class="form__group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="din@email.dk" required>
      </div>

      <div class="form__group">
        <label for="subject">Emne</label>
        <input type="text" id="subject" name="subject" placeholder="Hvad drejer henvendelsen sig om?" required>
      </div>

      <div class="form__group">
        <label for="message">Besked</label>
        <textarea id="message" name="message" rows="5" placeholder="Skriv din besked her..." required></textarea>
      </div>

      <div class="form__group">
        <label>
          <input type="checkbox" name="terms" value="true" required>
          Jeg accepterer <a href="#">betingelserne</a>
        </label>
      </div>

      <div class="form__group">
        <label>
          <input type="checkbox" name="subscribe" value="yes">
          Tilmeld mig nyhedsbrevet
        </label>
      </div>

      <button type="submit" name="submit" class="btn btn--primary">Send besked</button>
    </form>
  </div>
</main>
  

</main>