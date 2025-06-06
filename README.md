# 🧩 **jr-api**

A **multi-tenant REST API** built with **PHP 8.2**, featuring **JWT authentication**, **role-based access control**, and **email notifications**. Designed for scalable user management across multiple client domains.

---

## 🔑 Key Capabilities

* User registration/authentication
* Role-based permissions
* Password management
* Email integration
* Tenant isolation

---

## 🚀 Features Overview

* **Multi-tenant Architecture** – Domain-based client isolation with shared codebase
* **JWT Authentication** – Secure token-based authentication with device binding
* **Role-based Access Control** – Hierarchical permissions (user, moderator, admin)
* **User Management** – Registration, activation, blocking/unblocking capabilities
* **Email Notifications** – Account activation and password reset emails
* **Password Management** – Secure password hashing and reset functionality
* **RESTful API Design** – Clean endpoint structure with proper HTTP methods
* **Docker Environment** – Containerized development with PostgreSQL and MailHog

---

## 🧰 Tech Stack

| Layer         | Technology                                                |
| ------------- | --------------------------------------------------------- |
| **Backend**   | PHP 8.2, Apache                                           |
| **Database**  | PostgreSQL 15                                             |
| **ORM**       | Doctrine ORM (attribute-based mapping)                    |
| **Framework** | Symfony Components (HTTP Foundation, Routing, Validation) |
| **Auth**      | Firebase JWT (custom claims)                              |
| **Email**     | PHPMailer with SMTP                                       |
| **DI**        | PHP-DI container                                          |
| **Docker**    | Docker & Docker Compose                                   |
| **Dev Tools** | MailHog                                                   |

---

## 🗂 Project Structure

```
├── src/
│   ├── Entity/          # Doctrine entities (User, Client)
│   ├── Controller/      # API controllers
│   ├── Service/         # Business logic services
│   ├── Repository/      # Data access layer
│   ├── Exception/       # Custom exception classes
│   ├── DTO/             # Data transfer objects
│   └── Bootstrap/       # Application bootstrapping
├── config/              # Configuration files (routes, app settings)
├── public/              # Web root with entry point
├── bin/                 # CLI tools (Doctrine commands)
└── docker-compose.yml   # Container orchestration
```

---

## 📘 API Documentation

**Base URL:** `http://localhost`
**Authentication:** Bearer token in `Authorization` header for user/admin endpoints

---

### 👤 Guest Endpoints (No Authentication Required)

* `POST /guest/login` – User authentication
* `POST /guest/register` – User registration
* `POST /guest/activate` – Account activation via token
* `POST /guest/password-forgot` – Request password reset
* `POST /guest/password-reset` – Reset password with token

---

### 🔐 User Endpoints (Authentication Required)

* `GET /users/{id}` – Get user details
* `POST /users/renew` – Renew JWT token

---

### 🛠 Admin Endpoints (Admin/Moderator Only)

* `POST /users/{id}/block` – Block user account
* `POST /users/{id}/unblock` – Unblock user account

---

**Request Format:** JSON for `POST` requests
**Response Format:** JSON with `success`, `data`, and `error` fields
**Auth Header:** `Authorization: Bearer <jwt_token>`

---

## 🏢 Multi-Tenancy

Client isolation is achieved through **domain-based tenant resolution**.
Each client is identified by the request’s **Host header**, allowing multiple organizations to use the same API instance with **complete data separation**.

Users belong to specific clients and can only access resources within their tenant scope.

---

## 🔐 Authentication Flow

1. **Registration** → User registers → Receives activation email → Activates account
2. **Login** → User provides credentials → Receives JWT with client/device binding
3. **Authorization** → JWT required for protected endpoints → Validates user, client, and device
4. **Role Permissions** → Admins manage moderators/users; moderators manage users
5. **Token Renewal** → Active users refresh tokens without re-authentication

**Role Hierarchy:**
`Admin > Moderator > User`

---

## ❌ Error Handling

**Standard Response Format:**

```json
{
  "success": false,
  "data": null,
  "message": "ERROR_CODE"
}
```

### 🔁 Common Error Codes

* `BAD_CREDENTIALS` – Invalid login credentials
* `BAD_TOKEN` – Invalid or expired JWT token
* `NOT_FOUND_ERROR` – Resource not found
* `VALIDATION_ERROR` – Invalid request data
* `BUSINESS_ERROR` – Business logic violations
* `AUTH_ERROR` – Authentication failures

**Debug Mode:**
Development environment includes **detailed error traces** and **request context**.

---

## 📌 Missing Features (for later)

* **Installation & Setup** – Prerequisites, environment setup, DB migrations, initial client setup
* **Development** – Running dev environment, accessing services, Doctrine CLI usage, testing
* **Contributing** – Code style guidelines, development workflow
* **License** – License information
* **Database Schema** – Entity relationships, database structure details
* **Deployment** – Production deployment instructions
* **Detailed Examples** – Full curl examples for endpoints

---