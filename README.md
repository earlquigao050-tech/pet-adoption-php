# 🐾 PetAdopt India - Pet Adoption Website

A pet adoption website and Android-ready API backed by a standalone Node.js/Express server and MySQL.

## 🌐 Live Demo
http://13.126.251.220/pet-adoption/

## 🌟 Features
- Browse 15+ pets - Dogs Cats Birds Rabbits
- Filter by species size gender
- User registration and login
- Send adoption requests
- Admin panel - manage pets and requests
- Mobile friendly Bootstrap 5 design
- Pet photo upload
- Secure password hashing

## 🛠️ Tech Stack
| Technology | Details |
|-----------|---------|
| Website | PHP 8.3 |
| API backend | Node.js 20+ / Express |
| Database | MySQL 8.0 |
| Website web server | Apache 2.4 |
| API reverse proxy | Nginx |
| Frontend | Bootstrap 5.3 |
| Cloud | AWS EC2 Ubuntu 24.04 |


## 📁 Project Structure
```text
pet-adoption/
├── config.php          - Website-to-API configuration
├── config.example.php  - Website configuration template
├── header.php          - Navbar and styles
├── footer.php          - Footer
├── index.php           - Homepage
├── pets.php            - All pets with filters
├── pet_detail.php      - Pet detail and adopt
├── register.php        - User register
├── login.php           - User login
├── dashboard.php       - User dashboard
├── logout.php          - Logout
├── database.sql        - Database file
├── uploads/            - Pet photos
├── api/                - Database migration
│   └── migration.sql
├── backend/            - Standalone Node.js/Express API
│   ├── server.js
│   ├── package.json
│   └── .env.example
└── admin/              - Website admin pages
    ├── dashboard.php
    ├── add_pet.php
    ├── edit_pet.php
    ├── manage_pets.php
    ├── manage_requests.php
    └── manage_users.php
```

## 🗄️ Database
| Table | Description |
|-------|-------------|
| users | User accounts |
| pets | Pet listings |
| adoption_requests | Adoption requests |

## 🚀 Deployment

### Website host

Install Apache and PHP, deploy the repository, and configure the website to call the Node API:

```bash
sudo apt update
sudo apt install apache2 libapache2-mod-php php -y
cd /var/www/html
sudo git clone https://github.com/AdityaGhodk/pet-adoption-php.git pet-adoption
sudo cp /var/www/html/pet-adoption/config.example.php /var/www/html/pet-adoption/config.php
```

Copy `config.example.php` to the ignored `config.php`, or set `API_BASE_URL=https://api.example.com/api/v1` in the Apache environment. The website host does not need Node.js or MySQL credentials.

### API server and database

Install Node.js 20+, MySQL, and Nginx on the API VPS:

```bash
sudo apt update
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs mysql-server nginx -y
```

Deploy the repository and install the schema on a **fresh** API database (`database.sql` drops and recreates the application tables):

```bash
cd /var/www
sudo git clone https://github.com/AdityaGhodk/pet-adoption-php.git pet-adoption-backend
sudo mysql < /var/www/pet-adoption-backend/database.sql
cd /var/www/pet-adoption-backend/backend
npm ci --omit=dev
cp .env.example .env
```

Set production values in `backend/.env`, including `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `PUBLIC_API_URL=https://api.example.com`, `HOST=127.0.0.1`, and a restricted `CORS_ORIGIN`. Run the app under a process manager such as systemd or PM2. Configure Nginx using [`backend/nginx-api.conf.example`](./backend/nginx-api.conf.example) and enable HTTPS. Allow database access only from the API host; the website does not need database credentials. Change the sample admin password before production.

## 📱 Android / JSON API

The website and Android app both use the standalone Express server. The API returns JSON and uses bearer tokens for protected actions. For Android Emulator with a local Node server, use `http://10.0.2.2:3000/api/v1`; for a physical phone use `http://<computer-LAN-IP>:3000/api/v1`. For deployment use the API VPS URL, such as `https://api.example.com/api/v1`. Use HTTPS for public access.

Android needs the `INTERNET` permission. For local HTTP development only, enable cleartext traffic in the debug app configuration; do not enable it for production:

```xml
<uses-permission android:name="android.permission.INTERNET" />
<application android:usesCleartextTraffic="true" ...>
```

### Separate website and API servers

The website is a PHP frontend client and does not connect to MySQL directly. It calls `backend/server.js` for pet listings, account actions, adoption requests, and admin operations. Android calls the same Express API. If the Node server is unavailable, the website's data actions and Android app will both be unavailable; static page assets can still be served by Apache.

1. Provide a MySQL database reachable by the API VPS. Only the Node backend needs database credentials. Do not expose MySQL publicly; use a private network or restrict access to the API server's address.
2. Deploy the repository to the API VPS, run `npm ci --omit=dev` inside `backend/`, and create `backend/.env` from `.env.example`. Set production database credentials and `PUBLIC_API_URL`.
3. Run `api/migration.sql` once against an existing API database (fresh installs include the table in `database.sql`). Keep the `uploads/` directory writable by the Node service.
4. Configure Nginx using [`backend/nginx-api.conf.example`](./backend/nginx-api.conf.example), enable HTTPS, and keep the Node port private behind Nginx.
5. On the website host, set `API_BASE_URL=https://api.example.com/api/v1` (or configure it in the ignored `config.php`). The website no longer needs MySQL credentials. Android uses the same API base URL.

Keep `backend/.env` and website `config.php` out of Git; both are ignored. Keep real credentials out of source control.

### Local development

Install and start the API:
```bash
cd backend
npm ci
npm start
```

Set `API_BASE_URL=http://127.0.0.1:3000/api/v1` in the website's `config.php` if you need to override its default. For Android Emulator, use `http://10.0.2.2:3000/api/v1`. Set `PUBLIC_API_URL` in the Node `.env` to an address reachable from the client (for example `http://10.0.2.2:3000` for Android Emulator or your computer's LAN IP for a physical phone) so pet image URLs work there too.

Fresh database installs include the token table in `database.sql`. Run `api/migration.sql` once on an existing database.

The API server is `backend/server.js`. `GET /api/v1/health` can be used to check connectivity.

### Endpoints

| Method | Path | Access |
|---|---|---|
| `GET` | `/health` | Public |
| `GET` | `/stats` | Public |
| `POST` | `/auth/register` | Public; JSON: `name`, `email`, `password` |
| `POST` | `/auth/login` | Public; JSON: `email`, `password` |
| `POST` | `/auth/logout` | Bearer token |
| `GET` | `/pets` | Public; optional `search`, `species`, `size`, `gender`, `status`, `limit` filters |
| `GET` | `/pets/{id}` | Public |
| `GET` | `/me` | Bearer token |
| `GET` | `/me/requests` | Bearer token |
| `POST` | `/pets/{id}/adoption-requests` | Adopter bearer token; JSON: `message` |
| `GET` | `/admin/requests` | Admin bearer token |
| `PATCH` | `/admin/requests/{id}` | Admin bearer token; JSON: `status` (`Approved` or `Rejected`) |
| `GET` | `/admin/dashboard` | Admin bearer token |
| `GET` / `POST` | `/admin/pets` | Admin bearer token; `POST` accepts JSON or multipart form data |
| `PATCH` / `DELETE` | `/admin/pets/{id}` | Admin bearer token; `PATCH` accepts pet fields and optional multipart image |
| `GET` | `/admin/users` | Admin bearer token |
| `DELETE` | `/admin/users/{id}` | Admin bearer token |

Login and registration return `access_token`, `token_type`, `expires_at`, and `user`. Send the token on protected calls as `Authorization: Bearer <access_token>`. Tokens expire after 30 days and are stored hashed in the database.

Example:

```bash
curl -X POST http://localhost:3000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@petadopt.com","password":"password"}'
```

All errors use an `error.message` JSON field and an appropriate HTTP status code.

## 📸 Screenshots

### 🏠 Homepage
![Homepage](Screenshot%202026-04-26%20011503.png)

### 🐾 Pets Page
![Pets](Screenshot%202026-04-26%20011523.png)

### 🔐 Login Page
![Login](Screenshot%202026-04-26%20011607.png)

### 📝 Register Page
![Register](Screenshot%202026-04-26%20011711.png)

### ⚙️ Admin Dashboard
![Admin](Screenshot%202026-04-26%20011741.png)

### 🐕 Manage Pets
![Manage Pets](Screenshot%202026-04-26%20011809.png)

### 📋 Manage Requests
![Requests](Screenshot%202026-04-26%20011950.png)
