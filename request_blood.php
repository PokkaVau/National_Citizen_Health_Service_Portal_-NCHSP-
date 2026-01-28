<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $patient_name = trim($_POST['patient_name']);
    $blood_group = $_POST['blood_group'];
    $units_needed = (int) $_POST['units_needed'];
    $hospital = trim($_POST['hospital']);
    $contact_person = trim($_POST['contact_person']);
    $contact_number = trim($_POST['contact_number']);
    $date_needed = $_POST['date_needed'];
    $details = trim($_POST['details']);

    if (empty($patient_name) || empty($blood_group) || empty($units_needed) || empty($contact_number) || empty($date_needed)) {
        $message = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO blood_requests (user_id, patient_name, blood_group, units_needed, hospital, contact_person, contact_number, date_needed, details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $patient_name, $blood_group, $units_needed, $hospital, $contact_person, $contact_number, $date_needed, $details]);

            $success = true;
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Blood - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #ef4444;
            --primary-dark: #dc2626;
            --secondary: #f87171;
            --accent: #fecaca;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #fff7ed 50%, #f0f9ff 100%);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                radial-gradient(circle at 20% 80%, rgba(254, 202, 202, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(254, 202, 202, 0.2) 0%, transparent 50%);
            z-index: -1;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow:
                0 20px 40px rgba(239, 68, 68, 0.1),
                0 10px 20px rgba(0, 0, 0, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.5);
        }

        .blood-drop {
            position: relative;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .pulse-ring {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .form-input {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #f3f4f6;
        }

        .form-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .blood-group-option {
            position: relative;
            transition: all 0.2s ease;
        }

        .blood-group-option:hover {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            transform: scale(1.02);
        }

        .submit-btn {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(239, 68, 68, 0.3);
        }

        .success-card {
            animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .required-star {
            color: #ef4444;
            animation: twinkle 1.5s ease-in-out infinite;
        }

        @keyframes twinkle {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .emergency-pulse {
            animation: emergencyPulse 1.5s ease-in-out infinite;
        }

        @keyframes emergencyPulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
        }
    </style>
</head>

<body>
    <!-- Floating Blood Drops -->
    <div class="fixed top-10 left-10 w-16 h-16 blood-drop opacity-10"></div>
    <div class="fixed top-1/4 right-10 w-12 h-12 blood-drop opacity-5"></div>
    <div class="fixed bottom-1/4 left-20 w-20 h-20 blood-drop opacity-15"></div>

    <div class="min-h-screen flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-4xl">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center mb-4">
                    <div class="relative">
                        <div class="w-24 h-24 blood-drop flex items-center justify-center mb-4 mx-auto emergency-pulse">
                            <span class="material-symbols-outlined text-white text-4xl">water_drop</span>
                        </div>
                        <div
                            class="absolute -top-2 -right-2 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg">
                            <span class="material-symbols-outlined text-red-500 text-lg">emergency</span>
                        </div>
                    </div>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-3">Emergency Blood Request</h1>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Your request could save a life. Please provide accurate information for faster processing.
                </p>
            </div>

            <div class="glass-card rounded-3xl overflow-hidden shadow-2xl">
                <!-- Form Header -->
                <div class="relative overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-500 via-red-600 to-red-700"></div>
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=" 60" height="60"
                        viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg" %3E%3Cg fill="none" fill-rule="evenodd"
                        %3E%3Cg fill="%23ffffff" fill-opacity="0.1" %3E%3Cpath
                        d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"
                        /%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
                    <div class="relative p-8 flex flex-col md:flex-row justify-between items-center">
                        <div class="text-white mb-6 md:mb-0">
                            <h2 class="text-2xl font-bold flex items-center gap-3">
                                <span class="material-symbols-outlined text-3xl">hand_heart</span>
                                Blood Donation Request Form
                            </h2>
                            <p class="text-red-100 mt-2 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">info</span>
                                All fields marked with <span class="required-star">*</span> are required
                            </p>
                        </div>
                        <a href="dashboard.php"
                            class="bg-white/25 hover:bg-white/40 backdrop-blur-sm text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 group border border-white/30">
                            <span
                                class="material-symbols-outlined group-hover:-translate-x-1 transition-transform">arrow_back</span>
                            Back to Dashboard
                        </a>
                    </div>
                </div>

                <div class="p-8 md:p-10">
                    <?php if (isset($success)): ?>
                        <div class="success-card rounded-2xl p-8 text-center mb-8">
                            <div
                                class="w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                                <span class="material-symbols-outlined text-white text-4xl">check_circle</span>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">Request Submitted Successfully!</h3>
                            <p class="text-gray-700 mb-6 max-w-md mx-auto">
                                Your blood request has been submitted for approval. Our team will contact you shortly.
                            </p>
                            <div class="space-y-4">
                                <a href="dashboard.php"
                                    class="inline-flex items-center gap-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white px-8 py-4 rounded-xl font-bold hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                    <span class="material-symbols-outlined">dashboard</span>
                                    Go to Dashboard
                                </a>
                                <p class="text-sm text-gray-600 mt-4">
                                    <span class="material-symbols-outlined text-sm align-middle">schedule</span>
                                    Estimated response time: 2-4 hours
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($message): ?>
                            <div
                                class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 p-5 rounded-xl mb-8 flex items-start gap-4 animate-slideIn">
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="material-symbols-outlined text-red-500 text-2xl">error</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 mb-1">Please check your input</h4>
                                    <p class="text-gray-700">
                                        <?php echo htmlspecialchars($message); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="" method="post" class="space-y-8">
                            <!-- Patient & Blood Group Section -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Patient Details -->
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-red-500">person</span>
                                            Patient Information
                                            <span class="required-star">*</span>
                                        </label>
                                        <input type="text" name="patient_name" required
                                            class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400"
                                            placeholder="Full name of patient">
                                        <p class="text-xs text-gray-500 mt-2 ml-1">Enter the complete name as per medical
                                            records</p>
                                    </div>

                                    <!-- Contact Information -->
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-red-500">contact_phone</span>
                                                Contact Details
                                                <span class="required-star">*</span>
                                            </label>
                                            <input type="tel" name="contact_number" required
                                                class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400"
                                                placeholder="Emergency contact number">
                                        </div>
                                        <input type="text" name="contact_person"
                                            class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400"
                                            placeholder="Contact person name (if different)">
                                    </div>
                                </div>

                                <!-- Blood Group & Requirements -->
                                <div class="space-y-6">
                                    <div>
                                        <label
                                            class="block text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-red-500">bloodtype</span>
                                            Blood Requirements
                                            <span class="required-star">*</span>
                                        </label>
                                        <div class="grid grid-cols-2 gap-4 mb-4">
                                            <div>
                                                <select name="blood_group" required
                                                    class="form-input w-full px-5 py-4 rounded-xl bg-white appearance-none">
                                                    <option value="" disabled selected>Select Blood Group</option>
                                                    <option value="A+">A+</option>
                                                    <option value="A-">A-</option>
                                                    <option value="B+">B+</option>
                                                    <option value="B-">B-</option>
                                                    <option value="AB+">AB+</option>
                                                    <option value="AB-">AB-</option>
                                                    <option value="O+">O+</option>
                                                    <option value="O-">O-</option>
                                                </select>
                                            </div>
                                            <div class="relative">
                                                <input type="number" name="units_needed" min="1" max="10" required
                                                    class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400"
                                                    placeholder="Units needed">
                                                <span
                                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400">bags</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Date Needed & Hospital -->
                                    <div class="space-y-4">
                                        <div>
                                            <label
                                                class="block text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                                <span class="material-symbols-outlined text-red-500">event</span>
                                                Urgency & Location
                                                <span class="required-star">*</span>
                                            </label>
                                            <input type="date" name="date_needed" required
                                                min="<?php echo date('Y-m-d'); ?>"
                                                class="form-input w-full px-5 py-4 rounded-xl">
                                            <p class="text-xs text-gray-500 mt-2 ml-1">When is the blood needed?</p>
                                        </div>
                                        <input type="text" name="hospital"
                                            class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400"
                                            placeholder="Hospital / Clinic name (optional)">
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Details -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-red-500">description</span>
                                    Additional Medical Details
                                </label>
                                <div class="relative">
                                    <textarea name="details" rows="4"
                                        class="form-input w-full px-5 py-4 rounded-xl placeholder-gray-400 resize-none"
                                        placeholder="Please provide any additional information that might help donors (e.g., medical condition, special requirements, etc.)"></textarea>
                                    <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                                        <span class="material-symbols-outlined text-sm align-middle">info</span>
                                        Optional but helpful
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Notice -->
                            <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-100 rounded-2xl p-6">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 mb-2">Important Information</h4>
                                        <ul class="text-sm text-gray-700 space-y-2">
                                            <li class="flex items-start gap-2">
                                                <span
                                                    class="material-symbols-outlined text-red-500 text-sm mt-0.5">check_circle</span>
                                                <span>This form is for genuine medical emergencies only</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span
                                                    class="material-symbols-outlined text-red-500 text-sm mt-0.5">check_circle</span>
                                                <span>False requests may lead to account suspension</span>
                                            </li>
                                            <li class="flex items-start gap-2">
                                                <span
                                                    class="material-symbols-outlined text-red-500 text-sm mt-0.5">check_circle</span>
                                                <span>Our team verifies all requests before contacting donors</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Section -->
                            <div class="pt-4">
                                <button type="submit"
                                    class="submit-btn w-full text-white font-bold py-5 px-6 rounded-2xl text-lg shadow-xl flex items-center justify-center gap-3">
                                    <span class="material-symbols-outlined text-2xl">send</span>
                                    Submit Emergency Request
                                </button>

                                <div class="mt-6 text-center">
                                    <div
                                        class="inline-flex items-center gap-3 text-sm text-gray-500 bg-gray-50 px-6 py-3 rounded-xl">
                                        <span class="material-symbols-outlined text-red-500 text-sm">shield</span>
                                        <span>All information is encrypted and protected under HIPAA guidelines</span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-red-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-500">schedule</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Quick Response</h4>
                            <p class="text-sm text-gray-600">Average 2-4 hour response time</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-red-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-500">verified</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Verified Donors</h4>
                            <p class="text-sm text-gray-600">Pre-screened blood donors</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-red-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-500">local_hospital</span>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">24/7 Support</h4>
                            <p class="text-sm text-gray-600">Emergency helpline available</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animate form elements on load
        document.addEventListener('DOMContentLoaded', function () {
            // Add subtle animation to form inputs
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach((input, index) => {
                input.style.animationDelay = `${index * 0.05}s`;
            });

            // Focus first input
            if (document.querySelector('[name="patient_name"]')) {
                document.querySelector('[name="patient_name"]').focus();
            }

            // Add date placeholder enhancement
            const dateInput = document.querySelector('input[type="date"]');
            if (dateInput) {
                dateInput.addEventListener('focus', function () {
                    this.type = 'date';
                });
                dateInput.addEventListener('blur', function () {
                    if (!this.value) {
                        this.type = 'text';
                        this.placeholder = 'Select date needed';
                    }
                });
            }
        });
    </script>
</body>

</html>