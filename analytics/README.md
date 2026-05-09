# NetServe Analytics Module — Setup Guide

## 📁 Folder Structure

```
artel2/
├── analytics/
│   ├── generate_charts.py     ← Main Python script (EDA + ML)
│   ├── requirements.txt       ← Python dependencies
│   ├── README.md              ← This file
│   ├── plan_popularity.png
│   ├── payment_distribution.png
│   ├── booking_trend.png
│   ├── monthly_growth.png
│   ├── ml_plan_prediction.png
│   ├── ml_payment_prediction.png
│   ├── ml_booking_forecast.png
│   └── ml_predictions.json
├── analytics.php              ← PHP admin analytics page
└── view-booking.php           ← Admin dashboard (has Analytics link)
```

---

## 🚀 Quick Setup

### Step 1 — Install Python dependencies

Open PowerShell or Command Prompt:

```powershell
cd c:\xampp\htdocs\artel2\analytics
pip install -r requirements.txt
```

### Step 2 — Run the script manually (first time)

```powershell
python generate_charts.py
```

✅ Expected output:
```
[INFO] Connecting to MySQL …
[INFO] Loaded XX records from bookings table.
[EDA] Generating plan popularity chart …
[SAVED] plan_popularity.png
...
[ML] Training Plan Predictor (RandomForestClassifier) …
[ML] Plan Predictor Accuracy: XX.X%
...
✅  All charts and predictions generated successfully!
```

### Step 3 — Open the analytics page

Login to the admin panel at:
```
http://localhost/artel2/admin-login.php
```

Then click **📊 Data Analytics** in the navbar, or navigate to:
```
http://localhost/artel2/analytics.php
```

---

## 📊 Charts Generated

| File | Description |
|------|-------------|
| `plan_popularity.png` | Horizontal bar chart — bookings per plan |
| `payment_distribution.png` | Donut pie chart — Paid / Pending / Pay Later |
| `booking_trend.png` | Line chart — daily booking trend |
| `monthly_growth.png` | Bar chart — month-by-month growth |
| `ml_plan_prediction.png` | ML: predicted plan demand by day of week |
| `ml_payment_prediction.png` | ML: predicted payment behaviour per plan |
| `ml_booking_forecast.png` | ML: 30-day booking volume forecast |

---

## 🤖 ML Models

| Model | Algorithm | Predicts |
|-------|-----------|----------|
| Plan Predictor | RandomForestClassifier | Most booked plan per day |
| Payment Predictor | LogisticRegression | Payment method per plan |
| Volume Forecaster | LinearRegression | Daily booking count (30 days) |

---

## 🔧 Config (generate_charts.py)

Edit the `DB_CONFIG` dictionary at the top of `generate_charts.py` if your MySQL credentials differ:

```python
DB_CONFIG = {
    'host':     'localhost',
    'user':     'root',
    'password': '',          # ← set your MySQL password here
    'database': 'airtel_db',
}
```

---

## 🛠 Troubleshooting

| Problem | Fix |
|---------|-----|
| `ModuleNotFoundError` | Run `pip install -r requirements.txt` |
| `mysql.connector.errors.DatabaseError` | Check `DB_CONFIG` credentials |
| Charts not showing in browser | Click **Refresh Charts** on the analytics page |
| `python: not found` | Try `python3 generate_charts.py` |
| Charts show "demo data" | The `bookings` table is empty — add some bookings first via `seed-data.php` |

---

## 🔮 Future ML Roadmap

- **Churn Prediction** — which users are likely to leave
- **Plan Recommendation** — suggest best plan to new users
- **ARIMA/LSTM Forecasting** — better time-series models
- **Fraud Detection** — Isolation Forest for anomalies
- **Customer Segmentation** — K-Means clustering
- **Revenue Prediction** — predict next-month earnings
