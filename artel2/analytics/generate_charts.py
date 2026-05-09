"""
NetServe Services — Data Analytics & Machine Learning Module
============================================================
Connects to airtel_db, performs EDA on the bookings table,
generates 4 EDA charts + 3 ML prediction charts, and saves a
JSON summary. All outputs are written to the analytics/ folder.

Run: python generate_charts.py
"""

import os
import sys
import json
import warnings
import numpy as np
import pandas as pd
import matplotlib
matplotlib.use('Agg')           # non-interactive backend (no display needed)
import matplotlib.pyplot as plt
import seaborn as sns
import mysql.connector
from datetime import datetime, timedelta

# scikit-learn
from sklearn.ensemble import RandomForestClassifier
from sklearn.linear_model import LogisticRegression, LinearRegression
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score

warnings.filterwarnings('ignore')

# ──────────────────────────────────────────────────────────────
# 0.  CONFIGURATION
# ──────────────────────────────────────────────────────────────
DB_CONFIG = {
    'host':     '127.0.0.1',
    'user':     'root',
    'password': '',
    'database': 'airtel_db',
}

# Always save charts next to this script (analytics/ folder)
SCRIPT_DIR  = os.path.dirname(os.path.abspath(__file__))
OUTPUT_DIR  = SCRIPT_DIR          # analytics/ IS the output dir

# Shared style
PALETTE     = ['#e60000', '#4a90e2', '#2ecc71', '#f39c12', '#9b59b6',
               '#e67e22', '#1abc9c', '#e74c3c']
FONT_TITLE  = {'fontsize': 15, 'fontweight': 'bold', 'color': '#1a1a1a'}
FONT_AXIS   = {'fontsize': 11, 'color': '#555'}
FIG_BG      = '#f9f9f9'

sns.set_style('whitegrid')
plt.rcParams.update({
    'font.family': 'DejaVu Sans',
    'axes.spines.top':   False,
    'axes.spines.right': False,
    'figure.facecolor':  FIG_BG,
    'axes.facecolor':    FIG_BG,
})

# ──────────────────────────────────────────────────────────────
# 1.  LOAD DATA
# ──────────────────────────────────────────────────────────────
print("[INFO] Connecting to MySQL …")
try:
    conn   = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM bookings ORDER BY created_at ASC")
    rows   = cursor.fetchall()
    cursor.close()
    conn.close()
    print(f"[INFO] Loaded {len(rows)} records from bookings table.")
except mysql.connector.Error as err:
    print(f"[ERROR] Database connection failed: {err}")
    sys.exit(1)

if not rows:
    print("[WARNING] bookings table is empty. Generating sample charts with demo data.")
    # -- demo data so the page always renders something --------
    from random import choice, randint
    from datetime import datetime, timedelta
    plans = ['Broadband 299', 'Broadband 499', 'Broadband 999',
             'Recharge 149', 'Recharge 249', 'Recharge 399']
    statuses  = ['Paid', 'Pending', 'Pay Later']
    rows = []
    base = datetime(2025, 1, 1)
    for i in range(120):
        dt = base + timedelta(days=i // 2, hours=randint(8, 22))
        rows.append({
            'id': i+1, 'name': f'User {i+1}', 'mobile': '9999999999',
            'email': f'user{i+1}@mail.com', 'address': 'City',
            'plan': choice(plans), 'payment_status': choice(statuses),
            'status': choice(['Approved', 'Pending', 'Rejected']),
            'created_at': dt,
        })

df = pd.DataFrame(rows)

# ── Parse dates ───────────────────────────────────────────────
df['created_at'] = pd.to_datetime(df['created_at'])
df['date']       = df['created_at'].dt.date
df['hour']       = df['created_at'].dt.hour
df['day_of_week']= df['created_at'].dt.dayofweek     # 0=Mon … 6=Sun
df['month']      = df['created_at'].dt.to_period('M')
df['month_str']  = df['created_at'].dt.strftime('%b %Y')
df['date_ord']   = df['created_at'].map(datetime.toordinal)

# ──────────────────────────────────────────────────────────────
# HELPER
# ──────────────────────────────────────────────────────────────
def save(fig, filename):
    path = os.path.join(OUTPUT_DIR, filename)
    fig.savefig(path, bbox_inches='tight', dpi=130, facecolor=FIG_BG)
    plt.close(fig)
    print(f"[SAVED] {filename}")

# ══════════════════════════════════════════════════════════════
# SECTION A — EDA CHARTS
# ══════════════════════════════════════════════════════════════

# ── A1. Plan Popularity (horizontal bar) ─────────────────────
print("[EDA] Generating plan popularity chart …")
plan_counts = df['plan'].value_counts().sort_values()
colors_bar  = [PALETTE[i % len(PALETTE)] for i in range(len(plan_counts))]

fig, ax = plt.subplots(figsize=(10, max(4, len(plan_counts) * 0.7)))
bars = ax.barh(plan_counts.index, plan_counts.values, color=colors_bar,
               edgecolor='none', height=0.6)
for bar, val in zip(bars, plan_counts.values):
    ax.text(bar.get_width() + 0.3, bar.get_y() + bar.get_height() / 2,
            str(val), va='center', fontsize=10, color='#333')
ax.set_title('📊 Plan Popularity', **FONT_TITLE, pad=15)
ax.set_xlabel('Number of Bookings', **FONT_AXIS)
ax.set_ylabel('Plan', **FONT_AXIS)
ax.tick_params(labelsize=10)
fig.tight_layout()
save(fig, 'plan_popularity.png')

# ── A2. Payment Status Distribution (donut pie) ──────────────
print("[EDA] Generating payment distribution chart …")
pay_counts  = df['payment_status'].value_counts()
pay_colors  = {'Paid': '#2ecc71', 'Pending': '#f39c12', 'Pay Later': '#e67e22'}
pie_colors  = [pay_colors.get(l, '#aaa') for l in pay_counts.index]

fig, ax = plt.subplots(figsize=(7, 7))
wedges, texts, autotexts = ax.pie(
    pay_counts.values, labels=pay_counts.index, autopct='%1.1f%%',
    colors=pie_colors, startangle=140, pctdistance=0.75,
    wedgeprops=dict(width=0.5, edgecolor='white', linewidth=3))
for text in autotexts:
    text.set_fontsize(11)
    text.set_fontweight('bold')
ax.set_title('💳 Payment Status Distribution', **FONT_TITLE, pad=20)
# centre label
total_bk = len(df)
ax.text(0, 0, f'{total_bk}\nBookings', ha='center', va='center',
        fontsize=13, fontweight='bold', color='#333')
save(fig, 'payment_distribution.png')

# ── A3. Daily Booking Trend (line) ──────────────────────────
print("[EDA] Generating daily booking trend chart …")
daily = df.groupby('date').size().reset_index(name='count')
daily['date'] = pd.to_datetime(daily['date'])

fig, ax = plt.subplots(figsize=(12, 5))
ax.plot(daily['date'], daily['count'], color='#e60000', linewidth=2,
        marker='o', markersize=4, zorder=3)
ax.fill_between(daily['date'], daily['count'], alpha=0.15, color='#e60000')
ax.set_title('📈 Daily Booking Trend', **FONT_TITLE, pad=15)
ax.set_xlabel('Date', **FONT_AXIS)
ax.set_ylabel('Bookings', **FONT_AXIS)
ax.tick_params(axis='x', rotation=30, labelsize=9)
ax.tick_params(axis='y', labelsize=10)
fig.tight_layout()
save(fig, 'booking_trend.png')

# ── A4. Monthly Booking Growth (bar) ─────────────────────────
print("[EDA] Generating monthly booking growth chart …")
monthly = df.groupby('month_str').size().reset_index(name='count')
monthly = monthly.sort_values('count')   # keep original insertion order via month

fig, ax = plt.subplots(figsize=(12, 5))
bar_colors = [PALETTE[i % len(PALETTE)] for i in range(len(monthly))]
rects = ax.bar(monthly['month_str'], monthly['count'],
               color=bar_colors, edgecolor='none', width=0.6)
for rect in rects:
    ax.text(rect.get_x() + rect.get_width() / 2, rect.get_height() + 0.3,
            str(int(rect.get_height())), ha='center', va='bottom', fontsize=9)
ax.set_title('📅 Monthly Booking Growth', **FONT_TITLE, pad=15)
ax.set_xlabel('Month', **FONT_AXIS)
ax.set_ylabel('Bookings', **FONT_AXIS)
ax.tick_params(axis='x', rotation=30, labelsize=9)
fig.tight_layout()
save(fig, 'monthly_growth.png')

# ══════════════════════════════════════════════════════════════
# SECTION B — MACHINE LEARNING
# ══════════════════════════════════════════════════════════════
ml_summary = {}      # collects text predictions → saved to JSON

# ── B1. Most Popular Plan Predictor ──────────────────────────
print("[ML] Training Plan Predictor (RandomForestClassifier) …")
le_plan = LabelEncoder()
df['plan_enc'] = le_plan.fit_transform(df['plan'])

features_plan = df[['day_of_week', 'month', 'hour']].copy()
features_plan['month_num'] = df['created_at'].dt.month
features_plan = features_plan[['day_of_week', 'month_num', 'hour']]
target_plan   = df['plan_enc']

acc_plan = None
if len(df) >= 20:
    X_tr, X_te, y_tr, y_te = train_test_split(
        features_plan, target_plan, test_size=0.2, random_state=42)
    rf = RandomForestClassifier(n_estimators=100, random_state=42)
    rf.fit(X_tr, y_tr)
    acc_plan = round(accuracy_score(y_te, rf.predict(X_te)) * 100, 1)
    print(f"[ML] Plan Predictor Accuracy: {acc_plan}%")

    # predict for each day of week
    day_names = ['Monday', 'Tuesday', 'Wednesday',
                 'Thursday', 'Friday', 'Saturday', 'Sunday']
    pred_plans = []
    now = datetime.now()
    for d in range(7):
        sample = [[d, now.month, 14]]   # assume 2 PM
        p = le_plan.inverse_transform(rf.predict(sample))[0]
        pred_plans.append(p)

    # Chart — predicted plan demand per day
    plan_demand = pd.Series(pred_plans, index=day_names).value_counts()
    fig, ax = plt.subplots(figsize=(10, 5))
    bar_c = [PALETTE[i % len(PALETTE)] for i in range(len(plan_demand))]
    ax.bar(plan_demand.index, plan_demand.values, color=bar_c, edgecolor='none', width=0.5)
    ax.set_title(f'🤖 ML: Predicted Plan Demand by Plan\n(RF Accuracy: {acc_plan}%)',
                 **FONT_TITLE, pad=15)
    ax.set_xlabel('Plan', **FONT_AXIS)
    ax.set_ylabel('Days predicted as top', **FONT_AXIS)
    ax.tick_params(axis='x', rotation=25, labelsize=9)
    for i, v in enumerate(plan_demand.values):
        ax.text(i, v + 0.05, str(v), ha='center', fontsize=10)
    fig.tight_layout()
    save(fig, 'ml_plan_prediction.png')

    ml_summary['plan_predictions'] = {
        day: plan for day, plan in zip(day_names, pred_plans)}
    ml_summary['plan_model_accuracy'] = acc_plan
else:
    ml_summary['plan_predictions'] = {}
    ml_summary['plan_model_accuracy'] = 'N/A (insufficient data)'

# ── B2. Payment Behaviour Predictor ──────────────────────────
print("[ML] Training Payment Behaviour Predictor (LogisticRegression) …")
le_pay  = LabelEncoder()
le_plan2= LabelEncoder()

df['pay_enc']   = le_pay.fit_transform(df['payment_status'])
df['plan_enc2'] = le_plan2.fit_transform(df['plan'])

features_pay = df[['plan_enc2', 'day_of_week', 'hour']]
target_pay   = df['pay_enc']

acc_pay = None
if len(df) >= 20:
    X_tr, X_te, y_tr, y_te = train_test_split(
        features_pay, target_pay, test_size=0.2, random_state=42)
    lr = LogisticRegression(max_iter=500, random_state=42)
    lr.fit(X_tr, y_tr)
    acc_pay = round(accuracy_score(y_te, lr.predict(X_te)) * 100, 1)
    print(f"[ML] Payment Predictor Accuracy: {acc_pay}%")

    # Predict payment probabilities for each plan
    unique_plans_enc = sorted(df['plan_enc2'].unique())
    plan_names_sorted = le_plan2.inverse_transform(unique_plans_enc)
    proba_matrix = []
    for p_enc in unique_plans_enc:
        sample = [[p_enc, 3, 14]]   # Thursday, 2 PM (typical)
        prob   = lr.predict_proba(sample)[0]
        proba_matrix.append(prob)

    proba_df = pd.DataFrame(proba_matrix,
                            index=plan_names_sorted,
                            columns=le_pay.classes_)

    fig, ax = plt.subplots(figsize=(11, 5))
    proba_df.plot(kind='bar', ax=ax,
                  color=[pay_colors.get(c, '#aaa') for c in proba_df.columns],
                  edgecolor='none', width=0.65)
    ax.set_title(f'🤖 ML: Predicted Payment Behaviour per Plan\n(LR Accuracy: {acc_pay}%)',
                 **FONT_TITLE, pad=15)
    ax.set_xlabel('Plan', **FONT_AXIS)
    ax.set_ylabel('Probability', **FONT_AXIS)
    ax.set_ylim(0, 1)
    ax.tick_params(axis='x', rotation=30, labelsize=9)
    ax.legend(title='Payment Type', fontsize=9)
    fig.tight_layout()
    save(fig, 'ml_payment_prediction.png')

    # Most likely payment per plan
    ml_summary['payment_predictions'] = {
        plan: proba_df.loc[plan].idxmax() for plan in plan_names_sorted}
    ml_summary['payment_model_accuracy'] = acc_pay
else:
    ml_summary['payment_predictions'] = {}
    ml_summary['payment_model_accuracy'] = 'N/A (insufficient data)'

# ── B3. Booking Volume Forecaster ────────────────────────────
print("[ML] Training Booking Volume Forecaster (LinearRegression) …")
daily2 = df.groupby('date').size().reset_index(name='count')
daily2['date']     = pd.to_datetime(daily2['date'])
daily2['date_ord'] = daily2['date'].map(lambda d: d.toordinal())

X_all = daily2[['date_ord']]
y_all = daily2['count']

if len(daily2) >= 5:
    linreg = LinearRegression()
    linreg.fit(X_all, y_all)

    # Forecast next 30 days
    last_date  = daily2['date'].max()
    future_dates = pd.date_range(last_date + timedelta(days=1), periods=30)
    future_ord   = np.array([d.toordinal() for d in future_dates]).reshape(-1, 1)
    forecast_vals = linreg.predict(future_ord)
    forecast_vals = np.clip(forecast_vals, 0, None)   # no negative bookings

    fig, ax = plt.subplots(figsize=(13, 5))
    ax.plot(daily2['date'], y_all, color='#4a90e2', linewidth=2,
            label='Actual Bookings', marker='o', markersize=3)
    ax.plot(future_dates, forecast_vals, color='#e60000', linewidth=2,
            linestyle='--', label='Forecasted (30 days)', marker='x', markersize=4)
    ax.axvline(last_date, color='#aaa', linestyle=':', linewidth=1)
    ax.fill_between(future_dates, 0, forecast_vals, alpha=0.10, color='#e60000')
    ax.set_title('🤖 ML: Booking Volume Forecast (30-Day Ahead)',
                 **FONT_TITLE, pad=15)
    ax.set_xlabel('Date', **FONT_AXIS)
    ax.set_ylabel('Bookings', **FONT_AXIS)
    ax.tick_params(axis='x', rotation=30, labelsize=9)
    ax.legend(fontsize=10)
    fig.tight_layout()
    save(fig, 'ml_booking_forecast.png')

    avg_forecast = round(float(np.mean(forecast_vals)), 1)
    ml_summary['forecast_avg_daily'] = avg_forecast
    ml_summary['forecast_period']    = '30 days from ' + str(last_date.date())
else:
    ml_summary['forecast_avg_daily'] = 'N/A'
    ml_summary['forecast_period']    = 'N/A'

# ══════════════════════════════════════════════════════════════
# SECTION C — JSON SUMMARY
# ══════════════════════════════════════════════════════════════
# Basic EDA stats
total      = len(df)
top_plan   = df['plan'].value_counts().idxmax() if total else 'N/A'
top_plan_n = int(df['plan'].value_counts().max()) if total else 0

pay_dist = df['payment_status'].value_counts().to_dict()

ml_summary.update({
    'generated_at':         datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
    'total_bookings':       total,
    'top_plan':             top_plan,
    'top_plan_count':       top_plan_n,
    'payment_distribution': {k: int(v) for k, v in pay_dist.items()},
})

json_path = os.path.join(OUTPUT_DIR, 'ml_predictions.json')
with open(json_path, 'w') as f:
    json.dump(ml_summary, f, indent=4)
print(f"[SAVED] ml_predictions.json")

print("\n✅  All charts and predictions generated successfully!")
print(f"    Output folder: {OUTPUT_DIR}")
