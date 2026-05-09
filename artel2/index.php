<?php include 'db.php'; ?>

<!doctype html>
<html lang="en">
<head>
    <base href="./"/>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="theme-color" content="#000000"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="16x16" href="https://assets.NetServe.in/static-assets/new-home/img/favicon-16x16.png"/>
    <link rel="icon" type="image/png" sizes="32x32" href="https://assets.NetServe.in/static-assets/new-home/img/favicon-32x32.png"/>
    <link rel="icon" type="image/x-icon" href="https://assets.NetServe.in/static-assets/new-home/img/favicon.ico"/>
    <title>NetServe - India's Leading Telecom Service Provider</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        :root {
            --NetServe-red: #e40000;
            --NetServe-dark-red: #D0313B;
            --NetServe-blue: #0066CC;
            --NetServe-light-blue: #ecedff;
            --NetServe-dark: #1a1a1a;
            --NetServe-gray: #666666;
            --NetServe-light-gray: #f5f5f5;
            --NetServe-white: #ffffff;
            --shadow: 0 2px 10px rgba(0,0,0,0.05);
            --shadow-heavy: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        body {
            background-color: #f9f9f9;
            color: var(--NetServe-dark);
            line-height: 1.6;
        }
        
        /* Header Styles */
        .header {
            background-color: var(--NetServe-white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            cursor: pointer;
            text-decoration: none;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background-color: var(--NetServe-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .logo-icon svg {
            width: 24px;
            height: 24px;
        }
        
        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: var(--NetServe-red);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
        }
        
        .nav-item {
            margin: 0 15px;
            position: relative;
        }
        
        .nav-link {
            text-decoration: none;
            color: var(--NetServe-dark);
            font-weight: 500;
            font-size: 15px;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            padding: 8px 0;
        }
        
        .nav-link i {
            margin-left: 5px;
            font-size: 12px;
        }
        
        .nav-link:hover {
            color: var(--NetServe-red);
        }
        
        .nav-link.active {
            color: var(--NetServe-red);
            font-weight: 600;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background-color: white;
            box-shadow: var(--shadow-heavy);
            border-radius: 8px;
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 100;
        }
        
        .nav-item:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            color: var(--NetServe-dark);
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: var(--NetServe-light-gray);
            color: var(--NetServe-red);
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
        }
        
        .btn-login {
            background-color: transparent;
            border: 1px solid var(--NetServe-red);
            color: var(--NetServe-red);
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-right: 15px;
            text-decoration: none;
        }
        
        .btn-login:hover {
            background-color: rgba(228, 0, 0, 0.05);
        }
        
        .btn-buy {
            background-color: var(--NetServe-red);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-buy:hover {
            background-color: #c40000;
        }
        
        /* Main Content Container */
        .main-container {
            min-height: calc(100vh - 280px);
        }
        
        /* Page Content Animation */
        .page-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .page-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Common Section Styles */
        .section {
            padding: 80px 20px;
        }
        
        .section-title {
            text-align: center;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 50px;
            color: var(--NetServe-dark);
        }
        
        .section-subtitle {
            text-align: center;
            font-size: 18px;
            color: var(--NetServe-gray);
            max-width: 700px;
            margin: 0 auto 50px;
            line-height: 1.8;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Card Styles */
        .card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #eeeeee;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-heavy);
        }
        
        .btn {
            background-color: var(--NetServe-red);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #c40000;
        }
        
        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--NetServe-red);
            color: var(--NetServe-red);
        }
        
        .btn-secondary:hover {
            background-color: rgba(228, 0, 0, 0.05);
        }
        
        /* Home Page */
        .hero {
            background: linear-gradient(135deg, var(--NetServe-light-blue) 0%, #ffffff 100%);
            padding: 80px 20px;
            text-align: center;
        }
        
        .hero-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-size: 42px;
            font-weight: 700;
            color: var(--NetServe-dark);
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 20px;
            color: var(--NetServe-gray);
            margin-bottom: 40px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .hero-highlight {
            color: var(--NetServe-red);
        }
        
        .hero-search {
            max-width: 600px;
            margin: 0 auto;
            display: flex;
            background-color: white;
            border-radius: 8px;
            box-shadow: var(--shadow-heavy);
            overflow: hidden;
            padding: 5px;
        }
        
        .search-input {
            flex: 1;
            border: none;
            padding: 18px 20px;
            font-size: 16px;
            outline: none;
        }
        
        .search-btn {
            background-color: var(--NetServe-red);
            color: white;
            border: none;
            padding: 0 30px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .search-btn:hover {
            background-color: #c40000;
        }
        
        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .plan-card {
            text-align: center;
            padding: 30px;
        }
        
        .plan-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--NetServe-dark);
        }
        
        .plan-price {
            font-size: 36px;
            font-weight: 700;
            color: var(--NetServe-red);
            margin-bottom: 5px;
        }
        
        .plan-duration {
            color: var(--NetServe-gray);
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-top: 40px;
        }
        
        .feature-card {
            text-align: center;
            padding: 30px 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        
        .feature-icon-large {
            width: 70px;
            height: 70px;
            background-color: rgba(228, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--NetServe-red);
            font-size: 28px;
        }
        
        .feature-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--NetServe-dark);
        }
        
        /* Plans Page */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .plan-card.popular {
            border: 2px solid var(--NetServe-red);
            position: relative;
        }
        
        .popular-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: var(--NetServe-red);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .plan-header {
            padding: 30px 25px 20px;
            text-align: center;
            background-color: var(--NetServe-light-blue);
        }
        
        .plan-features {
            padding: 25px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .feature-icon {
            width: 20px;
            height: 20px;
            background-color: rgba(228, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: var(--NetServe-red);
            font-size: 10px;
        }
        
        .plan-footer {
            padding: 0 25px 30px;
            text-align: center;
        }
        
        /* Mobile Plans */
        .mobile-plans {
            background-color: var(--NetServe-light-gray);
        }
        
        .plan-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #ddd;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .plan-tab {
            padding: 15px 30px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 600;
            color: var(--NetServe-gray);
            cursor: pointer;
            position: relative;
            transition: color 0.3s;
        }
        
        .plan-tab.active {
            color: var(--NetServe-red);
        }
        
        .plan-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--NetServe-red);
        }
        
        .plans-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .plans-table th {
            background-color: var(--NetServe-light-blue);
            padding: 20px;
            text-align: left;
            font-weight: 600;
            color: var(--NetServe-dark);
        }
        
        .plans-table td {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .plans-table tr:hover {
            background-color: #f9f9f9;
        }
        
        /* DTH Page */
        .channel-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 300px));
    gap: 30px;
    justify-content: center;
}

        
        .channel-card {
            padding: 25px;
            text-align: center;
        }
        
        .channel-icon {
            font-size: 40px;
            color: var(--NetServe-red);
            margin-bottom: 20px;
        }
        
        .channel-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        /* Business Page */
        .business-services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .service-card {
            padding: 30px;
        }
        
        .service-icon {
            width: 60px;
            height: 60px;
            background-color: rgba(228, 0, 0, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: var(--NetServe-red);
            font-size: 24px;
        }
        
        .service-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--NetServe-dark);
        }
        
        /* Help Page */
        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .category-card {
            padding: 30px;
            text-align: center;
            cursor: pointer;
        }
        
        .faq-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .faq-question {
            padding: 20px;
            background-color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 600;
        }
        
        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            background-color: white;
            transition: max-height 0.3s, padding 0.3s;
        }
        
        .faq-item.active .faq-answer {
            padding: 20px;
            max-height: 300px;
        }
        
        /* Login Page */
        .login-container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 50px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            color: var(--NetServe-dark);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #dddddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--NetServe-red);
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: var(--NetServe-red);
            text-decoration: none;
        }
        
        .login-divider {
            text-align: center;
            margin: 30px 0;
            position: relative;
        }
        
        .login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: #eee;
        }
        
        .login-divider span {
            background-color: white;
            padding: 0 20px;
            color: var(--NetServe-gray);
        }
        
        .social-login {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .social-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--NetServe-dark);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            border-color: var(--NetServe-red);
            color: var(--NetServe-red);
        }
        
        .register-link {
            text-align: center;
            margin-top: 30px;
            color: var(--NetServe-gray);
        }
        
        .register-link a {
            color: var(--NetServe-red);
            text-decoration: none;
            font-weight: 600;
        }
        
        /* Profile Page */
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 40px;
        }
        
        .profile-sidebar {
            background-color: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: var(--shadow);
        }
        
        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--NetServe-light-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--NetServe-red);
            font-size: 40px;
        }
        
        .profile-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .profile-menu {
            list-style: none;
        }
        
        .profile-menu-item {
            margin-bottom: 10px;
        }
        
        .profile-menu-link {
            display: block;
            padding: 12px 20px;
            color: var(--NetServe-dark);
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .profile-menu-link:hover,
        .profile-menu-link.active {
            background-color: var(--NetServe-light-blue);
            color: var(--NetServe-red);
        }
        
        .profile-content {
            background-color: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: var(--shadow);
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        .profile-section-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 30px;
            color: var(--NetServe-dark);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 25px;
        }
        
        .info-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--NetServe-gray);
        }
        
        .info-value {
            font-size: 18px;
        }
        
        .bills-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .bill-card {
            padding: 25px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        .bill-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .bill-status.paid {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
        }
        
        .bill-status.pending {
            background-color: rgba(231, 76, 60, 0.1);
            color: #c0392b;
        }
        
        /* Footer */
        .footer {
            background-color: var(--NetServe-dark);
            color: white;
            padding: 60px 20px 30px;
            margin-top: 80px;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .footer-column-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }
        
        .footer-links {
            list-style: none;
        }
        
        .footer-link {
            margin-bottom: 12px;
        }
        
        .footer-link a {
            color: #cccccc;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-link a:hover {
            color: white;
        }
        
        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 30px;
            border-top: 1px solid #333333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .copyright {
            color: #999999;
            font-size: 14px;
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icon {
            width: 36px;
            height: 36px;
            background-color: #333333;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .social-icon:hover {
            background-color: var(--NetServe-red);
        }
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            padding: 40px;
            position: relative;
            transform: translateY(30px);
            transition: transform 0.3s;
        }
        
        .modal-overlay.active .modal {
            transform: translateY(0);
        }
        
        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--NetServe-gray);
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--NetServe-dark);
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--NetServe-dark);
            color: white;
            padding: 15px 25px;
            border-radius: 6px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 3000;
            transform: translateY(100px);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
        }
        
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
        
        .toast.success {
            background-color: #2ecc71;
        }
        
        .toast.error {
            background-color: var(--NetServe-red);
        }
        
        /* Loader */
        #init-loader {
            width: 100vw;
            height: 100vh;
            position: fixed;
            z-index: 9999;
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            text-align: center;
            background-color: #ecedff;
            transition: opacity 0.5s, visibility 0.5s;
        }
        
        #init-loader.hidden {
            opacity: 0;
            visibility: hidden;
        }
        
        .loader-logo {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .business-services {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
            }
            
            .hero-title {
                font-size: 32px;
            }
            
            .hero-subtitle {
                font-size: 18px;
            }
            
            .hero-search {
                flex-direction: column;
                padding: 0;
            }
            
            .search-input {
                padding: 15px;
                border-bottom: 1px solid #eeeeee;
            }
            
            .search-btn {
                padding: 15px;
            }
            
            .plans-container, .plans-grid {
                grid-template-columns: 1fr;
            }
            
            .plan-tabs {
                flex-direction: column;
                border-bottom: none;
            }
            
            .plan-tab {
                border-bottom: 1px solid #ddd;
            }
            
            .plans-table {
                display: block;
                overflow-x: auto;
            }
            
            .login-container {
                padding: 30px 20px;
            }
            
            .footer-bottom {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .header-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-login, .btn-buy {
                margin-right: 0;
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Initial Loader -->
    <div id="init-loader">
        <i class="fas fa-globe loader-logo" style="color: #D0313B; font-size: 80px;"></i>
        <h2 style="margin-top: 20px; color: #D0313B;">Loading NetServe</h2>
    </div>

    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="#home" class="logo" id="home-link">
                <div class="logo-icon">
                    <i class="fas fa-globe" style="color: white; font-size: 20px; padding-top: 2px;"></i>
                </div>
                <div class="logo-text">NetServe</div>
            </a>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="#home" class="nav-link active" id="nav-home">Home</a>
                </li>
                <li class="nav-item">
                    <a href="#plans" class="nav-link" id="nav-plans">Plans <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <a href="#broadband" class="dropdown-item">Broadband</a>
                        <a href="#mobile" class="dropdown-item">Mobile</a>
                        <a href="#dth" class="dropdown-item">DTH</a>
                        <a href="#business" class="dropdown-item">Business Plans</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="#dth" class="nav-link" id="nav-dth">DTH</a>
                </li>
                <li class="nav-item">
                    <a href="#business" class="nav-link" id="nav-business">Business</a>
                </li>
                <li class="nav-item">
                    <a href="#help" class="nav-link" id="nav-help">Help</a>
                </li>
            </ul>
            
            <div class="header-actions">
    <a href="#broadband" class="btn-buy" id="buy-btn">Buy Now</a>
</div>

        </div>
    </header>

    <!-- Main Content Container -->
    <main class="main-container" id="main-content">
        <!-- Home Page -->
        <div id="home-page" class="page-content active">
            <section class="hero">
                <div class="hero-container">
                    <h1 class="hero-title">Get <span class="hero-highlight">NetServe Black</span> - All in One Plan</h1>
                    <p class="hero-subtitle">Get unlimited data with speeds up to 1 Gbps, free landline, OTT subscriptions, and more with India's fastest broadband</p>
                    
                    <div class="hero-search">
                        <input type="text" class="search-input" id="address-input" placeholder="Enter your address to check availability">
                        <button class="search-btn" id="check-availability">Check Availability</button>
                    </div>
                    
                    <p style="margin-top: 20px; color: var(--NetServe-gray); font-size: 14px;">Service available in 1000+ cities across India</p>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <h2 class="section-title">Popular Broadband Plans</h2>
                    <div class="plans-container">
                        <div class="card plan-card">
                            <h3 class="plan-name">Basic</h3>
                            <div class="plan-price">₹499</div>
                            <div class="plan-duration">per month</div>
                            <p style="margin: 20px 0; color: var(--NetServe-gray);">40 Mbps Speed | Unlimited Data</p>
                            <a href="#broadband" class="btn">View Details</a>
                        </div>
                        
                        <div class="card plan-card">
                            <h3 class="plan-name">Family</h3>
                            <div class="plan-price">₹799</div>
                            <div class="plan-duration">per month</div>
                            <p style="margin: 20px 0; color: var(--NetServe-gray);">100 Mbps Speed | Xstream Premium</p>
                            <a href="#broadband" class="btn">View Details</a>
                        </div>
                        
                        <div class="card plan-card">
                            <h3 class="plan-name">Premium</h3>
                            <div class="plan-price">₹1199</div>
                            <div class="plan-duration">per month</div>
                            <p style="margin: 20px 0; color: var(--NetServe-gray);">300 Mbps Speed | Disney+ Hotstar</p>
                            <a href="#broadband" class="btn">View Details</a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section" style="background-color: var(--NetServe-light-gray);">
                <div class="container">
                    <h2 class="section-title">Why Choose NetServe?</h2>
                    <div class="features-container">
                        <div class="feature-card">
                            <div class="feature-icon-large">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <h3 class="feature-title">Super Fast Speeds</h3>
                            <p>Experience seamless streaming, gaming, and downloads with speeds up to 1 Gbps</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon-large">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="feature-title">Secure Connection</h3>
                            <p>Advanced security features to protect your data and privacy online</p>
                        </div>
                        
                        <div class="feature-card">
                            <div class="feature-icon-large">
                                <i class="fas fa-headset"></i>
                            </div>
                            <h3 class="feature-title">24/7 Support</h3>
                            <p>Round-the-clock customer support to resolve any issues quickly</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Broadband Plans Page -->
        <div id="broadband-page" class="page-content">
            <section class="section">
                <div class="container">
                    <h1 class="section-title">Broadband Plans</h1>
                    <p class="section-subtitle">Choose from a range of high-speed broadband plans tailored for your needs</p>
                    
                    <div class="plans-grid">
                        <div class="card">
                            <div class="plan-header">
                                <h3 class="plan-name">Basic</h3>
                                <div class="plan-price">₹499</div>
                                <div class="plan-duration">per month</div>
                            </div>
                            
                            <div class="plan-features">
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                                    <span>40 Mbps Speed</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-infinity"></i></div>
                                    <span>Unlimited Data</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-phone"></i></div>
                                    <span>Free Landline</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-wifi"></i></div>
                                    <span>Wi-Fi Router Included</span>
                                </div>
                            </div>
                            
                            <div class="plan-footer">
                                <button class="btn" onclick="selectPlan('Basic Plan', 499)">Select Plan</button>
                            </div>
                        </div>
                        
                        <div class="card plan-card popular">
                            <div class="popular-badge">Most Popular</div>
                            
                            <div class="plan-header">
                                <h3 class="plan-name">Family</h3>
                                <div class="plan-price">₹799</div>
                                <div class="plan-duration">per month</div>
                            </div>
                            
                            <div class="plan-features">
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                                    <span>100 Mbps Speed</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-infinity"></i></div>
                                    <span>Unlimited Data</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-tv"></i></div>
                                    <span>Xstream Premium</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-phone"></i></div>
                                    <span>Free Landline</span>
                                </div>
                            </div>
                            
                            <div class="plan-footer">
                                <button class="btn" onclick="selectPlan('Family Plan', 799)">Select Plan</button>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="plan-header">
                                <h3 class="plan-name">Premium</h3>
                                <div class="plan-price">₹1199</div>
                                <div class="plan-duration">per month</div>
                            </div>
                            
                            <div class="plan-features">
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-bolt"></i></div>
                                    <span>300 Mbps Speed</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-infinity"></i></div>
                                    <span>Unlimited Data</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-tv"></i></div>
                                    <span>Xstream Premium + Disney+ Hotstar</span>
                                </div>
                                <div class="feature">
                                    <div class="feature-icon"><i class="fas fa-phone"></i></div>
                                    <span>Free Landline</span>
                                </div>
                            </div>
                            
                            <div class="plan-footer">
                                <button class="btn" onclick="selectPlan('Premium Plan', 1199)">Select Plan</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Mobile Plans Page -->
        <div id="mobile-page" class="page-content">
            <section class="section">
                <div class="container">
                    <h1 class="section-title">Mobile Plans</h1>
                    <p class="section-subtitle">Choose from a variety of prepaid and postpaid mobile plans</p>
                    
                    <div class="plan-tabs">
                        <button class="plan-tab active" data-tab="prepaid">Prepaid</button>
                        <button class="plan-tab" data-tab="postpaid">Postpaid</button>
                    </div>
                    
                    <div id="prepaid-plans">
                        <table class="plans-table">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Validity</th>
                                    <th>Data</th>
                                    <th>Calls</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>₹299 Plan</td>
                                    <td>28 Days</td>
                                    <td>1.5GB/Day</td>
                                    <td>Unlimited</td>
                                    <td>₹299</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹299 Prepaid', 299)">Buy Now</button></td>
                                </tr>
                                <tr>
                                    <td>₹399 Plan</td>
                                    <td>56 Days</td>
                                    <td>2GB/Day</td>
                                    <td>Unlimited</td>
                                    <td>₹399</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹399 Prepaid', 399)">Buy Now</button></td>
                                </tr>
                                <tr>
                                    <td>₹499 Plan</td>
                                    <td>56 Days</td>
                                    <td>2.5GB/Day</td>
                                    <td>Unlimited</td>
                                    <td>₹499</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹499 Prepaid', 499)">Buy Now</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div id="postpaid-plans" style="display: none;">
                        <table class="plans-table">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Data</th>
                                    <th>Calls</th>
                                    <th>SMS</th>
                                    <th>Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>₹399 Postpaid</td>
                                    <td>75GB</td>
                                    <td>Unlimited</td>
                                    <td>100 SMS/Day</td>
                                    <td>₹399</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹399 Postpaid', 399)">Buy Now</button></td>
                                </tr>
                                <tr>
                                    <td>₹499 Postpaid</td>
                                    <td>100GB</td>
                                    <td>Unlimited</td>
                                    <td>100 SMS/Day</td>
                                    <td>₹499</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹499 Postpaid', 499)">Buy Now</button></td>
                                </tr>
                                <tr>
                                    <td>₹799 Postpaid</td>
                                    <td>150GB</td>
                                    <td>Unlimited</td>
                                    <td>100 SMS/Day</td>
                                    <td>₹799</td>
                                    <td><button class="btn" onclick="selectMobilePlan('₹799 Postpaid', 799)">Buy Now</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

        <!-- DTH Page -->
        <div id="dth-page" class="page-content">
            <section class="section">
                <div class="container">
                    <h1 class="section-title">DTH Services</h1>
                    <p class="section-subtitle">Experience crystal clear picture quality with NetServe Digital TV</p>
                    
                    <div class="channel-grid">
                        <div class="card channel-card">
                            <div class="channel-icon">
                                <i class="fas fa-satellite-dish"></i>
                            </div>
                            <h3 class="channel-name">Basic Pack</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">200+ Channels</p>
                            <div class="plan-price">₹249</div>
                            <div class="plan-duration">per month</div>
                            <button class="btn" style="margin-top: 20px;" onclick="selectDTHPlan('Basic Pack', 249)">Subscribe</button>
                        </div>
                        
                        <div class="card channel-card">
                            <div class="channel-icon">
                                <i class="fas fa-tv"></i>
                            </div>
                            <h3 class="channel-name">Family Pack</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">300+ Channels</p>
                            <div class="plan-price">₹349</div>
                            <div class="plan-duration">per month</div>
                            <button class="btn" style="margin-top: 20px;" onclick="selectDTHPlan('Family Pack', 349)">Subscribe</button>
                        </div>
                        
                        <div class="card channel-card">
                            <div class="channel-icon">
                                <i class="fas fa-film"></i>
                            </div>
                            <h3 class="channel-name">Premium Pack</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">400+ Channels + HD</p>
                            <div class="plan-price">₹499</div>
                            <div class="plan-duration">per month</div>
                            <button class="btn" style="margin-top: 20px;" onclick="selectDTHPlan('Premium Pack', 499)">Subscribe</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Business Page -->
        <div id="business-page" class="page-content">
            <section class="section">
                <div class="container">
                    <h1 class="section-title">Business Solutions</h1>
                    <p class="section-subtitle">Power your business with NetServe's enterprise-grade solutions</p>
                    
                    <div class="business-services">
                        <div class="card service-card">
                            <div class="service-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h3 class="service-title">Business Broadband</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">Dedicated high-speed internet for offices with priority support and uptime guarantee.</p>
                            <a href="#contact" class="btn">Get Quote</a>
                        </div>
                        
                        <div class="card service-card">
                            <div class="service-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h3 class="service-title">Corporate Mobile Plans</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">Custom mobile plans for your team with centralized billing and management.</p>
                            <a href="#contact" class="btn">Get Quote</a>
                        </div>
                        
                        <div class="card service-card">
                            <div class="service-icon">
                                <i class="fas fa-server"></i>
                            </div>
                            <h3 class="service-title">Cloud Services</h3>
                            <p style="color: var(--NetServe-gray); margin-bottom: 20px;">Secure cloud storage and computing solutions for businesses of all sizes.</p>
                            <a href="#contact" class="btn">Get Quote</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Help Page -->
        <div id="help-page" class="page-content">
            <section class="section">
                <div class="container">
                    <h1 class="section-title">Help & Support</h1>
                    <p class="section-subtitle">Find answers to common questions or get in touch with our support team</p>
                    
                    <div class="help-categories">
                        <div class="card category-card" onclick="showFAQ('billing')">
                            <div class="feature-icon-large">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <h3 class="feature-title">Billing & Payments</h3>
                            <p>Questions about bills, payments, and refunds</p>
                        </div>
                        
                        <div class="card category-card" onclick="showFAQ('technical')">
                            <div class="feature-icon-large">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h3 class="feature-title">Technical Support</h3>
                            <p>Internet, connection, and technical issues</p>
                        </div>
                        
                        <div class="card category-card" onclick="showFAQ('account')">
                            <div class="feature-icon-large">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <h3 class="feature-title">Account Management</h3>
                            <p>Login, password, and account settings</p>
                        </div>
                    </div>
                    
                    <div class="faq-container">
                        <h2 style="margin-bottom: 30px; text-align: center;">Frequently Asked Questions</h2>
                        
                        <div class="faq-item" id="faq1">
                            <div class="faq-question" onclick="toggleFAQ('faq1')">
                                How can I pay my NetServe bill?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                You can pay your NetServe bill through multiple channels: NetServe Thanks app, online via NetServe website, through net banking, credit/debit cards, UPI, or at any NetServe store.
                            </div>
                        </div>
                        
                        <div class="faq-item" id="faq2">
                            <div class="faq-question" onclick="toggleFAQ('faq2')">
                                How do I reset my WiFi password?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                You can reset your WiFi password by logging into your router settings at 192.168.1.1 or through the NetServe Thanks app under the broadband section.
                            </div>
                        </div>
                        
                        <div class="faq-item" id="faq3">
                            <div class="faq-question" onclick="toggleFAQ('faq3')">
                                How can I check my data balance?
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                Dial *121# from your NetServe number or use the NetServe Thanks app to check your data balance, validity, and other account details.
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
		
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div>
                <h3 class="footer-column-title">Products</h3>
                <ul class="footer-links">
                    <li class="footer-link"><a href="#mobile">Mobile</a></li>
                    <li class="footer-link"><a href="#broadband">Broadband</a></li>
                    <li class="footer-link"><a href="#dth">DTH</a></li>
                    <li class="footer-link"><a href="#business">Business</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="footer-column-title">Support</h3>
                <ul class="footer-links">
                    <li class="footer-link"><a href="#help">Help Center</a></li>
                    <li class="footer-link"><a href="#">Store Locator</a></li>
                    <li class="footer-link"><a href="#help">FAQs</a></li>
                    <li class="footer-link"><a href="#">Pay Bill Online</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="footer-column-title">About NetServe</h3>
                <ul class="footer-links">
                    <li class="footer-link"><a href="#">Investor Relations</a></li>
                    <li class="footer-link"><a href="#">Careers</a></li>
                    <li class="footer-link"><a href="#">Newsroom</a></li>
                    <li class="footer-link"><a href="#">Corporate Responsibility</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="footer-column-title">Connect With Us</h3>
                <div class="social-icons">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="copyright">
                © 2024 Bharti NetServe Limited. All rights reserved.
            </div>
            <div>
                <a href="#" style="color: #999999; text-decoration: none; margin-right: 20px;">Privacy Policy</a>
                <a href="#" style="color: #999999; text-decoration: none;">Terms & Conditions</a>
            </div>
        </div>
    </footer>
<!-- Plan Selection Modal -->
<div class="modal-overlay" id="plan-modal">
    <div class="modal">
        <button class="modal-close" id="close-plan">&times;</button>
        <h2 class="modal-title">Complete Your Order</h2>

        <form id="plan-form" action="submit-form.php" method="POST">

            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-input" id="full-name" name="name"
                       placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label class="form-label">Mobile Number</label>
                <input type="tel" class="form-input" id="mobile" name="mobile"
                       placeholder="Enter your 10-digit mobile number" pattern="[0-9]{10}" maxlength="10" title="Please enter a valid 10-digit mobile number" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-input" id="email" name="email"
                       placeholder="Enter your email address" required>
            </div>

            <div class="form-group">
                <label class="form-label">Full Address</label>
                <input type="text" class="form-input" id="address" name="address"
                       placeholder="Enter your complete address" required>
            </div>

            <div class="form-group">
                <label class="form-label">Selected Plan</label>
                <input type="text" class="form-input" id="plan-selected" name="plan" readonly>
            </div>
            
            <input type="hidden" id="plan-price" name="price" value="">
            <input type="hidden" name="source" value="index">

            <button type="submit" id="modal-submit-btn" class="btn" style="width: 100%; margin-top: 10px;">
                Proceed to Payment
            </button>

        </form>
    </div>
</div>

    <!-- Toast Notification -->
    <div class="toast" id="toast"></div>

    <script>
        // State Management
        const appState = {
            currentPage: 'home',
            isLoggedIn: false,
            userData: null,
            cart: []
        };

        // Wait for page to load
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loader after 1.5 seconds
            setTimeout(() => {
                document.getElementById('init-loader').classList.add('hidden');
            }, 1500);
            
            // Initialize the app
            initApp();
            
            // Console log
            console.log(`%cNetServe - Web Team(v2.8.2) ⓒ ${(new Date).getFullYear()}`, "color: #e40000; font-size: 18px; font-weight: bold");
        });
        
        function initApp() {
            // Set up navigation
            setupNavigation();
            
            // Set up forms
            setupForms();
            
            // Set up mobile plan tabs
            setupMobileTabs();
            
            // Set up profile navigation
            setupProfileNavigation();
            
            // Load initial page based on hash
            loadPageFromHash();
            
            // Handle hash changes for SPA navigation
            window.addEventListener('hashchange', loadPageFromHash);
        }
        
        function setupNavigation() {
            // Home link
            document.getElementById('home-link').addEventListener('click', function(e) {
                e.preventDefault();
                navigateTo('home');
            });
            
            // Navigation links
            const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-item)');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('href').substring(1);
                    navigateTo(page);
                });
            });
            
            // Dropdown links
            const dropdownLinks = document.querySelectorAll('.dropdown-item');
            dropdownLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const page = this.getAttribute('href').substring(1);
                    navigateTo(page);
                });
            });
            
           
            document.getElementById('buy-btn').addEventListener('click', function(e) {
                e.preventDefault();	
                navigateTo('broadband');
            });
            
            // Check availability button
            document.getElementById('check-availability').addEventListener('click', function() {
                const address = document.getElementById('address-input').value.trim();
                if (address) {
                    showToast(`Checking availability for: ${address}`, 'success');
                    setTimeout(() => {
                        showToast('Service is available at your location!', 'success');
                    }, 1500);
                } else {
                    showToast('Please enter your address', 'error');
                    document.getElementById('address-input').focus();
                }
            });
        }
        
        function setupForms() {
           
            // Register form
            const registerForm = document.getElementById('register-form');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const name = document.getElementById('reg-name').value;
                    const mobile = document.getElementById('reg-mobile').value;
                    const email = document.getElementById('reg-email').value;
                    const password = document.getElementById('reg-password').value;
                    const confirm = document.getElementById('reg-confirm').value;
                    
                    if (password !== confirm) {
                        showToast('Passwords do not match', 'error');
                        return;
                    }
                    
                    if (mobile.length === 10 && password.length >= 6) {
                        // Simulate registration
                        appState.isLoggedIn = true;
                        appState.userData = {
                            name: name,
                            mobile: mobile,
                            email: email
                        };
                        
                        showToast('Account created successfully!', 'success');
                        setTimeout(() => {
                            navigateTo('profile');
                        }, 1500);
                    } else {
                        showToast('Please fill all fields correctly', 'error');
                    }
                });
            }
            
            // Plan form — validate before allowing submit
            document.getElementById('plan-form').addEventListener('submit', function(e) {
                var name    = document.getElementById('full-name').value.trim();
                var mobile  = document.getElementById('mobile').value.trim();
                var email   = document.getElementById('email').value.trim();
                var address = document.getElementById('address').value.trim();
                var plan    = document.getElementById('plan-selected').value.trim();

                // Name
                if (name === '') {
                    e.preventDefault();
                    showToast('Name is required!', 'error'); return;
                }
                if (!/^[a-zA-Z\s]+$/.test(name)) {
                    e.preventDefault();
                    showToast('Name must contain letters only.', 'error'); return;
                }
                if (name.length < 3) {
                    e.preventDefault();
                    showToast('Name must be at least 3 characters.', 'error'); return;
                }

                // Mobile
                if (mobile === '') {
                    e.preventDefault();
                    showToast('Mobile number is required!', 'error'); return;
                }
                if (!/^[6-9][0-9]{9}$/.test(mobile)) {
                    e.preventDefault();
                    showToast('Enter a valid 10-digit Indian mobile number starting with 6-9.', 'error'); return;
                }

                // Email
                if (email === '') {
                    e.preventDefault();
                    showToast('Email address is required!', 'error'); return;
                }
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    e.preventDefault();
                    showToast('Please enter a valid email address.', 'error'); return;
                }

                // Address
                if (address === '') {
                    e.preventDefault();
                    showToast('Address is required!', 'error'); return;
                }
                if (address.length < 10) {
                    e.preventDefault();
                    showToast('Please enter a complete address (at least 10 characters).', 'error'); return;
                }

                // Plan must be selected
                if (plan === '') {
                    e.preventDefault();
                    showToast('No plan selected. Please choose a plan first.', 'error'); return;
                }

                // All valid — disable button to prevent double-submit
                var btn = document.getElementById('modal-submit-btn');
                btn.disabled = true;
                btn.textContent = 'Processing...';
                // Form will submit normally to submit-form.php
            });
            // Close modal
            document.getElementById('close-plan').addEventListener('click', function() {
                document.getElementById('plan-modal').classList.remove('active');
            });
            
            // Close modal when clicking outside
            document.getElementById('plan-modal').addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        }
        
        function setupMobileTabs() {
            const tabs = document.querySelectorAll('.plan-tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabType = this.getAttribute('data-tab');
                    document.getElementById('prepaid-plans').style.display = tabType === 'prepaid' ? 'block' : 'none';
                    document.getElementById('postpaid-plans').style.display = tabType === 'postpaid' ? 'block' : 'none';
                });
            });
        }
        
        function setupProfileNavigation() {
            const profileLinks = document.querySelectorAll('.profile-menu-link');
            profileLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const section = this.getAttribute('data-section');
                    
                    // Update active link
                    profileLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding section
                    document.querySelectorAll('.profile-section').forEach(s => {
                        s.classList.remove('active');
                    });
                    document.getElementById(`${section}-section`).classList.add('active');
                });
            });
        }
        
        function loadPageFromHash() {
            const hash = window.location.hash.substring(1) || 'home';
            navigateTo(hash);
        }
        
        function navigateTo(page) {
            // Update app state
            appState.currentPage = page;
            
            // Update URL hash
            window.location.hash = page;
            
            // Hide all pages
            document.querySelectorAll('.page-content').forEach(p => {
                p.classList.remove('active');
            });
            
            // Show target page
            const targetPage = document.getElementById(`${page}-page`);
            if (targetPage) {
                targetPage.classList.add('active');
                
                // Scroll to top
                window.scrollTo(0, 0);
                
                // Update active nav link
                updateActiveNavLink(page);
                
                // Update page title
                updatePageTitle(page);
                
                // Handle special page logic
                handlePageLogic(page);
            } else {
                // If page doesn't exist, go to home
                navigateTo('home');
            }
        }
        
        function updateActiveNavLink(page) {
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to current page nav link
            const navLink = document.getElementById(`nav-${page}`);
            if (navLink) {
                navLink.classList.add('active');
            }
            
            // Special handling for plans subpages
            if (['broadband', 'mobile', 'dth', 'business'].includes(page)) {
                document.getElementById('nav-plans').classList.add('active');
            }
        }
        
        function updatePageTitle(page) {
            const titles = {
                'home': 'NetServe - India\'s Leading Telecom Service Provider',
                'broadband': 'Broadband Plans - NetServe',
                'mobile': 'Mobile Plans - NetServe',
                'dth': 'DTH Services - NetServe',
                'business': 'Business Solutions - NetServe',
                'help': 'Help & Support - NetServe',
                'login': 'Login - NetServe',
                'profile': 'My Account - NetServe',
                'register': 'Create Account - NetServe'
            };
            
            document.title = titles[page] || titles['home'];
        }
        
        function handlePageLogic(page) {
            switch(page) {
                case 'profile':
                    if (!appState.isLoggedIn) {
                        navigateTo('login');
                        showToast('Please login to access your profile', 'error');
                    } else {
                        // Update profile name if user is logged in
                        if (appState.userData) {
                            document.getElementById('profile-name').textContent = appState.userData.name;
                        }
                    }
                    break;
                case 'login':
                    if (appState.isLoggedIn) {
                        navigateTo('profile');
                    }
                    break;
            }
        }
        
        function selectPlan(planName, price) {
            document.getElementById('plan-selected').value = planName;
            document.getElementById('plan-price').value = price;
            document.getElementById('plan-modal').classList.add('active');
        }
        
        function selectMobilePlan(planName, price) {
            document.getElementById('plan-selected').value = planName;
            document.getElementById('plan-price').value = price;
            document.getElementById('plan-modal').classList.add('active');
        }
        
        function selectDTHPlan(planName, price) {
            document.getElementById('plan-selected').value = planName;
            document.getElementById('plan-price').value = price;
            document.getElementById('plan-modal').classList.add('active');
        }
        
        function showFAQ(category) {
            showToast(`Showing FAQ for ${category}`, 'success');
        }
        
        function toggleFAQ(id) {
            const faqItem = document.getElementById(id);
            faqItem.classList.toggle('active');
        }
        
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show';
            toast.classList.add(type);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.classList.remove(type);
                }, 300);
            }, 3000);
        }
        
        // Global functions for onclick handlers
        window.selectPlan = selectPlan;
        window.selectMobilePlan = selectMobilePlan;
        window.selectDTHPlan = selectDTHPlan;
        window.showFAQ = showFAQ;
        window.toggleFAQ = toggleFAQ;
    </script>
</body>
</html>
