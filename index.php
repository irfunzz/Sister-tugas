<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Architecture - Sistem Terdistribusi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-100 font-sans text-slate-800 antialiased h-screen overflow-hidden flex flex-col">

    <!-- Header Atas: Indikator Arsitektur Sistem Terdistribusi -->
    <header class="bg-emerald-700 text-white px-4 py-2 flex items-center justify-between text-xs sm:text-sm shadow z-20">
        <div class="flex items-center space-x-2">
            <span class="font-bold tracking-wide flex items-center gap-1.5">
                <svg class="w-5 h-5 text-emerald-200" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
                Distributed Chat
            </span>
            <span class="hidden md:inline-block bg-emerald-800 text-emerald-200 px-2 py-0.5 rounded text-[11px]">
                Node: App Server (<span id="node-host">...</span>)
            </span>
        </div>
        
        <div class="flex items-center space-x-3">
            <div id="db-status-pill" class="flex items-center gap-1.5 bg-emerald-800/80 px-2.5 py-1 rounded-full text-[11px]">
                <span class="w-2 h-2 rounded-full bg-yellow-400 animate-pulse" id="db-indicator"></span>
                <span>Cloud DB: <strong id="db-status-text">Memeriksa...</strong></span>
            </div>
            <button onclick="openTimeSyncModal()" class="hover:bg-emerald-600 px-2 py-1 rounded flex items-center gap-1 text-[11px] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Waktu Cloud: <span id="header-cloud-time" class="font-mono">--:--:--</span></span>
            </button>
            <div id="auth-actions" class="hidden">
                <button onclick="handleLogout()" class="bg-red-500/80 hover:bg-red-600 px-2 py-0.5 rounded text-[11px] transition">Logout</button>
            </div>
        </div>
    </header>

    <!-- Kontainer Aplikasi -->
    <main class="flex-1 flex overflow-hidden relative">

        <!-- VIEW 1: AUTH (LOGIN & REGISTER) -->
        <section id="auth-view" class="absolute inset-0 bg-slate-200 flex items-center justify-center p-4 z-10">
            <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden">
                <div class="bg-emerald-600 p-6 text-white text-center">
                    <h1 class="text-xl font-bold">Sistem Terdistribusi WhatsApp</h1>
                    <p class="text-xs text-emerald-100 mt-1">Multi-Node Application Server & Cloud Database</p>
                </div>

                <div class="p-6">
                    <!-- Tab Switcher -->
                    <div class="flex border-b border-slate-200 mb-5">
                        <button id="tab-login-btn" onclick="switchAuthTab('login')" class="flex-1 pb-2 font-semibold text-emerald-600 border-b-2 border-emerald-600 text-sm">Masuk</button>
                        <button id="tab-register-btn" onclick="switchAuthTab('register')" class="flex-1 pb-2 font-medium text-slate-500 text-sm">Daftar Akun</button>
                    </div>

                    <!-- Form Login -->
                    <form id="login-form" onsubmit="submitLogin(event)" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Username atau Email</label>
                            <input type="text" id="login-ident" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="user1 atau user1@distribusys.local">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Password</label>
                            <input type="password" id="login-pass" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="••••••••">
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded text-sm font-semibold transition">Masuk ke App Server Ini</button>

                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-500 mb-2 font-medium">Uji Coba Demo Akun Cepat:</p>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" onclick="quickLogin('user1')" class="bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border text-xs py-1.5 rounded font-mono">User 1</button>
                                <button type="button" onclick="quickLogin('user2')" class="bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border text-xs py-1.5 rounded font-mono">User 2</button>
                                <button type="button" onclick="quickLogin('user3')" class="bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 border text-xs py-1.5 rounded font-mono">User 3</button>
                            </div>
                        </div>
                    </form>

                    <!-- Form Register -->
                    <form id="register-form" onsubmit="submitRegister(event)" class="space-y-3 hidden">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                            <input type="text" id="reg-name" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Nama Anda">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Username</label>
                            <input type="text" id="reg-user" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="username_unik">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Email</label>
                            <input type="email" id="reg-email" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="nama@email.com">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase mb-1">Password</label>
                            <input type="password" id="reg-pass" required class="w-full px-3 py-2 border rounded text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none" placeholder="Minimal 6 karakter">
                        </div>
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded text-sm font-semibold transition">Buat Akun Baru</button>
                    </form>

                    <div id="auth-error" class="hidden mt-4 p-2.5 bg-red-50 border border-red-200 text-red-600 text-xs rounded"></div>
                </div>
            </div>
        </section>

        <!-- VIEW 2: CHAT DASHBOARD -->
        <section id="chat-view" class="flex-1 flex w-full h-full bg-white">
            
            <!-- Sidebar Kiri: Daftar Kontak -->
            <aside class="w-full sm:w-80 md:w-96 border-r border-slate-200 flex flex-col bg-white">
                <!-- User Profile Header -->
                <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2.5">
                        <div id="my-avatar" class="w-9 h-9 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-sm">
                            U
                        </div>
                        <div class="leading-tight">
                            <h2 id="my-name" class="font-semibold text-sm text-slate-800">Nama User</h2>
                            <p id="my-user" class="text-xs text-slate-500">@username</p>
                        </div>
                    </div>
                    <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded font-mono">App Client</span>
                </div>

                <!-- Search Input -->
                <div class="p-2 border-b border-slate-100 bg-white">
                    <div class="relative">
                        <input type="text" id="search-contacts" oninput="filterContacts(this.value)" placeholder="Cari kontak..." class="w-full pl-8 pr-3 py-1.5 bg-slate-100 text-xs rounded-lg border-0 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <!-- Kontak List -->
                <div id="contacts-list" class="flex-1 overflow-y-auto custom-scrollbar divide-y divide-slate-100">
                    <div class="p-4 text-center text-xs text-slate-400">Memuat daftar pengguna...</div>
                </div>
            </aside>

            <!-- Chat Area Kanan -->
            <section class="flex-1 flex flex-col bg-[#efeae2] relative">
                
                <!-- State Kosong: Belum Pilih Chat -->
                <div id="chat-empty-state" class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-700">WhatsApp Arsitektur Terdistribusi</h3>
                    <p class="text-xs text-slate-500 max-w-sm mt-1">Pilih pengguna dari daftar kontak di sebelah kiri untuk memulai pertukaran pesan secara terdistribusi via Cloud Database.</p>
                </div>

                <!-- State Aktif: Ruang Percakapan -->
                <div id="chat-active-state" class="flex-1 flex flex-col h-full hidden">
                    
                    <!-- Chat Header -->
                    <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between shadow-sm z-10">
                        <div class="flex items-center space-x-3">
                            <div id="target-avatar" class="w-9 h-9 rounded-full bg-slate-400 text-white font-bold flex items-center justify-center text-sm">T</div>
                            <div>
                                <h3 id="target-name" class="font-semibold text-sm text-slate-800">Nama Penerima</h3>
                                <p id="target-user" class="text-xs text-slate-500">@penerima</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Privasi Terisolasi
                            </span>
                        </div>
                    </div>

                    <!-- Message Feed -->
                    <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-2.5 custom-scrollbar">
                        <!-- Pesan akan dimuat di sini -->
                    </div>

                    <!-- Input Bar -->
                    <div class="p-3 bg-slate-100 border-t border-slate-200">
                        <form onsubmit="sendMessage(event)" class="flex items-center space-x-2">
                            <input type="text" id="message-input" required autocomplete="off" placeholder="Ketik pesan..." class="flex-1 px-4 py-2 bg-white rounded-lg border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <button type="submit" id="send-btn" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition flex items-center gap-1.5">
                                <span>Kirim</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </form>
                    </div>

                </div>

            </section>
        </section>

    </main>

    <!-- Modal Info Sinkronisasi Waktu Server Cloud -->
    <div id="time-modal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 text-slate-800">
            <div class="flex items-center justify-between pb-3 border-b border-slate-200 mb-4">
                <h3 class="font-bold text-base flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Sinkronisasi Waktu Cloud Database
                </h3>
                <button onclick="closeTimeSyncModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
            </div>
            
            <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                Sesuai ketentuan tugas, seluruh timestamp pesan mengacu pada waktu **Cloud Database (Server Utama)** menggunakan fungsi <code class="bg-slate-100 text-emerald-700 px-1 py-0.5 rounded font-mono">NOW()</code>, bukan waktu jam lokal pada laptop pengguna.
            </p>

            <div class="space-y-2.5 text-xs bg-slate-50 p-3.5 rounded-lg border border-slate-200 font-mono">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Cloud DB Server Time:</span>
                    <span id="modal-cloud-now" class="font-bold text-emerald-700">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Cloud DB UTC:</span>
                    <span id="modal-cloud-utc">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Local App Server (PHP):</span>
                    <span id="modal-php-now">-</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">DB Version:</span>
                    <span id="modal-db-version">-</span>
                </div>
            </div>

            <div class="mt-5 flex justify-end">
                <button onclick="closeTimeSyncModal()" class="bg-slate-800 text-white text-xs px-4 py-2 rounded hover:bg-slate-700">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Script Logika Frontend -->
    <script>
        let currentUser = null;
        let activeTargetId = null;
        let lastSeenMessageId = 0;
        let pollingInterval = null;
        let contactsCache = [];

        // Inisialisasi awal
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('node-host').textContent = window.location.host;
            checkTimeSync();
            setInterval(checkTimeSync, 5000);
            checkSession();
        });

        async function checkTimeSync() {
            try {
                const res = await fetch('api/time.php');
                const data = await res.json();
                if (data.status === 'connected') {
                    document.getElementById('db-indicator').className = 'w-2 h-2 rounded-full bg-emerald-400';
                    document.getElementById('db-status-text').textContent = 'Online (' + data.cloud_database.version.split('-')[0] + ')';
                    document.getElementById('header-cloud-time').textContent = data.cloud_database.server_time_now.split(' ')[1] || '--:--:--';
                    
                    document.getElementById('modal-cloud-now').textContent = data.cloud_database.server_time_now;
                    document.getElementById('modal-cloud-utc').textContent = data.cloud_database.utc_timestamp;
                    document.getElementById('modal-php-now').textContent = data.local_app_server.php_time_now;
                    document.getElementById('modal-db-version').textContent = data.cloud_database.version;
                } else {
                    throw new Error(data.error);
                }
            } catch (err) {
                document.getElementById('db-indicator').className = 'w-2 h-2 rounded-full bg-red-500';
                document.getElementById('db-status-text').textContent = 'Offline';
            }
        }

        async function checkSession() {
            try {
                const res = await fetch('api/auth.php?action=me');
                if (res.ok) {
                    const data = await res.json();
                    onLoginSuccess(data.user);
                } else {
                    showAuthView();
                }
            } catch (err) {
                showAuthView();
            }
        }

        function showAuthView() {
            document.getElementById('auth-view').classList.remove('hidden');
            document.getElementById('auth-actions').classList.add('hidden');
            if (pollingInterval) clearInterval(pollingInterval);
        }

        function onLoginSuccess(user) {
            currentUser = user;
            document.getElementById('auth-view').classList.add('hidden');
            document.getElementById('auth-actions').classList.remove('hidden');
            
            document.getElementById('my-name').textContent = user.full_name;
            document.getElementById('my-user').textContent = '@' + user.username;
            document.getElementById('my-avatar').textContent = user.full_name.charAt(0).toUpperCase();

            loadContacts();
            startPolling();
        }

        function switchAuthTab(tab) {
            const isLogin = tab === 'login';
            document.getElementById('login-form').classList.toggle('hidden', !isLogin);
            document.getElementById('register-form').classList.toggle('hidden', isLogin);
            
            document.getElementById('tab-login-btn').className = isLogin 
                ? 'flex-1 pb-2 font-semibold text-emerald-600 border-b-2 border-emerald-600 text-sm'
                : 'flex-1 pb-2 font-medium text-slate-500 text-sm';
            document.getElementById('tab-register-btn').className = !isLogin 
                ? 'flex-1 pb-2 font-semibold text-emerald-600 border-b-2 border-emerald-600 text-sm'
                : 'flex-1 pb-2 font-medium text-slate-500 text-sm';
            document.getElementById('auth-error').classList.add('hidden');
        }

        async function quickLogin(username) {
            document.getElementById('login-ident').value = username;
            document.getElementById('login-pass').value = 'password123';
            document.getElementById('login-form').dispatchEvent(new Event('submit'));
        }

        async function submitLogin(e) {
            e.preventDefault();
            const ident = document.getElementById('login-ident').value;
            const pass = document.getElementById('login-pass').value;
            showError('');

            try {
                const res = await fetch('api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username_or_email: ident, password: pass })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Login gagal');
                onLoginSuccess(data.user);
            } catch (err) {
                showError(err.message);
            }
        }

        async function submitRegister(e) {
            e.preventDefault();
            const fullName = document.getElementById('reg-name').value;
            const username = document.getElementById('reg-user').value;
            const email = document.getElementById('reg-email').value;
            const password = document.getElementById('reg-pass').value;
            showError('');

            try {
                const res = await fetch('api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ full_name: fullName, username, email, password })
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.error || 'Registrasi gagal');
                onLoginSuccess(data.user);
            } catch (err) {
                showError(err.message);
            }
        }

        function showError(msg) {
            const el = document.getElementById('auth-error');
            if (msg) {
                el.textContent = msg;
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        }

        async function handleLogout() {
            await fetch('api/auth.php?action=logout');
            currentUser = null;
            activeTargetId = null;
            showAuthView();
        }

        async function loadContacts() {
            try {
                const res = await fetch('api/users.php');
                const data = await res.json();
                if (!res.ok) throw new Error(data.error);
                contactsCache = data.users || [];
                renderContacts(contactsCache);
            } catch (err) {
                console.error(err);
            }
        }

        function renderContacts(users) {
            const listEl = document.getElementById('contacts-list');
            if (!users.length) {
                listEl.innerHTML = '<div class="p-4 text-center text-xs text-slate-400">Belum ada user lain terdaftar di Cloud DB.</div>';
                return;
            }

            listEl.innerHTML = users.map(u => {
                const isActive = activeTargetId === u.id;
                const initial = u.full_name ? u.full_name.charAt(0).toUpperCase() : '?';
                const timeSnippet = u.last_message_time ? u.last_message_time.split(' ')[1].slice(0, 5) : '';
                const msgSnippet = u.last_message ? escapeHtml(u.last_message) : '<em class="text-slate-400">Belum ada pesan</em>';
                
                return `
                    <div onclick="selectContact(${u.id}, '${escapeHtml(u.full_name)}', '${escapeHtml(u.username)}')" 
                         class="p-3 flex items-center space-x-3 cursor-pointer transition ${isActive ? 'bg-slate-100' : 'hover:bg-slate-50'}">
                        <div class="w-10 h-10 rounded-full bg-slate-300 text-slate-700 font-bold flex items-center justify-center text-sm shrink-0">
                            ${initial}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="text-sm font-semibold text-slate-800 truncate">${escapeHtml(u.full_name)}</h4>
                                <span class="text-[10px] text-slate-400 shrink-0">${timeSnippet}</span>
                            </div>
                            <p class="text-xs text-slate-500 truncate">${msgSnippet}</p>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterContacts(query) {
            const q = query.toLowerCase();
            const filtered = contactsCache.filter(u => 
                u.full_name.toLowerCase().includes(q) || u.username.toLowerCase().includes(q)
            );
            renderContacts(filtered);
        }

        function selectContact(id, fullName, username) {
            activeTargetId = id;
            lastSeenMessageId = 0;
            
            document.getElementById('chat-empty-state').classList.add('hidden');
            document.getElementById('chat-active-state').classList.remove('hidden');

            document.getElementById('target-name').textContent = fullName;
            document.getElementById('target-user').textContent = '@' + username;
            document.getElementById('target-avatar').textContent = fullName.charAt(0).toUpperCase();

            document.getElementById('messages-container').innerHTML = '<div class="text-center text-xs text-slate-400 py-4">Mengambil pesan dari Cloud DB...</div>';
            
            loadContacts();
            fetchMessages();
        }

        async function fetchMessages() {
            if (!activeTargetId) return;
            try {
                const res = await fetch(`api/messages.php?target_user_id=${activeTargetId}&since_id=${lastSeenMessageId}`);
                if (!res.ok) return;
                const data = await res.json();
                
                if (data.messages && data.messages.length > 0) {
                    if (lastSeenMessageId === 0) {
                        document.getElementById('messages-container').innerHTML = '';
                    }
                    data.messages.forEach(msg => {
                        appendMessageBubble(msg);
                        if (msg.id > lastSeenMessageId) {
                            lastSeenMessageId = msg.id;
                        }
                    });
                    scrollToBottom();
                } else if (lastSeenMessageId === 0) {
                    document.getElementById('messages-container').innerHTML = '<div class="text-center text-xs text-slate-400 py-8">Mulai obrolan pribadi dengan pengguna ini.</div>';
                }
            } catch (err) {
                console.error(err);
            }
        }

        function appendMessageBubble(msg) {
            const isMe = msg.sender_id === currentUser.id;
            const container = document.getElementById('messages-container');
            const timeFormatted = msg.created_at ? msg.created_at.split(' ')[1].slice(0, 5) : '';

            const bubble = document.createElement('div');
            bubble.className = `flex ${isMe ? 'justify-end' : 'justify-start'}`;
            bubble.innerHTML = `
                <div class="max-w-[75%] rounded-lg px-3 py-1.5 shadow-sm text-sm ${isMe ? 'bg-[#d9fdd3] text-slate-800 rounded-tr-none' : 'bg-white text-slate-800 rounded-tl-none'}">
                    <p class="break-words leading-relaxed">${escapeHtml(msg.content)}</p>
                    <div class="text-[10px] text-slate-400 text-right mt-0.5 flex items-center justify-end gap-1">
                        <span>${timeFormatted}</span>
                        ${isMe ? '<svg class="w-3.5 h-3.5 text-blue-500 inline" viewBox="0 0 16 15" fill="none"><path d="M15.01 3.316l-7.98 7.98a.749.749 0 0 1-1.06 0l-4.24-4.24a.75.75 0 1 1 1.06-1.06l3.71 3.71 7.45-7.45a.75.75 0 1 1 1.06 1.06z" fill="currentColor"/><path d="M12.01 3.316l-7.98 7.98a.749.749 0 0 1-1.06 0l-.71-.71a.75.75 0 1 1 1.06-1.06l.18.18 7.45-7.45a.75.75 0 1 1 1.06 1.06z" fill="currentColor"/></svg>' : ''}
                    </div>
                </div>
            `;
            container.appendChild(bubble);
        }

        async function sendMessage(e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const content = input.value.trim();
            if (!content || !activeTargetId) return;

            input.value = '';
            input.focus();

            try {
                const res = await fetch('api/messages.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        receiver_id: activeTargetId,
                        content: content
                    })
                });
                const data = await res.json();
                if (res.ok && data.data) {
                    appendMessageBubble(data.data);
                    if (data.data.id > lastSeenMessageId) {
                        lastSeenMessageId = data.data.id;
                    }
                    scrollToBottom();
                    loadContacts();
                }
            } catch (err) {
                console.error(err);
            }
        }

        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(() => {
                if (activeTargetId) {
                    fetchMessages();
                }
                loadContacts();
            }, 1500);
        }

        function scrollToBottom() {
            const el = document.getElementById('messages-container');
            el.scrollTop = el.scrollHeight;
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function openTimeSyncModal() {
            document.getElementById('time-modal').classList.remove('hidden');
        }
        function closeTimeSyncModal() {
            document.getElementById('time-modal').classList.add('hidden');
        }
    </script>
</body>
</html>
