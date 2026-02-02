<!-- Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeReviewModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="submit_review.php" method="POST" class="p-6">
                <input type="hidden" name="appointment_id" id="modalApptId">
                <div class="text-center mb-6">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 mb-4">
                        <span class="material-symbols-outlined text-indigo-600 text-2xl">rate_review</span>
                    </div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Rate Your Experience</h3>
                    <p class="text-sm text-gray-500 mt-1">How was your appointment with <span id="modalDocName"
                            class="font-bold"></span>?</p>
                </div>

                <div class="mb-6 text-center">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer group">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" class="hidden peer" required>
                                <span
                                    class="material-symbols-outlined text-3xl text-gray-300 peer-checked:text-amber-400 group-hover:text-amber-300 transition-colors"
                                    onclick="setRating(<?php echo $i; ?>)">star</span>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <p class="text-xs text-gray-400">Click stars to rate</p>
                </div>

                <div class="mb-6">
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Write a Review
                        (Optional)</label>
                    <textarea name="comment" id="comment" rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                        placeholder="Share your experience..."></textarea>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="closeReviewModal()"
                        class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openReviewModal(apptId, docName) {
        document.getElementById('modalApptId').value = apptId;
        document.getElementById('modalDocName').textContent = docName;
        document.getElementById('reviewModal').classList.remove('hidden');
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').classList.add('hidden');
    }

    // Star rating interaction logic
    const stars = document.querySelectorAll('input[name="rating"] + span');
    const inputs = document.querySelectorAll('input[name="rating"]');

    inputs.forEach((input, index) => {
        input.addEventListener('change', () => {
            updateStars(index);
        });
    });

    function updateStars(selectedIndex) {
        stars.forEach((star, index) => {
            if (index <= selectedIndex) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-amber-400');
            } else {
                star.classList.add('text-gray-300');
                star.classList.remove('text-amber-400');
            }
        });
    }
</script>