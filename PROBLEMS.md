# ⚠️ NewsPortali peamiste kitsaskohtade ja probleemide analüüs

Käesolevas dokumendis on põhjalikult lahti kirjutatud NewsPortali veebiplatvormi 3 kõige kriitilisemat probleemi, kitsaskohta ja arhitektuurilist puudust, mis mõjutavad süsteemi turvalisust, skaleeritavust ja jõudlust.

---

## 1. Kõvakodeeritud andmebaasi konfiguratsioon ja DDL-migratsioonide käitamine igal päringul (`db.php`)

### Kirjeldus:
Failis [`db.php`](db.php) on andmebaasiühenduse parameetrid (`root`, tühi parool, `localhost`) kirjutatud otse lähtekoodi sisse. Lisaks käivitatakse iga kasutaja igal lehelaadimisel päringud `CREATE DATABASE IF NOT EXISTS` ning funktsioon `migrate_database_if_needed($conn)`, mis kontrollib ja loob tabeleid DDL-päringutega (`CREATE TABLE IF NOT EXISTS`).

### Tagajärjed ja riskid:
1. **Turvalisuse leke:** Kui lähtekood satub avalikku GitHubi repositooriumisse või lekib kolmandatele isikutele, on andmebaasi volitused koheselt kompromiteeritud.
2. **Keeruline paigaldus (Dev / Test / Prod):** Erinevates keskkondades (arendus, test, toodang) ei saa kasutada erinevaid paroole ja kasutajanimesid ilma lähtekoodi käsitsi muutmata.
3. **Jõudluse langus ja lukud:** DDL-päringud (`CREATE TABLE IF NOT EXISTS`, `SHOW COLUMNS`) vajavad MySQL-i tasemel metaandmete lukke (Metadata Locks). Suure külastatavuse korral tekitab see päringute järjekorra ja aeglustab kogu lehe tööd.

### Soovituslik lahendus:
- Viia andmebaasi konfiguratsioon eraldi keskkonnamuutujate faili (`.env`), mis on lisatud `.gitignore` faili, kasutades standardset `getenv()` või `vlucas/phpdotenv` lahendust.
- Eemaldada andmebaasi ja tabelite automaatne loomine lehe laadimise käitusajast (`db.php`). Migratsioone peaks käivitama ühekordselt käsurealt (CLI) paigalduse või uuenduste ajal.

---

## 2. Päringulimiitide (Rate Limiting) ja spämmikaitse puudumine API-s ning vaatamiste loenduris

### Kirjeldus:
Avalikes liidestes [`api/reactions.php`](api/reactions.php) ja [`api/comments.php`](api/comments.php) puudub päringusageduse piirang (Rate Limiting) ja kaitse automatiseeritud robotite vastu. Samuti suurendatakse failis [`news.php`](news.php) artikli vaatamiste loendurit (`UPDATE news SET views = views + 1 WHERE id = ?`) igal tavalisel lehe värskendamisel (F5) ilma sessiooni või IP-põhise unikaalsuse kontrollita.

### Tagajärjed ja riskid:
1. **Mõõdikute manipuleerimine:** Iga lihtne skript või robot saab saata tuhandeid POST-päringuid sekundis ning kerida artiklitele miljoneid meeldimisi või vaatamisi, muutes portaali analüütika ja edetabelid väärtusetuks.
2. **Kommentaariumide spämmimine:** Külaliste kommentaarid ilma robotilõksu (Honeypot) või kontrollkoodita (CAPTCHA) võimaldavad pahatahtlikel robotitel andmebaasi hetkega rämpspostiga üle koormata.
3. **Andmebaasi koormus (DoS oht):** Kontrollimatu kirjutamispäringute voog (`UPDATE news SET ...`) võib ammendada serveri andmebaasiühendused ja muuta veebilehe reaalsetele kasutajatele kättesaamatuks.

### Soovituslik lahendus:
- Rakendada vaatamiste unikaalsuse kontroll sessiooni põhiselt (`$_SESSION['viewed_news']`), et sama kasutaja lehte värskendades vaatamiste arv ei kasvaks korduvalt.
- Luua päringute piiraja (Throttling / Rate-Limiting) avalikele API otspunktidele (nt maksimaalselt 5–10 reaktsiooni minutis ühelt IP-aadressilt).
- Lisada külaliste kommentaarivormile Honeypot-tühi väli või Cloudflare Turnstile / reCAPTCHA integratsioon.

---

## 3. Leheküljendamise (Pagination) puudumine uudiste ja kommentaaride nimekirjades

### Kirjeldus:
Avalehel ([`index.php`](index.php)), halduspaneelis ([`admin/news.php`](admin/news.php), [`admin/comments.php`](admin/comments.php)) ja artikli kommentaarides ([`news.php`](news.php)) tehakse andmebaasipäringud ilma `LIMIT` ja `OFFSET` klausliteta (nt `SELECT cm.*, n.title ... ORDER BY cm.id DESC` laeb korraga mällu absoluutselt kõik süsteemis olevad kommentaarid).

### Tagajärjed ja riskid:
1. **Mälumahu ületamine (RAM Exhaustion):** Kui andmebaasis on sadu või tuhandeid artikleid ja kommentaare, püüab PHP korraga laadida mitmeid megabaite toorandmeid, mis toob kaasa `Fatal Error: Allowed memory size exhausted` vea.
2. **Kliendi brauseri aeglustumine:** Sadade või tuhandete DOM-kaartide korraga brauserisse joonistamine tekitab lehe kerimisel märgatavat hangumist ja halvendab oluliselt lehe esmast laadimiskiirust (Core Web Vitals / LCP).
3. **Võrgu- ja serverikoormus:** Iga päringuga kantakse üle tohutu hulk mittevajalikku infot, mis koormab nii veebiserverit kui ka mobiilse internetiga lugejaid.

### Soovituslik lahendus:
- Rakendada uudiste nimekirjadele klassikaline leheküljendamine (nt 9–12 uudist lehe kohta) koos URL-i parameetriga `?page=1`.
- Artikli kommentaariumis võtta kasutusele kas leheküljed või dünaamiline AJAX-nupp "Laadi järgmised 10 kommentaari".
- Halduspaneelis lisada tabelitele leheküljendamise ja kirjete arvu valiku võimalused.


