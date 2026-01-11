# BCMS Starter Repository - Quick Start Guide

## 🎉 What's Been Created

A complete starter repository for a Billing & Customer Management System (BCMS) for ISPs with:
- ✅ Laravel 12 backend with 20 database migrations
- ✅ Next.js 22 frontend with login & dashboard
- ✅ Docker infrastructure (Nginx, PostgreSQL, Redis)
- ✅ Comprehensive documentation
- ✅ CI/CD workflow
- ✅ Sample data seeders

## 🚀 Quick Start (5 Minutes)

### Option 1: Local Development (Without Docker)

#### 1. Backend Setup
```bash
cd apps/api

# Install dependencies (requires PHP 8.3 + Composer)
composer install

# Install Laravel packages
composer require laravel/sanctum laravel/horizon laravel/octane spiral/roadrunner

# Copy environment file
cp .env.example .env

# Configure .env (edit with your database credentials)
# DB_CONNECTION=sqlite  # Or pgsql if you have PostgreSQL
# DB_DATABASE=/path/to/database.sqlite

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed --class=BcmsSeeder

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

#### 2. Frontend Setup
```bash
# In a new terminal
cd apps/web

# Install dependencies
npm install

# Start development server
npm run dev
```

#### 3. Access the Application
- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api
- Login with: abramz@bcms.com / password123

### Option 2: Docker Development (Recommended)

```bash
# From project root
cd infra/docker

# Start all services
docker-compose -f docker-compose.dev.yml up -d

# Wait for services to be ready, then run migrations
docker exec -it bcms_api php artisan migrate
docker exec -it bcms_api php artisan db:seed --class=BcmsSeeder

# Access
# Frontend: http://localhost (via nginx)
# Backend: http://localhost/api
```

## 📝 Test the API

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"abramz@bcms.com","password":"password123"}'

# Response will include a token, use it for authenticated requests
TOKEN="your-token-here"

# Get current user
curl -X GET http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer $TOKEN"

# Get customers
curl -X GET http://localhost:8000/api/customers \
  -H "Authorization: Bearer $TOKEN"

# Get invoices
curl -X GET http://localhost:8000/api/invoices \
  -H "Authorization: Bearer $TOKEN"
```

## 📁 Key Files

### Backend
- `apps/api/database/migrations/` - All database schema
- `apps/api/database/seeders/BcmsSeeder.php` - Sample data
- `apps/api/routes/api.php` - API routes
- `apps/api/app/Http/Controllers/Api/` - Controllers
- `apps/api/app/Models/` - Eloquent models

### Frontend
- `apps/web/app/login/page.tsx` - Login page
- `apps/web/app/dashboard/page.tsx` - Dashboard
- `apps/web/.env.local` - Environment variables

### Infrastructure
- `infra/docker/docker-compose.dev.yml` - Development services
- `infra/nginx/` - Nginx configuration
- `docs/` - Documentation

## 🔐 Default Users

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| Administrator | abramz@bcms.com | password123 | Full access |
| Supervisor | fandi@bcms.com | password123 | Management |
| Finance | meci@bcms.com | password123 | Billing |
| Support | yogi@bcms.com | password123 | Tickets |

## 📊 Database Tables Created

1. `users_groups` - RBAC groups with JSON permissions
2. `users` - User accounts
3. `companies` - Multi-company support
4. `brands` - ISP brands
5. `products` - Service packages
6. `internet_services` - Bandwidth configs
7. `promotions` - Discounts
8. `routers` - Mikrotik routers
9. `customers` - Customer data
10. `subscriptions` - Active subscriptions
11. `provisionings` - PPPoE & queue configs
12. `invoices` - Billing invoices
13. `invoice_items` - Invoice line items
14. `payments` - Payment records
15. `templates` - Notification templates
16. `reminders` - Reminder logs
17. `tickets` - Support tickets
18. `audit_logs` - Activity audit trail

## 🛠️ Next Development Steps

### Immediate (Critical for MVP)
1. Install Laravel packages (Sanctum, Horizon, Octane)
2. Complete CRUD controllers for all resources
3. Build Customers list page in frontend
4. Build Invoices list page in frontend
5. Implement RBAC permission middleware

### Short Term (Core Features)
6. Create Mikrotik service layer (API + SSH)
7. Implement router test connection
8. Create provisioning automation
9. Build invoice generation job
10. Build payment webhook handlers

### Medium Term (Automation)
11. Implement reminder engine
12. Create auto soft-limit job
13. Create auto suspend job
14. Implement reactivation flow
15. Add notification services (Email/SMS/WhatsApp)

### Long Term (Polish & Scale)
16. Add comprehensive testing
17. Implement audit logging
18. Add monitoring (Horizon dashboard)
19. Optimize queries & caching
20. Production deployment guide

## 📖 Documentation

- **Architecture**: [docs/architecture.md](../docs/architecture.md)
- **Installation**: [docs/installation_guide.md](../docs/installation_guide.md)
- **Status**: [docs/implementation_status.md](../docs/implementation_status.md)
- **Frontend**: [apps/web/README.md](../apps/web/README.md)

## 🐛 Troubleshooting

### Backend won't start
- Check PHP version: `php -v` (needs 8.3+)
- Check database connection in `.env`
- Run: `php artisan config:clear && php artisan cache:clear`

### Frontend won't start
- Check Node version: `node -v` (needs 20+)
- Delete `node_modules` and `package-lock.json`, run `npm install` again
- Check `.env.local` has correct API URL

### Database errors
- Make sure migrations ran: `php artisan migrate:status`
- If needed, fresh install: `php artisan migrate:fresh --seed`

### Docker issues
- Check services: `docker-compose ps`
- View logs: `docker-compose logs -f`
- Restart: `docker-compose down && docker-compose up -d`

## 🎯 What You Can Do Right Now

1. **Login** to the frontend at http://localhost:3000
2. **View dashboard** with basic stats
3. **Test API endpoints** using curl or Postman
4. **Explore database** with your favorite DB client
5. **Read the architecture** to understand the system
6. **Start building** additional features!

## 💡 Tips

- Use `php artisan tinker` to interact with the database
- Use `php artisan route:list` to see all API routes
- Use Laravel Telescope for debugging (install separately)
- Use React DevTools for frontend debugging
- Check `storage/logs/laravel.log` for backend errors

---

**You're all set!** 🚀 Happy coding! If you have questions, check the documentation or create an issue.
