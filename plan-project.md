# 🚀 Busur: WhatsApp Gateway & Bulk Messaging System

**Busur** adalah aplikasi backoffice dan manajemen WhatsApp Gateway berbasis web yang tangguh, dirancang menggunakan framework **Laravel** dan diintegrasikan dengan Node.js WhatsApp service. Sistem ini memungkinkan pengiriman pesan WhatsApp secara asinkronus (background processing) yang aman, cepat, dan handal untuk kebutuhan pemasaran bisnis, broadcast info, serta layanan pelanggan.

---

## 🎯 Kegunaan Utama & Nilai Tambah

Aplikasi **Busur** dirancang khusus untuk mempermudah bisnis mengirimkan informasi secara massal kepada pelanggan tanpa mengalami kendala performa atau risiko pemblokiran nomor (*banned*) yang tinggi. Sistem ini menjembatani interaksi langsung ke WhatsApp melalui:
1. **Asynchronous Queue (Antrean Background):** Pesan dikirim melalui antrean Laravel Job, sehingga pengguna tidak perlu menunggu halaman dimuat saat mengirim ribuan pesan.
2. **Kestabilan Server:** Prosesor antrean berjalan di background secara terisolasi menggunakan Docker, mencegah overload memori dan kegagalan pengiriman.
3. **Keamanan Nomor (Anti-Ban):** Memadukan algoritma jeda dinamis mirip perilaku manusia untuk menghindari deteksi sistem deteksi spam pihak WhatsApp.

---

## 🛠️ Fitur Utama Aplikasi

### 1. 📲 Manajemen Koneksi WhatsApp
*   Deteksi status koneksi nomor WhatsApp secara real-time (Terhubung/Terputus).
*   Integrasi pairing via pemindaian (scan) QR Code secara instan dari aplikasi.

### 2. ✉️ Pesan Tunggal (Single Messaging)
*   **Kirim Cepat:** Kirim pesan individual ke nomor tujuan dalam hitungan detik.
*   **Media Attachment:** Mendukung pengiriman berkas gambar, dokumen, atau video.
*   **Real-time Live Preview:** Tampilan gelembung obrolan WhatsApp dinamis untuk mempratinjau isi pesan dan spintax sebelum dikirim.
*   **Real-time Number Check:** Deteksi otomatis apakah nomor tujuan terdaftar secara resmi di database WhatsApp sebelum proses kirim dimulai.

### 3. 🚀 Pesan Bulking (Bulk Messaging & Campaigns)
*   **Fleksibilitas Input Target:**
    *   *Excel/CSV Uploader:* Unggah basis data kontak langsung dari spreadsheet Excel.
    *   *Manual List:* Memasukkan nomor satu-persatu dalam form baris demi baris.
*   **Pesan Personalisasi:** Mendukung penggantian variabel dinamis seperti `{name}`, `{var1}`, `{var2}` dari kolom Excel untuk pesan yang lebih personal.
*   **Advanced Anti-Ban Systems:**
    *   *Jeda Acak (Random Delay):* Mengatur batas interval acak (misalnya jeda 5-15 detik antar pesan) agar pengiriman tidak terdeteksi sebagai robot.
    *   *Sesi Istirahat (Resting Period):* Sistem otomatis beristirahat/berhenti mengirim selama beberapa menit setelah sejumlah pesan tertentu terkirim (misalnya istirahat 2 menit setiap 50 pesan terkirim).
    *   *Spintax Randomization:* Mendukung format spintax seperti `{Halo|Hai|Selamat Pagi} {pelanggan|kakak}` untuk merotasi kata pembuka secara otomatis pada setiap pesan sehingga konten pesan selalu unik.
*   **Interactive Control & Monitoring:** Halaman monitoring progress real-time untuk melihat statistik pesan sukses/gagal, serta tombol kendali untuk menjeda (*Pause*) dan melanjutkan (*Resume*) campaign secara instan.
*   **Active Campaign Locking:** Demi menjaga stabilitas nomor, form pengiriman pesan baru dan menu input otomatis terkunci dengan overlay premium visual jika terdeteksi ada campaign yang sedang aktif berjalan.

### 4. 📊 Log, Audit & DataTables Terintegrasi
*   **Log Pesan Bulking:** Menampilkan riwayat ringkasan seluruh campaign massal, total target, jumlah sukses/gagal, serta metode input yang digunakan.
*   **Log Pesan Tunggal:** Menampilkan riwayat pengiriman pesan perorangan secara terperinci lengkap dengan status sukses/gagal dan log error jika gagal.
*   **Modular DataTables:** Mengintegrasikan pustaka DataTables secara dinamis untuk memudahkan pencarian cepat (*instant search*), penyaringan (*filtering*), dan pengurutan (*sorting*) berdasarkan kolom tabel log secara cepat di sisi client-side tanpa membebani server utama.

---

## 🏗️ Arsitektur & Teknologi Stack

*   **Backend Framework:** Laravel (PHP 8.x)
*   **Queue Driver:** Database/Redis Queue Processor (Laravel Horizon ready)
*   **WhatsApp Service:** Node.js Webhook & Puppeteer (WhatsApp Web API)
*   **Frontend UI:** CSS Vanilla dengan Template Admin Premium (Otika/Stisla), Bootstrap 4, FontAwesome 5, dan Feather Icons.
*   **Database:** MySQL / PostgreSQL
*   **Containerization:** Docker & Docker Compose
