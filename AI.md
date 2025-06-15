# AI.md

## Soru: Veritabanı bağlantısını mysqli ile mi yoksa PDO ile mi yapsam daha iyi olur?

**Cevap:**

Bu proje için `mysqli` kullanman daha uygun olur. Çünkü:

- Proje temel ve öğrenci seviyesinde olduğu için `mysqli`’nin kullanımı daha basit ve anlaşılırdır.
- MySQL’e özgü bazı fonksiyonları doğrudan kullanabilirsin, ekstra soyutlama yok.
- Hazırlanmış ifadeler (*prepared statements*) ve parametre bağlama özelliklerini `mysqli` ile rahatlıkla kullanabilirsin.
- PDO, farklı veritabanlarını destekler ama sen sadece MySQL ile çalışıyorsan `mysqli` işini fazlasıyla görür.
- Öğrenme sürecinde `mysqli` ile başlayıp, projen gelişince PDO’ya geçmek daha mantıklı olabilir.

Kısaca, bu aşamada `mysqli` ile devam etmek pratik ve yeterli olacaktır.

---

## Soru: PHP ile geliştirilmiş temel bir forum sitesinde nasıl güvenlik önlemleri alınmalı (PDO değil `mysqli` kullanıyorum)?

**Cevap:**

Bu forum projesini geliştirirken temel güvenlik önlemlerine dikkat edilmelidir. Projede kullanıcı verilerinin korunması ve kötü niyetli girişimlerin engellenmesi için aşağıdaki güvenlik yaklaşımları uygulanmalıdır:

### 1. SQL Injection'a Karşı Koruma

Veritabanı işlemleri yapılırken `mysqli` uzantısı kullanılıyorsa, SQL enjeksiyonuna karşı korunmak için hazırlanmış ifadeler (*prepared statements*) ve parametre bağlama (*bind_param*) yöntemleri tercih edilmelidir. Bu şekilde kullanıcıdan gelen veriler doğrudan sorguya gömülmez, veritabanı motoru tarafından güvenli şekilde işlenir. Örnek kullanım:

```php
$stmt = $baglanti->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->bind_param("i", $kullanici_id);
```

### 2. XSS (Cross-Site Scripting) Saldırılarına Karşı Koruma

Kullanıcıların girdiği konu başlığı, mesaj içeriği, kullanıcı adı gibi veriler HTML içinde gösterilmeden önce `htmlspecialchars()` fonksiyonu ile işlenmelidir. Bu, zararlı JavaScript kodlarının çalıştırılmasını önler. Örneğin:

```php
<?= htmlspecialchars($kullanici['kullanici_adi']) ?>
```

### 3. Yetki Kontrolleri

Kullanıcıların sadece kendi oluşturdukları içerikleri (örneğin mesajlar, yanıtlar) düzenleyebilmesi ve silebilmesi için kimlik doğrulaması yapılmalıdır. Bu amaçla, oturumdan alınan kullanıcı ID’si ile içeriğin sahibine ait ID karşılaştırılmalıdır.

### 4. Oturum Güvenliği

Kullanıcının giriş yapıp yapmadığı kontrol edilmeden hiçbir korumalı sayfaya erişimine izin verilmemelidir. Bunun için `session_start()` ile oturum başlatıldıktan sonra `$_SESSION` üzerinden giriş kontrolü yapılmalıdır. Örneğin:

```php
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.html");
    exit;
}
```

Bu önlemleri projeye başlarken uygulamak, ileride oluşabilecek güvenlik açıklarını büyük ölçüde engelleyecektir. Ayrıca projenin ilerleyen aşamalarında CSRF token kullanımı, şifrelerin hashlenmesi gibi daha ileri düzey güvenlik uygulamaları da düşünülmelidir.

---

## Soru: yanıt düzenleme kısımlarında düzenlemeden önceki durumu da gösterebilmek için veritabanında yeni tablo açmak gerekir mi

**Cevap:**

Hayır, illa yeni tablo açmana gerek yok; ama bu tamamen senin projenin ihtiyacına bağlı.

## Pratik ve Mantıklı Yaklaşım

- **Eğer “düzenlemeden önceki içerik” kaybolmasın**, yani değişiklik geçmişi tutulsun istiyorsan, ayrı bir versiyonlama (*revision/history*) tablosu açman gerekir.
  - Bu tabloya her düzenlemede önceki hali kaydedilir, böylece istediğin zaman eski versiyona dönebilir veya değişiklik geçmişini gösterebilirsin.
- **Ama eğer bu özellik projende gerekmiyorsa** veya basit tutulacaksa,
  - Sadece `yanitlar` tablosundaki `icerik` alanını direkt güncelleyebilirsin, eski halini saklamana gerek yok.

## Özet

- **Geçmiş tutma, versiyonlama istiyorsan**: Evet, yeni tablo açmak gerekir. Mesela `yanitlar_gecmis` gibi.
- **Sadece güncel hali göstermek yeterliyse**: Hayır, mevcut tabloda değiştirip kullanabilirsin.

## Dikkat Edilmesi Gerekenler

- Versiyonlama karmaşıklığı artırır ama kullanıcı deneyimini ve içerik takibini geliştirir.
- Öğrenci projesi ise zorunlu değilse çok karmaşıklaştırmana gerek yok.

İstersen versiyonlama için nasıl tablo yapabileceğini, sorguları ve mantığını da anlatabilirim.

---

## Soru: Beğenme butonuna her tıklandığında beğeni sayısı 2 artıp, beğeniyi kaldırdığımda 2 azalıyor. Bunun birer birer değişmesi lazım. `begeni.php`’de ilgili kod parçası şu şekilde:

```php
// Beğeni varsa 
if ($sonuc->num_rows > 0) {

    // Beğeniyi kaldır.
    $stmt = $baglanti->prepare("DELETE FROM begeniler WHERE yanit_id = ? AND kullanici_id = ?");
    $stmt->bind_param("ii", $yanit_id, $kullanici_id);
    $stmt->execute();

    // Beğeni sayısını azalt.
    $stmt = $baglanti->prepare("UPDATE yanitlar SET begeni_sayisi = begeni_sayisi - 1 WHERE id = ? AND begeni_sayisi > 0");
    $stmt->bind_param("i", $yanit_id);
    $stmt->execute();
} 

// Beğeni yoksa
else {

    // Beğeni ekle.
    $stmt = $baglanti->prepare("INSERT INTO begeniler (yanit_id, kullanici_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $yanit_id, $kullanici_id);
    $stmt->execute();

    // Beğeni sayısını artır.
    $stmt = $baglanti->prepare("UPDATE yanitlar SET begeni_sayisi = begeni_sayisi + 1 WHERE id = ?");
    $stmt->bind_param("i", $yanit_id);
    $stmt->execute();
}

$stmt->execute();
$stmt->close();
$baglanti->close();
```

**Cevap:**

İyi yakalamışsın, problem kodda fazladan bir `execute()` çağrısı ve muhtemelen `begeni_sayisi` sütununu güncelleyen sorguların iki kere çalışmasından kaynaklanıyor. Bak şunlara dikkat:

Son satırda zaten `$stmt->execute();` var. Oysa her sorgudan sonra `execute()` zaten çağrılıyor. Bu fazladan çağrı, son hazırlanan sorguyu bir kez daha çalıştırıyor olabilir.

Ayrıca, `UPDATE yanitlar SET begeni_sayisi = begeni_sayisi + 1 WHERE id = ?` sorgusunda parametre bağlarken kullanıcının ID’sini göndermiyorsun (ki gerek yok), sadece yanıt ID’si gerekiyor.

İkinci `execute()` çağrısı ve parametre bağlamaları doğru sırada yapılmalı.

Yani şu şekilde düzenle:

```php
// Beğeni varsa 
if ($sonuc->num_rows > 0) {

    // Beğeniyi kaldır.
    $stmt = $baglanti->prepare("DELETE FROM begeniler WHERE yanit_id = ? AND kullanici_id = ?");
    $stmt->bind_param("ii", $yanit_id, $kullanici_id);
    $stmt->execute();
    $stmt->close();

    // Beğeni sayısını azalt.
    $stmt = $baglanti->prepare("UPDATE yanitlar SET begeni_sayisi = begeni_sayisi - 1 WHERE id = ? AND begeni_sayisi > 0");
    $stmt->bind_param("i", $yanit_id);
    $stmt->execute();
    $stmt->close();

} else {

    // Beğeni ekle.
    $stmt = $baglanti->prepare("INSERT INTO begeniler (yanit_id, kullanici_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $yanit_id, $kullanici_id);
    $stmt->execute();
    $stmt->close();

    // Beğeni sayısını artır.
    $stmt = $baglanti->prepare("UPDATE yanitlar SET begeni_sayisi = begeni_sayisi + 1 WHERE id = ?");
    $stmt->bind_param("i", $yanit_id);
    $stmt->execute();
    $stmt->close();
}

$baglanti->close();
```

**Özet:**

- Her sorgu için ayrı `prepare()`, `bind_param()`, `execute()`, `close()` yap.
- Ortak bir `$stmt->execute()` veya `$stmt->close()` satırını fazladan koyma.
- Böylece her beğeni veya kaldırmada sayılar 1’er 1’er artıp azalacak.

Bu halini dene, sorun çözülecektir.
