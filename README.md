# SubbieHub - Website Generator for Subcontractors

A website generator built with Symfony (PHP 8.4) backend and Vue (TypeScript) frontend, containerized with Docker.

## Features

- Wizard-style form with minimal fields per step
- Step 1: Business Name and ABN (Australian Business Number) validation
- Modern, responsive UI with Vue 3 SPA
- RESTful API with Symfony
- PostgreSQL database
- Fully containerized with Docker

## Prerequisites

- Docker
- Docker Compose

## Getting Started

### 1. Build and Start Containers

```bash
docker-compose up -d --build
```

This will start three services:
- **Backend** (Symfony): http://localhost:8000
- **Frontend** (Vue): http://localhost:5173
- **Database** (PostgreSQL): localhost:5432

### 2. Install Backend Dependencies

```bash
docker-compose exec backend composer install
```

### 3. Run Database Migrations

```bash
docker-compose exec backend php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Access the Application

Open your browser and navigate to:
- **Frontend**: http://localhost:5173
- **Backend API**: http://localhost:8000/api/wizard/step1

## Project Structure

```
.
├── backend/                    # Symfony PHP 8.4 backend
│   ├── config/                # Configuration files
│   ├── migrations/            # Database migrations
│   ├── public/                # Public web root
│   └── src/
│       ├── Controller/        # API controllers
│       ├── Entity/            # Doctrine entities
│       └── Kernel.php
├── frontend/                   # Vue TypeScript frontend
│   ├── src/
│   │   ├── components/        # Vue components
│   │   ├── App.vue           # Root component
│   │   └── main.ts           # App entry point
│   └── index.html
└── docker-compose.yml         # Docker orchestration
```

## API Endpoints

### POST /api/wizard/step1
Submit step 1 of the wizard form.

**Request Body:**
```json
{
  "name": "Smith Plumbing Services",
  "abn": "12345678901"
}
```

**Response:**
```json
{
  "success": true,
  "submissionId": 1,
  "message": "Step 1 completed successfully"
}
```

### GET /api/wizard/submissions/{id}
Retrieve a submission by ID.

**Response:**
```json
{
  "success": true,
  "submission": {
    "id": 1,
    "name": "Smith Plumbing Services",
    "abn": "12345678901",
    "mobile": "0412345678",
    "email": "john@example.com",
    "currentStep": 2
  }
}
```

## Development

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f backend
docker-compose logs -f frontend
```

### Stop Containers

```bash
docker-compose down
```

### Rebuild After Changes

```bash
docker-compose down
docker-compose up -d --build
```

### Access Container Shell

```bash
# Backend
docker-compose exec backend bash

# Frontend
docker-compose exec frontend sh
```

## Database

The PostgreSQL database is accessible at `localhost:5432` with:
- **Database**: SubbieHub
- **User**: SubbieHub
- **Password**: SubbieHub

## Next Steps

- Implement additional wizard steps
- Add website generation logic
- Implement file uploads for logos/images
- Add email notifications
- Deploy to production

## Tech Stack

- **Backend**: Symfony 7.1, PHP 8.4 (FPM), nginx, Doctrine ORM
- **Frontend**: Vue 3, TypeScript, Vite, Vue Router, Axios, Tailwind CSS, shadcn-vue
- **Database**: PostgreSQL 16
- **Infrastructure**: Docker, Docker Compose
