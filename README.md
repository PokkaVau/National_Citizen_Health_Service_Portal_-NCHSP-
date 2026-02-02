# National Citizen Health Service Portal (NCHSP)

A comprehensive web-based platform designed to digitize and streamline healthcare services for citizens. The NCHSP bridges the gap between patients, doctors, hospitals, and blood banks, creating a unified ecosystem for local healthcare management.

## 🚀 Overview

The **National Citizen Health Service Portal (NCHSP)** allows users to book appointments, manage medical reports, request blood, and view information about health camps. It simplifies the patient journey from diagnosis to medication and facilitates emergency response through a centralized blood donor network.

## ✨ Key Features

### For Patients
- **Secure Registration & Profile**: Manage personal details and health metrics (Height, Weight, Blood Group).
- **Appointment Booking**: Browse doctors by specialization, view availability, and book slots.
- **Blood Request System**: Post blood requests, view status (Pending/Approved), and find potential donors.
- **Digital Medical Records**: Securely access and download diagnostic reports uploaded by labs/admins.
- **Health Camps**: View upcoming free health checkup camps with location details.
- **Medication Tracking**: Track daily prescriptions and set reminders.

### For Doctors
- **Doctor Dashboard**: Overview of daily appointments and patient statistics.
- **Schedule Management**: Define available time slots for patient bookings.
- **Patient Care**: View patient history and write digital prescriptions.
- **Assistant Management**: Add and manage personal assistants to help with workflow.

### For Administrators
- **System Monitoring**: Dashboard with key metrics (Total Doctors, Patients, Appointments).
- **User & Doctor Management**: Verify, approve, or remove user and doctor accounts.
- **Blood Request Management**: Approve or reject blood donation requests.
- **Health Camp Management**: Create and update health camp events and upload cover photos.
- **Report Management**: Upload patient diagnostic reports.

## 🛠️ Technology Stack

- **Frontend**: HTML5, JavaScript, Tailwind CSS (via CDN)
- **Backend**: PHP
- **Database**: MySQL
- **AI Integration**: Groq API (for AI-powered summaries and features)
- **Design**: Vanilla CSS & Tailwind for a modern, responsive UI

## ⚙️ Installation & Setup

### Prerequisites
- **XAMPP** or **WAMP** server (or any PHP/MySQL environment).
- **Git** (optional, for cloning).

### Steps

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    # OR download and extract the zip to your htdocs folder
    ```
    Ensure the project folder is named `dbms` (or update your URL accordingly).

2.  **Database Configuration**
    - Start Apache and MySQL modules in XAMPP.
    - Go to **phpMyAdmin** (`http://localhost/phpmyadmin`).
    - Create a new database named `nchsp_db`.
    - Import the `database/nchsp_db.sql` file provided in the project.

3.  **Environment Setup**
    - The project uses a `.env` file for configuration.
    - Ensure the `.env` file exists in the root directory with the following settings (adjust if your DB credentials differ):
      ```env
      DB_HOST=localhost
      DB_NAME=nchsp_db
      DB_USER=root
      DB_PASS=
      GROQ_API_KEY=your_groq_api_key_here
      GROQ_API_URL=https://api.groq.com/openai/v1/chat/completions
      ```

4.  **Run the Application**
    - Open your browser and navigate to:
      `http://localhost/dbms/`

## 📂 Project Structure

- `admin/` - Admin dashboard and management scripts.
- `doctor/` - Doctor portal and schedule management.
- `user/` - Patient dashboard and appointment features.
- `config/` - Database connection and configuration files.
- `database/` - SQL schema and migration files.
- `uploads/` - Directory for uploaded reports and images.
- `index.php` - Landing page.

## 🤝 Contributing

Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## 📄 License

This project is developed for educational and public service purposes.
