# BCMS - Billing & Customer Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-22.x-black)](https://nextjs.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-18-blue)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-8-red)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-Compose-blue)](https://docs.docker.com/compose/)

Sistem manajemen billing & pelanggan untuk Internet Service Provider (ISP) dengan integrasi Mikrotik RouterOS, payment gateway, dan automation engine.

## 🚀 Features

### Core Features
- 🔐 Multi-user dengan RBAC (Role-Based Access Control)
- 🏢 Multi-company & multi-brand support
- 📦 Product management dengan internet service packages
- 👥 Customer & subscription management
- 💰 Billing automation & invoice generation
- 💳 Payment integration (Midtrans, Xendit)
- 🎫 Ticketing system
- 📊 Audit trail & activity logs

### ISP-Specific Features
- 📡 Mikrotik RouterOS integration (API TLS + SSH fallback)
- 🔄 Auto provisioning (PPPoE, Queues, Firewall)
- ⚠️ Auto soft-limit (bandwidth reduction)
- 🚫 Auto suspend/reactivate
- 📨 Multi-channel notifications (Email, SMS, WhatsApp)
- 🔁 Idempotent automation jobs dengan retry/backoff
- ⏰ Flexible scheduling untuk reminder & automation

## 📋 Tech Stack

### Frontend
- **Next.js 22** (React 19) dengan Server-Side Rendering
- **TypeScript** untuk type safety
- **Tailwind CSS** untuk styling

### Backend
- **Laravel 12** (PHP 8.3)
- **Laravel Sanctum** untuk API authentication
- **Laravel Horizon** untuk queue management
- **Laravel Octane** dengan RoadRunner untuk performance

### Database & Cache
- **PostgreSQL 18** sebagai database utama
- **Redis 8** untuk caching & queue

### Infrastructure
- **Docker & Docker Compose**
- **Nginx 1.28** sebagai reverse proxy dengan TLS support
- **Let's Encrypt** untuk SSL certificates

## 📁 Project Structure

```
bcms_v3/
├── apps/
│   ├── web/              # Next.js frontend application
│   └── api/              # Laravel backend API
├── infra/
│   ├── docker/           # Docker compose files
│   └── nginx/            # Nginx configuration
├── docs/
│   ├── architecture.md           # Architecture design document
│   └── installation_guide.md     # Installation guide (Bahasa Indonesia)
├── scripts/              # Utility scripts
└── README.md
```

## 🛠️ Quick Start

### Prerequisites
- Docker & Docker Compose
- Git
- Domain name (untuk production dengan SSL)

### Development Setup

1. **Clone repository**
   ```bash
   git clone https://github.com/abramz-mrn/bcms_v3.git
   cd bcms_v3
   ```

2. **Setup environment**
   ```bash
   # Backend
   cp apps/api/.env.example apps/api/.env
   # Edit apps/api/.env sesuai kebutuhan
   ```

3. **Start development stack**
   ```bash
   cd infra/docker
   docker compose -f docker-compose.dev.yml up -d
   ```

4. **Run migrations**
   ```bash
   docker exec -it bcms_api php artisan migrate
   ```

5. **Access aplikasi**
   - Frontend: http://localhost (via nginx)
   - Backend API: http://localhost/api
   - Horizon Dashboard: http://localhost/api/horizon

## 📖 Documentation

- [Architecture Design](docs/architecture.md) - Detailed architecture diagram & design
- [Installation Guide](docs/installation_guide.md) - Step-by-step installation (Bahasa Indonesia)

## 🔌 Integrations

### Mikrotik RouterOS
- TLS API connection (port 8729)
- SSH fallback for command execution
- Auto provisioning: PPPoE secrets, Simple Queues, Firewall rules

### Payment Gateways
- **Midtrans** - QRIS, Virtual Account, E-Wallet
- **Xendit** - Virtual Account, E-Wallet, Retail Outlets

### Notification Channels
- **Email** - SMTP
- **SMS** - SMS gateway integration
- **WhatsApp** - WhatsApp Business API

## 🔄 Automation

### Invoice Generation
- H-7 sebelum start period
- Idempotent: tidak duplikat jika sudah exists

### Reminders
- H-7, H-3, H-1 sebelum jatuh tempo
- H+1 setelah jatuh tempo

### Auto Actions
- **Soft Limit**: Reduce bandwidth to 50%
- **Suspend**: Disable service
- **Reactivate**: Auto enable setelah payment confirmed

## 📦 Deployment

Lihat [Installation Guide](docs/installation_guide.md) untuk deployment ke production.

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- **Abramz** - Initial work - [abramz-mrn](https://github.com/abramz-mrn)

---

**⚠️ Note**: Ini adalah starter repository. Beberapa fitur masih dalam tahap development.

Untuk pertanyaan atau dukungan, silakan buka issue di repository ini.
