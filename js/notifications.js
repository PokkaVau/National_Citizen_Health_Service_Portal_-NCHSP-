// Notification System Logic

document.addEventListener('DOMContentLoaded', () => {
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDot = document.querySelector('.notification-dot');
    
    // Create Dropdown Element
    const dropdown = document.createElement('div');
    dropdown.id = 'notificationDropdown';
    dropdown.className = 'hidden absolute top-16 right-6 w-80 bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden z-50 animate-fade-in-up origin-top-right';
    document.body.appendChild(dropdown); // Append to body to avoid clipping, or handle positioning

    // Ensure button has relative positioning context or we use absolute positioning relative to button
    if(notificationBtn) {
        notificationBtn.parentElement.style.position = 'relative';
        notificationBtn.parentElement.appendChild(dropdown);
        dropdown.style.top = '100%';
        dropdown.style.right = '0';
        dropdown.style.marginTop = '0.5rem';
    }

    let unreadCount = 0;

    // Fetch Notifications
    async function fetchNotifications() {
        try {
            // Determine correct path relative to current page location. 
            // Assuming js/ is in root, api/ is in root.
            // If page is in admin/ or doctor/, we need ../api.
            // A simpler way is to use absolute paths if hosted on root, or relative detection.
            
            const pathPrefix = window.location.pathname.includes('/admin/') || window.location.pathname.includes('/doctor/') ? '../' : '';
            
            const response = await fetch(`${pathPrefix}api/fetch_notifications.php`);
            const data = await response.json();

            if (data.success) {
                renderNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    function renderNotifications(notifications) {
        unreadCount = notifications.length;
        
        // Update Badge
        if (unreadCount > 0) {
            notificationDot.style.display = 'block';
            notificationDot.classList.add('animate-pulse');
        } else {
            notificationDot.style.display = 'none';
        }

        // Render Dropdown Content
        let html = `
            <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="font-bold text-gray-800 text-sm">Notifications</h3>
                ${unreadCount > 0 ? `<button onclick="markAllRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>` : ''}
            </div>
            <div class="max-h-80 overflow-y-auto">
        `;

        if (notifications.length === 0) {
            html += `
                <div class="px-4 py-8 text-center text-gray-500 text-sm">
                    <span class="material-symbols-outlined text-gray-300 text-3xl mb-1">notifications_off</span>
                    <p>No new notifications</p>
                </div>
            `;
        } else {
            notifications.forEach(notif => {
                const colors = {
                    'info': 'text-blue-500 bg-blue-50',
                    'success': 'text-green-500 bg-green-50',
                    'warning': 'text-yellow-500 bg-yellow-50',
                    'danger': 'text-red-500 bg-red-50'
                };
                const icons = {
                    'info': 'info',
                    'success': 'check_circle',
                    'warning': 'warning',
                    'danger': 'error'
                };
                
                const colorClass = colors[notif.type] || colors['info'];
                const iconName = icons[notif.type] || 'notifications';

                html += `
                    <div onclick="handleNotificationClick(${notif.id}, '${notif.link || ''}')" class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-50 last:border-0 transition-colors">
                        <div class="flex gap-3">
                            <div class="size-8 rounded-full ${colorClass} flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-outlined text-sm">${iconName}</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800 leading-snug">${notif.message}</p>
                                <p class="text-xs text-gray-400 mt-1">${timeAgo(new Date(notif.created_at))}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        html += `</div>`;
        dropdown.innerHTML = html;
    }

    // Toggle Dropdown
    if(notificationBtn) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });
    }

    // Close on click outside
    document.addEventListener('click', () => {
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });

    dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Helper: Time Ago
    function timeAgo(date) {
        const seconds = Math.floor((new Date() - date) / 1000);
        let interval = seconds / 31536000;
        if (interval > 1) return Math.floor(interval) + "y ago";
        interval = seconds / 2592000;
        if (interval > 1) return Math.floor(interval) + "mo ago";
        interval = seconds / 86400;
        if (interval > 1) return Math.floor(interval) + "d ago";
        interval = seconds / 3600;
        if (interval > 1) return Math.floor(interval) + "h ago";
        interval = seconds / 60;
        if (interval > 1) return Math.floor(interval) + "m ago";
        return Math.floor(seconds) + "s ago";
    }

    // Expose functions globally
    window.markAllRead = async () => {
        const pathPrefix = window.location.pathname.includes('/admin/') || window.location.pathname.includes('/doctor/') ? '../' : '';
        await fetch(`${pathPrefix}api/mark_notification_read.php`, {
            method: 'POST',
            body: JSON.stringify({ mark_all: true })
        });
        fetchNotifications();
    };

    window.handleNotificationClick = async (id, link) => {
        const pathPrefix = window.location.pathname.includes('/admin/') || window.location.pathname.includes('/doctor/') ? '../' : '';
        await fetch(`${pathPrefix}api/mark_notification_read.php`, {
            method: 'POST',
            body: JSON.stringify({ id: id })
        });
        
        if (link && link !== 'null') {
            window.location.href = link;
        } else {
            fetchNotifications();
        }
    };

    // Initial Fetch & Poll
    fetchNotifications();
    setInterval(fetchNotifications, 30000); // Poll every 30s
});
