# Docker Deployment Guide - Indonesia AI Agent

This guide covers deploying Indonesia AI Agent using Docker and Docker Compose.

## Table of Contents
1. [Quick Start](#quick-start)
2. [Configuration Options](#configuration-options)
3. [Database Options](#database-options)
4. [Production Deployment](#production-deployment)
5. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Prerequisites
- Docker 20.10+
- Docker Compose 2.0+

### Basic Deployment (SQLite)

```bash
# Clone the repository
git clone <repository-url>
cd Agent-Ai

# Copy environment file
cp .env.example .env

# Edit .env with your settings (optional for SQLite)
nano .env

# Build and start the container
docker-compose up -d

# View logs
docker-compose logs -f app

# Access the application
open http://localhost:8123/chat.html
```

Default login credentials:
- **Username:** admin
- **Password:** admin123

---

## Configuration Options

### Environment Variables

All configuration is done via the `.env` file. Key variables:

```env
# Application
APP_NAME="Indonesia AI Agent"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8123

# Database (SQLite by default)
DB_CONNECTION=sqlite
DB_DATABASE=storage/database.sqlite

# GitHub OAuth (optional)
GITHUB_CLIENT_ID=your_client_id
GITHUB_CLIENT_SECRET=your_client_secret

# Session
SESSION_LIFETIME=120
```

### Docker Compose Profiles

The `docker-compose.yml` supports multiple profiles for different setups:

#### 1. **Default Profile** (SQLite)
```bash
docker-compose up -d
```
- Uses SQLite database
- Perfect for development and small deployments
- No additional services needed

#### 2. **MySQL Profile**
```bash
docker-compose --profile mysql up -d
```
- Adds MySQL 8.0 database
- Better for medium-to-large deployments
- Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=indonesia_ai
DB_USERNAME=indonesia_ai_user
DB_PASSWORD=secure_password
```

#### 3. **PostgreSQL Profile**
```bash
docker-compose --profile postgres up -d
```
- Adds PostgreSQL 15 database
- Enterprise-grade database
- Update `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=indonesia_ai
DB_USERNAME=indonesia_ai_user
DB_PASSWORD=secure_password
```

#### 4. **Production Profile** (with Nginx)
```bash
docker-compose --profile production up -d
```
- Adds Nginx reverse proxy
- SSL/TLS support
- Better performance and security
- Access via `http://localhost` (port 80)

#### 5. **Full Stack** (All Services)
```bash
docker-compose --profile mysql --profile cache --profile production up -d
```
- MySQL database
- Redis cache
- Nginx reverse proxy
- Recommended for production

---

## Database Options

### SQLite (Default)
**Pros:**
- Zero configuration
- Lightweight
- Perfect for single-user or development

**Cons:**
- Not suitable for high concurrency
- Limited scaling

**Setup:**
```bash
# Already configured by default
docker-compose up -d
```

### MySQL
**Pros:**
- Better performance
- Good concurrency support
- Industry standard

**Cons:**
- Requires additional container
- More memory usage

**Setup:**
```bash
# 1. Update .env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=indonesia_ai
DB_USERNAME=indonesia_ai_user
DB_PASSWORD=your_secure_password

# 2. Update docker-compose.yml MySQL service
MYSQL_PASSWORD=your_secure_password

# 3. Start with MySQL profile
docker-compose --profile mysql up -d

# 4. Run migrations
docker-compose exec app php migrate.php
```

### PostgreSQL
**Pros:**
- Advanced features
- Excellent for complex queries
- Best for large datasets

**Cons:**
- Slightly more complex
- Higher memory usage

**Setup:**
```bash
# 1. Update .env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=indonesia_ai
DB_USERNAME=indonesia_ai_user
DB_PASSWORD=your_secure_password

# 2. Start with PostgreSQL profile
docker-compose --profile postgres up -d

# 3. Run migrations
docker-compose exec app php migrate.php
```

---

## Production Deployment

### 1. Basic Production Setup

```bash
# 1. Prepare environment
cp .env.example .env
nano .env

# Update these values:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
DB_CONNECTION=mysql  # or pgsql

# 2. Build and start
docker-compose --profile mysql --profile production up -d

# 3. Check status
docker-compose ps
```

### 2. SSL/TLS Configuration

#### Option A: Let's Encrypt (Recommended)

```bash
# 1. Install certbot
sudo apt-get update
sudo apt-get install certbot

# 2. Obtain certificate
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# 3. Update docker/nginx/conf.d/default.conf
# Uncomment the HTTPS server block
# Update paths to:
ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

# 4. Add volume to docker-compose.yml nginx service:
volumes:
  - /etc/letsencrypt:/etc/letsencrypt:ro

# 5. Restart nginx
docker-compose restart nginx
```

#### Option B: Self-Signed Certificate (Development)

```bash
# 1. Generate certificate
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/privkey.pem \
  -out docker/nginx/ssl/fullchain.pem

# 2. Update nginx config to use self-signed cert
# 3. Restart services
docker-compose restart nginx
```

### 3. Performance Tuning

#### Add Redis Cache

```bash
# 1. Update .env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PORT=6379

# 2. Start with cache profile
docker-compose --profile cache up -d
```

#### Resource Limits

Update `docker-compose.yml`:

```yaml
services:
  app:
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 512M
        reservations:
          cpus: '0.5'
          memory: 256M
```

### 4. Backup Strategy

#### Database Backup (MySQL)

```bash
# Create backup
docker-compose exec mysql mysqldump -u root -p indonesia_ai > backup_$(date +%Y%m%d).sql

# Restore backup
docker-compose exec -T mysql mysql -u root -p indonesia_ai < backup_20251127.sql
```

#### Database Backup (SQLite)

```bash
# Create backup
docker-compose exec app cp storage/database.sqlite storage/database_backup_$(date +%Y%m%d).sqlite

# Or from host
cp storage/database.sqlite storage/database_backup_$(date +%Y%m%d).sqlite
```

#### Automated Backups

Create `backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups"
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
docker-compose exec -T mysql mysqldump -u root -pROOT_PASSWORD indonesia_ai | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Backup storage
tar -czf "$BACKUP_DIR/storage_$DATE.tar.gz" storage/

# Keep only last 7 days
find $BACKUP_DIR -type f -mtime +7 -delete
```

Add to crontab:
```bash
0 2 * * * /path/to/backup.sh
```

### 5. Monitoring

#### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql

# Last 100 lines
docker-compose logs --tail=100 app
```

#### Health Checks

```bash
# Check container health
docker-compose ps

# Check application health
curl http://localhost:8123/api/health
```

#### Resource Usage

```bash
# Container stats
docker stats

# Disk usage
docker system df
```

---

## Troubleshooting

### Container won't start

```bash
# Check logs
docker-compose logs app

# Check configuration
docker-compose config

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database connection errors

```bash
# Check database container
docker-compose logs mysql

# Verify database is running
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"

# Reset database
docker-compose down -v  # WARNING: Deletes all data
docker-compose up -d
docker-compose exec app php migrate.php
```

### Permission errors

```bash
# Fix storage permissions
docker-compose exec app chown -R appuser:appuser storage/
docker-compose exec app chmod -R 775 storage/
```

### Port already in use

```bash
# Find what's using the port
sudo lsof -i :8123

# Change port in docker-compose.yml
ports:
  - "8124:8080"  # Use 8124 instead
```

### Out of disk space

```bash
# Clean Docker
docker system prune -a

# Clean volumes (WARNING: Deletes data)
docker volume prune
```

### Slow performance

```bash
# Check resources
docker stats

# Increase memory limits in docker-compose.yml
# Add Redis cache
docker-compose --profile cache up -d

# Optimize database
docker-compose exec mysql mysqlcheck -u root -p --optimize --all-databases
```

---

## Useful Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart service
docker-compose restart app

# View running containers
docker-compose ps

# Execute command in container
docker-compose exec app php migrate.php

# Access container shell
docker-compose exec app /bin/sh

# View logs
docker-compose logs -f app

# Rebuild after code changes
docker-compose build app
docker-compose up -d app

# Full reset (WARNING: Deletes all data)
docker-compose down -v
docker-compose up -d
```

---

## Security Best Practices

1. **Change Default Passwords**
   ```bash
   # Update MySQL root password
   # Update application admin password
   docker-compose exec app php auth.php
   ```

2. **Use Environment Variables**
   - Never commit `.env` file
   - Use strong passwords
   - Rotate secrets regularly

3. **Enable Firewall**
   ```bash
   # Only allow necessary ports
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw enable
   ```

4. **Regular Updates**
   ```bash
   # Update Docker images
   docker-compose pull
   docker-compose up -d
   ```

5. **Use HTTPS**
   - Always use SSL/TLS in production
   - Configure proper certificates
   - Enable HSTS headers

---

## Support

For issues or questions:
- GitHub Issues: [Repository Issues]
- Documentation: `README.md`, `PANDUAN.md`
- Website: https://indonesiasatu.com

---

**Last Updated:** 2025-11-27  
**Version:** 1.0.0
