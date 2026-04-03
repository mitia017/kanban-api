# Kanban API Testing Instructions

## 🔐 Authentication

### 1. Registration
- **URL**: `POST /api/register`
- **Body (JSON)**:
  ```json
  {
    "name": "User Name",
    "email": "user@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Note**: The role is automatically set to `user`. Default admins should be created via seeder.

### 2. Login
- **URL**: `POST /api/login`
- **Body (JSON)**:
  ```json
  {
    "email": "admin@kanban.com",
    "password": "password"
  }
  ```
- **Response**: Copy the `access_token` for Bearer Token authentication.

### 3. Logout
- **URL**: `POST /api/logout`
- **Headers**: `Authorization: Bearer <token>`

---

## 👤 User Roles

- **Admin**: Has full access. Can assign tasks to any user.
- **User**: Can only see tasks assigned to them. Cannot reassign tasks.

---

## 🧩 Task Assignment (Admin Only)

### Assign a Task
- **URL**: `POST /api/tasks/{task_id}/assign`
- **Headers**: `Authorization: Bearer <admin_token>`
- **Body (JSON)**:
  ```json
  {
    "assigned_to": 2
  }
  ```

---

## 📋 Task Retrieval

### Get My Tasks
- **URL**: `GET /api/my-tasks`
- **Headers**: `Authorization: Bearer <token>`

### Get Tasks for a Column
- **URL**: `GET /api/columns/{column_id}/tasks`
- **Headers**: `Authorization: Bearer <token>`
- **Note**: Admins see all tasks in the column, Users only see their assigned tasks.

---

## 🚀 Setup & Seed
To test with default accounts, run:
```bash
php artisan migrate:fresh --seed
```
Default accounts:
- **Admin**: `admin@kanban.com` / `password`
- **User**: `user@kanban.com` / `password`
