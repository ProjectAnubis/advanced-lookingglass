# Advanced Looking Glass

Advanced Looking Glass, ağ testleri, analiz, kontrol ve izleme işlemlerini tek bir platformda toplayan, tam prodüksiyon seviyesinde geliştirilen, modüler ve genişletilebilir bir uygulamadır. Bu proje; gerçek zamanlı veri akışı, gelişmiş DNS işlemleri, port tarama, hız testi, WebSocket ile canlı çıktı aktarımı, RESTful API desteği, JWT/OAuth2 tabanlı güvenlik, otomatik uyarı sistemleri ve dağıtık mimari desteği gibi ileri seviye özellikleri entegre eder.

Advanced Looking Glass, tüm ileri seviye özellikleri entegre eden tam prodüksiyon seviyesinde bir ağ testleri platformudur. Hem web arayüzü hem de API üzerinden erişilebilen bu platform, canlı veri akışı, gelişmiş analiz, otomatik uyarı sistemleri ve dağıtık mimari desteğiyle geniş ölçekli ağ izleme ve analiz ihtiyaçlarını karşılayacak şekilde tasarlanmıştır.

## Özellikler

**Ağ Test Metotları**
- IPv4 ve IPv6 için ping testleri.
- Gelişmiş MTR (My Traceroute) testi: Her hop için istatistik hesaplamaları (ortalama, en iyi, en kötü, standart sapma); DNS cache optimizasyonu ve asenkron reverse DNS sorguları.
- Traceroute testleri (IPv4/IPv6).
- Port tarama (nmap tabanlı).
- Hız testi: iperf3 kullanılarak incoming ve outgoing testleri.

**Güvenlik**
- CSRF koruması, giriş doğrulama ve escapeshellarg() kullanımı.
- PDO prepared statement’lar ile SQL enjeksiyonuna karşı koruma.
- API erişiminde JWT/OAuth2 tabanlı kimlik doğrulaması (auth.php).
- Opsiyonel: İki faktörlü kimlik doğrulama (2FA).

**Gerçek Zamanlı Çıktı ve İletişim**
- Ratchet tabanlı WebSocket sunucusu ile canlı çıktı aktarımı (websocket.php).
- RESTful API desteği (api.php üzerinden JSON çıktı).

**Dashboard & Analitik**
- Chart.js kullanılarak grafik ve raporlama desteği (dashboard.php).
- Trend analizleri ve özet raporlar.

**Dağıtık Mimari ve Otomatik Bildirim**
- RabbitMQ gibi mesaj kuyruğu entegrasyonu ile merkezi log yönetimi ve asenkron görev işleme.
- Otomatik uyarı sistemi: PHPMailer kullanılarak e-posta/SMS bildirimleri (alerts.php).

## Kurulum

1. **Sunucu Gereksinimleri:**
   - PHP 7.4+ (önerilir)
   - MySQL veya uyumlu veritabanı (PDO desteği)
   - Gerekli sistem araçları: iperf3, nmap, mtr, traceroute vb.
   - Composer (PHP bağımlılıkları için)

2. **Veritabanı Kurulumu:**
   - `create_table.sql` dosyasını veritabanınıza uygulayarak `lg_logs` tablosunu oluşturun.
   - `config.php` dosyasında veritabanı bağlantı bilgilerinizi (DB_DSN, DB_USER, DB_PASS) güncelleyin.

3. **Composer Bağımlılıkları:**
   - Proje dizinine gidip terminalde şu komutları çalıştırın:
     ```bash
     composer require react/dns
     composer require cboden/ratchet
     composer require firebase/php-jwt
     composer require phpmailer/phpmailer
     composer require php-amqplib/php-amqplib
     ```
   - Gerekli paketler `vendor/` dizinine yüklenecektir.

4. **Web Sunucu Ayarları:**
   - Web sunucunuzu (Apache, Nginx vb.) projenin kök dizini olarak ayarlayın.
   - Gerekli .htaccess veya sunucu yapılandırmalarını gerçekleştirin.

5. **WebSocket Sunucusunu Çalıştırma:**
   - Terminalde aşağıdaki komutla WebSocket sunucusunu başlatın:
     ```bash
     php websocket.php
     ```
   - İstemciler, tanımlı port üzerinden (örneğin 8080) WebSocket sunucusuna bağlanabilir.

## Kullanım

- **Web Arayüzü:**
  - `index.php` üzerinden testleri başlatın. Hedef (IP/hostname) ve test metodu seçildikten sonra sonuçlar canlı olarak (WebSocket veya fetch streaming ile) görüntülenecektir.

- **RESTful API:**
  - `api.php` endpoint’ine API anahtarınız ile GET veya POST istekleri gönderin.
  - Örnek istek:
    ```
    GET /api.php?api_key=YOUR_SECURE_API_KEY&method=ping&target=8.8.8.8
    ```
  - JSON formatında çıktı alırsınız.

- **Dashboard & Analitik:**
  - `dashboard.php` üzerinden test sonuçlarınızı grafiksel olarak analiz edebilir, trend raporlarını görüntüleyebilirsiniz.

- **Dağıtık Mimari:**
  - Çoklu sunucu ortamı kullanıyorsanız, RabbitMQ entegrasyonunu gerçekleştirin.

## Ek Gelişmiş Özellikler & Notlar

- **WebSocket Entegrasyonu:**  
  Ratchet tabanlı WebSocket sunucusu ile canlı veri akışı, fetch temelli yöntemlere göre daha düşük gecikme sağlar.

- **Gelişmiş DNS İşlemleri:**  
  Parser içinde asenkron reverse DNS sorguları için AsyncDNSResolver kullanılmıştır. Performans ve hata yönetimi için genişletilebilir.

- **Hız Testi:**  
  iperf3 komutlarıyla yapılan hız testlerinde, `{target}` placeholder'ı güvenli bir şekilde değiştirilir. Testler, hem gelen hem giden yön için ayrı ayrı çalıştırılır.

- **Güvenlik:**  
  Tüm giriş verileri doğrulanmakta, CSRF token’ları kullanılmakta ve escapeshellarg() ile komut enjeksiyonuna karşı koruma sağlanmaktadır. API erişiminde JWT doğrulaması entegre edilmiştir.

- **Otomatik Uyarı:**  
  alerts.php üzerinden e-posta/SMS uyarıları yapılandırılabilir. Kritik durumlarda otomatik bildirim gönderilir.

- **Dağıtık Mimari:**  
  RabbitMQ kullanılarak merkezi log yönetimi ve asenkron görev işleme temel yapı sunar.

## Proje Durumu & Kontrol

- **Kod Yapısı:**  
  Tüm modüller ayrı dosyalarda, namespace kullanımı ile düzenlenmiştir. Composer ile bağımlılıklar yönetilmektedir.

- **Güvenlik:**  
  CSRF, input doğrulama, escapeshellarg() ve PDO prepared statement kullanımları mevcuttur. API erişiminde JWT doğrulaması eklenmiştir.

- **Canlı Çıktı Akışı:**  
  Hem WebSocket hem de fetch tabanlı streaming ile veri akışı sağlanmaktadır.

- **Asenkron İşlemler:**  
  AsyncDNSResolver, asenkron reverse DNS sorgusu için örnek sunmaktadır.

- **Sistem Gereksinimleri:**  
  Dış komutların (iperf3, nmap, mtr, traceroute vb.) sisteminizde kurulu olduğundan emin olun.

**Not:** Bu proje henüz geliştirme aşamasındadır. Bu nedenle, beklenmeyen hatalar meydana gelebilir ve bazı özellikler tam anlamıyla stabil olmayabilir.
