# 🚀 Panduan Lengkap - Indonesia AI Agent

## Selamat Datang! 👋

Project **Indonesia AI Agent** Anda sudah siap digunakan dengan tampilan yang **benar-benar berbeda** dari TCD-AI-Chat.

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi](#instalasi)
3. [Menjalankan Aplikasi](#menjalankan-aplikasi)
4. [Login Pertama Kali](#login-pertama-kali)
5. [Autentikasi GitHub](#autentikasi-github)
6. [Cara Menggunakan](#cara-menggunakan)
7. [Menambah Database](#menambah-database)
8. [Troubleshooting](#troubleshooting)
9. [Tips & Trik](#tips--trik)

---

## 📌 Persyaratan Sistem

Pastikan komputer Anda memiliki:

- ✅ **PHP 8.1** atau lebih tinggi
- ✅ **Composer** (package manager PHP)
- ✅ **Akun GitHub** (gratis)
- ✅ **Browser modern** (Chrome, Firefox, Safari, Edge)

### Cek Versi PHP
```bash
php -v
```

Harus menampilkan versi 8.1 atau lebih tinggi.

### Cek Composer
```bash
composer --version
```

Jika belum terinstall, download di: https://getcomposer.org/

---

## 🔧 Instalasi

### Langkah 1: Buka Terminal
Buka terminal/command prompt Anda.

### Langkah 2: Masuk ke Folder Project
```bash
cd /Users/thecodeindonesia/Agent-Ai
```

### Langkah 3: Install Dependencies (SUDAH SELESAI ✅)
Dependencies sudah terinstall. Tapi jika perlu install ulang:
```bash
composer install
```

### Langkah 4: Setup Database (SUDAH SELESAI ✅)
Database sudah dibuat. Tapi jika perlu reset:
```bash
php migrate.php up
```

---

## 🚀 Menjalankan Aplikasi

### Cara Termudah (3 Langkah)

#### 1️⃣ Jalankan Server
```bash
cd /Users/thecodeindonesia/Agent-Ai
php -S localhost:8123 -t public
```

Anda akan melihat:
```
PHP 8.1.0 Development Server (http://localhost:8123) started
```

**JANGAN TUTUP TERMINAL INI!** Biarkan tetap terbuka.

#### 2️⃣ Buka Browser
Buka browser favorit Anda dan kunjungi:
```
http://localhost:8123/chat.html
```

#### 3️⃣ Login
Gunakan kredensial default:
- **Username**: `admin`
- **Password**: `admin123`

**Selesai!** Anda sudah bisa menggunakan AI Agent! 🎉

---

## 🔑 Login Pertama Kali

### Apa yang Anda Lihat?

Halaman login dengan:
- 🎨 Background gradient ungu-biru yang indah
- ⚪ Form login putih bersih
- ✨ Animasi smooth saat hover

### Langkah Login

1. **Masukkan username**: `admin`
2. **Masukkan password**: `admin123`
3. **Klik tombol "Login"**

Jika berhasil:
- ✅ Anda akan masuk ke halaman chat
- ✅ Melihat interface yang cantik dengan gradient
- ✅ Ada pesan sambutan dari AI

### ⚠️ Catatan Keamanan

**PENTING**: Untuk production, ubah password default!

Cara mengubah:
1. Buka terminal baru
2. Jalankan:
```bash
cd /Users/thecodeindonesia/Agent-Ai
sqlite3 storage/database.sqlite
```
3. Ubah password (perlu di-hash dulu)

---

## 🔐 Autentikasi GitHub

Sebelum bisa menggunakan AI, Anda perlu autentikasi dengan GitHub.

### Mengapa Perlu GitHub?

AI Agent menggunakan GitHub Copilot API untuk mengakses model AI.

### Cara Autentikasi

#### Opsi 1: Via Command Line (Rekomendasi)

1. **Buka terminal baru** (jangan tutup server)

2. **Jalankan perintah**:
```bash
cd /Users/thecodeindonesia/Agent-Ai
php auth.php
```

3. **Ikuti instruksi**:
```
Visit: https://github.com/login/device
Enter code: XXXX-XXXX
```

4. **Buka URL tersebut** di browser

5. **Masukkan kode** yang ditampilkan

6. **Klik "Authorize"**

7. **Tunggu konfirmasi**:
```
✓ Authentication successful!
Access Token: ghu_xxxxxxxxxxxx...
Token saved to database
```

#### Opsi 2: Via Web Interface

1. **Setelah login** ke aplikasi
2. **Klik menu user** (👤) di kanan atas
3. **Lihat status**: "Please authenticate with GitHub"
4. **Klik "Authenticate"**
5. **Ikuti langkah yang sama**

### Verifikasi Autentikasi

Di menu user (👤), Anda akan melihat:
- ✅ **"Ready to use AI Agent"** (jika berhasil)
- ❌ **"Please authenticate"** (jika belum)

---

## 💬 Cara Menggunakan

### Interface Utama

Setelah login, Anda akan melihat:

#### 1. **Header (Atas)**
- 🤖 Logo "Indonesia AI Agent"
- 👤 Menu User (klik untuk opsi)

#### 2. **Area Chat (Tengah)**
- 💬 Pesan-pesan chat
- 📊 Visualisasi data (jika ada)
- 🗺️ Peta (jika ada)

#### 3. **Input Box (Bawah)**
- ✏️ Kotak untuk mengetik pesan
- ✨ Tombol "Send"
- ⚙️ Tombol Settings

#### 4. **Footer**
- 📊 Statistik (jumlah pesan, model, dll)

### Mengirim Pesan

#### Cara 1: Dengan Tombol
1. Ketik pesan di kotak input
2. Klik tombol **"Send ✨"**

#### Cara 2: Dengan Keyboard
1. Ketik pesan di kotak input
2. Tekan **Enter** di keyboard

### Contoh Pertanyaan

#### 🗣️ Pertanyaan Umum
```
"Halo! Perkenalkan dirimu"
"Apa yang bisa kamu lakukan?"
"Jelaskan apa itu REST API"
"Bagaimana cara belajar pemrograman?"
```

#### 💻 Bantuan Coding
```
"Buatkan fungsi PHP untuk validasi email"
"Bagaimana cara membuat API dengan Express.js?"
"Jelaskan perbedaan let, const, dan var di JavaScript"
"Buatkan contoh CRUD dengan Laravel"
```

#### 🎨 Permintaan Kreatif
```
"Buatkan nama untuk aplikasi chat"
"Berikan ide fitur untuk aplikasi e-commerce"
"Sarankan tech stack untuk website portfolio"
```

#### 🗄️ Query Database (jika sudah setup)
```
"Tampilkan semua user di database"
"Berapa total penjualan bulan ini?"
"Tampilkan 10 produk terlaris"
"Tampilkan lokasi customer di peta"
```

---

## 🗄️ Menambah Database

AI Agent bisa query database Anda dengan bahasa natural!

### Database yang Didukung
- ✅ PostgreSQL
- ✅ MySQL

### Langkah-Langkah

#### 1️⃣ Buka Menu Database
- Klik **menu user (👤)** di kanan atas
- Pilih **"Databases"**

#### 2️⃣ Tambah Koneksi Baru
- Klik tombol **"Add New Connection"**

#### 3️⃣ Isi Form

**Database Type**:
- Pilih: PostgreSQL atau MySQL

**Connection Name**:
- Contoh: "Database Produksi"
- Contoh: "DB Testing"

**Host**:
- Untuk lokal: `localhost`
- Untuk remote: IP address server

**Port**:
- PostgreSQL: `5432` (default)
- MySQL: `3306` (default)

**Database Name**:
- Nama database Anda
- Contoh: `myapp_db`

**Username**:
- User database
- Contoh: `postgres` atau `root`

**Password**:
- Password database
- Kosongkan jika tidak ada

#### 4️⃣ Test Koneksi
- Klik **"🔌 Test Connection"**
- Tunggu hasilnya:
  - ✅ **Hijau**: Berhasil terhubung
  - ❌ **Merah**: Ada error (cek kredensial)

#### 5️⃣ Simpan
- Jika test berhasil, klik **"💾 Save Connection"**

### Menggunakan Database

Setelah tersimpan, Anda bisa langsung tanya:

```
"Tampilkan semua data dari tabel users"
"Berapa jumlah order hari ini?"
"Siapa customer dengan pembelian terbesar?"
"Tampilkan distribusi customer per kota di peta"
```

AI akan:
1. Generate SQL query otomatis
2. Execute query ke database
3. Tampilkan hasil dalam format yang bagus (tabel/chart/map)

---

## ⚙️ Settings & Konfigurasi

### Membuka Settings

Klik tombol **⚙️** di kanan bawah (sebelah input box)

### Opsi yang Tersedia

#### 1. **AI Model**
Pilih model AI yang ingin digunakan:

**Model Gratis (0x cost)**:
- GPT-4o
- GPT-4.1
- GPT-5 Mini
- Grok Code Fast

**Model Berbayar (1x cost)**:
- Claude Sonnet 4.5
- Claude Opus 4
- Gemini 2.5 Pro
- GPT-5

#### 2. **Temperature** (Kreativitas)
Atur tingkat kreativitas AI:
- **0**: Sangat fokus, deterministik
- **0.1**: Presisi tinggi (default)
- **0.3**: Seimbang
- **0.7**: Kreatif
- **1.0**: Sangat kreatif

**Kapan gunakan**:
- Code generation: 0 - 0.1
- Pertanyaan umum: 0.3
- Brainstorming: 0.7 - 1.0

#### 3. **Max Tokens** (Panjang Respons)
Batasi panjang jawaban:
- **500**: Jawaban singkat
- **1000**: Jawaban sedang
- **2000**: Jawaban panjang
- **4096**: Jawaban sangat panjang (default)

#### 4. **Actions**

**💾 Export**:
- Download riwayat chat sebagai file JSON
- Berguna untuk backup atau analisis

**🗑️ Clear**:
- Hapus semua pesan chat
- Mulai percakapan baru
- **TIDAK BISA DIBATALKAN!**

---

## 📊 Fitur Visualisasi

### Tabel Data

Ketika AI menampilkan data dalam bentuk tabel:
- 📋 Data tersusun rapi dalam kolom dan baris
- 🔍 Mudah dibaca
- 📄 Bisa di-scroll jika banyak

### Chart (Grafik)

AI otomatis membuat chart untuk data numerik:

**Jenis Chart**:
- **Bar Chart**: Perbandingan kategori
- **Line Chart**: Trend waktu
- **Pie Chart**: Proporsi/persentase
- **Doughnut**: Seperti pie, tapi ada lubang

**Kontrol**:
- Klik tombol jenis chart untuk ganti
- Hover untuk lihat detail data

### Map (Peta)

Untuk data lokasi, AI tampilkan di peta:
- 🗺️ Peta interaktif dengan OpenStreetMap
- 📍 Pin/marker di setiap lokasi
- 🔍 Zoom in/out
- 🖱️ Drag untuk geser peta
- 💬 Klik marker untuk info detail

**Contoh Query**:
```
"Tampilkan lokasi semua customer di peta"
"Dimana saja cabang toko kita?"
"Tampilkan sebaran pengiriman hari ini"
```

---

## 🎨 Tentang Tampilan Baru

### Apa yang Berbeda?

#### Warna & Tema
- 🌈 **Background**: Gradient ungu-biru yang indah
- ✨ **Glassmorphism**: Efek kaca buram/frosted glass
- 💫 **Animasi**: Smooth transitions
- 🎨 **Aksen**: Warna vibrant dan energik

#### Komponen

**Tombol**:
- Bentuk: Rounded/bulat
- Warna: Gradient ungu
- Efek: Naik saat hover

**Input Box**:
- Background: Putih semi-transparan
- Border: Blur effect
- Fokus: Glow biru

**Pesan Chat**:
- User: Putih dengan shadow
- AI: Gelap dengan glassmorphism
- Rounded corners: Lebih smooth

#### Font & Typography
- **Font**: Inter (modern, clean)
- **Header**: Bold 700
- **Body**: Regular 400-500
- **Spacing**: Lebih lapang

### Kenapa Redesign?

Tujuan redesign:
1. ✨ Lebih modern dan menarik
2. 🎨 Membedakan dari TCD-AI-Chat
3. 💫 User experience lebih baik
4. 🌟 Visual identity yang unik

**Backend tetap 100% sama!**

---

## 🔧 Troubleshooting

### Masalah Umum & Solusi

#### 1. **Port Sudah Digunakan**

**Error**:
```
Address already in use
```

**Solusi**:
Gunakan port lain:
```bash
php -S localhost:8124 -t public
```

Lalu buka:
```
http://localhost:8124/chat.html
```

#### 2. **Autentikasi GitHub Gagal**

**Error**:
```
Authentication failed
```

**Solusi**:
1. Coba lagi: `php auth.php`
2. Pastikan kode diinput dengan benar
3. Pastikan GitHub tidak memblokir
4. Cek koneksi internet

#### 3. **Database Error**

**Error**:
```
Table not found
```

**Solusi**:
Reset database:
```bash
php migrate.php fresh
php migrate.php up
```

**⚠️ HATI-HATI**: Ini akan hapus semua data!

#### 4. **Halaman Blank/Putih**

**Solusi**:
1. Clear cache browser (Ctrl+Shift+Delete)
2. Coba browser lain
3. Cek console browser (F12) untuk error
4. Pastikan JavaScript enabled

#### 5. **AI Tidak Merespons**

**Cek**:
1. Apakah sudah autentikasi GitHub?
2. Cek menu user (👤) → harus "Ready to use AI Agent"
3. Jika belum, jalankan `php auth.php`

#### 6. **Database Connection Failed**

**Cek**:
1. Host, port, username, password benar?
2. Database server running?
3. Firewall tidak memblokir?
4. User punya akses ke database?

**Test manual**:
```bash
# PostgreSQL
psql -h localhost -U username -d database

# MySQL
mysql -h localhost -u username -p database
```

#### 7. **Composer Dependencies Error**

**Solusi**:
```bash
rm -rf vendor composer.lock
composer install
```

---

## 💡 Tips & Trik

### 1. **Gunakan Model yang Tepat**

**Untuk Coding**:
- GPT-4o (gratis, bagus untuk code)
- Claude Sonnet 4.5 (sangat bagus, berbayar)

**Untuk Pertanyaan Umum**:
- GPT-4.1 (gratis)
- GPT-5 Mini (gratis, cepat)

**Untuk Tugas Kompleks**:
- Claude Opus 4 (berbayar, paling pintar)
- GPT-5 (berbayar)

### 2. **Temperature yang Tepat**

**Code Generation**: 0 - 0.1
- Konsisten, deterministik
- Tidak ada variasi aneh

**Debugging**: 0.1 - 0.3
- Fokus pada masalah
- Solusi yang akurat

**Brainstorming**: 0.7 - 1.0
- Ide-ide kreatif
- Variasi jawaban

### 3. **Prompt yang Efektif**

**❌ Buruk**:
```
"buatkan website"
```

**✅ Bagus**:
```
"Buatkan struktur HTML untuk website portfolio dengan sections: header, about, projects, contact. Gunakan CSS modern dengan flexbox."
```

**Tips**:
- Spesifik
- Jelaskan konteks
- Sebutkan teknologi
- Berikan contoh jika perlu

### 4. **Query Database**

**❌ Buruk**:
```
"data customer"
```

**✅ Bagus**:
```
"Tampilkan nama, email, dan total pembelian dari 10 customer dengan transaksi terbanyak"
```

**Tips**:
- Jelaskan data apa yang ingin dilihat
- Sebutkan jumlah/limit
- Jelaskan pengurutan/sorting
- Jelaskan filter jika ada

### 5. **Visualisasi Data**

**Untuk Chart**:
```
"Tampilkan penjualan per bulan tahun ini dalam bentuk grafik"
```

**Untuk Map**:
```
"Tampilkan lokasi semua customer di peta"
```

**Untuk Tabel**:
```
"Tampilkan semua detail produk dalam tabel"
```

### 6. **Export Chat**

Gunakan untuk:
- Backup percakapan penting
- Share dengan tim
- Analisis nanti
- Dokumentasi

**Cara**:
1. Klik ⚙️ Settings
2. Klik 💾 Export
3. File JSON akan terdownload

### 7. **Shortcut Keyboard**

- **Enter**: Kirim pesan
- **Ctrl + Shift + Delete**: Clear cache browser
- **F12**: Buka developer tools
- **F5**: Refresh halaman

---

## 🚀 Langkah Selanjutnya

### Level Pemula
1. ✅ Coba berbagai pertanyaan
2. ✅ Eksplorasi semua model AI
3. ✅ Ubah temperature dan lihat perbedaannya
4. ✅ Export dan lihat format JSON

### Level Menengah
1. ✅ Tambah koneksi database
2. ✅ Coba query dengan natural language
3. ✅ Lihat visualisasi chart dan map
4. ✅ Customize warna di `public/chat.html`

### Level Lanjut
1. ✅ Deploy ke production server
2. ✅ Integrasikan dengan aplikasi lain
3. ✅ Tambah fitur custom
4. ✅ Setup monitoring dan logging

---

## 📚 Dokumentasi Tambahan

### File-file Dokumentasi

1. **START.md** (English)
   - Quick start guide
   - Basic instructions

2. **README.md** (English)
   - Full documentation
   - Feature list
   - API endpoints

3. **QUICKSTART.md** (English)
   - Quick installation steps
   - First steps

4. **COMPARISON.md** (English)
   - Perbandingan dengan TCD-AI-Chat
   - Detail perbedaan UI/UX
   - Code statistics

5. **PANDUAN.md** (Indonesia) ← File ini
   - Panduan lengkap bahasa Indonesia
   - Tutorial step-by-step

---

## ❓ FAQ (Pertanyaan Sering Ditanyakan)

### Q: Apakah gratis?
**A**: Backend gratis. Beberapa model AI gratis, beberapa berbayar. Lihat pricing di settings.

### Q: Apakah data aman?
**A**: Ya, data tersimpan lokal di `storage/database.sqlite`. Tidak ada data yang dikirim ke server lain kecuali ke AI provider.

### Q: Bisa ganti bahasa?
**A**: AI bisa merespons dalam bahasa apapun. Tanya dalam bahasa Indonesia, dia jawab dalam bahasa Indonesia.

### Q: Berapa batas pesan?
**A**: Tidak ada batas. Semua tersimpan di database lokal.

### Q: Bisa diakses dari komputer lain?
**A**: Tidak secara default (localhost). Untuk akses dari komputer lain, perlu deploy ke server atau ganti localhost dengan IP address.

### Q: Bisa offline?
**A**: Backend bisa offline, tapi AI perlu internet untuk akses model.

### Q: Bedanya dengan TCD-AI-Chat?
**A**: 100% backend sama, hanya tampilan berbeda. Lihat COMPARISON.md untuk detail.

### Q: Bisa custom warna?
**A**: Ya! Edit file `public/chat.html` bagian `<style>`.

### Q: Perlu bayar GitHub?
**A**: Tidak. Akun GitHub gratis sudah cukup.

### Q: Database wajib?
**A**: Tidak. Database opsional. Bisa digunakan untuk chat biasa tanpa database.

---

## 🎯 Kesimpulan

Selamat! Anda sekarang tahu cara:
- ✅ Menjalankan Indonesia AI Agent
- ✅ Login dan autentikasi
- ✅ Menggunakan AI untuk berbagai keperluan
- ✅ Menambah dan query database
- ✅ Kustomisasi settings
- ✅ Troubleshooting masalah umum

### Yang Membuat Project Ini Spesial

1. 🎨 **Tampilan Modern**: Gradient, glassmorphism, animasi smooth
2. 🤖 **AI Powerful**: Multi-model, smart context
3. 📊 **Visualisasi Cerdas**: Auto-generate chart/map
4. 🗄️ **Natural Language SQL**: Query database pakai bahasa natural
5. 💯 **Production Ready**: Error handling, session management

### Mulai Sekarang!

```bash
cd /Users/thecodeindonesia/Agent-Ai
php -S localhost:8123 -t public
```

Buka: http://localhost:8123/chat.html

**Selamat Mencoba! 🚀**

---

**Dibuat dengan 💻 dan ☕ oleh Developer Indonesia**

*Teknologi AI untuk Indonesia yang Lebih Maju*

---

## 📞 Butuh Bantuan?

Jika ada pertanyaan atau masalah:

1. 📖 Baca dokumentasi lengkap: README.md
2. 🔍 Cek troubleshooting di atas
3. 💡 Lihat contoh di QUICKSTART.md
4. 📊 Bandingkan dengan TCD-AI-Chat: COMPARISON.md

---

**Terakhir diupdate**: 27 November 2025

**Versi**: 1.0.0

**Status**: Production Ready ✅
