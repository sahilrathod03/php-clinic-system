# PHP Clinic Management System

This is a web-based hospital management system built using PHP and MySQL. It supports doctor and patient login, appointment booking, messaging, lab reports, prescriptions, password reset via OTP, and even includes an AI chatbot for patient symptom queries.

## Key Features

- Patient and doctor login system
- Book and manage appointments
- Upload/view prescriptions and lab reports
- Doctor-to-patient messaging
- Password reset via OTP (with contact number verification)
- Dark mode support
- AI chatbot popup for patient queries (currently simulated)

## AI Chatbot

Patients can click "Need Consult?" on their dashboard to open a popup chat window and talk to an AI bot about symptoms. It is currently simulated but can be connected to real AI like ChatGPT by adding an API key.

> If you want to enable real AI responses, message me and I’ll share the exact changes. You just need to add your API key.

## Technologies Used

- PHP (Vanilla)
- MySQL
- HTML/CSS
- JavaScript
- (Optional) OpenAI API for chatbot

## Setup

1. Clone the repository
2. Import the SQL file into your MySQL database
3. Update database config in db_connect.php
4. Host using XAMPP, Vercel, or any PHP-supported hosting

## Author

Built by Sahil Rathod — feel free to contact me if you want to use the AI chatbot or need help deploying.
