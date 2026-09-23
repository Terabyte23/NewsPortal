# 📖 NewsPortali Kasutusjuhend ja Dokumentatsioon (User Guide)

Käesolev dokumentatsioon kirjeldab põhjalikult **NewsPortal** veebiplatvormi kõiki võimalusi, kasutajarolle, funktsionaalsuseid ja haldustoiminguid. Juhend on mõeldud nii tavalugejatele, registreeritud kasutajatele kui ka toimetajatele ja peatoimetajatele (administraatoritele).

---

## Sisukord

1. [Süsteemi ülevaade ja tehniline arhitektuur](#1-süsteemi-ülevaade-ja-tehniline-arhitektuur)
2. [Kasutajarollid ja õiguste hierarhia](#2-kasutajarollid-ja-õiguste-hierarhia)
3. [Portaali avalik funktsionaalsus ja lugemiskogemus](#3-portaali-avalik-funktsionaalsus-ja-lugemiskogemus)
   - [Avaleht ja uudistevoog](#31-avaleht-ja-uudistevoog)
   - [Uudise lugemine ja interaktiivsed lisad](#32-uudise-lugemine-ja-interaktiivsed-lisad)
   - [Kiir- ja reaalajas otsing (Ctrl+K)](#33-kiir--ja-reaalajas-otsing-ctrlk)
   - [Kategooriate järgi filtreerimine](#34-kategooriate-järgi-filtreerimine)
   - [Reaktsioonid (Likes / Dislikes)](#35-reaktsioonid-likes--dislikes)
   - [Arutelu ja kommentaaride lisamine](#36-arutelu-ja-kommentaaride-lisamine)
   - [Järjehoidjad ja salvestatud lood (`saved.php`)](#37-järjehoidjad-ja-salvestatud-lood-savedphp)
   - [Kujunduse teema vahetus (Tume / Hele režiim)](#38-kujunduse-teema-vahetus-tume--hele-režiim)
4. [Kasutajakonto haldus](#4-kasutajakonto-haldus)
   - [Registreerumine (`register.php`)](#41-registreerumine-registerphp)
   - [Sisselogimine (`login.php`)](#42-sisselogimine-loginphp)
   - [Minu profiil ja andmete muutmine (`profile.php`)](#43-minu-profiil-ja-andmete-muutmine-profilephp)
   - [Turvaline väljalogimine (`logout.php`)](#44-turvaline-väljalogimine-logoutphp)
5. [Toimetuse ja administraatori halduspaneel (`/admin/`)](#5-toimetuse-ja-administraatori-halduspaneel-admin)
   - [Halduspaneelile ligipääs ja armatuurlaud](#51-halduspaneelile-ligipääs-ja-armatuurlaud)
   - [Uudiste lisamine, muutmine ja kustutamine](#52-uudiste-lisamine-muutmine-ja-kustutamine)
   - [Rubriikide / kategooriate haldamine](#53-rubriikide--kategooriate-haldamine)
   - [Kasutajate ja rollide juhtimine](#54-kasutajate-ja-rollide-juhtimine)
   - [Kommentaaride modereerimine ja kinnitusaknaga kustutamine](#55-kommentaaride-modereerimine-ja-kinnitusaknaga-kustutamine)
6. [Automaattestide ja visuaalse testija käivitamine](#6-automaattestide-ja-visuaalse-testija-käivitamine)
7. [Korduma kippuvad küsimused ja tõrkeotsing (FAQ)](#7-korduma-kippuvad-küsimused-ja-tõrkeotsing-faq)

---

## 1. Süsteemi ülevaade ja tehniline arhitektuur

NewsPortal on kaasaegne, turvaline ja mitmekülgne veebipõhine uudisteportaal. Süsteem on ehitatud puhtale PHP arhitektuurile koos MySQL andmebaasiga ning pakub reageerivat (responsive) kasutajaliidest, mis kohandub sujuvalt nii lauaarvutite, tahvlite kui ka nutitelefonidega.

### Tehnoloogiad:
- **Backend:** PHP 8.0+ (objekt-orienteeritud ja protseduuriline tugi, ettevalmistatud päringud `mysqli`).
- **Andmebaas:** MySQL / MariaDB (relatsiooniline mudel, kaskaadne seoste haldus).
- **Frontend:** Vanilla JavaScript (ES6+), kaasaegne CSS kohandatavate CSS-muutujatega (CSS Custom Properties).
- **Turvalisus:** CSRF-kaitsetõendid, Bcrypt parooliräsimine, XSS-kaitse (`htmlspecialchars`), sessioonihaldus.

---

## 2. Kasutajarollid ja õiguste hierarhia

Portaal toetab rollipõhist ligipääsuhaldust (RBAC - Role-Based Access Control):

| Roll | Tähis süsteemis | Õiguste ulatus |
| :--- | :--- | :--- |
| **Külaline (Guest)** | `null` | Uudiste lugemine, otsing, hääletamine (reaktsioonid), artiklite kuulamine (TTS), kommentaaride kirjutamine külalisena, lemmikute salvestamine kohalikku sessiooni. |
| **Lugeja (User)** | `user` | Kõik külalise õigused + isiklik konto, oma nime ja profiili haldus, parooli muutmine, kommenteerimine kinnitatud nimega. |
| **Toimetaja (Editor / Journalist)** | `editor` / `journalist` | Kõik lugeja õigused + ligipääs halduspaneelile `/admin/`, uute artiklite koostamine ja avaldamine, olemasolevate artiklite toimetamine. |
| **Peatoimetaja / Administraator (Admin)** | `admin` | Täielik kontroll portaali üle: artiklite lisamine, muutmine ja kustutamine, kategooriate haldus, kasutajate rollide muutmine, kommentaaride modereerimine ja kustutamine (nii halduspaneelis kui ka otse artiklite lehel). |

---

## 3. Portaali avalik funktsionaalsus ja lugemiskogemus

### 3.1 Avaleht ja uudistevoog
- **Jooksev uudisteriba (Breaking News Ticker):** Päises kuvatakse automaatselt viimaseid värskeid uudiseid koos kellaaja ja reaalajas ilmateatega.
- **Peauudis (Hero Section):** Esilehel tõstetakse suure visuaalse kaardina esile päeva kõige olulisem ja loetum sündmus.
- **Rubriigipõhised kaardid:** Uudised on jaotatud kategooriatesse (nt Tehnoloogia, Majandus, Teadus, Kultuur, Sport).
- **Reklaambänner:** Portaalis on interaktiivne reklaamiala koos helilülituse (Mute/Unmute) ja sulgemisvõimalusega.

### 3.2 Uudise lugemine ja interaktiivsed lisad
Iga artikli lehel (`news.php?id=X`) on lugejale kättesaadavad mitmed nutikad tööriistad:
1. **Lugemisaja prognoos:** Algoritm arvutab teksti mahu põhjal eeldatava lugemisaja (nt *3 min lugemist*).
2. **Vaatamiste loendur:** Iga külastus registreeritakse andmebaasis (👁️ *vaatamiste arv*).
3. **Kirjasuuruse reguleerimine:** Nupud **A-** ja **A+** võimaldavad muuta teksti suurust vastavalt lugeja eelistustele.
4. **Heliettekande vidin (AI Voice / TTS):** Nupule "Kuula artiklit" vajutades loeb veebibrauseri kõnesüntesaator artikli teksti ette eesti keeles.
5. **Jagamisnupud:** Kiirlingid loo jagamiseks sotsiaalvõrgustikes (Twitter/X, Telegram, Facebook) ning nupuke lingi lõikelauale kopeerimiseks.

### 3.3 Kiir- ja reaalajas otsing (Ctrl+K)
- Klõpsake päises luubi ikoonile või vajutage klahvikombinatsiooni **Ctrl + K** (Macil **Cmd + K**).
- Avanenud aknasse tippige otsitav märksõna (nt *tehisintellekt*, *majandus*).
- Otsingumootor teeb reaalajas asünkroonse AJAX-päringu (`api/search.php`) ja kuvab vasteid koheselt koos piltide ja kuupäevadega.

### 3.4 Kategooriate järgi filtreerimine
- Päisemenüüs on välja toodud peamised rubriigid.
- Klõpsates rubriigil (nt *Tehnoloogia*), suunatakse kasutaja lehele `category.php?id=X`, kus kuvatakse ainult vastavasse teemasse kuuluvaid artikleid.

### 3.5 Reaktsioonid (Likes / Dislikes)
Artikli lõpus saab lugeja ühe klõpsuga avaldada oma emotsiooni:
- 👍 **Meeldib (Like)**
- ❤️ **Armastan (Heart)**
- 💡 **Geniaalne (Insightful)**
- 🔥 **Kuum teema (Fire)**

Reaktsioonid salvestatakse asünkroonselt andmebaasi (`api/reactions.php`) ilma kogu veebilehte uuesti laadimata.

### 3.6 Arutelu ja kommentaaride lisamine
Artikli allosas asub diskussiooniala:
- **Külalisena kommenteerides:** Saate sisestada oma nime (või jätta tühjaks, mil nimeks määratakse "Lugeja") ning arvamuse teksti.
- **Registreeritud kasutajana:** Nimi võetakse automaatselt teie profiilist ja kommentaar seotakse teie kasutajakontoga.
- Kommentaarid kuvatakse kronoloogilises järjekorras koos suhtelise ajamääratlusega (nt *Just praegu*, *5 min tagasi*, *2 päeva tagasi*).

### 3.7 Järjehoidjad ja salvestatud lood (`saved.php`)
- Iga uudisekaardi või artikli juures on järjehoidja ikoon.
- Sellele vajutades salvestatakse artikkel lugeja isiklikku lugemisnimekirja.
- Päisest lingile **Järjehoidjad** vajutades avaneb leht `saved.php`, kus on koondatud kõik salvestatud lood. Loo saab nimekirjast igal ajal eemaldada.

### 3.8 Kujunduse teema vahetus (Tume / Hele režiim)
- Päises asub päikese/kuu ikooniga teemalüliti (**Theme Toggle**).
- Portaali saab kasutada nii kaasaegses silmasõbralikus tumedas režiimis (*Dark Mode*) kui ka selges heledas režiimis (*Light Mode*).
- Teema valik salvestatakse brauseri kohalikku mällu (`localStorage`), mistõttu püsib see ka lehe uuesti avamisel.

---

## 4. Kasutajakonto haldus

### 4.1 Registreerumine (`register.php`)
1. Klõpsake päises nupule **"Registreeru"**.
2. Täitke kohustuslikud väljad:
   - **Täisnimi:** Teie ees- ja perekonnanimi.
   - **E-post:** Kehtiv e-posti aadress.
   - **Kasutajatunnus:** Unikaalne hüüdnimi sisselogimiseks.
   - **Parool:** Turvaline parool (soovitatav vähemalt 8 tähemärki koos numbritega).
3. Pärast edukat registreerumist suunatakse teid sisselogimislehele.

### 4.2 Sisselogimine (`login.php`)
1. Klõpsake nupule **"Logi sisse"**.
2. Sisestage oma kasutajatunnus ja parool.
3. Süsteem kontrollib volitusi andmebaasis turvalise Bcrypt algoritmi abil.
4. Eduka sisselogimise järel kuvatakse päises teie profiili avatar ja avaneb rippmenüü.

### 4.3 Minu profiil ja andmete muutmine (`profile.php`)
Profiililehel saab kasutaja:
- Vaadata oma kasutajarolli (Lugeja, Toimetaja, Administraator) ja konto loomise kuupäeva.
- Uuendada oma nime, e-posti aadressi, telefoninumbrit ja ametinimetust.
- Soovi korral vahetada oma parooli uue vastu.

### 4.4 Turvaline väljalogimine (`logout.php`)
- Rippmenüüst valik **"Logi välja"** tühjendab turvaliselt serveripoolse sessiooni (`$_SESSION = []`, `session_destroy()`) ja suunab kasutaja tagasi avalehele.

---

## 5. Toimetuse ja administraatori halduspaneel (`/admin/`)

Administraatori halduspaneel on kaitstud moodul, kuhu pääsevad ligi ainult kasutajad rolliga `admin`, `editor` või `journalist`. Volitamata külastajad suunatakse automaatselt autentimislehele.

### 5.1 Halduspaneelile ligipääs ja armatuurlaud
- Halduspaneeli aadress on `http://localhost/NewsPortal/admin/index.php`.
- Armatuurlaual kuvatakse üldstatistikat: artiklite koguarv, kommentaaride hulk, kategooriad ja kasutajad.

### 5.2 Uudiste lisamine, muutmine ja kustutamine
- **Uue uudise lisamine (`admin/news-add.php`):**
  - Sisestage pealkiri ja sisu.
  - Valige rippmenüüst sobiv teemakategooria.
  - Määrake pildi URL (või jätke tühjaks, mil süsteem genereerib automaatselt sobiva Unsplash teemapildi).
  - Klõpsake **"Avalda uudis"**.
- **Uudise muutmine (`admin/news-edit.php?id=X`):**
  - Avab olemasoleva artikli andmed vormis, võimaldades parandada teksti, pealkirja või vahetada rubriiki.
- **Uudise kustutamine (`admin/news-delete.php?id=X`):**
  - Kustutab artikli andmebaasist. Süsteem teostab kaskaadse puhastuse, kustutades automaatselt ka kõik kõnealuse artikli kommentaarid.

### 5.3 Rubriikide / kategooriate haldamine (`admin/categories.php`)
- Võimaldab luua uusi rubriike (nt *Autod*, *Kinnisvara*, *Tervis*).
- Olemasolevaid rubriike saab ümber nimetada või eemaldada.

### 5.4 Kasutajate ja rollide juhtimine (`admin/users.php`)
- Peatoimetaja näeb kõiki registreeritud kasutajaid.
- Administraator saab muuta kasutaja staatust:
  - Tavalugeja ülendamine toimetajaks (`user` ➔ `editor`).
  - Toimetaja määramine administraatoriks (`editor` ➔ `admin`).

### 5.5 Kommentaaride modereerimine ja kinnitusaknaga kustutamine
Süsteem pakub kaheastmelist kommentaaride modereerimist:
1. **Koondvaade halduspaneelis (`admin/comments.php`):**
   - Nimekiri kõigist portaali kommentaaridest koos autori nime, kuupäeva ja seotud uudise pealkirjaga.
   - Sobimatud kommentaarid saab ühe klõpsuga eemaldada.
2. **Otsekustutamine artikli lehel (`news.php`) koos kohandatud kinnitusaknaga:**
   - Kui administraator loeb uudist tavavaates, kuvatakse iga kommentaari juures punane prügikastiikooniga nupp **"Kustuta"**.
   - Nupule vajutamisel avaneb kaasaegne kohandatud kinnitusaken (**Custom Modal Dialog**):
     - Pealkiri: *"Kommentaari kustutamine"*
     - Sisu: *"Kas soovid selle kommentaari kindlasti kustutada?"*
     - Valikud: **"Tühista"** (sulgeb akna ilma muudatusteta) või **"Jah, kustuta"** (kustutab kommentaari turvalise CSRF-tõendi kontrolliga).
   - Kustutamisel kuvatakse roheline kinnitusteade: *"✅ Kommentaar edukalt kustutatud!"*.

---

## 6. Automaattestide ja visuaalse testija käivitamine

NewsPortali kvaliteedi tagamiseks on loodud terviklik testimiskeskkond:

### 6.1 Konsoolitestid (PHPUnit TestDox)
Käivitage käsurealt (CMD või PowerShell):
```powershell
.\test.bat
```
- Käivitab automaatselt **kõik 54 testi** (12 laiahaardelist otsast lõpuni E2E testi ja 42 ühik-/integratsioonitesti).
- Tänu **TestDox** toele kuvatakse ekraanile iga testi kohta täpne inimloetav selgitus, mida kontrolliti ja milline oli tulemus.

### 6.2 Visuaalne E2E testija brauseris (Selenium-stiilis)
Käivitage käsurealt:
```powershell
.\run-visual-tests.bat
```
*või:*
```powershell
.\test.bat --visual
```
- Avab automaatselt Google Chrome'i aadressil `http://localhost/NewsPortal/tests/visual_runner.php`.
- Testib 10 terviklikku stsenaariumi, mis katavad 100% kõiki funktsionaalsusi, mida kontrollivad ka ühiktestid: otsing, rubriigid, artikli vaatamine, lugemisaeg, kommentaarid, reaktsioonid, järjehoidjad, kinnitusaknad (Custom Modal), registreerimine, autentimine ja teemavahetus!
- Näete kaheosalist liidest:
  - **Vasakul paneelil:** 10 teststsenaariumi, reaalajas tegevuste logi ja kiiruse regulaator (*Slow-Motion*).
  - **Paremal paneelil:** Reaalne töötav veebiportaal koos liikuva punase Seleniumi kursoriga, mis reaalajas vajutab nuppudele, trükib otsinguribale ja kommentaariväljadele teksti ning avab kinnitusaknaid!

### 6.3 Standarditele vastavus
- Süsteemi funktsionaalsed ja mittefunktsionaalsed nõuded on spetsifitseeritud vastavalt standardile **ISO/IEC/IEEE 29148:2018** dokumendis [`ISO_29148_REQUIREMENTS.md`](file:///c:/xampp/htdocs/NewsPortal/ISO_29148_REQUIREMENTS.md).

---

## 7. Korduma kippuvad küsimused ja tõrkeotsing (FAQ)

### K: Kuidas taastada ununenud parooli?
**V:** Arenduskeskkonnas saab administraator määrata kasutajale uue parooli faili `admin/users.php` kaudu või profiili vaates. Tulevikus lisatakse automaatne e-kirjaga parooli taastamise funktsioon.

### K: Veebileht teatab "Andmebaasi ühenduse viga". Mida teha?
**V:** Veenduge, et XAMPP juhtpaneelil (**XAMPP Control Panel**) on käivitatud nii **Apache** kui ka **MySQL** teenused (mõlemad rohelised).

### K: Kuidas lülitada sisse heledat režiimi?
**V:** Klõpsake päises paremal asuvale kuu/päikese ikoonile. Režiim vahetub koheselt ja jääb meelde ka järgmisel külastusel.

---
*Dokumentatsiooni versioon: 2.0 (2026)*  
*Koostaja: NewsPortal Arendusmeeskond*
