_**CRM Technical Exam**_

A Dockerized full-stack CRM application built with modern development practices.

**Tech Stack**

**Backend: **Laravel 10 (PHP-FPM)

**Frontend:** Angular 17

**Database:** MySQL 8

**Search Engine:** Elasticsearch 8

**Web Server:** Nginx (reverse proxy)

**Containerization:** Docker Compose

**Architecture**
Browser
   ↓
Nginx (controller)
   ├── Angular (static build)
   └── /api → Laravel (PHP-FPM)
                    ├── MySQL
                    └── Elasticsearch


**Highlights:**

   Angular is built and served via Nginx
   Laravel runs in a dedicated PHP-FPM container
   Elasticsearch handles full-text search
   Services are isolated and orchestrated via Docker Compose

**How To Run**
1️. Clone the repository
git clone <repository-url>
cd crm-online-technical-exam

2️. Start the containers
docker compose up --build

3️. Run migrations
docker exec -it crm_api sh
php artisan migrate --force

4️. Access the application

_**Frontend:** _http://localhost:8000

_**API Base URL:**_ http://localhost:8000/api

_**Elasticsearch:**_ http://localhost:9200


**API Endpoints**

GET /api/customers

POST /api/customers

PUT /api/customers/{id}

DELETE /api/customers/{id}

GET /api/customers?search=keyword


_**Search Implementation**_

   - Elasticsearch
   - Uses multi_match query

_**Supports:**_
   - Partial word search
   - First-letter matching

_**Automatically syncs on:**_
Create
Update
Delete


_**Validation Rules**_
   - Email must be unique
   - Database-level constraint

_**Request validation rule**_
   - First & Last Name
   - Letters
   - Spaces
   - Hyphens only
   - Phone number

_**Validated per selected country format**_

_**Testing**_

Run tests inside container:

**docker exec -it crm_api php artisan test**


Includes:
Feature tests for CRUD
Search endpoint tests
Elasticsearch mocked during tests
Database isolation in testing environment
