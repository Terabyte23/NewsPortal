# 📰 NewsPortal — Next-Gen Uudisteportaal & Toimetuse CMS

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777bb4?logo=php&logoColor=white)](https://php.net/)
[![Database](https://img.shields.io/badge/MySQL-5.7%2B%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Web Server](https://img.shields.io/badge/Apache-XAMPP-FB7A24?logo=apache&logoColor=white)](https://www.apachefriends.org/)
[![UI/UX](https://img.shields.io/badge/Design-Dark%20%26%20Light%20Mode-3b82f6)](style.css)
[![License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)

Kaasaegne, täisfunktsionaalne uudisteportaal ja toimetuse sisuhaldussüsteem (CMS), mis on loodud PHP 8, MySQL ja modernse JavaScripti baasil. Platvorm sisaldab reaalajas vidinaid, tehisintellekti helilugemise tuge (Text-to-Speech), interaktiivseid reaktsioone, järjehoidjaid ning võimsat administraatori juhtpaneeli.

---

## 🌟 Peamised võimalused (Features)

### 🖥️ 1. Avalik portaal ja lugejakogemus
- **Värske ja kaasaegne disain**: Tehnoloogia- ja meediaväljaannete (The Verge, Apple News) stiilis visuaalne identiteet.
- **Tume ja hele režiim (Dark/Light Theme)**: Sujuv teemavahetus, mis salvestatakse brauseri mällu (`localStorage`).
- **Päise reaalajas vidinad**:
  - Reaalajas eestikeelne kuupäev ja kell.
  - Ilmateade Eesti linnades (*Tallinn, Tartu, Narva / Jõhvi*).
  - Finantsturgude ja krüptoraha reaalajas koteeringute riba (*EUR/USD, BTC, ETH*).
  - Vilkuva indikaatoriga animeeritud **erakorraliste uudiste riba** (*Breaking News Ticker*).
- **Esilugu (Hero Lead Story)**: Suureformaadiline juhtartikkel lugemisaja arvutuse ja vaatamiste loenduriga.
- **Rubriikide filtreerimine**: Kiire ja sujuv uudiste sirvimine kategooriate kaupa (*Tehnoloogia, Haridus, Teadus, Internet, Majandus, Kultuur, Sport*).
- **Artikli siseleht (`news.php?id=...`)**:
  - 🎧 **Interaktiivne heliesitus (Text-to-Speech)**: Brauseripõhine AI-häälega artiklite ettelugemine koos edenemisribaga.
  - 🔍 **Kirjasuuruse regulaator (`A-` / `A+`)**: Mugavaks lugemiseks kohandatav tüpograafia.
  - 💖 **Reaktsioonid (AJAX)**: 👍 *Tubli*, ❤️ *Meeldib*, 💡 *Geniaalne*, 🔥 *Kuum* loenduritega.
  - 📑 **Järjehoidjad (Saved / Reading List)**: Artikli salvestamine 1-klõpsuga ja haldamine lehel [`saved.php`](saved.php).
  - 🔗 **Kiire jagamine ja lingi kopeerimine**: 1-klõpsuga kopeerimine lõikelauale koos Toast-teavitusega.
  - 📌 **Samast rubriigist soovitused**: Seotud uudiste plokk.
- **Reaalajas kommentaarium (AJAX)**:
  - Kommentaaride lisamine ilma lehe taaslaadimiseta.
  - Kasutaja rolli märgised (*Peatoimetaja / Autor / Lugeja*) ja suhteline aeg (*"5 min tagasi"*).
- **Kiir-otsing (Live Search Modal)**:
  - Käivitub ikoonist või kiirklahviga `Ctrl+K` / `Cmd+K`.
  - Reaalajas vasteid kuvav otsingumootor.

---

### 🛠️ 2. Toimetuse sisuhaldus (Admin CMS)
Asub aadressil **`/admin/`** ja on kaitstud rollipõhise autentimisega:

- **Analüütika ülevaade (`admin/index.php`)**:
  - Reaalajas KPI mõõdikud: artiklite arv, vaatamiste koguarv, kommentaarid ja registreeritud kasutajad.
  - Viimaste uudiste ja arutelude kiirülevaade.
- **Artiklite täielik CRUD (`admin/news.php`)**:
  - Uute artiklite loomine, muutmine ja kustutamine.
  - Piltide valik galeriist või URL-i kaudu, teemaviidete (tags) ja esiloo määramine.
- **Rubriikide haldus (`admin/categories.php`)**:
  - Kategooriate lisamine, muutmine ja artiklite arvu statistika.
- **Kommentaaride modereerimine (`admin/comments.php`)**:
  - Spämmi ja kohatute kommentaaride eemaldamine 1-klõpsuga.
- **Kasutajate haldus (`admin/users.php`)**:
  - Rolli muutmine (*Admin / User*) ja kasutajate haldus.
- **Demo andmete seeder (`admin/seed.php`)**:
  - Andmebaasi kohene täitmine realistlike eestikeelsete uudiste, Unsplash HD-fotode ja testkommentaaridega.

---

## 🔑 Testkontod (Demo Credentials)

Sisselogimislehel [login.php](login.php) on kiirnupud testandmete sisestamiseks:

| Roll | Kasutajanimi (Login) | Parool | Õigused |
|---|---|---|---|
| **Peatoimetaja (Admin)** | `admin` | `admin123` | Täielik ligipääs Admin CMS-ile ja sisuhaldusele |
| **Lugeja (User)** | `user` | `user123` | Artikli kommenteerimine, järjehoidjad, profiil |

---

## 📂 Projekti kaustapuu

```text
newsportal/
├── admin/                     # Toimetuse CMS paneel
│   ├── categories.php         # Rubriikide lisamine/kustutamine
│   ├── comments.php           # Kommentaaride modereerimine
│   ├── footer.php             # Admin jalus
│   ├── header.php             # Admin navigeerimine ja sidebar
│   ├── index.php              # Analüütika ja statistika
│   ├── news-add.php           # Uue artikli koostamine
│   ├── news-delete.php        # Artikli kustutamine
│   ├── news-edit.php          # Artikli muutmine
│   ├── news.php               # Uudiste nimekiri ja filter
│   ├── seed.php               # Andmebaasi demo täitja
│   └── users.php              # Kasutajate rollide haldus
├── api/                       # Asünkroonsed REST JSON lõpp-punktid
│   ├── comments.php           # Kommentaaride lisamine/kustutamine
│   ├── reactions.php          # Reaktsioonide/meeldimiste salvestamine
│   └── search.php             # Reaalajas otsingu API
├── images/                    # Kohalikud meediafailid
├── includes/                  # Globaalsed komponendid
│   ├── footer.php             # Portaalijalus, uudiskiri, sotsiaalmeedia
│   └── header.php             # Navigatsioon, reaalajas vidinad, otsing
├── js/
│   └── main.js                # TTS, Dark/Light, Search, Bookmarks, AJAX
├── category.php               # Rubriigipõhine uudisvoog
├── db.php                     # Andmebaasi ühendus ja auto-migratsioon
├── index.php                  # Portaal pealeht (Hero + Grid + Sidebar)
├── login.php                  # Sisselogimine
├── logout.php                 # Väljalogimine
├── news.php                   # Üksiku uudise täisvaade
├── profile.php                # Kasutaja profiili haldus
├── register.php               # Kasutaja registreerimine
├── saved.php                  # Salvestatud artiklid (Järjehoidjad)
├── search.php                 # Uudiste otsinguleht
├── style.css                  # Portaal terviklik CSS disainisüsteem
└── README.md                  # Projekti dokumentatsioon
```

---

## 🚀 Paigaldusjuhend (Installation)

### Nõuded:
- **XAMPP** (Apache + MySQL / MariaDB + PHP 8.0+)
- Veebibrauser (Chrome, Firefox, Edge, Safari)

### Samm-sammuline seadistus:

1. **Kopeeri failid XAMPP veebikausta:**
   ```bash
   # Kopeeri projekt kausta:
   C:\xampp\htdocs\newsportal\
   ```

2. **Käivita XAMPP Control Panel:**
   - Käivita **Apache** (Port 80)
   - Käivita **MySQL** (Port 3306)

3. **Andmebaasi seadistamine:**
   - Loo phpMyAdminis (`http://localhost/phpmyadmin/`) andmebaas nimega `newsportal`.
   - `db.php` skript sisaldab **automaatset skeemi migratsiooni** ning loob ja uuendab vajalikud tabelid automaatselt esimesel lehekülastusel!

4. **Demo andmete genereerimine:**
   - Ava veebibrauseris: `http://localhost/newsportal/admin/seed.php`
   - Logi sisse adminina (`admin` / `admin123`) ja klõpsa **"Täida andmebaas kohe"**.

5. **Naudi portaali!**
   - Avaleht: [http://localhost/newsportal/](http://localhost/newsportal/)
   - Admin CMS: [http://localhost/newsportal/admin/](http://localhost/newsportal/admin/)

---

## 🔌 API lõpp-punktid (AJAX Endpoints)

| Meetod | URL | Kirjeldus |
|---|---|---|
| `GET` | `/api/search.php?q={query}` | Tagastab otsingusõnale vastavad uudised JSON kujul |
| `POST` | `/api/reactions.php` | Suurendab artikli reaktsiooni (`like`, `heart`, `insightful`, `fire`) |
| `POST` | `/api/comments.php` | Salvestab uue kommentaari reaalajas |
| `DELETE` | `/api/comments.php?id={id}` | Kustutab kommentaari (Admin õigustega) |

---

## 🎓 Projekti kontekst
- **Õppeasutus**: Ida-Virumaa Kutsehariduskeskus (IVKHK)
- **Kursus**: NPTV23 Agiilne projekt / Tarkvaraarendus
- **Autor**: Sergei Lobyshev õppeaine raames valminud täiustatud projekt.
