# 🌐 NetServe Telecom Data Analytics System

> A full-stack PHP + MySQL telecom service management portal with real Razorpay payment integration, ML-powered analytics, and a professional admin dashboard.

---

## 📸 Screenshots

| Homepage | Admin Dashboard | Payment Gateway |
|---|---|---|
|<img width="1727" height="908" alt="Screenshot 2026-04-14 115242" src="https://github.com/user-attachments/assets/4cc13903-07d4-4caf-a4d2-83b218f08249" />
 | *(Add screenshot)* | *(Add screenshot)* |

| Analytics Dashboard | Plan Management | Payment Success |
|---|---|---|
| *(Add screenshot)* | *(Add screenshot)* | *(Add screenshot)* |

> **Tip:** Take screenshots and drop them into an `assets/images/screenshots/` folder, then update the table above.

---

## ✨ Features

### Customer-Facing
- 🌍 **Dynamic Plan Catalog** — Broadband, Mobile & DTH plans managed live by admin
- 📋 **Service Booking** — Customers book with name, mobile, email, address, and plan
- 💳 **Real Razorpay Payments** — UPI, Cards, Net Banking, Wallets via Razorpay Checkout
- 🔐 **Cryptographic Payment Verification** — HMAC-SHA256 server-side verification (unforgeable)
- 📧 **Automated Email Receipts** — Gmail SMTP via PHPMailer on booking + payment
- 🧾 **Printable Receipt** — PDF-style receipt page after successful payment
- ⏱️ **Pay Later Option** — Customers can defer payment after booking

### Admin Panel
- 📊 **Booking Dashboard** — Paginated table with search, filter, sort, date range
- ✅ **Approve / Reject Bookings** — One-click status updates with email notification
- 💰 **Payment Tracking** — Real-time Razorpay Payment ID, method, verified badge
- 📋 **Plan Management (CRUD)** — Add/edit/delete/toggle Broadband, Mobile, DTH plans
- 📥 **CSV Export** — Export filtered bookings to spreadsheet
- 📈 **Analytics Dashboard** — Chart.js visualizations for revenue, plan popularity, trends

### Analytics & ML
- 📉 **Python ML Predictions** — Booking forecast, payment classification, plan recommendation
- 📊 **Chart.js Dashboards** — Revenue trends, payment distribution, monthly growth
- 🤖 **scikit-learn Models** — Trained on booking history, visualized as PNG charts

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Frontend** | HTML5, CSS3, Bootstrap 5.3, Bootstrap Icons |
| **Backend** | PHP 8.0 (procedural, no framework) |
| **Database** | MySQL (via XAMPP / MariaDB) |
| **Payments** | Razorpay PHP SDK v2.9.0 |
| **Email** | PHPMailer v6 (Gmail SMTP) |
| **Analytics** | Chart.js 4, Python 3, scikit-learn, matplotlib, pandas |
| **Fonts** | Google Fonts — Inter |
| **Icons** | Bootstrap Icons |
| **Dev Environment** | XAMPP (Windows / Linux) |

---

## 📁 Project Structure

```
airtel2/
│
├── .env                      # 🔒 Secrets (DO NOT commit — in .gitignore)
├── .env.example              # ✅ Safe template for collaborators
├── .gitignore
├── README.md
│
├── index.php                 # Main customer-facing portal (Broadband / Mobile / DTH)
├── service.php               # Additional service page
│
├── config.php                # Loads .env, defines constants
├── db.php                    # MySQLi connection
├── razorpay-config.php       # Razorpay credentials (from .env)
│
├── submit-form.php           # Booking form handler
├── payment.php               # Razorpay checkout page (creates order)
├── verify-payment.php        # Server-side HMAC signature verification
├── payment-success.php       # Success page with payment receipt
├── payment-failed.php        # Failure page with retry option
├── payment-later.php         # Defer payment handler
│
├── receipt.php               # Printable booking receipt
├── thank-you.php             # Post-booking thank you page
│
├── admin-login.php           # Admin authentication
├── logout.php                # Admin logout
├── view-booking.php          # Admin: booking management dashboard
├── update-status.php         # Admin: approve / reject bookings
├── update-payment.php        # Admin: mark payment as paid manually
├── delete-booking.php        # Admin: delete a booking
├── export-csv.php            # Admin: export bookings as CSV
├── manage-plans.php          # Admin: plan CRUD management
├── plan-action.php           # Admin: plan CRUD AJAX handler
├── analytics.php             # Admin: analytics dashboard
│
├── includes/
│   ├── sidebar.php           # Admin layout (head, nav, body open)
│   ├── sidebar_end.php       # Admin layout (scripts, body close)
│   ├── mailer.php            # PHPMailer helper functions
│   └── db_note.php           # DB connection info note
│
├── analytics/
│   ├── generate_charts.py    # Python ML + Chart generation script
│   ├── requirements.txt      # Python dependencies
│   └── *.png                 # Generated chart images (gitignored)
│
├── Razorpay/                 # Razorpay PHP SDK v2.9.0 (manual install)
├── PHPMailer/                # PHPMailer v6 (manual install)
│
└── uploads/
    └── plans/                # Admin-uploaded plan images (gitignored)
```

---

## 🚀 Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.0+, MySQL 5.7+)
- Python 3.8+ (for analytics only)
- A [Razorpay account](https://razorpay.com/) (free test account)
- A Gmail account with [App Password](https://myaccount.google.com/apppasswords) enabled

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/netserve-telecom.git
cd netserve-telecom
```

Place the folder inside your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\airtel2\
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Open `.env` and fill in your values:
```ini
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=airtel_db

SMTP_USER=your_gmail@gmail.com
SMTP_PASS=xxxx xxxx xxxx xxxx   # Gmail App Password

RZP_KEY_ID=rzp_test_XXXXXXXXXXXXXX
RZP_KEY_SECRET=XXXXXXXXXXXXXXXXXXXXXXXX
```

### 3. Database Setup

1. Open phpMyAdmin → Create database `airtel_db`
2. Import the bookings schema:

```sql
CREATE TABLE bookings (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(100),
    mobile              VARCHAR(15),
    email               VARCHAR(100),
    address             TEXT,
    plan                VARCHAR(50),
    price               VARCHAR(50) DEFAULT '499',
    payment_status      VARCHAR(20) DEFAULT 'Pending',
    status              VARCHAR(20) DEFAULT 'Pending',
    razorpay_order_id   VARCHAR(100),
    razorpay_payment_id VARCHAR(100),
    payment_method      VARCHAR(50),
    payment_verified    TINYINT(1)  NOT NULL DEFAULT 0,
    paid_at             DATETIME,
    created_at          TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE plans (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    plan_name   VARCHAR(150) NOT NULL,
    category    ENUM('Broadband','Mobile','DTH') NOT NULL,
    price       VARCHAR(50) NOT NULL,
    description TEXT,
    validity    VARCHAR(100),
    status      ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
    image       VARCHAR(255),
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
```

3. *(Optional)* Run the seeder to add sample data:
```
http://localhost/airtel2/seed-data.php
```
> Delete or restrict `seed-data.php` after seeding in production.

4. Import initial plans:
```
http://localhost/airtel2/create_plans_table.sql
```
(Import via phpMyAdmin → SQL tab)

### 4. Start the Application

Start Apache + MySQL in XAMPP, then visit:
```
http://localhost/airtel2/
```

Admin panel:
```
http://localhost/airtel2/admin-login.php
```

### 5. Python Analytics Setup *(Optional)*

```bash
cd analytics
pip install -r requirements.txt
python generate_charts.py
```

Charts are saved as `.png` files and displayed in the admin analytics dashboard.

---

## 💳 Razorpay Setup

1. Create a free account at [razorpay.com](https://razorpay.com/)
2. Go to **Settings → API Keys → Generate Test Key**
3. Copy the **Key ID** (starts with `rzp_test_`) and **Key Secret**
4. Add them to your `.env` file:
   ```ini
   RZP_KEY_ID=rzp_test_XXXXXXXXXXXXXX
   RZP_KEY_SECRET=XXXXXXXXXXXXXXXXXXXXXXXX
   ```
5. Use any test card from [Razorpay Test Cards](https://razorpay.com/docs/payments/payments/test-card-upi-details/)
6. When going live, replace with `rzp_live_` keys

---

## 📧 Gmail SMTP Setup

1. Enable **2-Factor Authentication** on your Gmail account
2. Go to [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
3. Generate an App Password (select "Mail" + "Windows Computer")
4. Add to `.env`:
   ```ini
   SMTP_USER=your_gmail@gmail.com
   SMTP_PASS=xxxx xxxx xxxx xxxx
   ```

---

## 🔐 Security Features

| Feature | Implementation |
|---|---|
| Payment Verification | HMAC-SHA256 signature (Razorpay standard) |
| Duplicate Payment Guard | `payment_verified=0` condition in SQL UPDATE |
| SQL Injection Prevention | MySQLi prepared statements throughout |
| Secret Management | All credentials in `.env`, gitignored |
| XSS Prevention | `htmlspecialchars()` on all user output |

---

## 🔮 Future Scope

- [ ] User login & booking history (customer-side)
- [ ] OTP verification via SMS API (Twilio / MSG91)
- [ ] PDF receipt generation (DOMPDF / mPDF)
- [ ] Multi-admin support with role-based access
- [ ] Webhook handler for Razorpay events
- [ ] RESTful API for mobile app integration
- [ ] Docker containerization for deployment
- [ ] Automated testing (PHPUnit)
- [ ] CI/CD pipeline (GitHub Actions)

---

## 📄 License

This project is built for educational and portfolio purposes.

---

## 👤 Author

**Harshal Patil**  
📧 harshal815815815@gmail.com  
🔗 [GitHub](https://github.com/yourusername)

---

*Built with ❤️ using PHP, MySQL, Razorpay, Chart.js, and Python ML*
