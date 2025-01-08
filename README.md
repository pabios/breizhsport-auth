# BreizhSport Auth Microservice

The `auth` microservice is responsible for managing user authentication and authorization within the BreizhSport application.

## Features

- JWT-based authentication
- Secure login endpoint
- User role management

## Prerequisites

Ensure the following are installed on your machine:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/)
- PHP 8.3+
- Composer

## Repository Structure

```
BreizhSport-auth/
├── config/
├── src/
├── .env
├── Dockerfile
├── composer.json
├── README.md
```

## Getting Started

### Environment Variables

The `.env` file contains all necessary configurations:

```
APP_ENV=dev
APP_SECRET=<your_secret>
DATABASE_URL="postgresql://postgres:postgres@database:5432/breizhsport_auth_db"
JWT_SECRET_KEY="/app/config/jwt/private.pem"
JWT_PUBLIC_KEY="/app/config/jwt/public.pem"
JWT_PASSPHRASE=<your_passphrase>
```

Ensure the `JWT_SECRET_KEY` and `JWT_PUBLIC_KEY` paths are correct and point to the generated private and public keys.

### Building and Running the Service

To build and run the `auth` microservice:

```bash
docker build -t breizhsport-auth .
docker run --name auth -p 8081:80 --env-file .env breizhsport-auth
```

Alternatively, use Docker Compose from the `infra` repository:

```bash
docker-compose up --build auth
```

### Generating JWT Keys

Generate the private and public keys for JWT:

```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 2048
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

Set the correct permissions:

```bash
chmod 600 config/jwt/private.pem
chmod 644 config/jwt/public.pem
```

### Database Setup

Run the following commands to create and migrate the database:

```bash
docker exec -it auth bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Loading Fixtures

To load dummy data for testing:

```bash
php bin/console doctrine:fixtures:load
```

## API Endpoints

### Login

**POST** `/api/login`

- **Description**: Authenticates a user and returns a JWT token.
- **Payload**:

```json
{
  "email": "user@example.com",
  "password": "Password123!"
}
```

- **Response**:

```json
{
  "token": "<jwt_token>"
}
```

### Debug Token

**GET** `/api/debug-token`

- **Description**: Verifies the validity of a JWT token.
- **Response**:

```json
{
  "username": "user@example.com",
  "roles": ["ROLE_USER"]
}
```

## Testing

Run the tests with:

```bash
php bin/phpunit
```

## Contribution

To contribute to this microservice, fork the repository, make your changes, and submit a pull request.

## License

This project is licensed under the MIT License.

