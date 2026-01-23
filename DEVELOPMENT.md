# Prestasi Unpatti - Development Guide

## Tech Stack
- **Framework**: Laravel 11.x
- **Database**: PostgreSQL 16
- **PHP**: 8.2+
- **Frontend**: Tailwind CSS, Alpine.js

## Quick Start

### Setup
```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development
php artisan serve
npm run dev
```

### Default Credentials
- **Admin**: admin@unpatti.ac.id / password
- **Validator**: validator@unpatti.ac.id / password

## Code Quality

### Format Code
```bash
./vendor/bin/pint
```

### Clear Cache
```bash
php artisan optimize:clear
```

## Project Structure

```
app/
├── Enums/              # Enums for type safety
├── Http/
│   ├── Controllers/    # Request handlers
│   └── Middleware/     # HTTP middleware
├── Models/             # Eloquent models
└── Services/           # Business logic

database/
├── migrations/         # Database schema
└── seeders/           # Sample data

resources/
├── views/             # Blade templates
└── js/                # Frontend assets
```

## Key Features
- Multi-role system (Admin, Validator, Student)
- Achievement management with validation workflow
- Document upload & verification
- Appeal system
- Dashboard with analytics
- Academic period management
- SSO integration

## Database
Using PostgreSQL for better:
- ACID compliance
- Concurrency handling
- Advanced features (JSON, Arrays)
- Standards compliance

## Contributing
1. Follow PSR-12 coding standards
2. Run `./vendor/bin/pint` before commit
3. Write descriptive commit messages
4. Test your changes

## Support
For issues or questions, contact the development team.
