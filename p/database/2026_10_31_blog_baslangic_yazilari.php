<?php
/**
 * 31.10.2026 — Blog başlangıç yazıları (SADECE CLI)
 *
 * Kullanım:
 *   php p/database/2026_10_31_blog_baslangic_yazilari.php            # hangi yazıların ekleneceğini listeler
 *   php p/database/2026_10_31_blog_baslangic_yazilari.php --uygula   # ekler (aynı slug varsa atlar; mevcut yazıya dokunmaz)
 *
 * Önce 2026_10_31_blog_video_gelistirme.php --uygula çalışmış olmalı (kategori kolonu).
 * Yazılar TASLAK olarak eklenir: panelden okuyup düzenledikten sonra "Yayında" yapın.
 */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../../env.php';

$uygula = in_array('--uygula', $argv ?? [], true);
$pdo = new PDO(
    'mysql:host=' . prolynweb_env('DB_HOST', '127.0.0.1') . ';dbname=' . prolynweb_env('DB_NAME', 'prolyn_master') . ';charset=utf8mb4',
    prolynweb_env('DB_USER', ''), prolynweb_env('DB_PASS', ''),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
echo $uygula ? "== UYGULAMA MODU ==\n" : "== RAPOR MODU (eklemek için --uygula) ==\n";

$yazilar = [];

$yazilar[] = [
 'slug' => 'olcu-ve-montaj-randevusu-nasil-planlanir',
 'kategori' => 'Randevu ve Montaj',
 'title' => 'Ölçü ve montaj randevusu nasıl planlanır? Mutfak-dolap firmaları için 6 adım',
 'excerpt' => 'Ölçü, üretim ve montaj arasındaki boşlukları kapatan, çakışmayı ve boşa giden ziyareti azaltan basit bir randevu düzeni.',
 'content' => <<<'TXT'
Ölçü ve montaj randevusu; müşteri adresi, ekip müsaitliği, malzemenin hazır olması ve müşterinin evde olması gibi dört şeyin aynı güne denk gelmesidir. Biri aksarsa ziyaret boşa gider. Aşağıdaki altı adım bu riski küçültür.

## 1. Ölçü ve montajı iki ayrı randevu olarak açın

Ölçü randevusu bilgi toplar, montaj randevusu iş teslim eder. İkisini aynı kayıtta tutarsanız ölçüden sonra montaj tarihi "havada" kalır. Her biri için ayrı tarih, ekip ve not tutun.

## 2. Randevuyu müşteriye bağlı açın

Randevunun adı değil, müşteri kaydı olmalı. Böylece adres, telefon ve sözleşme tek tıkla görünür; ekip sahada "hangi sipariş bu?" diye aramaz.

## 3. Ekip takvimini tek yerden görün

Aynı ekibe aynı gün iki yerde iş yazmamak için ekip bazlı takvim kullanın. Çakışma gördüğünüz anda tarihi kaydırmak, müşteri aradıktan sonra kaydırmaktan çok daha ucuzdur.

## 4. Montajdan önce malzeme kontrolü yapın

Montaj tarihinden birkaç gün önce eksik malzeme listesine bakın. Kulp, menteşe, tezgah parçası ya da cam eksikse tarihi o gün değil, hâlâ vaktiniz varken değiştirin.

> Pratik kural: montaj tarihinden 3 iş günü önce "eksik var mı?" kontrolü yapın.

## 5. Durumları renkle ayırın

Planlandı, müşteri onayladı, yolda, tamamlandı, ertelendi gibi birkaç net durum yeterlidir. Renk ve sade etiket, ofiste herkesin aynı tabloya bakmasını sağlar.

## 6. Tamamlanan montajı tahsilata bağlayın

Montaj bittiği gün kalan bakiyeyi hatırlatın. Teslim anı, müşterinin ödeme isteğinin en yüksek olduğu andır; "sonra konuşuruz" demeden bakiyeyi netleştirin.

## Başlamak için

Önce son 10 randevunuza bakın: kaç tanesi ertelendi ve neden? Çoğu zaman cevap malzeme, ekip çakışması veya müşteri onayı olur. Bu üç noktayı yukarıdaki adımlarla kapatmak, düzeni büyük bir yazılım olmadan bile iyileştirir.
TXT,
];

$yazilar[] = [
 'slug' => 'eksik-malzeme-takibi-montaj-gunu-eli-bos-donmemek',
 'kategori' => 'Randevu ve Montaj',
 'title' => 'Eksik malzeme takibi: montaj günü eli boş dönmemek için 5 alışkanlık',
 'excerpt' => 'Montaja gidip tek bir parça yüzünden geri dönmek hem maliyet hem itibar kaybıdır. Eksikleri erken görmenin pratik yolu.',
 'content' => <<<'TXT'
Montaja giden ekibin "bir parça eksik" diyerek dönmesi; yol, işçilik ve müşteri güveni demektir. Çoğu eksik üretimde değil, bilgi kaybında doğar: ölçüde not edilir ama siparişe yansımaz.

## 1. Eksiği keşfettiğiniz anda yazın

Eksik parça fark edildiği an kayda geçmeli. Akşama bırakılan eksik, ertesi gün unutulur. Kayıt kısa olsun: ne eksik, hangi sipariş, kim tedarik edecek.

## 2. Her eksiğin bir sorumlusu olsun

"Biri bakar" sistemi çalışmaz. Her eksik için tek bir kişi ve tahmini gelme tarihi yazın.

## 3. Eksikleri montaj tarihine göre sıralayın

En acil eksik, en yakın montaja bağlı olandır. Listeyi tarihe göre sıralamak önceliği kendiliğinden gösterir.

## 4. Aynı gün birden çok eksik varsa tek özet çıkarın

Bir günde birden fazla işte eksik varsa tek sayfalık özet; hem depoyu hem tedarikçiyi tek aramada yönetmenizi sağlar.

## 5. Sorunlu işleri ayrı izleyin

Yanlış gelen, kırık gelen veya geç gelen parçalar normal eksikten farklıdır. Bunları "problem" olarak ayrı işaretleyin; tedarikçi performansını sonradan konuşabilmenin tek yolu budur.

## Kontrol listesi

- Eksik kaydı açıldı mı?
- Sorumlu ve tarih yazıldı mı?
- Montaj tarihi etkileniyor mu?
- Müşteri bilgilendirildi mi?

Dört soruya "evet" diyebiliyorsanız, montaj günü sürprizleri belirgin şekilde azalır.
TXT,
];

$yazilar[] = [
 'slug' => 'taksitli-satista-cek-takibi-sik-yapilan-hatalar',
 'kategori' => 'Tahsilat ve Cari',
 'title' => 'Taksitli satışta çek takibi: sık yapılan 6 hata',
 'excerpt' => 'Vadesi geçen çeki fark etmemek, tutarı yanlış eşlemek ya da sözleşmeyle bağlamamak... Çek ve taksit takibinde en sık görülen hatalar.',
 'content' => <<<'TXT'
Taksitli satışta alacağınızın büyük kısmı çek, senet ve ödeme planı kalemlerinde durur. Takip dağınıksa para kaybı olmasa bile nakit akışı şaşar. İşte en sık yapılan altı hata.

## 1. Çeki sözleşmeye bağlamamak

Elinizdeki çekin hangi müşteriye ve hangi sözleşmeye ait olduğu belli değilse, tahsil edildiğinde bakiyeyi doğru düşemezsiniz. Çek kaydını her zaman müşteri ve sözleşmeyle ilişkilendirin.

## 2. Vade tarihine güvenip hatırlatma kurmamak

Vade günü bankaya gitmeyi akılda tutmak kırılgan bir yöntemdir. Vadesi yaklaşan çekleri haftalık listeleyin.

## 3. Çek tutarı ile taksit tutarını eşleştirmemek

Bir çek birden fazla taksiti, bir taksit birden fazla çeki karşılayabilir. Hangi tutarın hangi taksite gittiğini kaydetmezseniz müşteri "ben bunu ödemiştim" dediğinde belge bulamazsınız.

## 4. Karşılıksız ve iade durumlarını kayda geçirmemek

Karşılıksız çıkan ya da iade edilen çek, alacağın geri açılması demektir. Bu durum kayda geçmezse müşteri bakiyesi gerçeğinden düşük görünür.

## 5. Kısmi ödemeleri yok saymak

Müşteri taksitin bir kısmını nakit, kalanını çekle ödeyebilir. Kısmi tutarları ayrı ayrı kaydedin; "kalan" tutar her an görünsün.

## 6. Silerek düzeltmek

Yanlış girilen kaydı silmek geçmişi bozar. Mümkünse iptal veya ters kayıt tercih edin; böylece kim, ne zaman, neyi değiştirdi izlenebilir kalır.

> Kural: para hareketi silinmez, iptal edilir. İzlenebilirlik, ileride çıkacak her tartışmada sizi korur.

## Özet

Çek takibinin üç sorusu: Kimin? Hangi sözleşme ve taksit için? Ne zaman vadesi geliyor? Bu üçüne tek bakışta cevap veremiyorsanız düzen yeniden kurulmalı.
TXT,
];

$yazilar[] = [
 'slug' => 'cari-hesap-nedir-musteri-bakiyesi-nasil-okunur',
 'kategori' => 'Tahsilat ve Cari',
 'title' => 'Cari hesap nedir? Müşteri bakiyesi nasıl okunur?',
 'excerpt' => 'Borç, alacak ve bakiye kavramlarını küçük işletme diliyle anlatan kısa bir rehber: bir müşterinin cari ekstresi nasıl okunur?',
 'content' => <<<'TXT'
Cari hesap, bir müşteri veya tedarikçiyle aranızdaki alacak-verecek ilişkisinin kronolojik kaydıdır. Her satış, tahsilat, iade ve düzeltme bu hesapta bir hareket olarak görünür.

## Temel kavramlar

- **Borç:** Müşterinin size ödemesi gereken tutar (satış, hizmet bedeli).
- **Alacak:** Müşterinin size yaptığı ödemeler ya da indirimler.
- **Bakiye:** Borç ile alacak arasındaki fark; müşterinin şu an ne kadar borçlu olduğu.

Yazılımlarda "borç" ve "alacak" ifadesinin hangi tarafın bakış açısıyla yazıldığı değişebilir. Ekranı kullanmadan önce bu yönü netleştirin.

## Bir ekstre nasıl okunur?

1. En üstteki **açılış bakiyesine** bakın.
2. Satırları tarih sırasıyla izleyin: satış bakiyeyi artırır, tahsilat azaltır.
3. En alttaki **güncel bakiye**, bugün müşteriden beklediğiniz tutardır.
4. Vadesi gelmemiş taksitler ile gecikenleri ayrı değerlendirin; toplam bakiye hepsini birlikte gösterir.

## Sık karışan nokta: bakiye ile vadesi gelen tutar

Bir müşterinin bakiyesi yüksek olabilir ama vadesi gelen tutar düşüktür; çünkü taksitler ileri tarihlidir. Tahsilat çağrısını vadesi gelen tutara göre yapın, aksi halde müşteri gereksiz baskı hisseder.

## Cari hesabı doğru tutmanın faydaları

- Müşteriye "borcunuz ne kadar?" sorusuna hemen cevap verirsiniz.
- Geciken alacaklar erken fark edilir.
- Muhasebeciyle konuşurken elinizde net bir rakam olur.

## Başlangıç önerisi

Önce en çok işlem yapan on müşterinizin ekstresini elle kontrol edin. Rakamlar tutuyorsa düzeniniz sağlamdır; tutmuyorsa farkın nereden doğduğunu bulmak, sistemi değiştirmekten önce yapılacak ilk iştir.
TXT,
];

$yazilar[] = [
 'slug' => 'gunluk-kasa-kapanisi-nasil-yapilir',
 'kategori' => 'Kasa ve Ön Muhasebe',
 'title' => 'Günlük kasa kapanışı nasıl yapılır? 10 dakikalık rutin',
 'excerpt' => 'Gün sonunda kasayı saymak, sistemle karşılaştırmak ve farkı kayda geçirmek için küçük işletmeye uygun kısa bir rutin.',
 'content' => <<<'TXT'
Kasa kapanışı, günün sonunda "elimdeki para ile kayıttaki para aynı mı?" sorusunun cevabıdır. Her gün yapılırsa farklar küçükken yakalanır; haftalara yayılırsa kaynağı bulunamaz.

## Kapanış rutini

1. **Hareketleri tamamlayın.** Gün içinde alınan tüm tahsilat ve yapılan ödemeler kayda girmiş olmalı.
2. **Fiziksel sayımı yapın.** Nakit parayı sayın; banknot ve bozuk parayı ayrı yazın.
3. **Sistem bakiyesiyle karşılaştırın.** Kayıtlı kasa bakiyesi ile sayım aynı mı?
4. **Farkı kaydedin.** Fark varsa açıklamasıyla ayrı bir hareket olarak yazın; sessizce düzeltmeyin.
5. **Kapanış notu bırakın.** Kim saydı, saat kaç, fark var mı.

## Fark çıkarsa

- Önce bugünkü hareketleri baştan kontrol edin: tutar hatası ve çift giriş en sık nedenlerdir.
- Para üstü ve küçük masraflar (yol, yemek) kayda geçmemiş olabilir.
- Başka kasadan transfer yapıldıysa karşı kayıt girilmiş mi bakın.

## Neden her gün?

Bir gün fark küçük görünür. Ama on gün sonra, hangi güne ait olduğunu bilmediğiniz bir fark çıkar ve bu hem zaman hem güven kaybıdır.

## Kasa ve cari birbirini tutmalı

Müşteriden alınan tahsilat hem kasaya girmeli hem müşteri bakiyesinden düşmeli. İki kayıt ayrı yapılıyorsa, aralarında fark oluşma ihtimali yüksektir; mümkünse tek işlemden iki kaydı birlikte oluşturan bir düzen kullanın.

> Kasa kapanışı yılda bir yapılan bir denetim değil, her gün 10 dakikalık bir alışkanlıktır.
TXT,
];

$yazilar[] = [
 'slug' => 'excelden-erp-ye-gecis-ilk-30-gun',
 'kategori' => 'Dijital Dönüşüm',
 'title' => 'Excel\'den ERP\'ye geçiş: ilk 30 günde ne yapılmalı?',
 'excerpt' => 'Yeni bir yazılıma geçerken sorun çoğunlukla yazılım değil, düzensiz başlangıçtır. İlk ayı yönetilebilir kılan basit bir yol haritası.',
 'content' => <<<'TXT'
Excel; esnek, tanıdık ve ucuzdur. Ancak müşteri sayısı, taksit ve randevu arttıkça aynı bilgi birden çok dosyada çoğalır, kim neyi güncelledi bilinmez. Geçiş bu noktada gündeme gelir.

## Hafta 1: Temizlik ve karar

- Hangi dosyalar gerçekten kullanılıyor? Listeleyin.
- Müşteri listesini tek dosyada birleştirin; çift kayıtları ayıklayın.
- Hangi işlemlerin yeni düzende yapılacağına karar verin (ör. tahsilat ve randevu).

## Hafta 2: Kurulum ve aktarım

- Firma bilgilerini, kasaları ve banka hesaplarını girin.
- Müşteri listesini aktarın. Telefon ve ad alanları temiz değilse arama ve eşleştirme zorlaşır.
- Açık bakiyeleri **açılış bakiyesi** olarak girin; geçmişin tamamını taşımanız şart değil.

## Hafta 3: Paralel çalışma

Yeni düzende yalnızca yeni işleri açın; eskiler Excel'de bitsin. İki sistemi yan yana tutmak ilk günlerde fazladan iş gibi görünür ama güvenli bir geçiştir.

## Hafta 4: Karşılaştırma ve kapanış

- Hafta sonu kasa ve cari rakamlarını Excel ile karşılaştırın.
- Fark varsa kaynağı bulun; sistem hatası mı, giriş hatası mı?
- Ekip eğitimini kısa tutun: herkes yalnızca kendi yaptığı 3-4 işlemi öğrensin.

## Başarıyı belirleyen üç şey

1. **Tek sorumlu:** Geçişi yürüten bir kişi olsun.
2. **Sade başlangıç:** İlk ay tüm modülleri açmayın; önce randevu, tahsilat ve kasa.
3. **Eski dosyayı salt okunur yapın:** Geçişten sonra Excel'i güncellemeyi bırakın, aksi halde iki gerçek ortaya çıkar.

> Hedef mükemmel bir geçiş değil; ilk ayın sonunda "bugün kimden ne alacağız?" sorusuna tek yerden cevap verebilmek.

Prolyn'de ilk kurulum sihirbazı bu adımlara rehberlik eder; video rehberlerimizde süreci ekranda görebilirsiniz.
TXT,
];

$yazilar[] = [
 'slug' => 'siparisten-tahsilata-bir-mutfak-siparisinin-yolculugu',
 'kategori' => 'Dijital Dönüşüm',
 'title' => 'Siparişten tahsilata: bir mutfak siparişinin yolculuğu',
 'excerpt' => 'Teklif, sözleşme, ölçü, üretim, montaj ve tahsilat... Bir mutfak siparişinin tüm aşamalarını ve her aşamada kaybolan bilgileri haritalıyoruz.',
 'content' => <<<'TXT'
Bir mutfak siparişi haftalar süren, birçok kişiyi ilgilendiren bir süreçtir. Her aşamada bilgi bir sonrakine aktarılmazsa, sorunlar montaj ya da tahsilat gününe kadar saklanır.

## 1. Teklif ve sözleşme

Müşterinin adı, adresi, ürün kalemleri, toplam tutar ve ödeme planı bu aşamada netleşir. Sözleşmede olmayan bir şey sonradan tartışma konusu olur; kapora ve taksit tarihlerini açıkça yazın.

## 2. Ölçü randevusu

Ölçü alınırken yapılan notlar (priz, tesisat, duvar durumu) üretime aktarılmazsa montajda sürpriz çıkar. Ölçü notlarını müşteri kaydına bağlayın.

## 3. Üretim ve tedarik

Bu aşamada eksik malzeme ve gecikme riski vardır. Üretim durumunu ofisin görebildiği bir yerde tutmak, müşteriye doğru tarih vermenizi sağlar.

## 4. Montaj

Montaj günü ekip; adres, müşteri telefonu, eksik listesi ve kalan bakiye bilgisine sahip olmalı. Montaj sonrası tamamlandı olarak işaretlemek, tahsilat sürecini başlatır.

## 5. Tahsilat ve kapanış

Kalan bakiye, çekler ve taksitler bu aşamada takip edilir. Teslimden sonraki tahsilat, en sık unutulan ve en pahalıya patlayan adımdır.

## 6. Satış sonrası

Küçük bir servis talebi, gelecekteki öneri ve yeni sipariş demektir. Şikayetleri ve servis taleplerini de müşteri kaydında tutun.

## Haritadan çıkan ders

Her aşamada üç şey aynı yerde olmalı: **müşteri, tarih ve para.** Bu üçü farklı defterlerde, dosyalarda ve telefon notlarında dağılırsa süreç, kişilere bağımlı hale gelir.

Bu rehberdeki aşamaları Prolyn'in randevu, cari, çek ve kasa modülleriyle tek akışta yönetebilirsiniz.
TXT,
];

$baslangic = strtotime('2026-10-31 09:00:00');
$ekle = $pdo->prepare("INSERT IGNORE INTO blog_yazilari (title, slug, excerpt, kategori, content, status, published_at, created_at)
                       VALUES (?, ?, ?, ?, ?, 'draft', NULL, NOW())");
foreach ($yazilar as $y) {
    $var = $pdo->prepare("SELECT 1 FROM blog_yazilari WHERE slug = ?");
    $var->execute([$y['slug']]);
    if ($var->fetchColumn()) { echo "[var]   {$y['slug']}\n"; continue; }
    echo "[yeni]  {$y['slug']}  ({$y['kategori']})\n";
    if ($uygula) { $ekle->execute([$y['title'], $y['slug'], $y['excerpt'], $y['kategori'], $y['content']]); echo "        -> taslak olarak eklendi\n"; }
}
echo "Bitti.\n";
