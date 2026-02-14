# CRM Technical Exam

## Tech Stack
- Laravel 10
- Angular 17
- MySQL 8
- Elasticsearch 8
- Docker Compose

## How To Run

1. Clone repository
2. docker compose up --build
3. php artisan migrate
4. Access http://localhost:8000

## API Endpoints

GET /api/customers
POST /api/customers
PUT /api/customers/{id}
DELETE /api/customers/{id}
GET /api/customers?search=

## Notes
- Email is unique (DB + validation)
- All create/update/delete sync to Elasticsearch
- Search supports partial matching
