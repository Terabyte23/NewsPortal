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

