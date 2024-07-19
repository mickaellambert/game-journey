# Game Journey

## Repository Status

🚧 **Under Construction** 🚧

This repository is currently a work in progress. I am actively developing the project and adding new features. Stay tuned for updates!

## Project Overview

This project is an API built with PHP and Symfony that allows users to create and manage their personal video game collections. The API integrates with the IGDB API to populate our database with game information as users add games to their libraries. Key features include:

- **User Management**: Users can register and manage their accounts.
- **Game Collection**: Users can add games to their personal collections.
- **IGDB Integration**: Automatically fetch game details from the IGDB API.
- **Status Tracking**: Track the status of each game (e.g., Not Started, In Progress, Completed).

The project adheres to the latest coding standards and best practices, including PSR, SOLID principles, and comprehensive API documentation.

## Getting Started

### Prerequisites

- PHP (version 8.1 or higher)
- Symfony (latest version)
- MySQL
- Composer

### Installation

1. Clone the repository:
```sh
git clone https://github.com/your-username/your-repo-name.git
````

2. Install project dependencies :
```sh
composer install
```

3. Set up the environment variables:
```sh
cp .env .env.local
```

4. Create and run the database migrations
```sh
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Import fake datas if you need them
```sh
php bin/console doctrine:fixtures:load
```

6. Start the server
```sh
symfony server:start
```
  
### Usage
Once the server is running, you can access the API documentation at:

```bash
http://localhost:8000/api
```
