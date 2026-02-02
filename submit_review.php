<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $appointment_id = $_POST['appointment_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);

    // 1. Verify Appointment Ownership & Status
    // Prevents fake reviews by ensuring:
    // - The appointment belongs to the logged-in user
    // - The status is 'completed'
    // - The appointment exists
    $stmt = $pdo->prepare("SELECT doctor_id FROM appointments WHERE id = ? AND user_id = ? AND status = 'completed'");
    $stmt->execute([$appointment_id, $user_id]);
    $appointment = $stmt->fetch();

    if ($appointment) {
        $doctor_id = $appointment['doctor_id'];

        try {
            // 2. Insert Review
            // Unique constraint on appointment_id prevents duplicate reviews for same appointment
            $stmt = $pdo->prepare("INSERT INTO doctor_reviews (appointment_id, user_id, doctor_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$appointment_id, $user_id, $doctor_id, $rating, $comment]);

            // Success Modal
            echo_modal("Review submitted successfully!", "success", "dashboard.php");
            exit();
        } catch (PDOException $e) {
            // Handle duplicate entry
            if ($e->getCode() == 23000) {
                echo_modal("You have already reviewed this appointment.", "error", "dashboard.php");
            } else {
                echo_modal("Error submitting review.", "error", "dashboard.php");
            }
            exit();
        }
    } else {
        echo_modal("Invalid appointment to review.", "error", "dashboard.php");
        exit();
    }
} else {
    header("Location: my_appointments.php");
    exit();
}

function echo_modal($message, $type, $redirect_url)
{
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Processing...</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>

    <body class="bg-gray-900 flex items-center justify-center h-screen">
        <div id="modal"
            class="bg-gray-800 p-8 rounded-xl shadow-2xl text-center transform scale-100 transition-transform duration-300 max-w-sm w-full mx-4 border border-gray-700">
            <div class="mb-4">
                <?php if ($type == 'success'): ?>
                    <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center mx-auto">
                        <i class="fas fa-check text-3xl text-green-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mt-4">Success!</h2>
                <?php else: ?>
                    <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto">
                        <i class="fas fa-times text-3xl text-red-500"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white mt-4">Error</h2>
                <?php endif; ?>
            </div>
            <p class="text-gray-300 mb-6 text-lg"><?php echo htmlspecialchars($message); ?></p>
            <div class="w-full bg-gray-700 rounded-full h-1.5 mb-2 overflow-hidden">
                <div class="bg-blue-500 h-1.5 rounded-full animate-[width_4s_linear_forwards]" style="width: 100%"></div>
            </div>
            <p class="text-gray-500 text-sm">Redirecting in 4 seconds...</p>
        </div>

        <script>
            setTimeout(function () {
                window.location.href = '<?php echo $redirect_url; ?>';
            }, 4000);

            // Add custom animation for progress bar
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes width {
                    from { width: 100%; }
                    to { width: 0%; }
                }
            `;
            document.head.appendChild(style);
        </script>
    </body>

    </html>
    <?php
}
?>