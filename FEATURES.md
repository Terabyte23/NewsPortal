# 🚀 NewsPortali 3 peamist funktsiooni ja arendusettepanekut platvormi täiustamiseks

Käesolevas dokumendis on põhjalikult lahti kirjutatud 3 strateegilist ja kaasaegset funktsiooni (Features), mis tõstaksid NewsPortali kasutajakogemuse, lugejaskonna kaasatuse (Engagement) ja tehnilise taseme uuele kõrgusele.

---

## 1. Reaalajas veebiteavitused ja teemapõhised tellimused (Web Push Notifications & Topic Subscriptions)

### Kirjeldus:
Võimalus lugejatel tellida veebibrauseri kaudu koheseid teavitusi (**Web Push Notifications**) erakorraliste uudiste (*Breaking News*) või neid huvitavate konkreetsete rubriikide (nt *Tehnoloogia*, *Majandus*, *Teadus*) kohta. Lisaks saavad registreeritud kasutajad tellida teavitusi, kui keegi vastab nende kommentaarile.

### Äriline ja kasutajakogemuse väärtus (Kasu ja mõju):
1. **Lugejaskonna tagasitoomine (Retention & DAU):** Veebiteavitused toovad lugejaid tagasi portaali ka siis, kui veebileht on suletud. Statistika kohaselt tõstab Web Push tellijate korduvkülastuste sagedust 30–50%.
2. **Kriitilise info operatiivne edastamine:** Erakorralised suursündmused (nt loodusõnnetused, tähtsad valitsuse otsused) jõuavad lugejateni reaalajas sekunditega.
3. **Kogukonna kaasatus:** Kui lugeja saab teavituse *"Kasutaja Artur vastas sinu kommentaarile artiklis..."*, naaseb ta koheselt diskussiooni jätkama, kasvatades lehe seansiaega ja lehevaatamisi.

### Tehniline arhitektuur ja teostuse kava:
- **Kliendipool:** Service Worker (`sw.js`) ja standardne W3C Push API / Notification API integratsioon.
- **Serveripool:** VAPID (Voluntary Application Server Identification) krüptovõtmete paar ja PHP teek `web-push-libs/web-push-php`.
- **Andmebaas:** Uus tabel `push_subscriptions` (`id`, `user_id`, `endpoint`, `p256dh_key`, `auth_token`, `topics`, `created_at`).
- **Haldusliides:** Toimetajale artikli avaldamisel valikuvõimalus: *"Saada teavitus selle teema tellijatele"*.

---

## 2. Tehisintellektil (AI) põhinev lühikokkuvõtja ja semantilised sisu soovitused (AI News Digest & Smart Recommendations)

### Kirjeldus:
Iga pikema uudiseartikli juurde genereeritakse automaatselt tehisintellekti abil 3–4 täpipunktiga lühikokkuvõte (**"TL;DR / Loe 20 sekundiga"**), mis kuvatakse artikli päises eraldi kaardina. Lisaks võetakse kasutusele nutikas soovitusmootor, mis analüüsib loetud artikli sisu ja pakub välja semantiliselt seotud lugusid ja taustamaterjale, mitte ainult suvalisi sama rubriigi artikleid.

### Äriline ja kasutajakogemuse väärtus (Kasu ja mõju):
1. **Kohanemine kiire elutempoga:** Tänapäeva lugejad hindavad aega. Võimalus haarata artikli iva paari sekundiga parandab oluliselt mobiilsete lugejate rahulolu ja vähendab lehelt lahkumise määra (Bounce Rate).
2. **Seansi kestuse ja lehevaatamiste kasv:** Semantiliselt täpsed soovitused (nt artiklile *"Eesti superarvuti"* soovitatakse *"Kvanttehnoloogia läbimurre"*, mitte suvalist spordiuudist) tõstavad klikkimise määra (CTR) 25–40%.
3. **Toimetuse produktiivsuse tõus:** AI aitab toimetajal automaatselt genereerida artiklile teemakohaseid silte/märksõnu (Tags) ja pealkirja alternatiive, hoides kokku väärtuslikku tööaega.

### Tehniline arhitektuur ja teostuse kava:
- **AI Integratsioon:** Kerge ja kuluefektiivne LLM API (Google Gemini API või OpenAI GPT-4o-mini) integratsioon artikli avaldamise ja toimetamise etapis (`admin/news-add.php`, `admin/news-edit.php`).
- **Vahemällu talletamine (Caching):** Genereeritud kokkuvõte salvestatakse andmebaasi veerus `news.ai_summary` ja sildid tabelis `news_tags`. Veebilehe laadimisel ei tehta korduvaid välispäringuid, tagades kiire lehe avanemise (< 0.5s).
- **Frontend UI:** Kokkuvõtte kaart stiilse gradient-äärisega ja laiendatava *"Loe täismahus"* nupuga.

---

## 3. Otseblogi (Live Blogging) ja interaktiivsed lugejaküsitlused (Interactive Polls)

### Kirjeldus:
Reaalajas uuenev otseblogi moodul (Live Coverage / Minut-minutilt kajastus) tähtsündmuste (nt valimised, spordivõistlused, tehnoloogiakonverentsid, erakorralised pressikonverentsid) kajastamiseks. Lisaks integreeritakse artiklitesse reaalajas tulemustega lugejaküsitlused (**Interactive Polls**), kus tulemused kuvatakse koheselt visuaalse tulpdiagrammina pärast hääletamist.

### Äriline ja kasutajakogemuse väärtus (Kasu ja mõju):
1. **Maksimaalne reaalajas kaasatus ja seansiaeg:** Otseülekannete ja suursündmuste ajal hoiavad lugejad lehte tundide viisi avatuna, mis tõstab samaaegsete kasutajate arvu (Concurrent Users) ja reklaamitulu mitmekordselt.
2. **Kogukonna arvamuse peegeldamine:** Interaktiivsed hääletused (nt *"Kas toetad riiklikku investeeringut tehisintellekti? Jah / Ei / Ei oska öelda"*) tekitavad lugejas tunde, et tema hääl loeb, ning soodustavad artikli jagamist sotsiaalmeedias.
3. **Multimeedia rikkus:** Otseblogi võimaldab postitada lühikesi tekstiampse koos piltide, videotega ja sotsiaalmeedia tsitaatidega ilma pikka toimetatud artiklit koostamata.

### Tehniline arhitektuur ja teostuse kava:
- **Andmebaas:**
  - `live_blogs` (`id`, `title`, `is_active`, `created_at`)
  - `live_blog_entries` (`id`, `blog_id`, `author_id`, `title`, `content`, `timestamp`)
  - `polls` (`id`, `news_id`, `question`, `is_active`), `poll_options` (`id`, `poll_id`, `option_text`, `votes`), `poll_votes` (`id`, `poll_id`, `user_id`, `ip_address`).
- **Reaalajas uuendamine:** Server-Sent Events (SSE) või optimeeritud 8–10 sekundiline asünkroonne päring (`api/live-blog.php?id=X&since=TIMESTAMP`), mis toob uued teated sujuva animatsiooniga lehe tippu ilma lehte värskendamata.
- **Manipuleerimiskaitse:** IP-aadressi ja sessioonipõhine unikaalsuse kontroll (üks hääl küsitluse kohta).
