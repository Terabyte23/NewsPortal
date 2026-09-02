<?php
require_once __DIR__ . '/../db.php';
$adminPage = 'seed';
$pageTitle = 'Demo andmebaasi täitja';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Clear existing sample data if requested
    if (isset($_POST['wipe'])) {
        $conn->query("DELETE FROM comments");
        $conn->query("DELETE FROM news");
    }

    // 2. Realistic Estonian News Articles with HD Unsplash Photography
    $articles = [
        [
            'title' => 'Eesti tehisintellekti keskus avas uue põlvkonna superarvuti kvantarvutuste uurimiseks',
            'text' => "Tallinna ja Tartu Teaduspargi koostöös käivitati Baltimaade võimsaim tehisintellekti superarvuti LUMI-Eesti. Uus klaster võimaldab teadlastel ja idufirmadel treenida suuri keelemudeleid ning teha keerukaid kliima- ja biomeditsiini simulatsioone.

Projektijuht rõhutas, et superarvuti ressurss on avatud nii kohalikele teadusasutustele kui ka rahvusvahelistele partneritele. Esimesed testid näitavad, et arvutusvõimsus ületab seniseid võimalusi enam kui seitsmekordselt.

Eesti teadlaste sõnul aitab see samm hoida riiki maailma tehnoloogiainnovatsiooni esirinnas ning luua praktilisi lahendusi hariduse ja tervishoiu digitaliseerimiseks.",
            'category_id' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 1,
            'views' => 1420,
            'likes' => 84,
            'tags' => 'AI, Tehnoloogia, Superarvuti, Teadus'
        ],
        [
            'title' => 'Ida-Virumaa Kutsehariduskeskus võttis kasutusele virtuaalreaalsuse õppelaborid',
            'text' => "Ida-Virumaa Kutsehariduskeskuses (IVKHK) avati moodne 3D-modelleerimise ja virtuaalreaalsuse (VR) õppelabor. Uus keskus pakub tarkvaraarenduse ja multimeedia õpilastele reaalset tööstuskogemust Unreal Engine 5 ja Maya keskkondades.

Õppejõudude sõnul võimaldab VR-tehnoloogia omandada keerulisi inseneri- ja disainioskusi oluliselt kiiremini. Laboris on 24 tipptasemel tööjaama RTX 50-seeria graafikakaartidega ja professionaalsed VR-peakomplektid.",
            'category_id' => 2,
            'image_url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 0,
            'views' => 980,
            'likes' => 45,
            'tags' => 'Haridus, IVKHK, VR, 3D'
        ],
        [
            'title' => 'James Webbi kosmoseteleskoop avastas uue Maa-sarnase eksoplaneedi atmosfääri',
            'text' => "Rahvusvaheline astronoomide meeskond teatas põnevast avastusest: 40 valgusaasta kaugusel asuva eksoplaneedi atmosfäärist leiti veeauru ja metaani jälgi. See viitab võimalusele, et planeedi pinnal võivad valitseda vedela vee säilimiseks sobivad tingimused.

Teadlased jätkavad spektroskoopilisi vaatlusi, et selgitada välja planeedi täpsem keemiline koostis ja temperatuurirežiim.",
            'category_id' => 3,
            'image_url' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 0,
            'views' => 1250,
            'likes' => 92,
            'tags' => 'Kosmos, Teadus, NASA, Eksoplaneet'
        ],
        [
            'title' => 'Küberturbe ekspertide hoiatus: uus krüptolunavara levib sotsiaalmeedia kaudu',
            'text' => "Riigi Infosüsteemi Amet (RIA) hoiatab ettevõtteid ja erakasutajaid uue lunavarakampaania eest. Ründajad kasutavad ära tehisintellektiga genereeritud usaldusväärseid sõnumeid ja manuseid.

Eksperdid soovitavad tungivalt uuendada operatsioonisüsteeme, kasutada mitmetasemelist autentimist (2FA) ning hoiduda tundmatute linkide avamisest.",
            'category_id' => 4,
            'image_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 0,
            'views' => 870,
            'likes' => 38,
            'tags' => 'Küberturve, Internet, RIA, Turvalisus'
        ],
        [
            'title' => 'Eesti tehnoloogiasektor kasvas aastaga rekordilise 18 protsendi võrra',
            'text' => "Statistikaameti värsked andmed kinnitavad, et info- ja kommunikatsioonitehnoloogia sektor moodustab juba üle 10% Eesti sisemajanduse koguproduktist. Suurimat kasvu näitasid pilveteenuste ja finantstehnoloogia eksport.

Ettevõtluse arendamise sihtasutus prognoosib järgnevateks aastateks uute kõrgtehnoloogiliste töökohtade lisandumist.",
            'category_id' => 5,
            'image_url' => 'https://images.unsplash.com/photo-1551836022-deb4988cc6c0?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 0,
            'views' => 640,
            'likes' => 29,
            'tags' => 'Majandus, Startups, IKT, Eesti'
        ],
        [
            'title' => 'Digikunsti biennaal toob Tallinna tulevikulinna visioonid ja generatiivse disaini',
            'text' => "Kumu kunstimuuseumis ja Noblessneri kvartalis avati rahvusvaheline digikunsti festival. Väljapanekul saab näha reaalajas kohanduvaid valgusskulptuure, tehisintellekti loodud helimaastikke ja interaktiivseid installatsioone.

Kuraatorite sõnul uurib näitus inimese ja algoritmi loovuse piire ning digitaalse keskkonna mõju linnakultuurile.",
            'category_id' => 6,
            'image_url' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80',
            'is_featured' => 0,
            'views' => 510,
            'likes' => 61,
            'tags' => 'Kultuur, Kunst, AI, Tallinn'
        ]
    ];

    foreach ($articles as $a) {
        $t = $conn->real_escape_string($a['title']);
        $txt = $conn->real_escape_string($a['text']);
        $c = $a['category_id'];
        $img = $conn->real_escape_string($a['image_url']);
        $feat = $a['is_featured'];
        $v = $a['views'];
        $l = $a['likes'];
        $tags = $conn->real_escape_string($a['tags']);
        $react = json_encode(['like' => rand(15, 45), 'heart' => rand(8, 30), 'insightful' => rand(5, 20), 'fire' => rand(10, 35)]);

        $conn->query("INSERT INTO news (title, text, picture, category_id, user_id, views, likes, image_url, is_featured, reactions, tags, created_at)
                      VALUES ('$t', '$txt', '', $c, 1, $v, $l, '$img', $feat, '$react', '$tags', NOW() - INTERVAL " . rand(1, 48) . " HOUR)");
        
        $newsId = $conn->insert_id;
        
        // Add sample comments
        $conn->query("INSERT INTO comments (news_id, text, date, author_name) 
                      VALUES ($newsId, 'Väga põhjalik ja huvitav artikkel! Ootan jätkulugusid.', NOW() - INTERVAL " . rand(5, 120) . " MINUTE, 'Marek Tamm')");
        $conn->query("INSERT INTO comments (news_id, text, date, author_name) 
                      VALUES ($newsId, 'Hea teada, et tehnoloogia areneb nii kiiresti ka meil Eestis.', NOW() - INTERVAL " . rand(10, 60) . " MINUTE, 'Kristiina Kallas')");
    }

    $message = 'Andmebaas on edukalt täidetud 6 kvaliteetse eesti artikli, fotode ja kommentaaridega!';
}

include 'header.php';
?>

<div style="max-width: 680px; margin: 0 auto;">
    <div style="margin-bottom: 25px;">
        <h1 style="font-size: 2rem; font-weight: 900; margin-bottom: 4px;">Andmebaasi demo täitja (Seeder)</h1>
        <p style="color: var(--text-secondary);">Populeeri portaal ühe klõpsuga kaasaegsete uudiste, HD fotode ja aruteludega.</p>
    </div>

    <?php if ($message): ?>
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid #10b981; color: #34d399; padding: 14px 18px; border-radius: var(--radius-md); margin-bottom: 25px; font-weight: 600;">
            ✨ <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 30px;">
        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 12px;">Genereeri esitlusvalmis sisu</h3>
        <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            See funktsioon lisab andmebaasi realistlikud eestikeelsed tehnoloogia-, haridus- ja teadusuudised koos reaalsete vaatamiste, reaktsioonide ja kommentaaridega.
        </p>

        <form method="POST" action="seed.php">
            <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px;">
                <input type="checkbox" name="wipe" id="wipeCheck" value="1" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                <label for="wipeCheck" style="margin-bottom: 0; cursor: pointer;">Asenda vanad testandmed uute kvaliteetsete uudistega</label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                ⚡ Täida andmebaas kohe
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
