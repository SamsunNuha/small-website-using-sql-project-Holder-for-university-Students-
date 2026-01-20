document.addEventListener('DOMContentLoaded', () => {
    // Check if running on file protocol
    if (window.location.protocol === 'file:') {
        alert("CRITICAL ERROR: You are running this file directly completely bypassing the server.\n\nPHP features (Login/Register/Database) WILL NOT WORK.\n\nClick OK to be automatically redirected to the working server.");
        window.location.href = 'http://localhost:8000';
    }

    // Elements
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const loginCard = document.getElementById('loginCard');
    const registerCard = document.getElementById('registerCard');
    const showRegisterBtn = document.getElementById('showRegister');
    const showLoginBtn = document.getElementById('showLogin');

    // Toggle Forms
    if (showRegisterBtn && showLoginBtn) {
        showRegisterBtn.addEventListener('click', (e) => {
            e.preventDefault();
            loginCard.style.display = 'none';
            registerCard.style.display = 'block';
        });

        showLoginBtn.addEventListener('click', (e) => {
            e.preventDefault();
            registerCard.style.display = 'none';
            loginCard.style.display = 'block';
        });
    }

    // Password Visibility Toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const eyeIcon = toggle.querySelector('.eye-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                `;
            }
        });
    });

    // Handle Login
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = loginForm.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Signing In...';
            btn.disabled = true;

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });
                const data = await response.json();

                if (data.success) {
                    // Save username and role for dashboard display
                    localStorage.setItem('fas_username', data.username);
                    localStorage.setItem('fas_role', data.role);

                    // Always redirect to student dashboard
                    window.location.href = 'dashboard.html';
                } else {
                    alert('Login Failed: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.location.protocol === 'file:') {
                    alert('ERROR: You are opening the file directly which breaks the app.\nRedirecting you to the working server: http://localhost:8000');
                    window.location.href = 'http://localhost:8000';
                } else {
                    alert('Login Error: ' + error.message);
                }
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    // Handle Registration
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = registerForm.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Registering...';
            btn.disabled = true;

            const username = document.getElementById('regUsername').value;
            const email = document.getElementById('regEmail').value;
            const password = document.getElementById('regPassword').value;

            try {
                const response = await fetch('register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, email, password })
                });

                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Text first to avoid JSON parse error hiding the real content
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Server returned invalid JSON:', text);
                    throw new Error('Server returned invalid response: ' + text.substring(0, 50) + '...');
                }

                if (data.success) {
                    alert('Registration successful! Please sign in.');
                    // Switch to login view
                    registerCard.style.display = 'none';
                    loginCard.style.display = 'block';
                    registerForm.reset();
                } else {
                    alert('Registration Failed: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                if (window.location.protocol === 'file:') {
                    alert('ERROR: You are opening the file directly which breaks the app.\nRedirecting you to the working server: http://localhost:8000');
                    window.location.href = 'http://localhost:8000';
                } else {
                    alert('Registration Error (' + window.location.protocol + '): ' + error.message + '\n\nPlease ensure the PHP server is running.');
                }
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }

    // Session Check: Redirect to login if not authenticated (except on login page)
    const currentPage = window.location.pathname.split('/').pop();
    const isLoginPage = currentPage === 'index.html' || currentPage === '';
    const storedUsername = localStorage.getItem('fas_username');

    if (!isLoginPage && !storedUsername) {
        window.location.href = 'index.html';
    }

    // Dashboard Greeting Logic
    const welcomeName = document.getElementById('welcomeName');
    const globalRecordsBody = document.getElementById('globalRecordsBody');

    if (welcomeName && storedUsername) {
        welcomeName.innerText = storedUsername;
    }

    if (globalRecordsBody) {
        loadGlobalRecords();
    }

    async function loadGlobalRecords() {
        try {
            const response = await fetch('get_all_projects.php');
            const data = await response.json();

            if (data.success) {
                renderGlobalRecords(data.records);
            } else {
                globalRecordsBody.innerHTML = `<tr><td colspan="2" style="text-align:center; color: red;">Error: ${data.message}</td></tr>`;
            }
        } catch (error) {
            console.error('Error loading global records:', error);
            globalRecordsBody.innerHTML = `<tr><td colspan="2" style="text-align:center; color: red;">Failed to connect to server.</td></tr>`;
        }
    }

    function renderGlobalRecords(records) {
        if (!records || records.length === 0) {
            globalRecordsBody.innerHTML = `<tr><td colspan="2" style="text-align:center; padding: 2rem; color: #999;">No project records found.</td></tr>`;
            return;
        }

        globalRecordsBody.innerHTML = records.map(record => `
            <tr>
                <td style="font-weight: 500;">${record.username}</td>
                <td>${record.title}</td>
            </tr>
        `).join('');
    }


    // Handle Project Submission from New Page
    const submissionPageForm = document.getElementById('submissionPageForm');
    if (submissionPageForm) {
        submissionPageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = submissionPageForm.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Submitting...';
            btn.disabled = true;

            const formData = new FormData(submissionPageForm);

            try {
                const response = await fetch('submit_project.php', {
                    method: 'POST',
                    body: formData
                });

                // Get response as text first for debugging
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (jsonErr) {
                    console.error('Invalid JSON response:', text);
                    throw new Error('Server error: ' + text.substring(0, 100));
                }

                if (data.success) {
                    alert('Project Submitted Successfully!');
                    window.location.href = 'dashboard.html';
                } else {
                    alert('Submission Failed: ' + data.message);
                }
            } catch (error) {
                console.error('Submission Error:', error);
                alert('Submission Error: ' + error.message);
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });
    }
});


