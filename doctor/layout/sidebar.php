<aside class="w-64 bg-slate-900 text-white flex flex-col hidden md:flex sticky top-0 h-screen overflow-y-auto">
    <div class="p-6 flex items-center gap-3">
        <div class="bg-indigo-600 p-2 rounded-lg">
            <span class="material-symbols-outlined text-white">medical_services</span>
        </div>
        <div>
            <h1 class="text-lg font-bold">Doctor Portal</h1>
            <p class="text-xs text-slate-400">Welcome</p>
        </div>
    </div>

    <nav class="flex-1 px-4 space-y-2 mt-4">
        <a href="dashboard.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> transition-colors">
            <span class="material-symbols-outlined">dashboard</span>
            <span>Dashboard</span>
        </a>

        <a href="schedule.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> transition-colors">
            <span class="material-symbols-outlined">calendar_month</span>
            <span>Schedule</span>
        </a>

        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'doctor'): ?>
            <a href="manage_assistants.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'manage_assistants.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> transition-colors">
                <span class="material-symbols-outlined">group</span>
                <span>Assistants</span>
            </a>

            <a href="profile.php"
                class="flex items-center gap-3 px-4 py-3 rounded-lg <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'; ?> transition-colors">
                <span class="material-symbols-outlined">person</span>
                <span>My Profile</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="p-4 mt-auto border-t border-slate-800">
        <a href="../logout.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-400 hover:bg-slate-800 hover:text-red-300 transition-colors">
            <span class="material-symbols-outlined">logout</span>
            <span>Logout</span>
        </a>
    </div>
</aside>
<!-- Mobile Menu Button (Visible only on small screens) -->
<div class="md:hidden p-4 bg-slate-900 text-white flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center gap-2">
        <span class="material-symbols-outlined">medical_services</span>
        <span class="font-bold">Doctor Portal</span>
    </div>
    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="p-2">
        <span class="material-symbols-outlined">menu</span>
    </button>
</div>
<div id="mobile-menu" class="hidden md:hidden bg-slate-800 text-white p-4 absolute w-full z-40">
    <nav class="space-y-4">
        <a href="dashboard.php" class="block">Dashboard</a>
        <a href="schedule.php" class="block">Schedule</a>
        <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] == 'doctor'): ?>
            <a href="manage_assistants.php" class="block">Assistants</a>
            <a href="profile.php" class="block">Profile</a>
        <?php endif; ?>
        <a href="../logout.php" class="block text-red-400">Logout</a>
    </nav>
</div>