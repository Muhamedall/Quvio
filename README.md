#  Quvio — Invoice & Quote SaaS Platform

Quvio is a modern full-stack SaaS application designed for freelancers and small businesses to manage quotes, invoices, and payments efficiently.

It provides a complete workflow — from creating quotes to receiving payments — with automation, analytics, and a clean user experience.

---

##  Features

*  Client Management (CRUD + search)
*  Create professional quotes with line items, taxes, and PDF preview
*  Convert quotes to invoices in one click
*  Invoice management with overdue tracking
*  Stripe payment link generation
*  Automated emails via n8n (quote sent, invoice created, paid, overdue)
*  PDF generation using DomPDF
*  Real-time dashboard with revenue analytics
*  Profile & branding customization
*  Secure API with UUID & Laravel Sanctum authentication

---

## 🛠 Tech Stack

| Layer      | Technology                |
| ---------- | ------------------------- |
| Frontend   | Angular 21 + Tailwind CSS |
| Backend    | Laravel 13 (PHP 8.3+)     |
| Database   | MySQL 8                   |
| Auth       | Laravel Sanctum           |
| Payments   | Stripe                    |
| Automation | n8n                       |
| PDF        | DomPDF                    |

---

##  Requirements

### Backend

* PHP >= 8.3
* Composer >= 2.x
* MySQL >= 8.0
* Node.js >= 20.19
* npm >= 10

### Frontend

* Node.js >= 20.19
* npm >= 10
* Angular CLI >= 21

---

##  Installation

### 1. Clone Repository

```bash
git clone https://github.com/Muhamedall/Quvio.git
cd quvio
```

---

### 2. Backend Setup (Laravel)

```bash
cd back-end
composer install
cp .env.example .env
php artisan key:generate
```

### Configure `.env`

```env
APP_NAME=Quvio
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:4200

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quvio
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Run Database & Server

```bash
php artisan migrate
php artisan db:seed
php artisan quvio:fill-uuids
php artisan serve
```

Backend URL:
 http://localhost:8000

---

### 3. Frontend Setup (Angular)

```bash
cd front-end
npm install
```

Update API URL in:
`src/environments/environment.ts`

```ts
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
};
```

Run frontend:

```bash
ng serve
```

Frontend URL:
 http://localhost:4200

---

## 🔑 Default Login

After seeding the database:

```
Email:    allaoui@quvio.com
Password: password123
```

---

##  Environment Variables

### Stripe (Optional)

```env
STRIPE_KEY=pk_test_xxxxxxxxxxxx
STRIPE_SECRET=sk_test_xxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx
```

---

### n8n Webhooks (Optional)

```env
N8N_QUOTE_CREATED_URL=http://localhost:5678/webhook/quote-sent
N8N_INVOICE_CREATED_URL=http://localhost:5678/webhook/invoice-created
N8N_INVOICE_PAID_URL=http://localhost:5678/webhook/invoice-paid
N8N_INVOICE_OVERDUE_URL=http://localhost:5678/webhook/invoice-overdue
```

---

## 🔌 API Reference

Base URL:

```
http://localhost:8000/api
```

### Authentication

| Method | Endpoint       | Description  |
| ------ | -------------- | ------------ |
| POST   | /auth/register | Register     |
| POST   | /auth/login    | Login        |
| POST   | /auth/logout   | Logout       |
| GET    | /auth/me       | Current user |

---

### Clients

| Method | Endpoint      | Description   |
| ------ | ------------- | ------------- |
| GET    | /clients      | List clients  |
| POST   | /clients      | Create client |
| GET    | /clients/{id} | Get client    |
| PUT    | /clients/{id} | Update client |
| DELETE | /clients/{id} | Delete client |

---

### Quotes

| Method | Endpoint               | Description        |
| ------ | ---------------------- | ------------------ |
| GET    | /quotes                | List quotes        |
| POST   | /quotes                | Create quote       |
| GET    | /quotes/{uuid}         | Get quote          |
| PUT    | /quotes/{uuid}         | Update             |
| DELETE | /quotes/{uuid}         | Delete             |
| POST   | /quotes/{uuid}/send    | Send quote         |
| POST   | /quotes/{uuid}/convert | Convert to invoice |
| GET    | /quotes/{uuid}/pdf     | Download PDF       |

---

### Invoices

| Method | Endpoint                      | Description          |
| ------ | ----------------------------- | -------------------- |
| GET    | /invoices                     | List invoices        |
| POST   | /invoices                     | Create invoice       |
| GET    | /invoices/{uuid}              | Get invoice          |
| PUT    | /invoices/{uuid}              | Update               |
| DELETE | /invoices/{uuid}              | Delete               |
| POST   | /invoices/{uuid}/send         | Send invoice         |
| GET    | /invoices/{uuid}/pdf          | Download PDF         |
| POST   | /invoices/{uuid}/payment-link | Generate Stripe link |
| POST   | /webhooks/stripe              | Stripe webhook       |

---

##  Project Structure

### Frontend

```
src/app/
├── core/
│   ├── auth/
│   ├── models/
│   └── services/
├── layout/
├── features/
└── shared/
```

---

### Backend

```
app/
├── Console/Commands/
├── Http/Controllers/Api/
├── Http/Requests/
├── Http/Resources/
├── Models/
├── Services/
└── resources/views/pdf/
```

---

## 🤖 Automation with n8n

Install:

```bash
npm install -g n8n
n8n start
```

Access:
👉 http://localhost:5678

### Workflows

* Quote Sent
* Invoice Created
* Invoice Paid
* Invoice Overdue

Each workflow:

* Webhook trigger
* Email sender (SMTP/Gmail)
* Response node

---

## 💳 Stripe Integration

* Generate payment links from invoices
* Use Stripe webhooks for payment confirmation
* Test mode supported (no real charges)

---

## 🧾 Useful Commands

### Laravel

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed
php artisan quvio:fill-uuids
php artisan invoices:mark-overdue
php artisan config:clear
php artisan serve
```

---

### Angular

```bash
ng serve
ng build --configuration production
ng generate component component-name
```

---

### n8n

```bash
npm install -g n8n
n8n start
npm update -g n8n
```

---

##  Deployment

### Frontend (Vercel / Netlify)

```bash
ng build --configuration production
```

Deploy the `dist/` folder and set:

```
API_URL=https://your-backend-url/api
```

---

### Backend (VPS)

```bash
composer install --no-dev
php artisan config:cache
php artisan route:cache
```

Set:

```
APP_ENV=production
APP_DEBUG=false
```

### Cron Job

```
* * * * * php artisan schedule:run
```

---

### n8n (Production)

Use Docker:
https://docs.n8n.io/hosting/installation/docker/

---

## 🗄 Database Schema

### Tables

* users
* clients
* quotes
* invoices
* invoice_items

### Notes

* UUID used for security (quotes & invoices)
* invoice_items linked to quotes or invoices
* branding stored as JSON in users table

---

## 📄 License

MIT License © 2025–2026 — Quvio Project

---

## ⭐ Final Note

Quvio demonstrates a **real-world SaaS architecture** combining:

* Angular (modern frontend)
* Laravel 13 (powerful backend)
* Stripe payments integration
* n8n workflow automation

Perfect for freelancers, developers, and scalable platforms.

---

 If you like this project, don't forget to **star the repo**
 
<img width="1863" height="869" alt="Screenshot 2026-04-05 031649" src="https://github.com/user-attachments/assets/97d2923a-8a6a-496e-8264-ff10c5b792f7" />
<img width="1919" height="832" alt="Screenshot 2026-04-09 020306" src="https://github.com/user-attachments/assets/08aa69a9-d38f-4eb2-bf81-0d4950069034" />


