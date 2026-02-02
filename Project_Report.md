# National Citizen Health Service Portal (NCHSP)
## Project Report

### Introduction / Overview
The **National Citizen Health Service Portal (NCHSP)** is a comprehensive web-based platform designed to digitize and streamline healthcare services for citizens. It serves as a bridge between patients, doctors, hospitals, and blood banks. The system allows users to book appointments, manage medical reports, requesting blood, and view info about health camps. It also includes dedicated portals for doctors and administrators to manage their respective workflows efficiently.

### Motivation
In many developing regions, healthcare services are often fragmented. Patients struggle to find doctors, book appointments, or locate blood donors in emergencies. Medical records are often paper-based and easily lost.
**Key Motivations:**
-   **Accessibility:** To provide a centralized platform where citizens can access various health services from home.
-   **Efficiency:** To reduce waiting times and manual paperwork in hospitals and clinics.
-   **Emergency Response:** To facilitate quick access to blood donors and critical health information.
-   **Digitalization:** To maintain secure digital health records for patients.

### Related or Similar Projects
-   **Practo / DocPlanner:** Commercial platforms for booking doctor appointments and managing health records.
-   **National Health Stack (India):** A government initiative to create a unified health identity and data standard.
-   **Red Cross Blood App:** A dedicated app for blood donation services.
-   **Hospital Management Systems (HMS):** Custom software used by individual hospitals for internal management.

### Benchmark Analysis
Compared to existing manual systems or basic single-feature applications:
-   **Integration:** Unlike standalone appointment apps or blood bank sites, NCHSP integrates appointments, blood requests, and medical reports in one place.
-   **User-Centric:** Focuses on the patient's holistic journey (diagnosis -> appointment -> medication -> reports).
-   **Cost:** Designed as a public service tool, unlike expensive commercial alternatives.
-   **Localization:** Tailored for local medical infrastructure (e.g., Union-based filtering, local health camps).

### Complete Feature List

#### User / Patient Module
-   **Registration & Authentication:** Secure sign-up with personal interactions, including height, weight, and blood group.
-   **Dashboard:** specialized view of upcoming appointments, medications, and health status.
-   **Appointment Booking:** Browse doctors by specialization, view availability slots, and book appointments.
-   **Blood Request System:** Post requests for blood, view status (Pending/Approved), and find donors.
-   **Medical Reports:** View and download diagnostic reports uploaded by labs/admins.
-   **Health Camps:** Information about upcoming free health checkup camps with map locations.
-   **Medications & Reminders:** Track daily prescriptions and set reminders.

#### Doctor Module
-   **Doctor Dashboard:** Overview of daily appointments and patient statistics.
-   **Schedule Management:** Define available time slots for patients to book.
-   **Patient Care:** View patient details, history, and write prescriptions.
-   **Profile Management:** Update bio, specialization, and profile picture.
-   **Assistant Management:** Add and manage personal assistants.

#### Admin Module
-   **System Monitoring:** Dashboard with key metrics (Total Doctors, Patients, Appointments).
-   **User & Doctor Management:** Add, verify, or remove users and doctors.
-   **Blood Request Management:** Approve or reject user blood requests.
-   **Health Camp Management:** Create and update health camp events.
-   **Hospital & Representative Management:** Manage hospital listings and their representatives.
-   **Report Management:** Upload patient diagnostic reports.

### Database Design Approach
The database is designed using a **Relational Database Management System (RDBMS)** with **MySQL**. The design focuses on:
-   **Normalization:** Tables are normalized (mostly to 3NF) to reduce data redundancy. For example, `doctors` are linked to `admins` via `admin_id`, and `appointments` link users and doctors via IDs.
-   **Data Integrity:** Foreign keys are used to enforce relationships (e.g., deleting a user deletes their appointments).
-   **Scalability:** Separate tables for distinct entities like `blood_requests`, `health_camps`, and `reports` allow the system to grow without structural changes.
-   **Security:** Passwords are hashed using `bcrypt` (seen in `users` and `admins` tables).

### Schema Diagram
The database `nchsp_db` consists of the following key tables:

1.  **users**: Stores patient details (Name, DOB, Mobile, Password, Health Metrics).
2.  **admins**: Stores credentials for Admins, Doctors, and Assistants.
3.  **doctors**: Profiles of doctors linked to `admins`.
4.  **appointments**: Links `users` and `doctors` with date, time, and status.
5.  **doctor_schedules**: Available time slots for doctors.
6.  **blood_requests**: User requests for blood donation.
7.  **health_camps**: Events and locations for health camps.
8.  **reports**: Medical test PDF/files linked to users.
9.  **medications**: User prescriptions and tracking.
10. **prescriptions**: Digital prescriptions created by doctors for appointments.

*(Note: In a visual report, an ER Diagram would be placed here. Conceptually, `users` 1:N `appointments` N:1 `doctors`.)*

### Queries for Feature Implementation

**1. User Login (Authentication)**
```sql
SELECT * FROM users WHERE mobile = ?
```

**2. Fetching Available Doctors**
```sql
SELECT d.*, 
       (SELECT AVG(rating) FROM doctor_reviews dr WHERE dr.doctor_id = d.id) as avg_rating
FROM doctors d 
ORDER BY name ASC
```

**3. Booking an Appointment Slot**
```sql
-- Step 1: Check availability
SELECT * FROM doctor_schedules WHERE id = ? AND is_booked = 0;

-- Step 2: Insert Appointment
INSERT INTO appointments (user_id, doctor_id, schedule_id, appointment_date, description, status) 
VALUES (?, ?, ?, ?, ?, 'pending');

-- Step 3: Mark Slot as Booked
UPDATE doctor_schedules SET is_booked = 1 WHERE id = ?;
```

**4. Retrieving Patient Blood Requests**
```sql
SELECT * FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC
```

**5. Fetching Upcoming Health Camps**
```sql
SELECT * FROM health_camps WHERE camp_date >= CURDATE() ORDER BY camp_date ASC
```

### Limitations
-   **Payment Gateway:** The system currently does not handle online payments for appointments or tests.
-   **Real-time Chat:** There is no live chat feature between doctors and patients; communication is slot-based.
-   **Mobile App:** The interface is responsive but not a native mobile application, which might limit offline access.
-   **SMS Integration:** While designed for notifications, actual SMS gateway integration (e.g., Twilio) needs to be configured with a paid plan.

### Future Work
The project has significant potential for expansion. Future enhancements include:
-   **Telemedicine Integration:** Implementing video conferencing for remote consultations.
-   **AI-Driven Health Insights:** Using machine learning to analyze user health data and predict potential risks.
-   **Mobile Health App:** Developing a native Android/iOS application for better accessibility and offline support.
-   **Online Payment Gateway:** Integrating secure online payments (e.g., SSLCommerz, Stripe) for appointments and tests.
-   **Pharmacy Integration:** Connecting with local pharmacies to allow users to order prescribed medicines online.

### Conclusion
The **National Citizen Health Service Portal** successfully demonstrates a digital ecosystem for local healthcare management. By integrating appointment booking, blood donation requests, and medical record management, it improves operational efficiency and patient convenience. The modular design allows for future scalability, such as adding telemedicine features or AI-based health predictions. The project meets its primary object of simplifying access to healthcare services for the common citizen.
