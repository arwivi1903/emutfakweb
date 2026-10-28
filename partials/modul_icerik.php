<?php
/**
 * Modül anlatımları — TEK KAYNAK.
 * Ana sayfadaki modül bölümleri ve moduller.php (ayrıntılı rehber) aynı diziyi kullanır.
 * Yeni bir özellik eklenince yalnızca burası güncellenir.
 *
 * Alanlar:
 *  slug       : bölüm bağlantısı (#slug) ve illüstrasyon dosya adı (partials/ill/<slug>.php)
 *  ad         : kısa ad (menü, çipler, tablo)
 *  anahtarlar : paketteki modül anahtarları (paket_ozellikleri.allowed_modules) — "hangi pakette?" buradan çıkar
 *  renk       : tema.css'teki --m-* değişkeni
 *  durum      : 'hazir' | 'kismen' (bir kısmı henüz açık değil — 'durum_not' yazar)
 *  baslik     : bölüm başlığı (faydayı söyler)
 *  ozet       : 1-2 cümle, ne işe yaradığı
 *  maddeler   : ana sayfada gösterilen 4 madde [başlık, açıklama]
 *  ayrinti    : moduller.php'de ek olarak gösterilen maddeler
 *  kimler     : hangi roller kullanır
 *  ornek      : "bir gün nasıl işler" cümlesi
 *
 * NOT: Burada yalnızca uygulamada bugün çalışan özellikler yazılıdır. Yeni bir
 * özellik yayına girmeden buraya eklenmemelidir.
 */
function prolynweb_modul_icerik(): array
{
    return [
        [
            'slug' => 'musteri', 'ad' => 'Müşteri', 'anahtarlar' => ['customer'], 'renk' => 'musteri', 'durum' => 'hazir',
            'baslik' => 'Müşterinin bütün geçmişi tek kartta',
            'ozet' => 'Telefon, adres ve notlar bir yerde durur; sözleşmesi, randevuları ve ödemeleri aynı müşteri kartından açılır. Aynı kişiyi iki kez aramaz, defter karıştırmazsınız.',
            'maddeler' => [
                ['Müşteri kartı', 'İletişim bilgileri, adres, il ve ilçe, notlar tek ekranda.'],
                ['Türkçe karakterle arama', 'Müşteriyi adından veya telefonundan yazdığınız gibi bulursunuz.'],
                ['Bağlı kayıtlar', 'Sözleşme, randevu ve cari bakiye müşteri kartından açılır.'],
                ['Satış personeline kendi kayıtları', 'Satış personeli randevu ve cari ekranlarında yalnızca kendi kayıtlarını görür.'],
            ],
            'ayrinti' => ['Müşteri ekleme ve düzenleme ayrı sayfada yapılır; küçük pencerelerde kaybolan formlar yoktur.', 'Müşteri açıklamasına biçimli not yazılabilir.'],
            'kimler' => 'Yönetici, satış',
            'ornek' => 'Müşteri arar, adını söyler; kartını açıp sözleşmesini, bir sonraki randevusunu ve kalan borcunu aynı ekranda görürsünüz.',
        ],
        [
            'slug' => 'randevu', 'ad' => 'Randevu ve montaj', 'anahtarlar' => ['appointment'], 'renk' => 'randevu', 'durum' => 'hazir',
            'baslik' => 'Ölçü, montaj ve servis günleri takvimde; durumu renkten okursunuz',
            'ozet' => 'Her iş bir randevu kaydıdır ve altı durumdan birinde durur. Takvime bakan, hangi işin planlandığını, hangisinin imalatta, hangisinin eksik malzemede beklediğini anlar.',
            'maddeler' => [
                ['Ay, hafta ve liste görünümü', 'Türkçe takvimde günlük planı ya da aylık yükü görün; boş güne tıklayıp yeni randevu açın.'],
                ['Altı durum, altı renk', 'Beklemede, Planlamada, İmalatta, Montaja Hazır, Eksik Malzeme, Tamamlandı. Durum çipine tıklayınca o renk takvimden gizlenir.'],
                ['Eksik malzeme takibi', 'Montajda eksik çıkan parçayı sebebiyle kaydedin; ana randevuya bağlı yeni bir randevu kendiliğinden açılır.'],
                ['Depo için eksik özeti', 'Eksikleri A4 yatay tek listede, imza alanıyla yazdırın; depoda tek tek işaretleyin.'],
            ],
            'ayrinti' => ['Takvim balonunda müşteri, şube, istikamet ve tarih aralığı görünür.', 'Yönetici, satış, genel kontrol ve montaj panolarında takvim doğrudan açılış ekranındadır.', 'Eksik kaydında kaçıncı ziyaret ve kaçıncı problem olduğu tutulur.', 'Aynı gün birden fazla eksik malzeme kaydedilebilir.'],
            'kimler' => 'Yönetici, satış, depo, montaj, genel kontrol',
            'ornek' => 'Montaj ekibi dolap kapağının eksik geldiğini kaydeder; ertesi hafta için eksik işlem randevusu kendiliğinden açılır, depo listesine düşer.',
        ],
        [
            'slug' => 'sozlesme', 'ad' => 'Sözleşme', 'anahtarlar' => ['contract'], 'renk' => 'sozlesme', 'durum' => 'hazir',
            'baslik' => 'Sözleşme yapılır, taksit planı onunla birlikte kurulur',
            'ozet' => 'Satışı sözleşmeye, ödemeyi taksit ve çeklere bağlarsınız. Müşteri ne zaman ne ödeyecek, ne kadarı ödendi, ekranda yazar.',
            'maddeler' => [
                ['Sözleşme ve taksit planı', 'Kapora, taksit adedi ve vadeler sözleşmeyle birlikte tanımlanır.'],
                ['Kısmi tahsilat', 'Taksitin bir kısmı alınabilir; kalan tutar aynı vadeyle açık kalır.'],
                ['Kendi metninizle çıktı', 'Sözleşme, tahsilat dekontu ve çek dekontu çıktıları belge şablonlarındaki metninizle basılır.'],
                ['Şablon yetkisi', 'Belge şablonlarını yönetici ve genel kontrol rolü düzenler.'],
            ],
            'ayrinti' => ['Sözleşme çıktısı yazdırılabilir.', 'Birden çok sözleşmesi olan müşteride sözleşmeler ayrı ayrı izlenir.'],
            'kimler' => 'Yönetici, satış, genel kontrol',
            'ornek' => 'Mutfak sözleşmesi imzalanır: kapora alınır, kalan tutar altı taksite bölünür, ilk vade takvime düşer.',
        ],
        [
            'slug' => 'cari', 'ad' => 'Cari hesap', 'anahtarlar' => ['current_account'], 'renk' => 'cari', 'durum' => 'hazir',
            'baslik' => 'Kim ne kadar borçlu, hangi taksit geçti: hesap yapmadan görürsünüz',
            'ozet' => 'Her müşterinin satış tutarı, aldığınız ve kalan tutar otomatik hesaplanır. Yaklaşan ve geçmiş taksitler ayrı listelerde durur.',
            'maddeler' => [
                ['Üç rakamda bakiye', 'Satış tutarı, alınan tutar ve kalan tutar müşteri kartının başında.'],
                ['Ekstre', 'Tarih aralığı seçip müşterinin hareketlerini devir bakiyesiyle birlikte dökün.'],
                ['Bekleyen ve alınan tahsilat', 'Alacağınız taksitleri ve tahsil ettiklerinizi sekmelerde ayrı görün.'],
                ['Yaklaşan ve geçmiş taksitler', 'Vadesi gelen ve geçen ödemeleri tek listede toplayıp arama sırasını belirleyin.'],
            ],
            'ayrinti' => ['Tahsilat dekontu yazdırılır.', 'Cari durum ekranı tüm müşterilerin toplam alacağını gösterir.', 'Satış personeli yalnızca kendi müşterilerinin hareketlerini görür.'],
            'kimler' => 'Yönetici, satış, muhasebe',
            'ornek' => 'Sabah muhasebe, bugün ve geçen haftanın vadesi gelen taksit listesini açar; aranacak müşteriler hazırdır.',
        ],
        [
            'slug' => 'kasa', 'ad' => 'Ana kasa', 'anahtarlar' => ['accounting'], 'renk' => 'kasa', 'durum' => 'hazir',
            'baslik' => 'Kasada ne var: nakit, banka ve çek ayrı ayrı',
            'ozet' => 'Ana kasa ekranı toplam bakiyeyi nakit, banka hesapları ve çek olarak ayırır. Satış personelinin kendi kasası ayrıdır; topladığı tahsilatı merkez kasaya transfer eder.',
            'maddeler' => [
                ['Toplam bakiye dökümü', 'Nakit, banka hesapları ve çek ayrı ayrı; toplam bakiye nakit ile banka toplamıdır.'],
                ['Kasa hareketleri', 'Giriş ve çıkışlar kasa tipiyle birlikte kaydedilir ve listelenir.'],
                ['Personelin kendi kasası', 'Tahsilatı alan satış personelinin kasası ayrı izlenir, merkez kasaya transfer edilir.'],
                ['Şube ve personel kasa durumu', 'Hangi şubede, hangi personelde ne kadar kasa kaldığı tek bakışta.'],
            ],
            'ayrinti' => ['Muhasebe panosu, ana kasanın nakit, banka ve çek rakamlarını doğrudan gösterir.', 'Kasa tipleri firmanıza göre tanımlanır.', 'Muhasebe rolü kendi kasa yerine doğrudan ana kasa üzerinde çalışır.'],
            'kimler' => 'Yönetici, muhasebe, satış (kendi kasası)',
            'ornek' => 'Satışçı gün içinde ₺18.000 tahsil eder; akşam merkez kasaya transfer eder.',
        ],
        [
            'slug' => 'cek', 'ad' => 'Çek ve taksit birleştirme', 'anahtarlar' => ['accounting'], 'renk' => 'cek', 'durum' => 'hazir',
            'baslik' => 'Müşteri tek çek verdi: üç sözleşmenin taksitini tek seferde kapatın',
            'ozet' => 'Müşterinin birden fazla sözleşmesi olsa bile, seçtiğiniz taksitleri tek çekle kapatırsınız. Her sözleşmenin hesabına kendi payı yazılır, çek tek numarayla izlenir.',
            'maddeler' => [
                ['Farklı sözleşmeden taksit seçimi', 'Tüm bekleyen taksitler tek tabloda sözleşmelere göre gruplanır; istediklerinizi işaretleyin.'],
                ['Kısmi tutar', 'Taksitin yalnızca bir kısmını çekle kapatabilirsiniz; fark aynı vadeyle açık kalır.'],
                ['Sözleşme kırılımı', 'Hangi sözleşmeden ne kadar kapandığı canlı görünür; sözleşme toplamları bozulmaz.'],
                ['Çeklerin takibi', 'Çekler listelenir, merkez kasaya aktarılır, çek dekontu yazdırılır.'],
            ],
            'ayrinti' => ['Birleştirme tek işlemdir: herhangi bir adım başarısızsa hiçbir kayıt yazılmaz.', 'Çek parçaları aynı çek numarasını taşır.', 'Çek listelerinde birleşik çek sözleşme başına bir satır olarak görünür.'],
            'kimler' => 'Yönetici, satış, muhasebe',
            'ornek' => 'Müşteri dört taksit yerine tek çek getirir; çeki girersiniz, dört taksit de ilgili sözleşmelerinde kapanır.',
        ],
        [
            'slug' => 'banka', 'ad' => 'Banka', 'anahtarlar' => ['bank'], 'renk' => 'banka', 'durum' => 'hazir',
            'baslik' => 'Banka hesaplarınız ve aralarındaki hareket',
            'ozet' => 'Her hesabın bakiyesi ve hareketleri ayrı izlenir. Kayıtlı tutarla hesaplanan bakiye uyuşmazsa fark ekranda uyarı olarak çıkar.',
            'maddeler' => [
                ['Hesap bazlı bakiye', 'Her banka hesabı için net bakiye ve kayıtlı tutar yan yana.'],
                ['Fark uyarısı', 'Net bakiye ile kayıtlı tutar tutmuyorsa hesap uyarı rozetiyle işaretlenir.'],
                ['Virman', 'Hesaplar arası aktarımı tek kayıtla yapın.'],
                ['Bakiye kontrolü', 'Ana kasa ve personel ödemelerinde, bakiyesi yetmeyen banka hesabından ödeme kaydı yapılamaz.'],
            ],
            'ayrinti' => ['Banka hareketleri hesaba göre listelenir.', 'Banka ile yapılan ödemeler ana kasada banka bakiyesinden düşer.'],
            'kimler' => 'Yönetici, muhasebe',
            'ornek' => 'Ay sonunda iki hesap arasında virman yapılır; ana kasanın banka toplamı değişmez, hesap bakiyeleri güncellenir.',
        ],
        [
            'slug' => 'personel', 'ad' => 'Personel giderleri', 'anahtarlar' => ['accounting'], 'renk' => 'personel', 'durum' => 'hazir',
            'baslik' => 'Maaş, avans, prim ve kesintiyi personel bazında izleyin',
            'ozet' => 'Her personele aylık maaş tanımlarsınız; ödedikçe kalan tutar kendiliğinden hesaplanır. Ödemeyi kasadan düşmesini isteyip istemediğinize siz karar verirsiniz.',
            'maddeler' => [
                ['Tarihçeli maaş tanımı', 'Zam yapınca yeni tanım eklenir; eski dönemlerin hesabı bozulmaz.'],
                ['Sekiz ödeme türü', 'Maaş, avans, prim, mesai, yol, yemek, kesinti ve diğer.'],
                ['Kalan hesabı', 'Tanımlı maaş eksi ödenen maaş ve avans eksi kesinti: personelin o aydaki kalanı.'],
                ['Kasaya bağlantı', 'Ödemede "kasadan düş" kutusunu işaretlerseniz ana kasadan veya seçtiğiniz banka hesabından düşer.'],
            ],
            'ayrinti' => ['Dönem, şube, personel ve türe göre süzün; Excel, PDF veya yazdır.', 'Kesinti kasadan para çıkarmaz, personelin borcunu azaltır.', 'Ödeme silinince bağlı kasa ve banka hareketi geri alınır.', 'Muhasebe rolü ödeme ekler; silme yalnızca yöneticidedir.'],
            'kimler' => 'Yönetici, muhasebe',
            'ornek' => 'Ayın 15\'inde ₺5.000 avans verilir; ay sonunda maaş ödemesinde kalan tutar otomatik görünür.',
        ],
        [
            'slug' => 'ticari', 'ad' => 'Ticari cari ve fatura', 'anahtarlar' => ['commercial', 'invoice'], 'renk' => 'ticari', 'durum' => 'hazir',
            'baslik' => 'Tedarikçi ve ticari müşterilerle hesabınız, alış ve satış faturalarıyla',
            'ozet' => 'Bireysel müşteriden ayrı olarak firmalarla olan hesabınızı ticari cari ekranlarında izlersiniz. Alış ve satış faturalarınızı da kaydedersiniz.',
            'maddeler' => [
                ['Ticari cari', 'Firma müşteri ve tedarikçilerin alacak ve borç durumu ayrı ekranda.'],
                ['Alış ve satış faturası', 'Fatura kayıtlarını girin, listeleyin ve fatura ekranından izleyin.'],
                ['Ticari cari durum', 'Tüm ticari carilerin bakiyesini tek listede görün.'],
                ['Muhasebe için sade giriş', 'Muhasebe rolü bu ekranlara doğrudan menüsünden ulaşır.'],
            ],
            'ayrinti' => ['Ticari müşteri, bireysel müşteriden ayrı yetkilendirilir.'],
            'kimler' => 'Yönetici, muhasebe',
            'ornek' => 'Muhasebe sabah ticari cari durum listesini açar ve hangi firmayla hesabın açık olduğunu görür.',
        ],
        [
            'slug' => 'rapor', 'ad' => 'Raporlar ve panolar', 'anahtarlar' => ['reports', 'statistics'], 'renk' => 'rapor', 'durum' => 'hazir',
            'baslik' => 'Herkes açılışta kendi işine uygun panoyu görür',
            'ozet' => 'Sekiz rolün her biri için ayrı ana pano vardır: yönetici, satış, depo, montaj, genel kontrol, muhasebe, planlama ve imalat. Raporlar ve istatistikler bunların üstüne gelir.',
            'maddeler' => [
                ['Role göre pano', 'Muhasebe kasa ve tahsilatı, montaj kendi takvimini, depo eksik listesini görür.'],
                ['Muhasebe panosu', 'Ana kasa rakamları, bu ay tahsilat ve geciken taksit sayısı açılış ekranında.'],
                ['Tarih raporu', 'Seçtiğiniz tarih aralığındaki randevu ve işleri listeleyin.'],
                ['İstatistik ve grafik', 'Grafik raporlama ve istatistik ekranlarıyla işin gidişini izleyin.'],
            ],
            'ayrinti' => ['Rapor ve istatistik modülleri paketinize göre açılır.', 'Yetkisi olmayan kullanıcı boş ekran yerine neden göremediğini söyleyen bir bilgi ekranı görür.'],
            'kimler' => 'Tüm roller (kapsamı paket ve rol belirler)',
            'ornek' => 'Yönetici sabah panoda geciken taksit ve açık eksik sayısını görür; muhasebe aynı ekranda kasa rakamlarına bakar.',
        ],
        [
            'slug' => 'planlama', 'ad' => 'Planlama ve imalat', 'anahtarlar' => ['planning'], 'renk' => 'planlama', 'durum' => 'hazir',
            'baslik' => 'Siparişin planlamadan imalata, imalattan montaja yolu',
            'ozet' => 'Planlama ve imalat ekipleri için sade bir çalışma alanı vardır: yalnızca kendi işleri, kendi listeleri. Durum değiştikçe satışçı ve montajcı randevuda güncel aşamayı görür.',
            'maddeler' => [
                ['Üç aşamalı akış', 'Planlamada, İmalatta, Montaja Hazır. Her aşama randevuda kendi rengiyle görünür.'],
                ['Planlama ve imalat panoları', 'Bu iki rol yalnızca ihtiyaç duyduğu ekranları görür; menüde kalabalık yok.'],
                ['Sipariş listeleri', 'Planlama ve imalat için ayrı sipariş ve iş listeleri.'],
                ['Rapor erişimi', 'Planlama ve imalat kullanıcıları tarih raporunu açabilir.'],
            ],
            'ayrinti' => ['Planlama ve imalat, paket tablosunda gösterildiği gibi yalnızca bu modülü içeren paketlerde açılır.'],
            'kimler' => 'Yönetici, planlama, imalat',
            'ornek' => 'Planlama siparişi imalata verir; imalat bitirince durum "Montaja Hazır" olur, montaj ekibi takvimde görür.',
        ],
        [
            'slug' => 'ekip', 'ad' => 'Kullanıcı, rol ve güvenlik', 'anahtarlar' => ['core'], 'renk' => 'ekip', 'durum' => 'hazir',
            'baslik' => 'Herkes yalnızca yetkisi olan ekranı görür; verileriniz size ait ayrı bir veritabanındadır',
            'ozet' => 'Sekiz hazır rol ve istediğiniz özel gruplar. Her firmanın verisi kendi ayrı veritabanında tutulur ve başka firmanın kullanıcıları tarafından görülemez.',
            'maddeler' => [
                ['Rol bazlı menü', 'Yetkisi olmayan ekran menüde görünmez; adresini bilse de açamaz.'],
                ['Ayrı veritabanı', 'Verileriniz başka bir firmayla aynı tabloyu paylaşmaz.'],
                ['İşlem kayıtları', 'Önemli işlemler kimin yaptığıyla kayıt altına alınır; yönetici log ekranından izler.'],
                ['Panelden destek talebi', 'Sorunu panelden yazın, ekran görüntüsünü yapıştırın; yanıtlar aynı yazışmada toplanır.'],
            ],
            'ayrinti' => ['Paket kullanıcı ve şube limitini belirler; limit dolunca yeni kayıt eklenemez, uyarı çıkar, mevcut kayıt silinmez.', 'İlk girişte kurulum sihirbazı firma bilgilerini, ilk şubeyi ve ekibi adım adım ister; atlanabilir.', 'Destek talebine eklenen dosyaları yalnızca talebi açan kullanıcı, kurum yöneticisi ve destek ekibi açabilir.', 'Kullanıcı şifreleri geri döndürülemez biçimde saklanır.'],
            'kimler' => 'Yönetici',
            'ornek' => 'Yeni satışçı eklenir, yalnızca müşteri, sözleşme ve randevu menüsünü görür; kasa ve ayarlar ekranları kendisine kapalıdır.',
        ],
        [
            'slug' => 'urun', 'ad' => 'Ürün ve stok', 'anahtarlar' => ['inventory'], 'renk' => 'urun', 'durum' => 'kismen',
            'durum_not' => 'Ürün kartları bugün kullanılıyor; depo stok takibi geliştirme aşamasında.',
            'baslik' => 'Ürün kartları hazır, depo stok takibi yolda',
            'ozet' => 'Ürünlerinizi kartlarla tanımlarsınız, montajda eksik çıkan malzemeyi randevudan takip edersiniz. Depodaki miktarı izleyen ayrıntılı stok modülü henüz açık değil.',
            'maddeler' => [
                ['Ürün kartları (hazır)', 'Ürün ve malzemeleri tanımlayın, sözleşme ve randevularda kullanın.'],
                ['Eksik malzeme takibi (hazır)', 'Montajda eksik çıkanlar randevuya bağlı kaydedilir ve depo listesine düşer.'],
                ['Depo stok takibi (yakında)', 'Giriş, çıkış ve depodaki miktar takibi hazırlanıyor.'],
            ],
            'ayrinti' => ['Depo stok modülü açıldığında hangi pakette yer alacağı paket tablosunda gösterilir.'],
            'kimler' => 'Yönetici, genel kontrol',
            'ornek' => 'Bugün: ürün kartlarıyla çalışır, eksik malzemeyi randevudan izlersiniz.',
        ],
    ];
}
