<?php
// Footer component
?>
<footer class="footer">
    <div class="container footer-grid">
        
        <!-- COL 1: BRAND -->
        <div class="footer-col footer-brand">
            <div class="logo">
                <span class="logo-accent">NEWS</span><span class="logo-text">PORTAL</span>
            </div>
            <p class="footer-desc">
                Eesti kaasaegseim tehnoloogia-, teadus- ja haridusuudiste platvorm. Objektiivne, kiire ja analüütiline ajakirjandus.
            </p>
            <div class="footer-socials">
                <a href="#" class="social-btn" title="Twitter / X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                <a href="#" class="social-btn" title="Telegram"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.75-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/></svg></a>
                <a href="#" class="social-btn" title="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                <a href="#" class="social-btn" title="RSS Voog"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg></a>
            </div>
        </div>

        <!-- COL 2: RUBRIIGID -->
        <div class="footer-col">
            <h4 class="footer-heading">Rubriigid</h4>
            <ul class="footer-links">
                <li><a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=1' : 'category.php?id=1' ?>">Tehnoloogia</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=2' : 'category.php?id=2' ?>">Haridus ja Ülikoolid</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=3' : 'category.php?id=3' ?>">Teadus ja Kosmos</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=4' : 'category.php?id=4' ?>">Internet & Küberturve</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../category.php?id=5' : 'category.php?id=5' ?>">Majandus & Krüpto</a></li>
            </ul>
        </div>

        <!-- COL 3: PORTAAL -->
        <div class="footer-col">
            <h4 class="footer-heading">Portaal</h4>
            <ul class="footer-links">
                <li><a href="<?= isset($depth) && $depth == 1 ? '../index.php' : 'index.php' ?>">Avaleht</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../saved.php' : 'saved.php' ?>">Salvestatud artiklid</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../search.php' : 'search.php' ?>">Täpsem otsing</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? 'index.php' : 'admin/index.php' ?>">Toimetuse CMS</a></li>
                <li><a href="<?= isset($depth) && $depth == 1 ? '../admin/seed.php' : 'admin/seed.php' ?>">Andmebaasi demo täitja</a></li>
            </ul>
        </div>

        <!-- COL 4: NEWSLETTER -->
        <div class="footer-col footer-newsletter">
            <h4 class="footer-heading">Telli uudiskiri</h4>
            <p class="footer-subtext">Saa iga hommik olulisemad tehnoloogia- ja teadusuudised otse postkasti.</p>
            <form class="newsletter-form" id="newsletterForm">
                <div class="newsletter-input-group">
                    <input type="email" placeholder="Sinu e-posti aadress" required class="newsletter-input" id="newsletterEmail">
                    <button type="submit" class="btn btn-primary-sm">Telli</button>
                </div>
            </form>
            <div class="footer-badge">
                <span>⚡ Powered by PHP 8 & MySQL</span>
            </div>
        </div>

    </div>

    <!-- BOTTOM COPYRIGHT -->
    <div class="footer-bottom">
        <div class="container footer-bottom-inner">
            <p>© <?= date('Y') ?> NewsPortal — Kõik õigused kaitstud. Agiilne tarkvaraarenduse projekt.</p>
            <div class="footer-bottom-links">
                <a href="#">Privaatsustingimused</a>
                <span>•</span>
                <a href="#">Küpsised</a>
                <span>•</span>
                <a href="#">Toimetuse reeglid</a>
            </div>
        </div>
    </div>
</footer>

<!-- JS SCRIPTS -->
<script src="<?= isset($depth) && $depth == 1 ? '../js/main.js' : 'js/main.js' ?>"></script>
</body>
</html>