# GEMINI.md - Laks AI Agent

## Project Overview

This project, **Laks AI Agent**, is a sophisticated AI chat application built with PHP. It's designed with a modern glassmorphism UI and is specifically tailored for Indonesian developers, with documentation primarily in Bahasa Indonesia.

The application functions as a web-based chat interface that can connect to multiple AI models (including OpenAI's GPT, Anthropic's Claude, and Google's Gemini). A key feature is its ability to connect to user-provided PostgreSQL or MySQL databases, allowing the AI to answer questions by generating and executing SQL queries based on natural language prompts.

### Core Technologies

*   **Backend:** PHP 8.1+
*   **Dependencies:** `guzzlehttp/guzzle` for HTTP requests. Managed via Composer.
*   **Database:** Supports SQLite (default), MySQL, and PostgreSQL. It uses a custom PDO wrapper for database interaction and has a built-in migration system.
*   **Frontend:** Vanilla JavaScript, CSS3, Chart.js for data visualization, and Leaflet.js for maps.
*   **DevOps:** The entire environment is containerized using Docker and Docker Compose. It includes configurations for a PHP application service, Nginx reverse proxy, and optional database services (MySQL, PostgreSQL, Redis).

### Architecture

The application follows a standard PHP web application structure:

*   `public/`: Contains the frontend assets (`chat.html`) and the main API entry point (`index.php`).
*   `src/`: Contains the backend PHP code, organized by feature (e.g., `Auth`, `Chat`, `Database`, `Client`). The code is namespaced under `PhpCopilot\\`.
*   `storage/`: Holds the default SQLite database (`database.sqlite`) and other storage files.
*   `docker/`: Contains Docker-related configuration files, such as the Nginx setup.
*   `migrate.php`: A CLI script for managing database schema migrations.
*   `auth.php`: A CLI script to handle GitHub OAuth authentication, which is required to use the AI functionalities.

## Building and Running

The project is designed to be run with Docker.

### 1. Initial Setup

First-time setup requires copying the environment file:

```bash
cp .env.example .env
```

The `.env` file contains configuration for the application, including database connections and API keys.

### 2. Running the Application (Docker)

The quickest way to get started is using Docker Compose. This will start the application using the default SQLite database.

```bash
docker-compose up -d
```

The application will be available at `http://localhost:8123/chat.html`.

### 3. Running with Other Databases

The `docker-compose.yml` uses profiles to enable optional services like MySQL or PostgreSQL.

*   **To run with MySQL:**
    ```bash
    docker-compose --profile mysql up -d
    ```

*   **To run with PostgreSQL:**
    ```bash
    docker-compose --profile postgres up -d
    ```

When using an external database, you must update the `DB_*` variables in the `.env` file accordingly.

### 4. Database Migrations

After starting the application for the first time with a fresh database, you need to run the migrations.

```bash
docker-compose exec app php migrate.php
```

To reset the database entirely:

```bash
docker-compose exec app php migrate.php fresh
```

### 5. Authentication

The AI features require authentication with GitHub. Run the following command and follow the on-screen instructions:

```bash
docker-compose exec app php auth.php
```

## Development Conventions

### Testing

The `README.md` file provides commands for running tests:

*   **PHP Syntax Check:**
    ```bash
    find src public -name "*.php" -print0 | xargs -0 -n1 php -l
    ```

*   **Code Style (PSR-12):**
    ```bash
    # composer require --dev squizlabs/php_codesniffer
    vendor/bin/phpcs --standard=PSR12 src/
    ```

*   **Security Scan:**
    ```bash
    composer audit
    ```

### API Endpoints

The main API is defined in `public/index.php`. Key endpoints include:

*   `POST /api/login`: User login.
*   `GET /api/status`: Check authentication status.
*   `POST /api/chat`: The main endpoint for sending chat messages to the AI.
*   `POST /api/auth/start`: Initiates the GitHub device flow authentication.
*   `/api/postgres/*` and `/api/mysql/*`: A suite of endpoints for managing external database connections.

### Database Interaction

All database logic is handled through the `src/Database/` classes. The main `Database` class provides a generic interface for SQLite, while `PostgresConnection.php` and `MySQLConnection.php` extend functionality for those specific database systems. Migrations are located in `src/Database/Migrations/`.
