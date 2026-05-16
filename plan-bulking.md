# Plan: Fitur Pesan Bulking (Broadcast)

Rencana pengembangan fitur pengiriman pesan massal (Bulking) dengan fokus pada keamanan akun (Anti-Ban) dan kemudahan manajemen data.

## 1. Fitur Utama
*   **Metode Input Ganda**:
    *   **Import Excel**: Mendukung pembacaan file .xlsx/.csv.
    *   **Tulis Manual**: Input nomor dipisahkan dengan titik koma (`;`).
*   **Personalisasi & Konten**:
    *   **Placeholder Dinamis**: Mendukung tag seperti `{nama}`, `{tagihan}`, dll (sesuai header Excel).
    *   **Spin-tax**: Variasi kata otomatis (format: `{Halo|Hai|Salam}`) untuk menghindari deteksi spam.
    *   **Media Support**: Kirim gambar/dokumen dengan caption.

## 2. Strategi Anti-Ban (Keamanan Akun)
*   **Random Delay**: Jeda acak 10-30 detik antar setiap pesan.
*   **Batching & Resting**:
    *   Pengiriman dilakukan per batch (misal: 10 pesan).
    *   Istirahat otomatis selama 2 menit setiap setelah 1 batch selesai.
    *   **UI Notification**: Animasi dan informasi status "Sedang Istirahat" muncul di halaman pengiriman (bulking page) dan log detail saat proses batching berhenti sejenak.

## 3. Manajemen Campaign & Log
*   **Grouping**: Setiap aksi bulking dikelompokkan dalam satu `Campaign`.
*   **Log Dashboard**:
    *   Halaman utama log menampilkan ringkasan Campaign (Nama, Progress, Status, Waktu).
    *   **Detail View**: Tombol detail untuk melihat status pengiriman per nomor telepon (Sent, Pending, Failed).

## 4. Rencana Teknis (Tanpa Artisan)
### Database
*   Tabel `campaigns`: `id`, `nama`, `total`, `terkirim`, `gagal`, `status`, `settings` (JSON).
*   Update Tabel `pesans`: Tambahkan `campaign_id`.

### Backend
*   `BulkingController`: Menangani routing dan view.
*   `BulkingService`: Logika parsing excel, spintax, dan placeholder.
*   `ProcessBulkingJob`: Queue job untuk handle pengiriman dengan delay dan batching logic.


## 5. Pertanyaan Terbuka / Diskusi Selanjutnya
*   Library Excel: Menggunakan **OpenSpout** (sangat ringan, hemat RAM karena sistem streaming, cocok untuk hanya baca/import).
*   Desain animasi "Resting" yang paling pas dengan tema Otika.

## 6. Plan to Exec
### 6.1 Layout Halaman Pesan Bulking
*   **Tujuan Halaman**:
    *   Membuat layout halaman **Pesan Bulking** yang fokus pada kemudahan input target, pengaturan isi pesan, dan preview real-time sebelum proses kirim dilakukan.

*   **Struktur Layout Utama**:
    *   Halaman dibagi menjadi **2 area besar**:
    *   **Area kiri / form builder** untuk input data target, konten chat, dan media.
    *   **Area kanan / live preview** dengan tampilan yang sama persis seperti preview pada fitur **Pesan Tunggal**.

*   **Section Input Target**:
    *   Gunakan **2 section** yang jelas dan mudah dipahami.
    *   Opsi implementasi:
    *   **Bootstrap Accordion** agar hemat ruang dan user bisa fokus ke satu metode input.
    *   Atau gunakan **card layout bawaan template Otika** bila ingin konsisten dengan pattern halaman admin yang sudah ada.
    *   Section 1: **Input Nomor WhatsApp Manual**
    *   Sediakan textarea untuk memasukkan beberapa nomor WhatsApp secara manual.
    *   Nomor dapat dipisahkan dengan tanda ;
    *   Tambahkan helper text contoh format input.
    *   Section 2: **Input File Excel**
    *   Sediakan upload file untuk `.xlsx` / `.csv`.
    *   Tampilkan area info singkat terkait format kolom yang didukung.
    *   Bila perlu, sediakan hint bahwa data dari Excel bisa dipakai untuk placeholder dinamis.
    *   Ada tombol untuk download template file excel

*   **Section Chat Panel**:
    *   Tambahkan **chat panel** dengan kontrol yang mengikuti pola dari fitur **Pesan Tunggal**.
    *   Komponen kontrol dibuat semirip mungkin agar user tidak perlu belajar ulang.
    *   Minimal mencakup:
    *   Input isi pesan.
    *   Dukungan placeholder / variabel jika memang sudah tersedia di pesan tunggal.
    *   Tombol atau kontrol tambahan yang sama seperti panel pesan tunggal bila relevan.

*   **Input Media**:
    *   Sediakan field upload media pada area chat panel.
    *   Media dapat berupa gambar atau file lain sesuai dukungan sistem saat ini.
    *   Posisi input media dibuat menyatu dengan form pesan agar alur compose tetap natural.

*   **Live Preview**:
    *   Tambahkan **live preview** yang tampil real-time saat user mengubah isi pesan atau media.
    *   Tampilan preview harus **sama persis** dengan live preview di fitur **Pesan Tunggal**.
    *   Jika pesan tunggal sudah memiliki komponen preview reusable, gunakan komponen yang sama untuk menjaga konsistensi UI dan perilaku.

*   **Arah Implementasi UI**:
    *   Prioritaskan reuse komponen dari halaman **Pesan Tunggal** untuk:
    *   chat panel,
    *   input media,
    *   dan live preview.
    *   Untuk area metode input target, sesuaikan dengan komponen layout Otika yang paling dekat agar hasilnya tetap konsisten dengan halaman lain.

*   **Output yang Diharapkan**:
    *   User bisa memilih metode input target melalui manual atau Excel.
    *   User bisa menulis pesan dan melampirkan media dalam satu alur.
    *   User langsung melihat hasil preview pesan sebelum proses bulking dijalankan.

### 6.2 Model dan Migration Table Campaign
*   **Tujuan**:
    *   Membuat struktur data utama untuk menyimpan informasi campaign bulking agar proses kirim, monitoring, dan reporting dapat dikelola dengan baik.

*   **Pekerjaan Utama**:
    *   Buat **Laravel Model `Campaign`**.
    *   Buat **migration table `campaigns`**.
    *   Jika model atau migration sudah tersedia, lakukan pemeriksaan lalu sempurnakan struktur yang kurang tanpa merusak data atau alur yang sudah ada.

*   **Cakupan Pemeriksaan Jika Sudah Ada**:
    *   Periksa nama tabel apakah sudah konsisten menggunakan `campaigns`.
    *   Periksa field inti apakah sudah mendukung kebutuhan fitur bulking.
    *   Periksa relasi ke tabel pesan atau riwayat kirim bila sudah mulai dibangun.
    *   Periksa `casts`, `fillable`, dan `default value` pada model.

*   **Struktur Minimal Table `campaigns`**:
    *   `id`
    *   `nama`
    *   `tipe_input`
    *   `total`
    *   `terkirim`
    *   `gagal`
    *   `status`
    *   `settings` bertipe JSON untuk menyimpan konfigurasi pengiriman
    *   `created_at`
    *   `updated_at`

*   **Penyempurnaan yang Disarankan**:
    *   Tambahkan field yang relevan bila diperlukan seperti `started_at`, `finished_at`, atau `last_processed_at`.
    *   Pastikan field `status` memiliki nilai yang jelas seperti `draft`, `queued`, `running`, `paused`, `completed`, dan `failed`.
    *   Pastikan `settings` cukup fleksibel untuk menyimpan delay, batch size, rest duration, dan parameter anti-ban lainnya.

*   **Model `Campaign`**:
    *   Definisikan properti `fillable` hanya untuk field yang aman diisi dari request.
    *   Gunakan `casts` untuk field JSON, angka, dan datetime.
    *   Siapkan relasi ke data pesan atau log pengiriman bila tabel terkait sudah tersedia atau akan dibuat berikutnya.

*   **Output yang Diharapkan**:
    *   Tersedia model `Campaign` yang siap dipakai oleh fitur bulking.
    *   Tersedia migration `campaigns` yang rapi, aman, dan cukup fleksibel untuk pengembangan lanjutan.
    *   Jika struktur sebelumnya sudah ada, hasil akhirnya menjadi versi yang lebih lengkap dan konsisten.

### Catatan Eksekusi 6.3 - 6.5
*   **Tujuan Perapihan**:
    *   Bagian `6.3 - 6.5` diperlakukan sebagai urutan kerja AI untuk mengubah fitur pesan bulking dari sekadar static view menjadi flow yang benar-benar bisa dijalankan end-to-end.

*   **Urutan Prompt yang Disarankan**:
    1. `6.4` kerjakan lebih dulu untuk menyiapkan parser Excel, parser input manual, dan payload target yang seragam.
    2. `6.3` kerjakan setelah itu untuk menyiapkan engine eksekusi bulking dengan anti-ban.
    3. `6.5` kerjakan paling akhir untuk menyambungkan request UI ke parser, campaign, dan job eksekusi.

*   **Aturan Saat Melempar Prompt ke AI**:
    *   Satu prompt hanya fokus ke satu nomor sub-bab.
    *   Minta AI menyebutkan file yang diubah, flow yang dibuat, dan risiko regresi.
    *   Setelah tiap step selesai, review dulu sebelum lanjut ke step berikutnya.
    *   Jangan minta AI menggabungkan `6.3`, `6.4`, dan `6.5` sekaligus agar jejak perubahan tetap mudah dipantau.

*   **Format Laporan yang Wajib Diminta dari AI di Tiap Step**:
    *   File yang dibuat atau diubah.
    *   Ringkasan flow yang ditambahkan.
    *   Asumsi yang dipakai.
    *   Hal yang belum dikerjakan.
    *   Cara test manual step tersebut.

### 6.3 Logic untuk Anti-Ban
*   **Posisi dalam Urutan Kerja**:
    *   Step ke-2.
    *   Dikerjakan setelah `6.4` selesai karena logic anti-ban harus menerima daftar target yang sudah bersih dan seragam.

*   **Tujuan Step Ini**:
    *   Membuat engine eksekusi campaign bulking yang mengatur ritme kirim agar lebih aman dan tidak terlihat seperti spam.

*   **Fokus Kerja AI**:
    *   Buat service atau job processor khusus bulking.
    *   Terapkan `random delay`, `batching`, dan `resting`.
    *   Update statistik campaign seperti `total`, `terkirim`, `gagal`, dan `status`.
    *   Pastikan flow ini terpisah dari flow `pesan tunggal`.

*   **Input yang Diasumsikan Sudah Tersedia**:
    *   Data `campaign`.
    *   Daftar target hasil normalisasi dari `6.4`.
    *   Konfigurasi anti-ban dari field `settings`, misalnya `min_delay`, `max_delay`, `batch_size`, `rest_after_batch`, dan `retry_limit`.

*   **Yang Tidak Boleh Diurus di Step Ini**:
    *   Jangan parsing file Excel mentah di logic anti-ban.
    *   Jangan membangun UI baru.
    *   Jangan mengubah flow kirim `pesan tunggal` yang sudah ada.

*   **Hasil Kode yang Diharapkan**:
    *   Ada class service atau job untuk menjalankan campaign bulking.
    *   Ada mekanisme delay acak antar pesan.
    *   Ada mekanisme istirahat tiap selesai satu batch.
    *   Ada update status campaign seperti `queued`, `running`, `resting`, `completed`, dan `failed`.

*   **Checklist Review Setelah AI Selesai**:
    *   Apakah logic anti-ban hanya aktif untuk bulking.
    *   Apakah status campaign ter-update dengan jelas.
    *   Apakah tidak ada perubahan berisiko pada flow `pesan tunggal`.
    *   Apakah konfigurasi anti-ban dibaca dari `settings`, bukan hard-coded semua.

### 6.4 Logic Parsing Excel, Input Manual, dan Placeholder
*   **Posisi dalam Urutan Kerja**:
    *   Step ke-1.
    *   Ini adalah pondasi sebelum `6.3` dan `6.5`.

*   **Tujuan Step Ini**:
    *   Membuat satu pintu normalisasi target bulking agar input manual dan input Excel berakhir dalam struktur data yang sama.

*   **Fokus Kerja AI**:
    *   Parsing file `.xlsx` atau `.csv`.
    *   Parsing input manual dari textarea.
    *   Membersihkan nomor WhatsApp ke format yang siap dipakai.
    *   Membentuk payload placeholder dari header Excel atau data manual.
    *   Menghasilkan output target yang konsisten untuk step berikutnya.

*   **Struktur Output yang Disarankan**:
    *   Setiap target minimal memiliki `nomor`.
    *   Jika berasal dari Excel, target memiliki `placeholders` berbentuk key-value.
    *   Jika perlu logging, tambahkan metadata seperti `row_number` atau `source`.

*   **Aturan Placeholder**:
    *   Header Excel menjadi key placeholder, misalnya kolom `nama` dipakai untuk `{nama}`.
    *   Placeholder di pesan diganti berdasarkan data penerima masing-masing.
    *   Jika ada placeholder yang tidak punya pasangan kolom, sistem harus memberi validasi atau fallback yang jelas.

*   **Yang Tidak Boleh Diurus di Step Ini**:
    *   Jangan mengirim pesan.
    *   Jangan menerapkan delay, batching, atau resting.
    *   Jangan membuat controller final submit campaign.

*   **Hasil Kode yang Diharapkan**:
    *   Ada service parser untuk Excel dan input manual.
    *   Ada format output target yang seragam.
    *   Ada validasi dasar untuk file, header, dan nomor.
    *   Hasil parsing siap dipakai oleh `6.3` dan `6.5`.

*   **Checklist Review Setelah AI Selesai**:
    *   Apakah input manual dan Excel berakhir di format data yang sama.
    *   Apakah placeholder dari Excel benar-benar bisa dipakai.
    *   Apakah baris invalid ditandai dengan aman.
    *   Apakah service parser cukup terpisah dan mudah dites.

### 6.5 Logic Controller untuk Menjalankan Pesan Bulking
*   **Posisi dalam Urutan Kerja**:
    *   Step ke-3.
    *   Dikerjakan setelah `6.4` dan `6.3` tersedia.

*   **Tujuan Step Ini**:
    *   Menjadikan halaman bulking benar-benar bisa submit dan memulai campaign.

*   **Fokus Kerja AI**:
    *   Menerima request dari halaman bulking.
    *   Memvalidasi mode input, isi pesan, media, dan konfigurasi campaign.
    *   Membuat record campaign.
    *   Memanggil parser dari `6.4`.
    *   Meneruskan hasilnya ke service atau job eksekusi dari `6.3`.

*   **Alur Controller yang Diinginkan**:
    1. User submit form bulking.
    2. Controller validasi request.
    3. Controller simpan media jika ada.
    4. Controller tentukan `tipe_input` manual atau Excel.
    5. Controller panggil parser agar target menjadi struktur seragam.
    6. Controller buat record `campaign` dan simpan `settings`.
    7. Controller dispatch job atau service executor bulking.
    8. Controller redirect ke halaman monitoring atau detail campaign.

*   **Yang Tidak Boleh Diurus di Step Ini**:
    *   Jangan menaruh logic parsing detail langsung di controller.
    *   Jangan menaruh logic anti-ban detail langsung di controller.
    *   Jangan membuat controller menjadi tempat seluruh business logic.

*   **Hasil Kode yang Diharapkan**:
    *   Ada controller method yang menjadi entry point submit bulking.
    *   Ada integrasi ke parser target dan executor bulking.
    *   Ada response sukses dan gagal yang jelas.
    *   Arsitektur controller tetap tipis.

*   **Checklist Review Setelah AI Selesai**:
    *   Apakah controller hanya berperan sebagai orchestrator.
    *   Apakah parser dan anti-ban dipanggil lewat service atau job terpisah.
    *   Apakah media, campaign, dan settings tersimpan dengan benar.
    *   Apakah flow selesai dengan redirect atau response yang bisa dipantau user.

### 6.6 Prompt Eksekusi AI untuk Point 6
*   **Urutan Eksekusi yang Disarankan**:
    1. `6.1` Layout halaman bulking.
    2. `6.2` Model dan migration campaign.
    3. `6.4` Parsing Excel, input manual, dan placeholder.
    4. `6.3` Logic anti-ban.
    5. `6.5` Controller menjalankan pesan bulking.
    6. Review dan hardening final.

*   **Aturan Pakai Prompt**:
    *   Jalankan satu prompt per step.
    *   Review hasil step sebelumnya sebelum lanjut ke step berikutnya.
    *   Minta AI selalu melaporkan file yang diubah, flow yang dibuat, asumsi, hal yang belum dikerjakan, dan cara test manual.

#### Prompt 6.1 - Layout Halaman Bulking
```text
Kerjakan point 6.1 pada plan-bulking.

Tujuan:
Membuat layout halaman Pesan Bulking dari static view menjadi UI yang rapi dan siap dipakai, tetapi fokus step ini hanya frontend/view dulu, belum logic kirim.

Scope kerja:
- Buat layout halaman Pesan Bulking dengan 2 area utama:
  1. area kiri untuk form builder
  2. area kanan untuk live preview
- Di area input target, buat 2 section:
  1. input beberapa nomor WhatsApp manual
  2. input dengan file Excel
- Boleh gunakan Bootstrap accordion atau pattern card/layout yang sudah ada di template Otika, pilih yang paling konsisten dengan codebase saat ini
- Tambahkan chat panel dengan kontrol yang mengikuti pola pesan tunggal
- Tambahkan input media
- Tambahkan live preview yang tampilannya sama persis dengan live preview pesan tunggal
- Prioritaskan reuse komponen pesan tunggal jika memang sudah ada

Batasan:
- Jangan implement logic backend kirim bulking
- Jangan implement parsing Excel
- Jangan implement anti-ban
- Jangan ubah flow pesan tunggal yang sudah ada
- Jangan membuat UI baru yang berbeda jauh dari pattern existing bila sudah ada komponen yang bisa dipakai ulang

Instruksi kerja:
- Cek dulu struktur halaman pesan tunggal dan komponen preview/chat panel yang sudah ada
- Reuse sebanyak mungkin bagian yang memang cocok
- Kalau ada komponen yang belum reusable, refactor secukupnya tanpa mengubah perilaku pesan tunggal

Output yang saya minta dari Anda:
- Sebutkan file yang diubah/dibuat
- Ringkas flow UI yang berhasil dibuat
- Jelaskan bagian mana yang reuse dari pesan tunggal
- Sebutkan hal yang belum dikerjakan
- Berikan cara test manual
```

#### Prompt 6.2 - Model dan Migration Campaign
```text
Kerjakan point 6.2 pada plan-bulking.

Tujuan:
Membuat struktur data campaign untuk fitur pesan bulking.

Scope kerja:
- Periksa apakah model Laravel `Campaign` dan migration table `campaigns` sudah ada
- Jika belum ada, buat model dan migration-nya tanpa menggunakan perintah artisan
- Jika sudah ada, jangan buat ulang; cukup periksa dan sempurnakan termasuk penyesuaian table pesan supaya bisa direlasikan dengan tabel campaigns
- Pastikan struktur campaign mendukung flow bulking

Struktur minimal yang harus didukung:
- id
- nama
- tipe_input
- total
- terkirim
- gagal
- status
- settings JSON
- timestamps

Penyempurnaan yang boleh ditambahkan jika memang relevan:
- started_at
- finished_at
- last_processed_at

Ketentuan:
- Pastikan `status` mendukung nilai seperti draft, queued, running, paused, resting, completed, failed
- Pastikan `settings` bisa menyimpan konfigurasi anti-ban seperti min_delay, max_delay, batch_size, rest_after_batch, retry_limit
- Atur `fillable` dan `casts` dengan benar
- Jika ada relasi ke tabel lain yang memang sudah ada, sambungkan dengan aman
- Jangan merusak migration atau data lama jika struktur sudah ada

Batasan:
- Jangan implement controller bulking dulu
- Jangan implement parser Excel dulu
- Jangan implement logic kirim dulu

Output yang saya minta dari Anda:
- File yang diubah/dibuat
- Hasil pengecekan apakah model/migration sudah ada atau belum
- Struktur final tabel campaigns
- Fillable, casts, dan relasi yang ditambahkan
- Potensi risiko migrasi jika project sudah punya data
- Cara verifikasi manual
```

#### Prompt 6.4 - Parsing Excel, Manual, dan Placeholder
```text
Kerjakan point 6.4 pada plan-bulking.

Tujuan:
Membuat satu pintu normalisasi target bulking agar input manual dan input Excel menghasilkan struktur data target yang sama.

Scope kerja:
- Buat logic parsing untuk input manual nomor WhatsApp
- Buat logic parsing untuk file Excel/CSV
- Bersihkan dan normalisasi nomor WhatsApp
- Bentuk output target yang seragam
- Bentuk payload placeholder dari data Excel
- Siapkan validasi dasar

Aturan parsing:
- Input manual bisa berupa banyak nomor
- Nomor manual dibersihkan agar siap dipakai untuk pengiriman
- File Excel/CSV dibaca dengan baris pertama sebagai header
- Harus ada kolom nomor utama, misalnya nomor/phone/whatsapp
- Kolom lain menjadi placeholder dinamis
- Contoh: kolom `nama` dipakai untuk placeholder `{nama}`

Struktur output yang diinginkan:
- tiap target minimal punya `nomor`
- bila dari Excel, target punya `placeholders`
- bila perlu, tambahkan metadata seperti `row_number` dan `source`

Validasi:
- validasi format file
- validasi header wajib
- validasi nomor
- baris invalid jangan merusak seluruh proses bila masih bisa ditangani dengan aman

Batasan:
- Jangan kirim pesan
- Jangan implement anti-ban
- Jangan buat controller submit bulking
- Jangan letakkan logic ini di controller; buat service/helper yang jelas

Instruksi kerja:
- Cek dulu codebase apakah sudah ada helper parsing nomor, helper placeholder, atau library Excel yang sudah dipakai
- Reuse jika ada
- Kalau belum ada, buat service yang rapi dan mudah dites

Output yang saya minta dari Anda:
- File yang diubah/dibuat
- Format output target final
- Cara parser manual bekerja
- Cara parser Excel bekerja
- Cara placeholder dipetakan
- Hal yang belum dikerjakan
- Cara test manual step ini
```

#### Prompt 6.3 - Logic Anti-Ban
```text
Kerjakan point 6.3 pada plan-bulking.

Tujuan:
Membuat engine eksekusi campaign bulking dengan logic anti-ban.

Asumsi:
- Struktur campaign dari point 6.2 sudah tersedia
- Parser target dari point 6.4 sudah tersedia
- Jadi step ini fokus pada executor bulking, bukan parsing input

Scope kerja:
- Buat service atau job processor khusus untuk menjalankan campaign bulking
- Terapkan random delay antar pesan
- Terapkan batching
- Terapkan resting/cooldown setelah satu batch selesai
- Update statistik campaign dan status campaign selama proses berjalan

Konfigurasi yang harus dibaca dari campaign settings:
- min_delay
- max_delay
- batch_size
- rest_after_batch
- retry_limit

Status yang diharapkan:
- queued
- running
- resting
- completed
- failed

Ketentuan penting:
- Logic anti-ban hanya untuk bulking
- Jangan ubah flow pesan tunggal
- Jika perlu reuse code kirim WhatsApp existing, reuse hanya layer pengiriman paling bawah
- Jangan parsing Excel mentah di step ini
- Jangan menaruh logic ini di controller

Perilaku yang diinginkan:
- menerima campaign dan daftar target yang sudah dinormalisasi
- mengirim pesan per target dengan delay acak
- setiap mencapai batch_size, masuk status resting lalu lanjut lagi
- update nilai total, terkirim, gagal, dan status campaign secara konsisten

Output yang saya minta dari Anda:
- File yang diubah/dibuat
- Penjelasan arsitektur executor anti-ban
- Dari mana konfigurasi dibaca
- Bagaimana status campaign berubah selama proses
- Risiko regresi terhadap pesan tunggal
- Cara test manual step ini
```

#### Prompt 6.5 - Controller Menjalankan Pesan Bulking
```text
Kerjakan point 6.5 pada plan-bulking.

Tujuan:
Menyambungkan halaman bulking agar benar-benar bisa submit dan memulai campaign dengan memakai hasil point 6.2, 6.4, dan 6.3.

Asumsi:
- Layout/view bulking sudah ada
- Model dan migration campaign sudah siap
- Parser target sudah siap
- Executor anti-ban sudah siap

Scope kerja:
- Buat atau sempurnakan controller method untuk submit pesan bulking
- Validasi request
- Tangani mode input manual atau Excel
- Tangani isi pesan dan media
- Simpan campaign
- Simpan settings campaign
- Panggil parser target
- Dispatch executor bulking
- Redirect atau response ke halaman monitoring/detail campaign

Alur yang diinginkan:
1. user submit form bulking
2. controller validasi request
3. media disimpan bila ada
4. controller tentukan tipe_input
5. controller panggil parser dari point 6.4
6. controller buat record campaign
7. controller simpan settings anti-ban
8. controller dispatch job/service executor dari point 6.3
9. controller kembalikan response sukses

Batasan:
- Jangan pindahkan business logic parsing detail ke controller
- Jangan pindahkan anti-ban detail ke controller
- Controller harus tetap tipis dan berperan sebagai orchestrator
- Jangan merusak flow pesan tunggal

Output yang saya minta dari Anda:
- File yang diubah/dibuat
- Route/controller method yang ditambahkan atau diubah
- Flow submit bulking dari request sampai dispatch job
- Cara media ditangani
- Hal yang masih menjadi asumsi
- Cara test manual end-to-end
```

#### Prompt 6.Final - Review dan Hardening Point 6
```text
Lakukan review dan hardening untuk seluruh implementasi point 6 pada plan-bulking.

Yang harus diperiksa:
- konsistensi flow 6.1, 6.2, 6.4, 6.3, 6.5
- apakah controller tetap tipis
- apakah parser terpisah dengan baik
- apakah anti-ban hanya aktif untuk bulking
- apakah flow pesan tunggal tetap aman
- apakah campaign status dan statistik ter-update dengan benar
- apakah handling media, manual input, dan Excel sudah konsisten
- apakah ada potensi bug, race condition, atau regresi

Yang saya minta:
- temuan review, prioritaskan bug/risk dulu
- file dan area yang paling berisiko
- usulan perbaikan kecil yang perlu dilakukan
- jika perlu, langsung implementasikan perbaikan yang aman dan jelaskan apa yang diubah
- berikan checklist test manual final
```
