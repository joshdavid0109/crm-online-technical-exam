CRM Technical Exam

A Dockerized full-stack CRM application built with modern development practices.

🧱 Tech Stack

Backend: Laravel 10 (PHP-FPM)

Frontend: Angular 17

Database: MySQL 8

Search Engine: Elasticsearch 8

Web Server: Nginx (reverse proxy)

Containerization: Docker Compose

🏗 Architecture
Browser
   ↓
Nginx (controller)
   ├── Angular (static build)
   └── /api → Laravel (PHP-FPM)
                    ├── MySQL
                    └── Elasticsearch


Highlights:

Angular is built and served via Nginx

Laravel runs in a dedicated PHP-FPM container

Elasticsearch handles full-text search

Services are isolated and orchestrated via Docker Compose

⚙️ How To Run
1️⃣ Clone the repository
git clone <repository-url>
cd crm-online-technical-exam

2️⃣ Start the containers
docker compose up --build

3️⃣ Run migrations
docker exec -it crm_api sh
php artisan migrate --force

4️⃣ Access the application

Frontend: http://localhost:8000

API Base URL: http://localhost:8000/api

Elasticsearch: http://localhost:9200

📡 API Endpoints

GET /api/customers

POST /api/customers

PUT /api/customers/{id}

DELETE /api/customers/{id}

GET /api/customers?search=keyword

🔍 Search Implementation

Powered by Elasticsearch

Uses multi_match query

Supports:

Partial word search

First-letter matching

Automatically syncs on:

Create

Update

Delete

Gracefully handles Elasticsearch failures

✅ Validation Rules

Email must be unique

Database-level constraint

Request validation rule

First & Last Name

Letters

Spaces

Hyphens only

Phone number

Validated per selected country format

🧪 Testing

Run tests inside container:

docker exec -it crm_api php artisan test


Includes:

Feature tests for CRUD

Search endpoint tests

Elasticsearch mocked during tests

Database isolation in testing environment