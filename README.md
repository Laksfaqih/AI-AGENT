# 🇮🇩 Indonesia AI Agent

> **Agen AI Modern dengan Antarmuka Glassmorphism untuk Pengembang Indonesia**

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Ready-brightgreen.svg)](https://docker.com)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## 📖 Tentang Proyek

**Laks AI Agent** adalah aplikasi chat AI berbasis PHP dengan tampilan modern glassmorphism. Dibangun khusus untuk pengembang Indonesia dengan dokumentasi lengkap dalam Bahasa Indonesia.

### ✨ Fitur Utama

- 🎨 **8 Tema Warna** - Kustomisasi sesuai selera
- 🤖 **Multi AI Models** - GPT-4, Claude, Gemini
- 💬 **Chat Pintar** - Dengan context awareness
- 📊 **Visualisasi Data** - Chart, map, tabel interaktif
- 🗄️ **Database Query** - Natural language ke SQL
- 💾 **History Otomatis** - Semua percakapan tersimpan
- 🐳 **Docker Ready** - Deploy dalam 5 menit
- 🚀 **CI/CD Pipeline** - Automated testing & deployment

---

## 🚀 Quick Start

### Cara Paling Cepat (Docker)

```bash
# 1. Clone repository
git clone <repository-url>
cd Agent-Ai

# 2. Jalankan dengan Docker
docker-compose up -d

# 3. Buka browser
open http://localhost:8123/chat.html

# Login: admin / admin123
```

### Cara Manual (Tanpa Docker)

```bash
# 1. Install dependencies
composer install

# 2. Setup database
php migrate.php

# 3. Jalankan server
php -S localhost:8123 -t public

# 4. Autentikasi GitHub
php auth.php

# 5. Buka browser
open http://localhost:8123/chat.html
```

---

## 📚 Dokumentasi Lengkap

### Untuk Pengguna
- 📘 **[PANDUAN.md](PANDUAN.md)** - Panduan lengkap dalam Bahasa Indonesia
- ⚡ **[MULAI.md](MULAI.md)** - Quick start dan tips singkat

### Untuk Developer
- 🐳 **[DOCKER.md](DOCKER.md)** - Panduan deployment dengan Docker
- 🔄 **[COMPARISON.md](COMPARISON.md)** - Perbandingan dengan TCD-AI-Chat
- 📋 **[tasks/PROJECT_ROADMAP.md](tasks/PROJECT_ROADMAP.md)** - Roadmap pengembangan

---

## 🎨 Tema yang Tersedia

Aplikasi ini menyediakan 8 tema cantik:

1. 🟣 **Purple Gradient** (default) - Modern dan profesional
2. 🌊 **Ocean Blue** - Tenang dan menyegarkan  
3. 🌅 **Sunset Vibes** - Vibrant dan energik
4. 🌲 **Forest Green** - Natural dan menenangkan
5. 🌙 **Dark Mode** - Nyaman di mata
6. 🪸 **Coral Reef** - Hangat dan ramah
7. 💜 **Lavender Dreams** - Lembut dan elegan
8. 🌌 **Midnight Blue** - Dalam dan misterius

**Cara mengganti tema:** Klik ikon 🎨 di header, pilih tema favorit Anda!

---

## 🛠️ Teknologi

### Backend
- **PHP 8.2+** - Modern PHP dengan fitur terbaru
- **SQLite/MySQL/PostgreSQL** - Pilihan database fleksibel
- **Composer** - Dependency management
- **Guzzle HTTP** - API client

### Frontend
- **Vanilla JavaScript** - No framework, super cepat
- **CSS3** - Glassmorphism effects
- **Chart.js** - Visualisasi data
- **Leaflet.js** - Peta interaktif

### DevOps
- **Docker & Docker Compose** - Containerization
- **GitHub Actions** - CI/CD pipeline
- **Nginx** - Reverse proxy (production)
- **Redis** - Caching (optional)

---

## 📦 Instalasi

### Persyaratan Sistem

- ✅ PHP 8.1 atau lebih tinggi
- ✅ Composer
- ✅ Akun GitHub (gratis)
- ✅ Browser modern

### Opsi 1: Docker (Direkomendasikan)

```bash
# SQLite (default)
docker-compose up -d

# Dengan MySQL
docker-compose --profile mysql up -d

# Production dengan Nginx
docker-compose --profile mysql --profile production up -d
```

### Opsi 2: Manual

```bash
# Install dependencies
composer install

# Setup database
php migrate.php

# Jalankan development server
php -S localhost:8123 -t public
```

**Detail lengkap:** Lihat [PANDUAN.md](PANDUAN.md)

---

## 🎯 Penggunaan

### 1. Login Pertama Kali

```
URL: http://localhost:8123/chat.html
Username: admin
Password: admin123
```

### 2. Autentikasi GitHub

```bash
php auth.php
# Ikuti instruksi di terminal
```

### 3. Mulai Chat

Ketik pertanyaan Anda di chat box dan AI akan menjawab!

**Contoh pertanyaan:**
- "Buatkan website sederhana dengan PHP"
- "Jelaskan cara kerja Docker"
- "Buat grafik penjualan tahun 2024"

---

## 🔧 Konfigurasi

### Environment Variables

Copy `.env.example` ke `.env`:

```bash
cp .env.example .env
```

Konfigurasi penting:

```env
# Aplikasi
APP_NAME="Indonesia AI Agent"
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=sqlite
DB_DATABASE=storage/database.sqlite

# GitHub OAuth
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret
```

**90+ opsi konfigurasi** tersedia di `.env.example`

---

## 🐳 Docker Deployment

### Development

```bash
docker-compose up -d
```

### Production

```bash
# Dengan MySQL + Nginx + Redis
docker-compose --profile mysql --profile cache --profile production up -d
```

### Backup Database

```bash
# SQLite
cp storage/database.sqlite storage/backup_$(date +%Y%m%d).sqlite

# MySQL
docker-compose exec mysql mysqldump -u root -p indonesia_ai > backup.sql
```

**Panduan lengkap:** Lihat [DOCKER.md](DOCKER.md)

---

## 🧪 Testing

```bash
# Syntax check
find src public -name "*.php" -print0 | xargs -0 -n1 php -l

# Code style
composer require --dev squizlabs/php_codesniffer
vendor/bin/phpcs --standard=PSR12 src/

# Security scan
composer audit
```

---

## 🚀 Deployment

### Via Docker

```bash
# Build image
docker build -t indonesia-ai-agent .

# Push to registry
docker tag indonesia-ai-agent yourusername/indonesia-ai-agent
docker push yourusername/indonesia-ai-agent

# Deploy
docker-compose up -d
```

### Via CI/CD

Push ke GitHub, pipeline otomatis akan:
1. ✅ Cek kualitas kode
2. ✅ Scan keamanan
3. ✅ Build Docker image
4. ✅ Deploy ke staging (branch `develop`)
5. ✅ Deploy ke production (branch `main`)

---

## 📊 Struktur Project

```
Agent-Ai/
├── .github/workflows/    # CI/CD pipelines
├── docker/              # Docker configs
├── public/              # Frontend files
│   ├── chat.html       # UI utama
│   ├── index.php       # API endpoint
│   ├── themes.js       # Theme manager
│   └── themes.css      # Theme styles
├── src/                # Backend PHP
│   ├── Auth/           # Autentikasi
│   ├── Chat/           # Chat management
│   ├── Client/         # AI client
│   ├── Config/         # Konfigurasi
│   ├── Database/       # Database layer
│   └── Utils/          # Utilities
├── storage/            # Database & cache
├── tasks/              # Planning & docs
├── .env.example        # Config template
├── Dockerfile          # Container image
├── docker-compose.yml  # Orchestration
├── PANDUAN.md         # Panduan lengkap 🇮🇩
├── MULAI.md           # Quick start 🇮🇩
└── DOCKER.md          # Docker guide
```

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Silakan:

1. Fork repository ini
2. Buat branch fitur (`git checkout -b fitur-keren`)
3. Commit perubahan (`git commit -m 'Tambah fitur keren'`)
4. Push ke branch (`git push origin fitur-keren`)
5. Buat Pull Request

**Panduan kontribusi:** Lihat [tasks/PROJECT_ROADMAP.md](tasks/PROJECT_ROADMAP.md)

---

## 🐛 Troubleshooting

### Port Sudah Digunakan

```bash
# Ganti port di docker-compose.yml
ports:
  - "8124:8080"  # Gunakan 8124
```

### Database Error

```bash
# Reset database
rm storage/database.sqlite
php migrate.php
```

### Permission Error

```bash
chmod -R 775 storage/
```

**Masalah lain?** Lihat [PANDUAN.md - Troubleshooting](PANDUAN.md#troubleshooting)

---

## 📈 Roadmap

- ✅ **Phase 1:** Foundation (SELESAI)
- ✅ **Phase 2:** DevOps & Themes (SELESAI)
- 🔄 **Phase 3:** Testing & Validation (PROSES)
- 📋 **Phase 4:** UI/UX Enhancements (RENCANA)
- 🔮 **Phase 5:** Mobile & PWA (MASA DEPAN)

**Detail lengkap:** [tasks/PROJECT_ROADMAP.md](tasks/PROJECT_ROADMAP.md)

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah MIT License - lihat file [LICENSE](LICENSE) untuk detail.

---

## 👥 Tim

**Indonesia AI Agent** dikembangkan oleh:

- **Organisasi:** Indonesian Developers
- **Website:** [indonesiasatu.com](https://indonesiasatu.com)
- **Email:** support@indonesiasatu.com

---

## ⭐ Support

Jika proyek ini membantu Anda, berikan ⭐ di GitHub!

**Ada pertanyaan?** Buka [Issues](https://github.com/yourusername/Agent-Ai/issues)

---

<div align="center">

**Dibuat dengan ❤️ untuk Pengembang Indonesia**

🇮🇩 **Laks AI Agent** - Memberdayakan Indonesia dengan Teknologi AI

[Website](https://laksfaqih.my.id) • 
[Dokumentasi](PANDUAN.md) • 
[Quick Start](MULAI.md) • 
[Docker](DOCKER.md)

</div>
