# Murang'a County Rental Marketplace

A hyper-local rental marketplace platform designed specifically for Murang'a University (MUT) students and local professionals in Murang'a County, Kenya. The platform connects verified landlords with tenants through an intuitive, feature-rich system that includes property listings, M-Pesa payments, real-time communication, and AI-powered recommendations.

## 🎯 Project Overview

This platform addresses the unique housing challenges faced by MUT students and local professionals by providing:

- **Verified Listings**: All properties and landlords undergo verification
- **Budget-Accurate Search**: Advanced filters to find properties within specific budgets
- **Local Payment Integration**: M-Pesa integration for seamless rent payments
- **Proximity-Based Search**: Find properties near MUT campus
- **Real-time Communication**: Chat directly with landlords
- **Mobile-First Design**: Responsive web app and native mobile applications

## 📋 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)
- [Documentation](#documentation)
- [Development Phases](#development-phases)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## ✨ Features

### For Tenants
- 🔍 Advanced property search with multiple filters
- 📍 Location-based search (proximity to MUT)
- 💰 Budget-accurate filtering
- ⭐ Property reviews and ratings
- 💬 Real-time chat with landlords
- 📱 Mobile app for iOS and Android
- 🔔 Instant notifications (SMS, Email, Push)
- 💾 Save favorite properties and searches
- 🤖 AI-powered property recommendations
- 📄 Digital contract signing
- 🔧 Maintenance request system

### For Landlords
- 📝 Easy property listing management
- 📸 Multiple image uploads with virtual tours
- 📊 Analytics dashboard
- 💳 M-Pesa payment integration
- 📅 Booking management system
- ✅ Tenant verification
- 💬 Direct communication with tenants
- 📈 Revenue tracking and reporting
- 🔔 Automated rent reminders
- 📋 Maintenance request tracking

### For Administrators
- 👥 User management and moderation
- ✅ Property verification workflow
- 📊 Platform analytics and insights
- 🔍 Dispute resolution system
- 💰 Payment reconciliation
- 📧 Bulk notifications
- 🛡️ Security monitoring
- 📈 Performance metrics

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Laravel Queue with Redis
- **Search**: Laravel Scout + Meilisearch
- **Storage**: AWS S3 / DigitalOcean Spaces

### Frontend
- **Framework**: Vue.js 3 (Composition API)
- **State Management**: Pinia
- **UI Framework**: Tailwind CSS + Headless UI
- **Build Tool**: Vite
- **Icons**: Heroicons

### Mobile
- **Framework**: Flutter
- **State Management**: Riverpod
- **Platforms**: iOS & Android

### Third-Party Services
- **Payments**: M-Pesa Daraja API
- **SMS**: Africa's Talking
- **Email**: SendGrid / AWS SES
- **Maps**: Google Maps API
- **AI**: OpenAI API
- **Real-time**: Laravel Reverb / Pusher
- **Monitoring**: Sentry
- **Analytics**: Google Analytics + Mixpanel

### Infrastructure
- **Hosting**: AWS EC2 / DigitalOcean
- **CDN**: CloudFlare
- **CI/CD**: GitHub Actions
- **Containerization**: Docker (optional)

## 📁 Project Structure

```
muranga-rentals/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── Auth/
│   │   │   │   ├── Property/
│   │   │   │   ├── Booking/
│   │   │   │   ├── Payment/
│   │   │   │   └── ...
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   ├── Services/
│   │   ├── Payment/
│   │   ├── Notification/
│   │   ├── AI/
│   │   └── Contract/
│   ├── Repositories/
│   ├── Events/
│   ├── Listeners/
│   ├── Jobs/
│   └── Policies/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── js/
│   │   ├── components/
│   │   ├── views/
│   │   ├── router/
│   │   ├── stores/
│   │   └── utils/
│   └── css/
├── routes/
│   ├── api.php
│   ├── web.php
│   └── channels.php
├── tests/
│   ├── Unit/
│   ├── Feature/
│   └── Browser/
├── mobile/
│   └── flutter_app/
├── docs/
│   ├── PROJECT_PLAN.md
│   ├── TECHNICAL_SPECIFICATION.md
│   └── IMPLEMENTATION_GUIDE.md
└── docker/
```

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0
- Redis
- Git

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/muranga-rentals.git
cd muranga-rentals
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Install JavaScript dependencies**
```bash
npm install
```

4. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure your `.env` file**
```env
DB_DATABASE=muranga_rentals
DB_USERNAME=your_username
DB_PASSWORD=your_password

MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=your_secret
# ... other configurations
```

6. **Run migrations and seeders**
```bash
php artisan migrate --seed
```

7. **Install Laravel Passport (for API authentication)**
```bash
php artisan passport:install
```

8. **Build frontend assets**
```bash
npm run dev
```

9. **Start the development server**
```bash
php artisan serve
```

10. **Start the queue worker**
```bash
php artisan queue:work
```

The application will be available at `http://localhost:8000`

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage
```

## 📚 Documentation

Comprehensive documentation is available in the `docs/` directory:

- **[PROJECT_PLAN.md](PROJECT_PLAN.md)** - Complete project overview, architecture, and planning
- **[TECHNICAL_SPECIFICATION.md](TECHNICAL_SPECIFICATION.md)** - Detailed database schema and API endpoints
- **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)** - Step-by-step implementation instructions

### Quick Links

- [Database Schema](TECHNICAL_SPECIFICATION.md#database-schema-detailed)
- [API Endpoints](TECHNICAL_SPECIFICATION.md#api-endpoints)
- [Implementation Phases](IMPLEMENTATION_GUIDE.md#phase-1-foundation-weeks-1-3)
- [Testing Strategy](IMPLEMENTATION_GUIDE.md#testing-strategy)
- [Deployment Guide](IMPLEMENTATION_GUIDE.md#deployment-checklist)

## 🗓️ Development Phases

### Phase 1: Foundation (Weeks 1-3)
- ✅ Project setup and initialization
- ✅ Database design and migrations
- ✅ Authentication system
- ✅ Basic user management

### Phase 2: Core Features (Weeks 4-7)
- 🔄 Property listing CRUD
- 🔄 Image upload and management
- 🔄 Search and filter functionality
- 🔄 Landlord dashboard

### Phase 3: Booking & Payments (Weeks 8-10)
- ⏳ Booking system
- ⏳ M-Pesa integration
- ⏳ Payment tracking
- ⏳ Contract generation

### Phase 4: Communication (Weeks 11-12)
- ⏳ Real-time chat
- ⏳ SMS integration
- ⏳ Email notifications
- ⏳ Notification center

### Phase 5: Advanced Features (Weeks 13-16)
- ⏳ AI recommendations
- ⏳ Virtual tours
- ⏳ Verification system
- ⏳ Reviews and ratings

### Phase 6: Mobile App (Weeks 17-20)
- ⏳ Flutter app development
- ⏳ API optimization
- ⏳ Push notifications
- ⏳ App store deployment

### Phase 7: Testing & Optimization (Weeks 21-23)
- ⏳ Comprehensive testing
- ⏳ Performance optimization
- ⏳ Security audit
- ⏳ Load testing

### Phase 8: Launch (Week 24)
- ⏳ Beta testing
- ⏳ Feedback collection
- ⏳ Production deployment
- ⏳ Marketing launch

## 🔌 API Documentation

### Authentication
```http
POST /api/auth/register
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/verify-email
POST /api/auth/verify-phone
```

### Properties
```http
GET    /api/properties
POST   /api/properties
GET    /api/properties/{id}
PUT    /api/properties/{id}
DELETE /api/properties/{id}
POST   /api/properties/{id}/images
```

### Bookings
```http
GET  /api/bookings
POST /api/bookings
GET  /api/bookings/{id}
PUT  /api/bookings/{id}/approve
PUT  /api/bookings/{id}/reject
```

### Payments
```http
GET  /api/payments
POST /api/payments/initiate-mpesa
POST /api/payments/mpesa-callback
```

For complete API documentation, see [TECHNICAL_SPECIFICATION.md](TECHNICAL_SPECIFICATION.md#api-endpoints)

## 🧪 Testing

### Test Coverage Goals
- Unit Tests: 80%+ coverage
- Feature Tests: All critical paths
- Integration Tests: Third-party services
- E2E Tests: User workflows

### Running Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/PropertyApiTest.php

# With coverage report
php artisan test --coverage --min=80

# Frontend tests
npm run test
```

## 🚢 Deployment

### Production Checklist

#### Pre-deployment
- [ ] All tests passing
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] Assets compiled and optimized
- [ ] API documentation complete
- [ ] Security audit completed

#### Deployment Steps
1. Set up cloud infrastructure (AWS/DigitalOcean)
2. Configure database and Redis
3. Deploy application code
4. Run migrations
5. Configure CDN
6. Set up SSL certificates
7. Configure monitoring (Sentry)
8. Test all critical paths
9. Enable automated backups
10. Go live!

#### Post-deployment
- [ ] Monitor error rates
- [ ] Check performance metrics
- [ ] Verify payment integration
- [ ] Test notification delivery
- [ ] Monitor user feedback

### Environment Variables

Required environment variables for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://muranga-rentals.com

DB_CONNECTION=mysql
DB_HOST=your_host
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

MPESA_CONSUMER_KEY=your_key
MPESA_CONSUMER_SECRET=your_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey

AT_USERNAME=your_username
AT_API_KEY=your_api_key

GOOGLE_MAPS_API_KEY=your_key
OPENAI_API_KEY=your_key

AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_BUCKET=your_bucket

SENTRY_LARAVEL_DSN=your_dsn
```

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards
- Follow PSR-12 for PHP code
- Use ESLint for JavaScript/Vue code
- Write tests for new features
- Update documentation as needed

## 📊 Project Status

**Current Status**: Planning & Design Phase

**Progress**: 
- ✅ Project planning complete
- ✅ Technical specifications defined
- ✅ Implementation guide created
- 🔄 Ready for development phase

## 🎓 Target Audience

### Primary Users
- **MUT Students**: Looking for affordable, verified housing near campus
- **Local Professionals**: Seeking quality rental properties in Murang'a County
- **Landlords**: Property owners wanting to reach verified tenants

### Geographic Focus
- Murang'a County, Kenya
- Primary focus: Areas near Murang'a University of Technology
- Secondary: Murang'a Town and surrounding areas

## 💡 Key Differentiators

1. **Hyper-Local Focus**: Specifically designed for Murang'a County
2. **Student-Centric**: Features tailored for university students
3. **Verification System**: All properties and users verified
4. **M-Pesa Integration**: Local payment method support
5. **Proximity Search**: Find properties near MUT campus
6. **AI Recommendations**: Personalized property suggestions
7. **Mobile-First**: Native mobile apps for better accessibility

## 📞 Support

For support, please contact:
- Email: support@muranga-rentals.com
- Phone: +254 XXX XXX XXX
- Website: https://muranga-rentals.com

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Murang'a University of Technology community
- Local landlords and property owners
- Beta testers and early adopters
- Open source community

## 🗺️ Roadmap

### Q2 2026
- ✅ Complete planning and design
- 🔄 Begin Phase 1 development
- 🔄 Set up infrastructure

### Q3 2026
- Complete core features
- Integrate M-Pesa payments
- Launch beta version

### Q4 2026
- Mobile app development
- Advanced features implementation
- Public launch

### 2027
- Expand to other universities
- Add more features based on feedback
- Scale infrastructure

---

**Built with ❤️ for the Murang'a community**

For detailed implementation instructions, see [IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)