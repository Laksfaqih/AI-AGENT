# 🇮🇩 Laks AI AGENT - CARA MENJALANKAN

## ⚡ Quick Start (Bahasa Indonesia)

### 1️⃣ Buka Terminal

```bash
cd /Users/thecodeindonesia/Agent-Ai
```

### 2️⃣ Jalankan Server

```bash
php -S localhost:8123 -t public
```

### 3️⃣ Buka Browser

```
http://localhost:8123/chat.html
```

### 4️⃣ Login

- **Username**: `admin`
- **Password**: `admin123`

### 5️⃣ Autentikasi GitHub (Jika Belum)

**Buka terminal baru** (jangan tutup server):

```bash
cd /Users/thecodeindonesia/Agent-Ai
php auth.php
```

Ikuti instruksi yang muncul.

---

## ✨ Apa yang Akan Anda Lihat?

### Tampilan Baru yang Indah

- 🎨 Background gradient ungu-biru
- ✨ Efek glassmorphism (kaca buram)
- 💫 Animasi smooth dan modern
- 🌈 Warna-warna vibrant
- 📱 Responsive di semua device

### Fitur Lengkap

- 🤖 Multi AI Models (GPT-4, Claude, Gemini)
- 💬 Chat pintar dengan context
- 📊 Visualisasi data (chart, map, tabel)
- 🗄️ Query database dengan bahasa natural
- 💾 History conversation otomatis tersimpan
- 🔐 Autentikasi GitHub yang aman

---

## 📚 Dokumentasi

### Bahasa Indonesia

**PANDUAN.md** ← **BACA INI!** 📖

- Panduan lengkap bahasa Indonesia
- Tutorial step-by-step
- Troubleshooting
- Tips & trik

---

## 🎯 Contoh Penggunaan

### Chat Biasa

```
"Halo! Perkenalkan dirimu"
"Jelaskan apa itu REST API"
"Buatkan fungsi PHP untuk validasi email"
```

### Query Database (jika sudah setup)

```
"Tampilkan semua user"
"Berapa total penjualan hari ini?"
"Tampilkan customer di peta"
```

---

## ⚙️ Settings

Klik ikon **⚙️** di kanan bawah untuk:

- 🤖 Pilih AI model
- 🌡️ Atur temperature (kreativitas)
- 📏 Atur max tokens (panjang jawaban)
- 💾 Export chat
- 🗑️ Clear history

---

## 🗄️ Tambah Database

1. Klik menu user **(👤)** di kanan atas
2. Pilih **"Databases"**
3. Klik **"Add New Connection"**
4. Isi detail database:
   - Type: PostgreSQL/MySQL
   - Host: localhost
   - Port: 5432/3306
   - Database, username, password
5. Klik **"Test Connection"**
6. Jika berhasil, klik **"Save"**

Setelah itu bisa langsung query:

```
"Tampilkan data dari tabel users"
```

---

## 🆘 Masalah?

### Port Sudah Dipakai

```bash
php -S localhost:8124 -t public
```

Lalu buka: http://localhost:8124/chat.html

### Reset Database

```bash
php migrate.php fresh
php migrate.php up
```

### Autentikasi Ulang

```bash
php auth.php
```

### Halaman Blank

1. Clear cache browser (Ctrl+Shift+Del)
2. Refresh (F5)
3. Coba browser lain

---

## 📁 File Penting

```
Agent-Ai/
├── PANDUAN.md          ← Baca ini (Bahasa Indonesia)
├── README.md           ← Full docs (English)
├── START.md            ← Quick start (English)
├── QUICKSTART.md       ← Installation (English)
├── COMPARISON.md       ← Perbandingan detail
├── public/
│   ├── chat.html       ← UI baru (gradient purple)
│   └── index.php       ← API backend
├── src/                ← Source code PHP
├── storage/            ← Database SQLite
└── vendor/             ← Dependencies
```

---

## 🚀 Siap Digunakan!

Project sudah 100% siap:

- ✅ Dependencies installed
- ✅ Database migrated
- ✅ UI completely redesigned
- ✅ Backend fully functional
- ✅ Dokumentasi lengkap (ID & EN)

**Tinggal jalankan dan nikmati!**

---

## 💡 Tips Cepat

### Untuk Coding

- Model: GPT-4o atau Claude Sonnet 4.5
- Temperature: 0 - 0.1
- Prompt spesifik dengan detail teknologi

### Untuk Brainstorming

- Model: Claude Opus 4 atau GPT-5
- Temperature: 0.7 - 1.0
- Tanya open-ended questions

### Untuk Database

- Tambah koneksi dulu
- Tanya dengan natural language
- AI auto-generate SQL dan execute
- Hasil ditampilkan dalam chart/map/table

---

## 📞 Bantuan Lebih Lanjut

1. **Baca PANDUAN.md** untuk tutorial lengkap
2. **Cek troubleshooting** untuk solusi masalah
3. **Lihat contoh** di QUICKSTART.md

---

**Selamat Menggunakan Indonesia AI Agent! 🎉**

Dibuat dengan 💻 dan ☕ oleh Developer Indonesia

_Teknologi AI untuk Indonesia yang Lebih Maju_

---

**Versi**: 1.0.0  
**Status**: Production Ready ✅  
**Tanggal**: 27 November 2025
