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
