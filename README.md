# Game Journey

## Repository Status

🚧 **Under Construction** 🚧

This repository is currently a work in progress. We are actively developing the project and adding new features. Stay tuned for updates!

## Project Overview

**Game Journey** is a web application that allows users to create and manage their personal video game collections. The backend is built with PHP and Symfony, while the frontend is developed using React. The application integrates with the IGDB API to automatically populate the database with game information as users add games to their collections. Key features include:

- **User Management**: Users can register and manage their accounts.
- **Game Collection**: Users can add and track games in their personal collections.
- **IGDB Integration**: Automatic retrieval of game details from the IGDB API.
- **Status Tracking**: Monitor the status of each game (e.g., Not Started, In Progress, Completed).

The project follows modern development practices, including adherence to PSR standards, SOLID principles, and comprehensive API documentation.

## Project Structure

The project is divided into two main directories:

- `backend/`: Contains the Symfony-based API.
- `frontend/`: Contains the React-based frontend.

## Getting Started

### Prerequisites

To run this project locally, you will need:

- **PHP**: 8.3.9
- **Symfony**: 7.1.2
- **MySQL**: 8.0.30
- **Composer**: 2.7.7
- **Node.js**: 20.11.0
- **npm**: 10.2.4
- **React**: 18.3.1
- **Git**: 2.45.2

### Installation

1. **Clone the repository:**

    ```sh
    git clone https://github.com/your-username/your-repo-name.git
    cd your-repo-name
    ```

2. **Set up the backend:**

    a. Navigate to the backend directory:

    ```sh
    cd backend
    ```

    b. Install backend dependencies:

    ```sh
    composer install
    ```

    c. Set up the environment variables:

    ```sh
    cp .env .env.local
    ```

    d. Create and run the database migrations:

    ```sh
    php bin/console doctrine:database:create
    php bin/console doctrine:migrations:migrate
    ```

    e. Start the Symfony server:

    ```sh
    symfony server:start
    ```

3. **Set up the frontend:**

    a. Navigate to the frontend directory:

    ```sh
    cd ../frontend
    ```

    b. Install frontend dependencies:

    ```sh
    npm install
    ```

    c. Start the React development server:

    ```sh
    npm start
    ```

### Usage

Once the servers are running, you can access the following:

- **API Documentation:** Access the API documentation at:
  
    ```bash
    http://localhost:8000/api
    ```

- **React Frontend:** Access the frontend at:
  
    ```bash
    http://localhost:3000
    ```

## Contributing

Contributions are welcome! Please follow the standard GitHub fork and pull request workflow. Before starting work on a feature or bugfix, please create an issue to discuss it.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
