<?php
require('../config/db.php');
require('../auth_session.php');
check_admin_login();

$message = "";

if (isset($_POST['add_camp'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $camp_date = $_POST['camp_date'];
    $description = $_POST['description'];
    $google_map_link = $_POST['google_map_link'];
    $contact_number = $_POST['contact_number'];
    $image_path = null;

    // Handle Image Upload
    if (isset($_FILES['camp_image']) && $_FILES['camp_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['camp_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $upload_dir = '../uploads/camps/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (move_uploaded_file($_FILES['camp_image']['tmp_name'], $upload_dir . $new_name)) {
                $image_path = 'uploads/camps/' . $new_name;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO health_camps (name, location, camp_date, description, google_map_link, contact_number, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $location, $camp_date, $description, $google_map_link, $contact_number, $image_path])) {
        $message = "Health Camp added successfully!";
    } else {
        $message = "Error adding camp.";
    }
}

$camps = $pdo->query("SELECT * FROM health_camps ORDER BY camp_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Health Camps - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Manage Health Camps</h1>
            <a href="dashboard.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
                <p>
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Add Camp Form -->
        <div class="bg-white p-6 rounded-xl shadow-sm mb-8">
            <h2 class="text-lg font-bold mb-4">Add New Health Camp</h2>
            <form action="" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Camp Name</label>
                    <input type="text" name="name" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" name="camp_date" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maps Link</label>
                    <input type="url" name="google_map_link"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Contact Number</label>
                    <input type="text" name="contact_number"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cover Image</label>
                    <input type="file" name="camp_image" accept="image/*"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <div class="md:col-span-2 pt-4">
                    <button type="submit" name="add_camp"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Publish Health
                        Camp</button>
                </div>
            </form>
        </div>

        <!-- Camps List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <h2 class="text-lg font-bold p-6 bg-gray-50 border-b">Existing Camps</h2>
            <div class="divide-y divide-gray-100">
                <?php foreach ($camps as $camp): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-900">
                                    <?php echo htmlspecialchars($camp['name']); ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    <?php echo htmlspecialchars($camp['location']); ?> •
                                    <?php echo htmlspecialchars($camp['camp_date']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>

</html>