<?php
require('config/db.php');
require('auth_session.php');
check_user_login();

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Medical Summarizer - NCHSP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-dark: #7c3aed;
            --gradient-start: #6366f1;
            --gradient-mid: #a855f7;
            --gradient-end: #ec4899;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }

        .glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-panel:hover {
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.07), 0 1px 4px rgba(0, 0, 0, 0.03);
            transform: translateY(-1px);
        }

        .upload-zone {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='20' ry='20' stroke='%23CBD5E1FF' stroke-width='2' stroke-dasharray='12%2c 12' stroke-dashoffset='0' stroke-linecap='round'/%3e%3c/svg%3e");
            position: relative;
            overflow: hidden;
        }

        .upload-zone::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.05), transparent);
            transition: left 0.6s ease;
        }

        .upload-zone:hover::before {
            left: 100%;
        }

        .upload-zone.dragover {
            background-color: rgba(245, 243, 255, 0.6);
            background-image: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3crect width='100%25' height='100%25' fill='none' rx='20' ry='20' stroke='%238B5CF6FF' stroke-width='3' stroke-dasharray='12%2c 12' stroke-dashoffset='0' stroke-linecap='round'/%3e%3c/svg%3e");
            transform: scale(1.02);
        }

        .ai-loader {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(243, 244, 246, 0.8);
            border-top-color: var(--gradient-start);
            border-right-color: var(--gradient-mid);
            border-bottom-color: var(--gradient-end);
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1.2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-mid) 50%, var(--gradient-end) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gradient-ai {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-mid) 50%, var(--gradient-end) 100%);
            background-size: 200% 200%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gradient-ai:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.3);
        }

        .gradient-ai:active {
            transform: translateY(0);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.5);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-mid) 100%);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--gradient-mid) 0%, var(--gradient-end) 100%);
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

        .status-badge {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        .markdown-content h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
            margin-top: 2em;
            margin-bottom: 0.75em;
            padding-bottom: 0.5em;
            border-bottom: 2px solid #e5e7eb;
            position: relative;
        }

        .markdown-content h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-mid));
            border-radius: 2px;
        }

        .markdown-content p {
            margin-bottom: 1.25em;
            line-height: 1.7;
            color: #4b5563;
        }

        .markdown-content ul {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 1.5em;
        }

        .markdown-content li {
            margin-bottom: 0.75em;
            color: #4b5563;
            padding-left: 1.75em;
            position: relative;
        }

        .markdown-content li::before {
            content: '•';
            position: absolute;
            left: 0.5em;
            color: var(--gradient-mid);
            font-weight: bold;
            font-size: 1.2em;
        }

        .markdown-content strong {
            color: #1f2937;
            font-weight: 600;
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>

<body class="min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-xl sticky top-0 z-50 border-b border-slate-200/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <a href="dashboard.php"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl hover:bg-slate-100/80 transition-all duration-300 text-slate-700 font-medium card-hover group">
                        <span
                            class="material-symbols-outlined transition-transform group-hover:-translate-x-1">arrow_back</span>
                        <span>Back to Dashboard</span>
                    </a>
                    <div class="flex items-center gap-3 ml-4">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-xl gradient-ai flex items-center justify-center shadow-lg">
                                <span class="material-symbols-outlined text-white text-2xl">smart_toy</span>
                            </div>
                            <div
                                class="absolute -inset-2 rounded-xl bg-gradient-to-r from-purple-500/20 to-pink-500/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold gradient-text">
                                AI Health Assistant
                            </h1>
                            <p class="text-sm text-slate-500">Intelligent medical document analysis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8 lg:py-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <!-- Input Section -->
            <div class="space-y-8">
                <div class="glass-panel rounded-3xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-purple-600">upload_file</span>
                            </div>
                            <span>Upload Medical Records</span>
                        </h2>
                        <span class="text-xs font-medium px-3 py-1 rounded-full bg-blue-100 text-blue-700">
                            Step 1 of 2
                        </span>
                    </div>

                    <form id="aiForm" class="space-y-6">
                        <!-- File Upload -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-3 ml-1">Medical Documents</label>
                            <div id="dropZone"
                                class="upload-zone rounded-3xl p-12 text-center cursor-pointer hover:bg-slate-50/50 relative group">
                                <input type="file" name="prescription" id="fileInput" accept="image/*,.pdf"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                <div class="space-y-4 pointer-events-none relative z-0">
                                    <div class="floating">
                                        <div
                                            class="w-20 h-20 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl flex items-center justify-center mx-auto transition-all duration-500 group-hover:scale-110 group-hover:shadow-lg">
                                            <span
                                                class="material-symbols-outlined text-4xl gradient-text">add_a_photo</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="font-semibold text-slate-800 text-lg">Drop your files here</p>
                                        <p class="text-sm text-slate-500">Supports JPG, PNG, PDF • Max 10MB</p>
                                    </div>
                                    <button type="button" onclick="document.getElementById('fileInput').click()"
                                        class="px-6 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm transition-all duration-300 inline-flex items-center gap-2">
                                        <span class="material-symbols-outlined text-base">folder_open</span>
                                        Browse Files
                                    </button>
                                    <div id="filePreview" class="hidden mt-6 animate-fade-in">
                                        <div
                                            class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                                <span
                                                    class="material-symbols-outlined text-green-600 text-lg">check_circle</span>
                                            </div>
                                            <div class="text-left">
                                                <span id="fileName"
                                                    class="font-medium text-green-800 text-sm block"></span>
                                                <span class="text-xs text-green-600">Ready for analysis</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Text Input -->
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-slate-700 ml-1 flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-base">notes</span>
                                Additional Notes / Symptoms
                            </label>
                            <div class="relative">
                                <textarea name="symptoms" rows="4"
                                    class="w-full px-5 py-4 rounded-2xl border-2 border-slate-200/80 focus:border-purple-500 focus:ring-4 focus:ring-purple-100 outline-none transition-all duration-300 resize-none bg-white/50 placeholder-slate-400"
                                    placeholder="Describe your symptoms, concerns, or any additional information..."></textarea>
                                <div class="absolute bottom-3 right-3 text-xs text-slate-400 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">info</span>
                                    Optional
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="card-hover">
                            <label
                                class="flex items-start gap-4 p-5 rounded-2xl border-2 border-slate-200/80 cursor-pointer hover:bg-slate-50/50 transition-all duration-300 bg-white/50">
                                <div class="flex items-start mt-1">
                                    <input type="checkbox" name="include_history" value="true" checked
                                        class="w-5 h-5 text-purple-600 rounded-xl focus:ring-2 focus:ring-purple-500 border-slate-300 cursor-pointer transition-all">
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-semibold text-slate-800">Include Medical History</span>
                                        <span
                                            class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">Recommended</span>
                                    </div>
                                    <p class="text-sm text-slate-600 leading-relaxed">Allow AI to access your current
                                        medications and recent lab results for personalized analysis and better
                                        recommendations.</p>
                                </div>
                            </label>
                        </div>

                        <div class="pt-4">
                            <button type="submit" id="analyzeBtn"
                                class="w-full py-5 rounded-2xl gradient-ai text-white font-bold shadow-xl hover:shadow-2xl transition-all duration-500 flex items-center justify-center gap-3 text-lg group">
                                <span
                                    class="material-symbols-outlined text-xl group-hover:scale-110 transition-transform duration-300">auto_awesome</span>
                                <span>Analyze with AI</span>
                                <span
                                    class="material-symbols-outlined text-xl group-hover:translate-x-1 transition-transform duration-300">arrow_forward</span>
                            </button>
                            <p class="text-center text-sm text-slate-500 mt-3">
                                Analysis typically takes 15-30 seconds
                            </p>
                        </div>
                    </form>
                </div>

                <div
                    class="glass-panel rounded-3xl p-6 border border-blue-200/50 bg-gradient-to-r from-blue-50/50 to-indigo-50/50">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
                                <span class="material-symbols-outlined text-blue-600">enhanced_encryption</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                                <span>Your Data is Secure</span>
                                <span class="text-xs px-2 py-1 bg-blue-200 text-blue-800 rounded-full">HIPAA
                                    Compliant</span>
                            </h3>
                            <p class="text-sm text-blue-800 leading-relaxed">
                                All documents are processed with end-to-end encryption. Your medical data is never
                                stored permanently and is deleted after analysis. AI insights are for informational
                                purposes only.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Result Section -->
            <div class="relative">
                <div class="sticky top-24">
                    <div class="glass-panel rounded-3xl p-8 h-full flex flex-col">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-100 to-pink-100 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-purple-600 text-2xl">analytics</span>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-slate-800">AI Analysis Result</h2>
                                    <p class="text-sm text-slate-500">Personalized health insights</p>
                                </div>
                            </div>
                            <div id="statusBadge" class="status-badge hidden">
                                <div
                                    class="flex items-center gap-2 px-4 py-2 rounded-full bg-slate-100 text-slate-600 font-medium text-sm">
                                    <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                    <span>Waiting</span>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div id="loader"
                            class="hidden flex-1 flex flex-col items-center justify-center text-center p-8">
                            <div class="relative mb-8">
                                <div class="ai-loader"></div>
                                <div
                                    class="absolute inset-0 rounded-full bg-gradient-to-r from-purple-500/10 to-pink-500/10 blur-lg">
                                </div>
                            </div>
                            <h3 class="text-xl font-semibold text-slate-800 mb-3">Processing Your Documents</h3>
                            <p class="text-slate-500 max-w-sm mb-6">Our AI is analyzing your medical records and
                                cross-referencing with clinical data.</p>
                            <div class="w-64 h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full animate-shimmer"
                                    style="background-size: 1000px 100%;"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-4">This may take a moment...</p>
                        </div>

                        <!-- Empty State -->
                        <div id="emptyState" class="flex-1 flex flex-col items-center justify-center text-center p-8">
                            <div class="mb-6 relative">
                                <div
                                    class="w-32 h-32 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center floating">
                                    <span class="material-symbols-outlined text-5xl text-slate-300">smart_toy</span>
                                </div>
                                <div
                                    class="absolute -inset-4 rounded-full bg-gradient-to-r from-purple-500/5 to-pink-500/5 blur-xl">
                                </div>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-700 mb-2">Ready to Analyze</h3>
                            <p class="text-slate-500 max-w-xs mb-6">Upload a medical document or describe your symptoms
                                to receive AI-powered health insights.</p>
                            <div class="flex gap-2 text-sm text-slate-400">
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Secure
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Instant
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">check_circle</span>
                                    Accurate
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div id="resultContent" class="hidden flex-1 overflow-y-auto pr-4 custom-scrollbar">
                            <div class="markdown-content p-1">
                                <!-- Markdown content will be injected here -->
                            </div>
                        </div>

                        <!-- Result Actions -->
                        <div id="resultActions" class="hidden mt-8 pt-6 border-t border-slate-200/50">
                            <div class="flex gap-3">
                                <button onclick="printResult()"
                                    class="flex-1 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium transition-all duration-300 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">print</span>
                                    Print Report
                                </button>
                                <button onclick="downloadResult()"
                                    class="flex-1 py-3 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium transition-all duration-300 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">download</span>
                                    Save PDF
                                </button>
                                <button onclick="shareResult()"
                                    class="flex-1 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium transition-all duration-300 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined">share</span>
                                    Share
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');
        const filePreview = document.getElementById('filePreview');
        const form = document.getElementById('aiForm');
        const analyzeBtn = document.getElementById('analyzeBtn');
        const loader = document.getElementById('loader');
        const emptyState = document.getElementById('emptyState');
        const resultContent = document.getElementById('resultContent');
        const resultActions = document.getElementById('resultActions');
        const statusBadge = document.getElementById('statusBadge');
        const markdownContainer = resultContent.querySelector('.markdown-content');

        // Store the processed image blob
        let processedFile = null;

        // Drag & Drop Handling with improved visual feedback
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                dropZone.classList.add('dragover');
                document.body.style.cursor = 'copy';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                dropZone.classList.remove('dragover');
                document.body.style.cursor = 'default';
            }, false);
        });

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFiles(files);
        }

        fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

        async function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];

                // Show file preview with animation
                filePreview.classList.remove('hidden');
                filePreview.style.opacity = '0';
                filePreview.style.transform = 'translateY(10px)';

                // Update status badge
                statusBadge.classList.remove('hidden');
                statusBadge.innerHTML = `
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 text-yellow-700 font-medium text-sm animate-pulse">
                        <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                        <span>Processing File</span>
                    </div>
                `;

                // File type validation
                if (!file.type.match(/image\/(jpeg|png|jpg|webp)|application\/pdf/)) {
                    showError('Please upload a valid image or PDF file.');
                    return;
                }

                // File size validation (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    showError('File size exceeds 10MB limit.');
                    return;
                }

                // If it's a PDF, convert to image
                if (file.type === 'application/pdf') {
                    fileName.innerHTML = `<span class="font-medium">${file.name}</span><br><span class="text-xs text-yellow-600">Converting PDF...</span>`;
                    try {
                        processedFile = await convertPdfToImage(file);
                        fileName.innerHTML = `<span class="font-medium">${file.name}</span><br><span class="text-xs text-green-600">✓ Ready for analysis</span>`;

                        statusBadge.innerHTML = `
                            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 font-medium text-sm">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <span>File Ready</span>
                            </div>
                        `;
                    } catch (err) {
                        fileName.innerHTML = `<span class="text-red-600">Error converting PDF</span>`;
                        console.error(err);
                        showError("Could not process PDF. Please try an image file.");
                        processedFile = null;
                    }
                } else {
                    // Create thumbnail for images
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            // You could add thumbnail preview here if needed
                        };
                        reader.readAsDataURL(file);
                    }
                    processedFile = file; // Use existing file if it's an image
                    fileName.innerHTML = `<span class="font-medium">${file.name}</span><br><span class="text-xs text-green-600">✓ Ready for analysis</span>`;

                    statusBadge.innerHTML = `
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 font-medium text-sm">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span>File Ready</span>
                        </div>
                    `;
                }

                // Animate in
                setTimeout(() => {
                    filePreview.style.opacity = '1';
                    filePreview.style.transform = 'translateY(0)';
                    filePreview.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                }, 100);
            } else {
                filePreview.style.opacity = '0';
                setTimeout(() => {
                    filePreview.classList.add('hidden');
                }, 400);
                processedFile = null;
            }
        }

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'fixed top-4 right-4 z-50 px-6 py-4 rounded-xl bg-red-50 border border-red-200 text-red-700 font-medium shadow-lg animate-fade-in';
            errorDiv.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-red-600">error</span>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(errorDiv);

            setTimeout(() => {
                errorDiv.style.opacity = '0';
                errorDiv.style.transform = 'translateX(100%)';
                setTimeout(() => errorDiv.remove(), 300);
            }, 4000);
        }

        async function convertPdfToImage(file) {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            const page = await pdf.getPage(1); // Get first page

            const scale = 2.0; // Higher scale for better quality text recognition
            const viewport = page.getViewport({ scale });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            await page.render({
                canvasContext: context,
                viewport: viewport
            }).promise;

            return new Promise((resolve) => {
                canvas.toBlob((blob) => {
                    resolve(new File([blob], file.name.replace('.pdf', '.jpg'), { type: 'image/jpeg' }));
                }, 'image/jpeg', 0.95);
            });
        }

        // Form Submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!processedFile && !document.querySelector('[name="symptoms"]').value.trim()) {
                showError("Please upload a file or enter symptoms to analyze.");
                return;
            }

            // UI Updates with animations
            analyzeBtn.disabled = true;
            analyzeBtn.classList.add('opacity-80', 'cursor-not-allowed');
            analyzeBtn.innerHTML = '<span class="material-symbols-outlined animate-spin">sync</span> Processing Analysis...';

            emptyState.classList.add('hidden');
            resultContent.classList.add('hidden');
            resultActions.classList.add('hidden');
            loader.classList.remove('hidden');

            statusBadge.classList.remove('hidden');
            statusBadge.innerHTML = `
                <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-purple-100 text-purple-700 font-medium text-sm animate-pulse">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                    <span>AI Processing</span>
                </div>
            `;

            const formData = new FormData(form);
            // Replace the file input with our processed file (image)
            if (processedFile) {
                formData.set('prescription', processedFile);
            }

            try {
                const response = await fetch('api/generate_summary.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Render Markdown with animation
                    markdownContainer.innerHTML = marked.parse(data.summary);
                    resultContent.classList.remove('hidden');
                    resultActions.classList.remove('hidden');

                    statusBadge.innerHTML = `
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-green-100 text-green-700 font-medium text-sm">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <span>Analysis Complete</span>
                        </div>
                    `;

                    // Animate in content
                    markdownContainer.style.opacity = '0';
                    markdownContainer.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        markdownContainer.style.opacity = '1';
                        markdownContainer.style.transform = 'translateY(0)';
                        markdownContainer.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    }, 100);

                    // Show success notification
                    showSuccess('Analysis completed successfully!');
                } else {
                    throw new Error(data.error || 'Unable to process your request. Please try again.');
                }
            } catch (error) {
                markdownContainer.innerHTML = `
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 border border-red-200 rounded-2xl p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-red-600 text-2xl">error</span>
                        </div>
                        <h3 class="text-lg font-semibold text-red-800 mb-2">Analysis Failed</h3>
                        <p class="text-red-600 mb-4">${error.message}</p>
                        <button onclick="retryAnalysis()" class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg font-medium transition-colors">
                            Try Again
                        </button>
                    </div>
                `;
                resultContent.classList.remove('hidden');

                statusBadge.innerHTML = `
                    <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 text-red-700 font-medium text-sm">
                        <div class="w-2 h-2 rounded-full bg-red-500"></div>
                        <span>Analysis Failed</span>
                    </div>
                `;
            } finally {
                loader.classList.add('hidden');
                analyzeBtn.disabled = false;
                analyzeBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                analyzeBtn.innerHTML = `
                    <span class="material-symbols-outlined">auto_awesome</span>
                    <span>Analyze with AI</span>
                    <span class="material-symbols-outlined">arrow_forward</span>
                `;
            }
        });

        function showSuccess(message) {
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 z-50 px-6 py-4 rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 font-medium shadow-lg animate-fade-in';
            successDiv.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    <span>${message}</span>
                </div>
            `;
            document.body.appendChild(successDiv);

            setTimeout(() => {
                successDiv.style.opacity = '0';
                successDiv.style.transform = 'translateX(100%)';
                setTimeout(() => successDiv.remove(), 300);
            }, 4000);
        }

        // Placeholder functions for result actions
        function printResult() {
            showSuccess('Print dialog will open...');
            // Implement print functionality
        }

        function downloadResult() {
            showSuccess('Downloading report...');
            // Implement download functionality
        }

        function shareResult() {
            showSuccess('Share options coming soon...');
            // Implement share functionality
        }

        function retryAnalysis() {
            form.dispatchEvent(new Event('submit'));
        }

        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', () => {
            // Add subtle background animation
            const bgAnimation = document.createElement('div');
            bgAnimation.className = 'fixed inset-0 -z-10 opacity-50';
            bgAnimation.innerHTML = `
                <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-1/3 right-1/4 w-96 h-96 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-1/4 left-1/3 w-96 h-96 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            `;
            document.body.appendChild(bgAnimation);
        });
    </script>
</body>

</html>