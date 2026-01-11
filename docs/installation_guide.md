# Panduan Instalasi & Konfigurasi BCMS End-to-End

## Daftar Isi
1. [Persiapan VM](#1-persiapan-vm)
2. [Instalasi Ubuntu Server](#2-instalasi-ubuntu-server)
3. [Instalasi Docker & Docker Compose](#3-instalasi-docker--docker-compose)
4. [Setup Environment & Secrets](#4-setup-environment--secrets)
5. [Setup SSL dengan Let's Encrypt](#5-setup-ssl-dengan-lets-encrypt)
6. [Menjalankan Stack](#6-menjalankan-stack)
7. [Migrasi & Seeding Database](#7-migrasi--seeding-database)
8. [Validasi Endpoint Dasar](#8-validasi-endpoint-dasar)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Persiapan VM

### Spesifikasi Minimum
- **CPU**: 4 vCPU
- **RAM**: 8 GB
- **Storage**: 100 GB SSD
- **OS**: Ubuntu Server 22.04 LTS
- **Network**: Public IP + Domain (untuk SSL)

### Konfigurasi Firewall
Buka port berikut:
```bash
# HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# SSH
sudo ufw allow 22/tcp

# PostgreSQL (optional, untuk akses eksternal)
# sudo ufw allow 5432/tcp

# Enable firewall
sudo ufw enable
sudo ufw status
```

---

## 2. Instalasi Ubuntu Server

### Update sistem
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git vim htop net-tools
```

### Set timezone
```bash
sudo timedatectl set-timezone Asia/Jakarta
timedatectl
```

### Konfigurasi hostname
```bash
sudo hostnamectl set-hostname bcms-server
```

### Buat user non-root (opsional tapi disarankan)
```bash
sudo adduser bcms
sudo usermod -aG sudo bcms
sudo usermod -aG docker bcms  # setelah Docker terinstall
```

---

## 3. Instalasi Docker & Docker Compose

### Instalasi Docker
```bash
# Hapus versi lama (jika ada)
sudo apt remove docker docker-engine docker.io containerd runc

# Install dependencies
sudo apt install -y \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# Add Docker GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
    sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# Setup repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io \
    docker-buildx-plugin docker-compose-plugin

# Verify installation
sudo docker --version
sudo docker compose version
```

### Post-installation
```bash
# Add current user to docker group
sudo usermod -aG docker $USER

# Apply new group membership (re-login atau jalankan)
newgrp docker

# Test Docker without sudo
docker run hello-world
```

### Enable Docker to start on boot
```bash
sudo systemctl enable docker.service
sudo systemctl enable containerd.service
```

---

## 4. Setup Environment & Secrets

### Clone repository
```bash
cd /home/bcms  # atau direktori pilihan Anda
git clone https://github.com/abramz-mrn/bcms_v3.git
cd bcms_v3
```

### Setup environment Laravel (API)
```bash
cd apps/api

# Copy .env.example
cp .env.example .env

# Edit .env
vi .env
```

**Isi minimal untuk production:**
```env
APP_NAME="BCMS"
APP_ENV=production
APP_KEY=  # akan di-generate nanti
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=bcms
DB_USERNAME=bcms_user
DB_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

SANCTUM_STATEFUL_DOMAINS=your-domain.com
SESSION_DOMAIN=.your-domain.com

# Mikrotik Default (bisa di-override per router di database)
MIKROTIK_DEFAULT_PORT=8729
MIKROTIK_TIMEOUT=10

# Payment Gateway
MIDTRANS_SERVER_KEY=your-midtrans-server-key
MIDTRANS_CLIENT_KEY=your-midtrans-client-key
MIDTRANS_IS_PRODUCTION=false

XENDIT_SECRET_KEY=your-xendit-secret-key
XENDIT_WEBHOOK_TOKEN=your-xendit-webhook-token

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# SMS Gateway (contoh Zenziva)
SMS_API_URL=https://console.zenziva.net/wareguler/api/sendWA/
SMS_USERKEY=your-userkey
SMS_PASSKEY=your-passkey

# WhatsApp (contoh Fonnte)
WHATSAPP_API_URL=https://api.fonnte.com/send
WHATSAPP_TOKEN=your-fonnte-token
```

### Generate Laravel app key
```bash
docker run --rm -v $(pwd):/app composer:latest sh -c "cd /app && php artisan key:generate"
# atau jika sudah ada composer lokal:
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

### Setup environment Next.js (Web)
```bash
cd ../web

# Buat file .env.local
cat > .env.local << EOF
NEXT_PUBLIC_API_URL=https://your-domain.com/api
NEXT_PUBLIC_APP_NAME=BCMS
EOF
```

### Setup environment Docker Compose
```bash
cd ../../infra/docker

# Edit docker-compose.prod.yml jika perlu
# Pastikan DB_PASSWORD sama dengan yang di .env Laravel
```

---

## 5. Setup SSL dengan Let's Encrypt

### Install Certbot
```bash
sudo apt install -y certbot python3-certbot-nginx
```

### Dapatkan SSL certificate
```bash
# Pastikan domain sudah pointing ke IP server
# Stop nginx container dulu jika sudah jalan
docker stop bcms_nginx 2>/dev/null || true

# Request certificate
sudo certbot certonly --standalone \
    -d your-domain.com \
    -d www.your-domain.com \
    --email your-email@example.com \
    --agree-tos \
    --non-interactive

# Certificate akan tersimpan di:
# /etc/letsencrypt/live/your-domain.com/fullchain.pem
# /etc/letsencrypt/live/your-domain.com/privkey.pem
```

### Setup auto-renewal
```bash
# Test renewal
sudo certbot renew --dry-run

# Crontab untuk auto-renewal (setiap hari jam 3 pagi)
sudo crontab -e

# Tambahkan baris ini:
0 3 * * * certbot renew --quiet --post-hook "docker restart bcms_nginx"
```

### Copy certificate ke folder project
```bash
cd /home/bcms/bcms_v3
sudo mkdir -p infra/nginx/ssl
sudo cp /etc/letsencrypt/live/your-domain.com/fullchain.pem infra/nginx/ssl/
sudo cp /etc/letsencrypt/live/your-domain.com/privkey.pem infra/nginx/ssl/
sudo chmod 644 infra/nginx/ssl/*.pem
```

### Update nginx config untuk HTTPS
Edit `infra/nginx/conf.d/bcms.conf` dan uncomment bagian server HTTPS (port 443).

---

## 6. Menjalankan Stack

### Build & Start containers
```bash
cd /home/bcms/bcms_v3/infra/docker

# Development
docker compose -f docker-compose.dev.yml up -d

# Production
docker compose -f docker-compose.prod.yml up -d --build
```

### Cek status containers
```bash
docker ps
# Harus ada: bcms_nginx, bcms_web, bcms_api, bcms_horizon, bcms_scheduler, bcms_postgres, bcms_redis
```

### Cek logs
```bash
# Semua containers
docker compose -f docker-compose.prod.yml logs -f

# Container tertentu
docker logs -f bcms_api
docker logs -f bcms_nginx
```

---

## 7. Migrasi & Seeding Database

### Jalankan migrasi
```bash
# Masuk ke container API
docker exec -it bcms_api sh

# Di dalam container:
cd /var/www/html

# Clear cache
php artisan config:clear
php artisan cache:clear

# Jalankan migrasi
php artisan migrate --force

# Jika perlu rollback & fresh
# php artisan migrate:fresh --force
```

### Jalankan seeder
```bash
# Masih di dalam container bcms_api
php artisan db:seed --force

# Atau seed specific seeder
# php artisan db:seed --class=UsersGroupSeeder --force
# php artisan db:seed --class=UserSeeder --force

# Exit container
exit
```

### Verify database
```bash
# Connect ke PostgreSQL
docker exec -it bcms_postgres psql -U bcms_user -d bcms

# List tables
\dt

# Query sample
SELECT * FROM users;
SELECT * FROM users_groups;
SELECT * FROM companies;

# Exit
\q
```

---

## 8. Validasi Endpoint Dasar

### Test endpoint auth
```bash
# Login
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "abramz@bcms.com",
    "password": "password123"
  }'

# Response akan berisi token:
# {
#   "token": "1|xxxxxxxxxxxxx",
#   "user": { ... }
# }

# Get current user (gunakan token dari response login)
curl -X GET https://your-domain.com/api/auth/me \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx" \
  -H "Accept: application/json"
```

### Test endpoint CRUD
```bash
# Get customers (dengan token)
curl -X GET https://your-domain.com/api/customers \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Get invoices
curl -X GET https://your-domain.com/api/invoices \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Test frontend
```bash
# Buka browser
https://your-domain.com

# Harus muncul halaman login Next.js
```

### Test Horizon dashboard
```bash
# Buka browser
https://your-domain.com/api/horizon

# Harus muncul dashboard Horizon (hanya untuk user terautentikasi)
```

---

## 9. Troubleshooting

### Container tidak start
```bash
# Cek logs
docker compose -f docker-compose.prod.yml logs

# Restart container tertentu
docker restart bcms_api

# Rebuild container
docker compose -f docker-compose.prod.yml up -d --build --force-recreate
```

### Database connection error
```bash
# Cek apakah PostgreSQL sudah ready
docker exec -it bcms_postgres pg_isready -U bcms_user

# Cek environment di container API
docker exec -it bcms_api env | grep DB_

# Test koneksi manual
docker exec -it bcms_api php artisan tinker
# > DB::connection()->getPdo();
```

### Permission denied error
```bash
# Fix permission di Laravel storage
docker exec -it bcms_api sh -c "chown -R www-data:www-data /var/www/html/storage"
docker exec -it bcms_api sh -c "chmod -R 775 /var/www/html/storage"
```

### Nginx 502 Bad Gateway
```bash
# Cek apakah backend up
docker ps | grep bcms_api
docker logs bcms_api

# Test koneksi dari nginx ke backend
docker exec -it bcms_nginx wget -O- http://api:8000/api/health
```

### Migration error
```bash
# Drop & recreate database
docker exec -it bcms_postgres psql -U bcms_user
# > DROP DATABASE bcms;
# > CREATE DATABASE bcms;
# > \q

# Jalankan ulang migrasi
docker exec -it bcms_api php artisan migrate:fresh --seed --force
```

### Horizon not running
```bash
# Cek status
docker logs bcms_horizon

# Restart Horizon
docker restart bcms_horizon

# Pause/Continue Horizon
docker exec -it bcms_horizon php artisan horizon:pause
docker exec -it bcms_horizon php artisan horizon:continue
```

### Scheduler not working
```bash
# Cek cron di host
crontab -l

# Test manual
docker exec -it bcms_scheduler php artisan schedule:run
docker exec -it bcms_scheduler php artisan schedule:list
```

### SSL certificate error
```bash
# Renew manual
sudo certbot renew

# Check expiry
sudo certbot certificates

# Force renew
sudo certbot renew --force-renewal
```

---

## Maintenance

### Backup database
```bash
# Backup
docker exec -it bcms_postgres pg_dump -U bcms_user bcms > backup_$(date +%Y%m%d).sql

# Restore
docker exec -i bcms_postgres psql -U bcms_user bcms < backup_20260111.sql
```

### Update aplikasi
```bash
cd /home/bcms/bcms_v3
git pull origin main

# Rebuild containers
cd infra/docker
docker compose -f docker-compose.prod.yml up -d --build

# Run migrations (jika ada)
docker exec -it bcms_api php artisan migrate --force

# Clear cache
docker exec -it bcms_api php artisan optimize:clear
```

### Monitor resources
```bash
# Docker stats
docker stats

# Disk usage
df -h
docker system df

# Clean up
docker system prune -a
```

---

**Selamat! BCMS Anda sudah running. 🚀**

Untuk pertanyaan lebih lanjut, silakan buka issue di repository atau hubungi tim development.
