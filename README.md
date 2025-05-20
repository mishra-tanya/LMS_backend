<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Learning Management System (LMS) Backend

A robust Laravel-based API backend for a Learning Management System that manages courses, subjects, chapters, and student reviews.

## Table of Contents

- [Project Overview](#project-overview)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [API Documentation](#api-documentation)
  - [Request & Response Formats](#request--response-formats)
  - [Authentication Endpoints](#authentication-endpoints)
  - [User Endpoints](#user-endpoints)
  - [Course Endpoints](#course-endpoints)
  - [Subject Endpoints](#subject-endpoints)
  - [Chapter Endpoints](#chapter-endpoints)
  - [Review Endpoints](#review-endpoints)
- [Authentication](#authentication)
- [Installation](#installation)
- [Environment Setup](#environment-setup)
- [Running the Project](#running-the-project)
- [Sample Data](#sample-data)

## Project Overview

This LMS (Learning Management System) backend provides a comprehensive API for managing educational content including courses, subjects, chapters, and student reviews. The system includes user authentication with JWT, email verification, and password reset functionality.

## Technology Stack

- **Framework**: Laravel
- **PHP Version**: 8.x
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Tokens)
- **API Format**: RESTful JSON

## Project Structure

The project follows Laravel's standard MVC architecture with additional organization:

```
LMS_backend/
├── app/
│   ├── Helpers/           # Custom helper classes (ApiResponse)
│   ├── Http/
│   │   ├── Controllers/   # API controllers
│   │   └── Requests/      # Form requests for validation
│   ├── Models/            # Eloquent models
│   ├── Providers/         # Service providers
│   └── Services/          # Business logic services
├── config/                # Configuration files
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── routes/
│   ├── api.php            # API routes
│   ├── web.php            # Web routes
│   └── console.php        # Console commands
└── tests/                 # Automated tests
```

## Database Schema

The system consists of the following core entities:

### Users

The standard Laravel users table with additional fields for authentication.

### Courses

```php
Schema::create('courses', function (Blueprint $table) {
    $table->id('course_id');
    $table->string('course_name');
    $table->integer('total_semester');
    $table->timestamps();
});
```

### Subjects

```php
Schema::create('subjects', function (Blueprint $table) {
    $table->id('subject_id');
    $table->string('subject_name');
    $table->unsignedBigInteger('course_id');
    $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
    $table->string('resource_link')->nullable();
    $table->integer('semester');
    $table->timestamps();
});
```

### Chapters

```php
Schema::create('chapters', function (Blueprint $table) {
    $table->id('chapter_id');
    $table->string('chapter_name');
    $table->unsignedBigInteger('subject_id');
    $table->foreign('subject_id')->references('subject_id')->on('subjects')->onDelete('cascade');
    $table->string('resource_link')->nullable();
    $table->timestamps();
});
```

### Reviews

```php
Schema::create('reviews', function (Blueprint $table) {
    $table->id('review_id');
    $table->unsignedBigInteger('course_id');
    $table->unsignedBigInteger('user_id');
    $table->integer('rating')->default(0);
    $table->text('review_description')->nullable();
    $table->boolean('is_approved')->default(false);
    $table->timestamps();
    
    $table->foreign('course_id')->references('course_id')->on('courses')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```

## API Documentation

### Request & Response Formats

All API requests with a body should be sent as `application/json` content type. All responses are returned in JSON format.

### Authentication Endpoints

| Method | Endpoint                         | Description                             | Request Body                                                | Response                                          |
|--------|---------------------------------|-----------------------------------------|------------------------------------------------------------|---------------------------------------------------|
| POST   | `/api/login`                     | User login                              | JSON: `{ "email": "user@example.com", "password": "secret" }` | JWT token, user data                              |
| POST   | `/api/register`                  | User registration                       | JSON: `{ "name": "John Doe", "email": "user@example.com", "password": "secret", "password_confirmation": "secret" }` | User data, verification info                      |
| POST   | `/api/email/verification-notification` | Resend verification email         | No body required (JWT auth header needed)                   | Confirmation message                              |
| GET    | `/api/email/verify/{id}/{hash}`  | Verify email address                    | No body required (URL parameters)                           | Verification status                               |
| POST   | `/api/forgot-password`           | Request password reset                  | JSON: `{ "email": "user@example.com" }`                     | Reset link info                                   |
| POST   | `/api/reset-password`            | Reset password                          | JSON: `{ "email": "user@example.com", "token": "reset-token", "password": "newpassword", "password_confirmation": "newpassword" }` | Success message                                   |

### User Endpoints (Protected)

| Method | Endpoint                         | Description                             | Request Body                                                | Response                                          |
|--------|---------------------------------|-----------------------------------------|------------------------------------------------------------|---------------------------------------------------|
| GET    | `/api/me`                        | Get authenticated user info             | No body required (JWT auth header needed)                   | User data                                         |
| POST   | `/api/logout`                    | Logout user                             | No body required (JWT auth header needed)                   | Logout confirmation                               |

### Course Endpoints

| Method | Endpoint                         | Description                             | Request Body                                                | Response                                          |
|--------|---------------------------------|-----------------------------------------|------------------------------------------------------------|---------------------------------------------------|
| GET    | `/api/courses`                   | Get all courses                         | No body required                                           | List of courses                                   |
| GET    | `/api/courses/{id}`              | Get course by ID                        | No body required                                           | Course details                                    |
| POST   | `/api/courses`                   | Create new course                       | JSON: `{ "course_name": "Computer Science", "total_semester": 8 }` | Created course                                    |
| PUT    | `/api/courses/{id}`              | Update course                           | JSON: `{ "course_name": "Data Science", "total_semester": 6 }` | Updated course                                    |
| DELETE | `/api/courses/{id}`              | Delete course                           | No body required                                           | Success message                                   |

### Subject Endpoints

| Method | Endpoint                         | Description                             | Request Body                                                | Response                                          |
|--------|---------------------------------|-----------------------------------------|------------------------------------------------------------|---------------------------------------------------|
| GET    | `/api/subjects`                  | Get all subjects                        | No body required                                           | List of subjects                                  |
| GET    | `/api/subjects/{id}`             | Get subject by ID                       | No body required                                           | Subject details                                   |
| GET    | `/api/subjects/course/{course_id}` | Get subjects by course ID            | No body required                                           | List of subjects for course                       |
| POST   | `/api/subjects`                  | Create new subject                      | JSON: `{ "subject_name": "Data Structures", "course_id": 1, "resource_link": "https://example.com/ds-resources", "semester": 3 }` | Created subject                                   |
| PUT    | `/api/subjects/{id}`             | Update subject                          | JSON: `{ "subject_name": "Advanced Data Structures", "course_id": 1, "resource_link": "https://example.com/ads-resources", "semester": 4 }` | Updated subject                                   |
| DELETE | `/api/subjects/{id}`             | Delete subject                          | No body required                                           | Success message                                   |

### Chapter Endpoints

| Method | Endpoint                          | Description                             | Request Body                                                | Response                                          |
|--------|----------------------------------|-----------------------------------------|------------------------------------------------------------|---------------------------------------------------|
| GET    | `/api/chapters`                   | Get all chapters                        | No body required                                           | List of all chapters with subject details         |
| GET    | `/api/chapters/{id}`              | Get chapter by ID                       | No body required                                           | Chapter details                                   |
| GET    | `/api/chapters/subject/{subject_id}` | Get chapters by subject ID          | No body required                                           | Subject details with its chapters                 |
| GET    | `/api/chapters/course/{course_id}` | Get all chapters for a course         | No body required                                           | Chapters organized by subjects for the course     |
| POST   | `/api/chapters`                   | Create new chapter                      | JSON: `{ "chapter_name": "Introduction to Arrays", "subject_id": 7, "resource_link": "https://example.com/arrays" }` | Created chapter                                   |
| PUT    | `/api/chapters/{id}`              | Update chapter                          | JSON: `{ "chapter_name": "Advanced Arrays", "subject_id": 7, "resource_link": "https://example.com/advanced-arrays" }` | Updated chapter                                   |
| DELETE | `/api/chapters/{id}`              | Delete chapter                          | No body required                                           | Success message                                   |

### Review Endpoints

| Method | Endpoint                          | Description                             | Request Body                                               | Response                                          |
|--------|----------------------------------|-----------------------------------------|-----------------------------------------------------------|---------------------------------------------------|
| POST   | `/api/reviews`                    | Create new review                       | JSON: `{ "course_id": 1, "rating": 5, "review_description": "Excellent course material and teaching!" }` | Created review                                    |
| GET    | `/api/reviews/subject/{subject_id}` | Get reviews by subject ID            | No body required                                           | List of reviews for subject                       |
| PUT    | `/api/reviews/{review_id}`        | Approve review                          | JSON: `{ "is_approved": true }`                            | Updated review                                    |
| DELETE | `/api/reviews/{review_id}`        | Delete review                           | No body required                                           | Success message                                   |

## Response Format

All API responses follow a consistent format:

### Success Response

```json
{
    "status": true,
    "message": "Success message",
    "data": {
        // Response data
    }
}
```

### Error Response

```json
{
    "status": false,
    "error_type": "client_error|server_error",
    "message": "Error message",
    "data": {
        // Optional error details
    }
}
```

## Authentication

The API uses JWT (JSON Web Token) for authentication. Protected routes require a valid JWT token to be included in the Authorization header:

```
Authorization: Bearer {token}
```

## Installation

1. Clone the repository:
   ```bash
   git clone https://your-repo-url/LMS_backend.git
   cd LMS_backend
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Generate JWT secret:
   ```bash
   php artisan jwt:secret
   ```

## Environment Setup

Configure your `.env` file with the following settings:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_db
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=your_mail_port
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Running the Project

1. Run database migrations:
   ```bash
   php artisan migrate
   ```

2. Start the development server:
   ```bash
   php artisan serve
   ```

3. The API will be available at:
   ```
   http://localhost:8000/api
   ```

## Testing

Run the automated tests with:
```bash
php artisan test
```

## Sample Data

The system includes seeders to populate the database with sample educational content:

1. **CourseSeeder**: Creates sample courses including Computer Science, Electrical Engineering, Data Science, and AI/ML.

2. **SubjectSeeder**: Adds subjects for each course organized by semester.

3. **ChapterSeeder**: Populates chapters for various subjects including:
   - Data Structures chapters (arrays, linked lists, trees, etc.)
   - Object-Oriented Programming chapters (inheritance, polymorphism, etc.)
   - Python Programming chapters
   - Deep Learning chapters

To seed your database with this sample data, run:
```bash
php artisan db:seed
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
