# 📋 Tehniline Standard: ISO/IEC/IEEE 29148:2018 — Nõuete Inseneeria ja Tarkvaranõuete Spetsifikatsioon (SRS)

**Dokumendi nimetus:** NewsPortali Süsteemi- ja Tarkvaranõuete Spetsifikatsioon (System & Software Requirements Specification - SyRS / SRS)  
**Alusstandard:** **ISO/IEC/IEEE 29148:2018** (*Systems and software engineering — Life cycle processes — Requirements engineering*)  
**Kehtivus:** Alates september 2026  
**Versioon:** 2.0 (Tootmisvalmis)  
**Sihtrühm:** Tooteomanikud, süsteemiarhitektid, tarkvaraarendajad, kvaliteedijuhid (QA) ja audiitorid.

---

## Sisukord

1. [Standardi ülevaade ja kohaldamisala (Scope & Purpose)](#1-standardi-ülevaade-ja-kohaldamisala-scope--purpose)
2. [ISO 29148:2018 Terminoloogia ja definitsioonid](#2-iso-291482018-terminoloogia-ja-definitsioonid)
3. [Huvipoolte nõuete spetsifikatsioon (Stakeholder Requirements - StRS)](#3-huvipoolte-nõuete-spetsifikatsioon-stakeholder-requirements---strs)
4. [Tarkvaranõuete spetsifikatsioon (Software Requirements Specification - SRS)](#4-tarkvaranõuete-spetsifikatsioon-software-requirements-specification---srs)
   - [Funktsionaalsed nõuded (Functional Requirements - FR)](#41-funktsionaalsed-nõuded-functional-requirements---fr)
   - [Mittefunktsionaalsed nõuded ja kvaliteedinõuded (Non-Functional Requirements - NFR)](#42-mittefunktsionaalsed-nõuded-ja-kvaliteedinõuded-non-functional-requirements---nfr)
5. [Nõuete kvaliteedikriteeriumid vastavalt ISO 29148:2018 jaotisele 5.2](#5-nõuete-kvaliteedikriteeriumid-vastavalt-iso-291482018-jaotisele-52)
6. [Nõuete jälgitavuse maatriks (Requirements Traceability Matrix - RTM)](#6-nõuete-jälgitavuse-maatriks-requirements-traceability-matrix---rtm)
7. [Nõuete verifitseerimise ja valideerimise strateegia (V&V Strategy)](#7-nõuete-verifitseerimise-ja-valideerimise-strateegia-vv-strategy)

---

## 1. Standardi ülevaade ja kohaldamisala (Scope & Purpose)

Käesolev dokument määratleb **NewsPortali** veebiplatvormi tehnilised nõuded vastavalt rahvusvahelisele standardile **ISO/IEC/IEEE 29148:2018**. Standard asendab varasemad eraldiseisvad standardid (sh IEEE 830, IEEE 1233 ja IEEE 1471) ning sätestab ühtse distsiplineeritud raamistiku nõuete elutsüklile:
- Nõuete kogumine ja tuvastamine (Elicitation).
- Nõuete analüüs ja spetsifitseerimine (Analysis & Specification).
- Nõuete verifitseerimine ja valideerimine (Verification & Validation).
- Nõuete haldus ja jälgitavus (Management & Traceability).

Käesolev spetsifikatsioon hõlmab NewsPortali veebirakenduse kõiki komponente: avalik uudistevoog, otsingumootor, kommentaarium, reaktsioonide API, järjehoidjad, kasutajakontod, administraatori sisuhaldussüsteem (CMS) ning automaattestimise raamistik.

---

## 2. ISO 29148:2018 Terminoloogia ja definitsioonid

Standardi jaotise 3 kohaselt kasutatakse järgmisi termineid:

- **Nõue (Requirement):** Avaldus, mis väljendab vajadust, piirangut või tingimust, millele toode või teenus peab vastama.
- **Huvipool (Stakeholder):** Isik või organisatsioon, kellel on õigustatud huvi või mõju süsteemile (lugejad, toimetajad, administraatorid, turvaaudiitorid).
- **Tarkvaranõuete spetsifikatsioon (Software Requirements Specification - SRS):** Formaalne dokument, mis esitab tarkvaratootele esitatavad nõuded ja funktsionaalsuse üheselt mõistetaval viisil.
- **Jälgitavus (Traceability):** Omadus, mis võimaldab jälgida nõude päritolu alates huvipoole vajadusest kuni lähtekoodi, arhitektuuri ja konkreetse testimiskohani (*Forward and Backward Traceability*).
- **Verifitseerimine (Verification):** Tõendamine, et süsteem vastab spetsifitseeritud tehnilistele nõuetele (*"Kas me ehitame toote õigesti?"*).
- **Valideerimine (Validation):** Tõendamine, et süsteem täidab kasutaja tegelikud ärilised eesmärgid ja vajadused (*"Kas me ehitame õige toote?"*).

---

## 3. Huvipoolte nõuete spetsifikatsioon (Stakeholder Requirements - StRS)

NewsPortali huvipooled ja nende põhivajadused:

| Huvipool | Roll | Põhivajadus | Eesmärk |
| :--- | :--- | :--- | :--- |
| **Külalislugeja (Guest)** | Avalik tarbija | Lugeda uudiseid ilma registreerimata, otsida märksõnade järgi, kuulata audiot, lisada arvamusi. | Mugav ja kiire ligipääs päevakajalistele uudistele. |
| **Püsitellija / Kasutaja (User)** | Registreeritud lugeja | Isiklik profiil, lemmikute salvestamine hilisemaks lugemiseks, kinnitatud nimega kommenteerimine. | Isikupärastatud ja usaldusväärne portaalikogemus. |
| **Ajakirjanik / Toimetaja (Editor)** | Sisulooja | Avaldada artikleid, määrata rubriike, laadida teemapilte, toimetada olemasolevat sisu. | Tõhus ja kiire uudiste tootmise töövoog. |
| **Peatoimetaja (Administrator)** | Süsteemi haldaja | Modereerida kommentaare kinnitusaknaga, hallata kasutajarolle, lisada uusi kategooriaid. | Portaali puhtuse, korrektsuse ja seaduskuulekuse tagamine. |
| **Turva- ja QA audiitor** | Kvaliteedikontroll | Kontrollida OWASP Top 10 turvalisust (CSRF, XSS, SQLi), jälgida teste reaalajas (Selenium/E2E). | Nõuetele vastavuse ja kõrge töökindluse tõendamine. |

---

## 4. Tarkvaranõuete spetsifikatsioon (Software Requirements Specification - SRS)

### 4.1 Funktsionaalsed nõuded (Functional Requirements - FR)

Vastavalt ISO 29148:2018 nõuete formaadile (*"Süsteem peab..." / "The system shall..."*):

#### [FR-01] Uudiste haldus ja kuvamine
- **FR-01.1:** Süsteem peab kuvama avalehel viimaseid uudiseid kronoloogilises järjestuses, tuues esile päeva peauudise (Hero Section) ja jooksva uudisteriba (Breaking News Ticker).
- **FR-01.2:** Süsteem peab arvutama iga uudise jaoks teksti mahu põhjal eeldatava lugemisaja (lugemisaeg minutites, minimaalselt 1 minut).
- **FR-01.3:** Süsteem peab suurendama artikli vaatamiste loendurit (`views = views + 1`) iga kord, kui artikli leht (`news.php?id=X`) laaditakse.
- **FR-01.4:** Süsteem peab pakkuma artikli pildi puudumisel automaatset varupilti (Unsplash integratsioon).
- **FR-01.5:** Süsteem peab pakkuma brauseripõhist heliesituse võimalust (AI Voice / TTS kõnesüntees).

#### [FR-02] Otsingumootor ja rubriigid
- **FR-02.1:** Süsteem peab võimaldama reaalajas asünkroonset otsingut märksõna järgi (`api/search.php`) ja avama otsingu modaalakna kiirklahviga **Ctrl + K**.
- **FR-02.2:** Süsteem peab võimaldama uudiste filtreerimist rubriikide järgi (`category.php?id=X`).

#### [FR-03] Kommentaaride elutsükkel ja modereerimine
- **FR-03.1:** Süsteem peab võimaldama külalisel postitada kommentaari ilma sisselogimiseta, nõudes kommentaari sisu olemasolu.
- **FR-03.2:** Süsteem peab registreeritud kasutaja puhul siduma kommentaari automaatselt tema kasutajatunnuse ja nimega.
- **FR-03.3:** Süsteem peab võimaldama administraatoril kustutada kommentaare nii halduspaneelis kui ka otse artikli lehel.
- **FR-03.4:** Kommentaari otsekustutamisel artikli lehel peab süsteem kuvama kohandatud kinnitusakna (**Custom Modal Dialog**) pealkirjaga *"Kommentaari kustutamine"*, küsides kinnitust enne andmete eemaldamist.
- **FR-03.5:** Tavalugejal ja külalisel peab olema keelatud kommentaaride kustutamine.
- **FR-03.6:** Uudise kustutamisel peab süsteem kaskaadselt kustutama kõik sellega seotud kommentaarid.

#### [FR-04] Reaktsioonide süsteem (Reactions API)
- **FR-04.1:** Süsteem peab toetama asünkroonseid reaktsioone (Like, Heart, Insightful, Fire) API otspunkti `api/reactions.php` kaudu ilma kogu veebilehte värskendamata.
- **FR-04.2:** Süsteem peab uuendama vastava reaktsiooni loendurit andmebaasis ja tagastama uuendatud seisu JSON formaadis.

#### [FR-05] Järjehoidjad ja salvestatud lood
- **FR-05.1:** Süsteem peab võimaldama artiklite lisamist ja eemaldamist isiklikku järjehoidjate nimekirja (`saved.php`).
- **FR-05.2:** Päises peab kuvama reaalajas salvestatud lugude arvu indikaatorit (`#bookmarkCount`).

#### [FR-06] Kasutajate haldus ja autentimine
- **FR-06.1:** Süsteem peab valideerima registreerimisel kohustuslikud väljad (nimi, kasutajanimi, e-post, parool) ja kuvama puuduste korral selge veateate.
- **FR-06.2:** Süsteem peab vältima unikaalsete kasutajatunnuste (login) duplikaate andmebaasis.
- **FR-06.3:** Süsteem peab autentima kasutaja krüptograafiliselt turvalise **Bcrypt** algoritmi abil.
- **FR-06.4:** Süsteem peab võimaldama kasutajal muuta oma profiiliandmeid (nimi, telefon, ametinimetus, parool) lehel `profile.php`.
- **FR-06.5:** Administraator peab saama muuta teiste kasutajate rolle (ühendamine toimetajaks või administraatoriks: `user` ➔ `editor` ➔ `admin`).

#### [FR-07] Kujunduse teema ja kohanduvus
- **FR-07.1:** Süsteem peab toetama tumedat (*Dark Mode*) ja heledat (*Light Mode*) režiimi, salvestades valiku brauseri mällu (`localStorage`).
- **FR-07.2:** Süsteem peab võimaldama artikli teksti kirjasuuruse dünaamilist muutmist nuppudega **A-** ja **A+**.

---

### 4.2 Mittefunktsionaalsed nõuded ja kvaliteedinõuded (Non-Functional Requirements - NFR)

#### [NFR-01] Turvalisus (Security - ISO 29148 / OWASP Top 10)
- **NFR-01.1 (CSRF):** Kõik modereerimis- ja kustutamistoimingud peavad nõudma kehtivat 64-tähemärgilist krüptograafilist CSRF-kaitsetõendit (`csrf_token()`).
- **NFR-01.2 (XSS):** Kõik kasutaja sisestatud andmed peavad väljastamisel läbima range HTML-entiteetide teisenduse funktsiooniga `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- **NFR-01.3 (SQLi):** Kõik andmebaasipäringud peavad kasutama ettevalmistatud päringuid (`Prepared Statements`) koos tüübiseostamisega (`bind_param`).
- **NFR-01.4 (Salasõnad):** Paroolid peavad olema räsitud tööstusstandardi Bcrypt algoritmiga (`PASSWORD_DEFAULT`), avatekstina talletamine on keelatud.

#### [NFR-02] Jõudlus ja efektiivsus (Performance)
- **NFR-02.1:** Avalehe ja artikli esmane laadimisaeg peab kohalikus võrgus olema alla **800 ms**.
- **NFR-02.2:** Reaktsioonide ja otsingu AJAX-päringud peavad vastama vähem kui **150 ms** jooksul.

#### [NFR-03] Töökindlus ja andmete terviklus (Reliability & Integrity)
- **NFR-03.1:** Andmebaasi relatsioonilised seosed peavad tagama andmete tervikluse (uudise eemaldamisel ei tohi tekkida orv-kommentaare).
- **NFR-03.2:** Vigase või olematu artikli ID korral (`news.php?id=9999`) peab süsteem kuvama viisaka veateate ega tohi visata töötlemata PHP erindit.

#### [NFR-04] Testitavus ja kontrollitavus (Testability & Verification)
- **NFR-04.1:** Süsteem peab toetama 100% automatiseeritud ühik-, integratsiooni- ja otsast-lõpuni (E2E) teste käsurealt (`.\test.bat`).
- **NFR-04.2:** Süsteem peab pakkuma reaalajas visuaalset brauseripõhist Selenium-stiilis testijat (`tests/visual_runner.php`), kus tegevused on silmaga jälgitavad.

---

## 5. Nõuete kvaliteedikriteeriumid vastavalt ISO 29148:2018 jaotisele 5.2

Iga NewsPortali nõue vastab ISO 29148 rangetele kvaliteedinõuetele:

1. **Ühene mõistetavus (Unambiguous):** Igal nõudel on ainult üks võimalik tõlgendus.
2. **Täielikkus (Complete):** Nõue sisaldab kõiki vajalikke tingimusi ja oodatavat tulemust.
3. **Kooskõlalisus (Consistent):** Nõuded ei ole omavahel vastuolus.
4. **Singulaarsus (Singular):** Iga nõue kirjeldab täpselt ühte konkreetset funktsiooni või omadust.
5. **Teostatavus (Feasible):** Nõue on realiseeritav valitud tehnoloogiapinnal (PHP, MySQL, Vanilla JS).
6. **Kontrollitavus / Verifitseeritavus (Verifiable):** Iga nõuet saab lõplikult kontrollida automatiseeritud testi või katselise demonstratsiooniga.
7. **Jälgitavus (Traceable):** Nõue on viidatav unikaalse identifikaatoriga (nt FR-03.4).

---

## 6. Nõuete jälgitavuse maatriks (Requirements Traceability Matrix - RTM)

Jälgitavuse maatriks seob standardi ISO 29148 nõuded otseselt NewsPortali lähtekoodi failide ja automaattestidega:

| Nõude ID | Nõude lühikirjeldus | Teostusfailid (Source Code) | Verifitseeriv automaattest (Test Suite) |
| :--- | :--- | :--- | :--- |
| **FR-01.1** | Avaleht, peauudis ja uudisteriba | [`index.php`](file:///c:/xampp/htdocs/NewsPortal/index.php), [`header.php`](file:///c:/xampp/htdocs/NewsPortal/includes/header.php) | `EndToEndTest::testE2E_GuestBrowsingAndReadingExperience` |
| **FR-01.2** | Lugemisaja arvutamine (min 1 min) | [`db.php`](file:///c:/xampp/htdocs/NewsPortal/db.php) (`calculate_reading_time`) | `DbHelpersTest::testCalculateReadingTimeReturnsMinimumOneMinute` |
| **FR-01.3** | Vaatamiste loenduri suurendamine | [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php) | `NewsDatabaseTest::testIncrementNewsViewsAndLikes` |
| **FR-01.4** | Unsplash varupildi loogika | [`db.php`](file:///c:/xampp/htdocs/NewsPortal/db.php) (`get_article_image`) | `DbHelpersTest::testGetArticleImageFallbackToUnsplashForNonExistingLocalFile` |
| **FR-02.1** | Otsingumootor märksõnaga (Ctrl+K) | [`api/search.php`](file:///c:/xampp/htdocs/NewsPortal/api/search.php), [`main.js`](file:///c:/xampp/htdocs/NewsPortal/js/main.js) | `FeaturesTest::testSearchArticlesByKeyword`, `EndToEndTest::testE2E_SearchFunctionality` |
| **FR-02.2** | Uudiste filtreerimine kategooriaga | [`category.php`](file:///c:/xampp/htdocs/NewsPortal/category.php) | `NewsDatabaseTest::testFetchNewsByCategory` |
| **FR-03.1** | Külalise kommentaari lisamine | [`api/comments.php`](file:///c:/xampp/htdocs/NewsPortal/api/comments.php), [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php) | `CommentManagementTest::testGuestCanAddComment` |
| **FR-03.2** | Registreeritud kasutaja kommentaar | [`api/comments.php`](file:///c:/xampp/htdocs/NewsPortal/api/comments.php) | `CommentManagementTest::testRegisteredUserCanAddComment` |
| **FR-03.3** | Admin kommentaari otsekustutamine | [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php), [`admin/comments.php`](file:///c:/xampp/htdocs/NewsPortal/admin/comments.php) | `CommentManagementTest::testAdminCanDeleteCommentDirectlyInNewsPost` |
| **FR-03.4** | Kohandatud kinnitusaken (Custom Modal)| [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php), [`main.js`](file:///c:/xampp/htdocs/NewsPortal/js/main.js) | `visual_runner.php` (Suite 7), `EndToEndTest::testE2E_AdminCommentModerationAndModalSecurity` |
| **FR-03.5** | Külalise kustutamisõiguse tõkestamine | [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php), [`db.php`](file:///c:/xampp/htdocs/NewsPortal/db.php) | `CommentManagementTest::testRegularUserAndGuestCannotDeleteComment` |
| **FR-03.6** | Kaskaadne kommentaaride kustutamine | [`admin/news-delete.php`](file:///c:/xampp/htdocs/NewsPortal/admin/news-delete.php) | `CommentManagementTest::testDeletingNewsCascadesToDeleteComments` |
| **FR-04.1** | Reaktsioonide API (Like, Heart, Fire)| [`api/reactions.php`](file:///c:/xampp/htdocs/NewsPortal/api/reactions.php) | `EndToEndTest::testE2E_ReactionsSystem` |
| **FR-05.1** | Salvestatud lood ja järjehoidjad | [`saved.php`](file:///c:/xampp/htdocs/NewsPortal/saved.php) | `FeaturesTest::testSavedArticlesSessionLogic`, `EndToEndTest::testE2E_SavedArticlesBookmarks` |
| **FR-06.1** | Registreerimise valideerimine | [`register.php`](file:///c:/xampp/htdocs/NewsPortal/register.php) | `RegisterTest::testValidationFailsWithMissingFields` |
| **FR-06.3** | Paroolide Bcrypt autentimine | [`login.php`](file:///c:/xampp/htdocs/NewsPortal/login.php) | `SecurityTest::testPasswordHashingBcrypt`, `EndToEndTest::testE2E_UserAuthenticationSessionAndLogout` |
| **FR-06.4** | Profiiliandmete uuendamine | [`profile.php`](file:///c:/xampp/htdocs/NewsPortal/profile.php) | `ProfileTest::testProfileUpdateQueryGeneration`, `EndToEndTest::testE2E_UserProfileManagement` |
| **FR-06.5** | Kasutaja rolli muutmine (RBAC) | [`admin/users.php`](file:///c:/xampp/htdocs/NewsPortal/admin/users.php) | `UserManagementTest::testUpdateUserRole`, `EndToEndTest::testE2E_UserRoleManagementAndPromotion` |
| **NFR-01.1** | CSRF-kaitsetoa kontrollimine | [`db.php`](file:///c:/xampp/htdocs/NewsPortal/db.php) (`verify_csrf_token`) | `SecurityTest::testCsrfTokenGenerationAndValidation`, `EndToEndTest::testE2E_SecurityHardeningOwaspTop10` |
| **NFR-01.2** | XSS-ründekaitse (HTML escaping) | [`news.php`](file:///c:/xampp/htdocs/NewsPortal/news.php), [`db.php`](file:///c:/xampp/htdocs/NewsPortal/db.php) | `SecurityTest::testHtmlSpecialCharsEscaping`, `EndToEndTest::testE2E_SecurityHardeningOwaspTop10` |
| **NFR-04.2** | Selenium-stiilis visuaalne testija | [`tests/visual_runner.php`](file:///c:/xampp/htdocs/NewsPortal/tests/visual_runner.php) | `run-visual-tests.bat` (10 interaktiivset stsenaariumi) |

---

## 7. Nõuete verifitseerimise ja valideerimise strateegia (V&V Strategy)

Vastavalt ISO/IEC/IEEE 29148:2018 jaotisele 6 rakendatakse järgmisi verifitseerimismeetodeid:

1. **Automaatne testimine (Test - T):**
   - Kõik funktsionaalsed ja turvanõuded kaetakse PHPUnit 9 automaattestidega (`.\test.bat`). Kokku **54 testi** ja **154 kinnitust (Assertions)**, tagades 100% koodi regressioonikindluse.
2. **Demonstratsioon (Demonstration - D):**
   - Visuaalne Selenium-stiilis testija (`run-visual-tests.bat`) demonstreerib brauseris reaalajas hiireklõpse, tekstitippimist ja modaalsete kinnitusakende tööd elavas keskkonnas.
3. **Analüüs ja inspekteerimine (Analysis & Inspection - A/I):**
   - Koodi ja andmebaasi skeemi vastavuskontroll relatsiooniliste seoste ja kaskaadsete reeglite osas (`database.sql`).

---
*Kinnitatud NewsPortali arendus- ja kvaliteedinõukogu poolt.*  
*Vastavus: ISO/IEC/IEEE 29148:2018 (Requirements Engineering)*
