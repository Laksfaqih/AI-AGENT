# 🛠️ Troubleshooting - Indonesia AI Agent

Panduan lengkap mengatasi error yang umum terjadi pada aplikasi AI.

---

## ⏰ **Error: Rate Limit Exceeded (429)**

### Gejala:
```
❌ Error: Internal server error
Copilot API request failed: 429 Too Many Requests
quota exceeded
```

### Penyebab:
- GitHub Copilot API memiliki batas request per menit/jam
- Terlalu banyak request dalam waktu singkat
- Quota API sudah habis

### Solusi:

#### 1. **Tunggu Beberapa Menit** ⏳
Rate limit biasanya reset dalam 5-10 menit.

```bash
# Tunggu 5-10 menit, lalu coba lagi
```

#### 2. **Refresh GitHub Token** 🔑
```bash
cd /Users/thecodeindonesia/Agent-Ai
php auth.php
```

Ikuti instruksi di terminal:
- Buka URL yang diberikan
- Masukkan kode verifikasi
- Login dengan akun GitHub Anda

#### 3. **Cek GitHub Copilot Status** 🔍
Pastikan Copilot aktif di akun GitHub Anda:
- Buka: https://github.com/settings/copilot
- Pastikan subscription masih aktif
- Cek quota/limits yang tersedia

#### 4. **Gunakan Model AI Berbeda** 🤖
Beberapa model memiliki rate limit yang berbeda:
- Coba model gratis lainnya: `gpt-4o`, `gpt-5-mini`
- Atau model paid dengan quota lebih besar

---

## 🔐 **Error: GitHub token not found**

### Gejala:
```
❌ Error: GitHub token not found
Please authenticate with GitHub first
```

### Solusi:

```bash
# 1. Jalankan autentikasi GitHub
php auth.php

# 2. Ikuti instruksi di terminal
# 3. Login ke GitHub
# 4. Refresh halaman browser
```

---

## 🗄️ **Error: Database error**

### Gejala:
```
Database connection error
SQLite database not found
```

### Solusi:

```bash
# Reset database
rm storage/database.sqlite

# Jalankan migrations ulang
php migrate.php

# Restart server
php -S localhost:8123 -t public
```

---

## 🌐 **Error: Port already in use**

### Gejala:
```
Address already in use
Failed to bind to 0.0.0.0:8123
```

### Solusi:

#### Opsi 1: Matikan process yang menggunakan port
```bash
# Cari process yang menggunakan port 8123
lsof -i :8123

# Matikan process (ganti PID dengan nomor yang ditemukan)
kill -9 PID
```

#### Opsi 2: Gunakan port lain
```bash
# Gunakan port 8124
php -S localhost:8124 -t public

# Akses di browser
open http://localhost:8124/chat.html
```

---

## 🔌 **Error: Connection refused**

### Gejala:
```
Failed to connect to localhost:8123
Connection refused
```

### Solusi:

```bash
# Pastikan server berjalan
ps aux | grep "php.*8123"

# Jika tidak ada output, jalankan server:
php -S localhost:8123 -t public
```

---

## 🔒 **Error: Session expired**

### Gejala:
```
Not authenticated
Session token invalid
Please login first
```

### Solusi:

1. **Logout dan Login ulang**
   - Klik tombol Logout di header
   - Login dengan `admin` / `admin123`

2. **Clear browser cache**
   - Tekan `Cmd+Shift+R` (Mac) atau `Ctrl+Shift+R` (Windows)
   - Clear localStorage di DevTools Console:
     ```javascript
     localStorage.clear()
     location.reload()
     ```

---

## 📦 **Error: Composer dependencies not found**

### Gejala:
```
Class 'GuzzleHttp\Client' not found
Composer autoload not found
```

### Solusi:

```bash
# Install dependencies
composer install

# Jika composer belum terinstall:
brew install composer  # Mac
# atau download dari: https://getcomposer.org
```

---

## 🐘 **Error: PHP version too old**

### Gejala:
```
PHP Fatal error: version 8.1 or higher required
```

### Solusi:

```bash
# Cek versi PHP saat ini
php -v

# Install PHP 8.2+ (Mac dengan Homebrew)
brew install php@8.2
brew link php@8.2

# Verifikasi instalasi
php -v
```

---

## 🔍 **Error: Vendor autoload not found**

### Gejala:
```
Fatal error: require_once(): Failed opening required '../vendor/autoload.php'
```

### Solusi:

```bash
# Pastikan di root directory project
cd /Users/thecodeindonesia/Agent-Ai

# Install dependencies
composer install

# Verifikasi
ls -la vendor/autoload.php
```

---

## 🎨 **Error: Themes not loading**

### Gejala:
- Theme picker tidak muncul
- Warna tema tidak berubah

### Solusi:

```bash
# Clear browser cache
# Tekan Cmd+Shift+R atau Ctrl+Shift+R

# Atau clear localStorage
# Di DevTools Console:
localStorage.clear()
location.reload()
```

---

## 📊 **Error: Chart not displaying**

### Gejala:
- Query berhasil tapi chart tidak muncul
- "Canvas not found" error

### Solusi:

1. **Periksa data query**
   - Pastikan query mengembalikan data numerik
   - Minimal 2 kolom (label + value)

2. **Refresh halaman**
   ```bash
   # Tekan F5 atau Cmd+R
   ```

3. **Periksa Console untuk error**
   - Buka DevTools (F12)
   - Lihat tab Console untuk error message

---

## 🗺️ **Error: Map not displaying**

### Gejala:
- Query dengan data lokasi tidak menampilkan map
- Map tiles tidak load

### Solusi:

1. **Periksa data query**
   - Pastikan ada kolom: `city`, `province`, `address`, atau `region`
   - Data lokasi harus berisi nama kota/alamat yang valid

2. **Periksa koneksi internet**
   - Map tiles membutuhkan koneksi internet
   - Coba load ulang halaman

3. **Clear cache dan reload**
   ```bash
   # Tekan Cmd+Shift+R atau Ctrl+Shift+R
   ```

---

## 🔧 **Debug Mode**

Untuk debugging lebih lanjut:

### 1. Enable PHP Error Display
Edit `public/index.php`:
```php
// Tambahkan di bagian atas
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### 2. Check PHP Logs
```bash
# Lihat error logs
tail -f /tmp/php_errors.log

# Atau check debug file
tail -f /tmp/query-debug.txt
```

### 3. Browser DevTools
```javascript
// Buka Console (F12) dan jalankan:
console.log('Session Token:', localStorage.getItem('session_token'));
console.log('Conversation History:', conversationHistory);
```

---

## 📞 **Bantuan Lebih Lanjut**

Jika masalah masih belum teratasi:

1. **Check Documentation**
   - [PANDUAN.md](PANDUAN.md) - Panduan lengkap
   - [MULAI.md](MULAI.md) - Quick start guide
   - [DOCKER.md](DOCKER.md) - Docker deployment

2. **Check GitHub Issues**
   - Cari issue yang mirip
   - Buat issue baru dengan detail error

3. **Contact Support**
   - Email: support@indonesiasatu.com
   - Website: https://indonesiasatu.com

---

## ✅ **Checklist Sebelum Troubleshooting**

Sebelum mencoba solusi di atas, pastikan:

- [ ] PHP 8.1+ terinstall (`php -v`)
- [ ] Composer dependencies terinstall (`ls vendor/`)
- [ ] Database exists (`ls storage/database.sqlite`)
- [ ] Server berjalan (`ps aux | grep php`)
- [ ] Browser modern (Chrome, Firefox, Safari latest)
- [ ] Internet connection stabil
- [ ] GitHub Copilot subscription aktif

---

<div align="center">

**🛠️ Happy Troubleshooting!**

Dibuat dengan ❤️ untuk Pengembang Indonesia

[Kembali ke README](README.md)

</div>
