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
