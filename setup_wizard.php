<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>B2B System - Configuration Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .status {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .status.error {
            background: #ffebee;
            border-left-color: #e74c3c;
            color: #c0392b;
        }
        .status.success {
            background: #e8f5e9;
            border-left-color: #27ae60;
            color: #27ae60;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            font-weight: 600;
        }
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .info-box {
            background: #f9f9f9;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
            line-height: 1.6;
        }
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }
        .tab-btn {
            flex: 1;
            padding: 10px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            color: #999;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Database Configuration</h1>
            <p>Connect your B2B System to the database</p>
        </div>

        <div id="status" class="status" style="display: none;"></div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('hostinger')">🌐 Hostinger</button>
            <button class="tab-btn" onclick="switchTab('xampp')">💻 XAMPP</button>
            <button class="tab-btn" onclick="switchTab('custom')">⚙️ Custom</button>
        </div>

        <div id="hostinger-tab" class="tab-content active">
            <div class="info-box">
                <strong>Hostinger Configuration</strong><br>
                Database credentials are automatically detected from Hostinger defaults.
                <br><br>
                <strong>If you changed your password:</strong><br>
                1. Log in to Hostinger cPanel<br>
                2. Go to Databases section<br>
                3. Find your password<br>
                4. Enter it below
            </div>
        </div>

        <div id="xampp-tab" class="tab-content">
            <div class="info-box">
                <strong>XAMPP Configuration</strong><br>
                These are standard XAMPP defaults. 
                Change them if you configured XAMPP differently.
            </div>
        </div>

        <div id="custom-tab" class="tab-content">
            <div class="info-box">
                <strong>Custom Configuration</strong><br>
                Enter your database credentials manually.
                All fields must be filled correctly for connection to work.
            </div>
        </div>

        <form id="setupForm">
            <div class="form-group">
                <label for="host">Database Host</label>
                <input type="text" id="host" name="host" value="localhost" required>
                <div class="hint">Usually 'localhost' for most hosting</div>
            </div>

            <div class="form-group">
                <label for="user">Database User</label>
                <input type="text" id="user" name="user" value="u110596290_b2bsystem" required>
                <div class="hint" id="user-hint">Hostinger default: u110596290_b2bsystem</div>
            </div>

            <div class="form-group">
                <label for="pass">Database Password</label>
                <input type="password" id="pass" name="pass" value="" placeholder="Enter your database password">
                <div class="hint">Check your hosting control panel for this</div>
            </div>

            <div class="form-group">
                <label for="dbname">Database Name</label>
                <input type="text" id="dbname" name="dbname" value="u110596290_b2bsystem" required>
                <div class="hint" id="dbname-hint">Hostinger default: u110596290_b2bsystem</div>
            </div>

            <div class="button-group">
                <button type="button" class="btn-secondary" onclick="testConnection()">
                    Test Connection
                </button>
                <button type="submit" class="btn-primary">
                    Save & Continue
                </button>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <span>Testing connection...</span>
            </div>
        </form>
    </div>

    <script>
        function switchTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');
            event.target.classList.add('active');

            // Update form hints
            if (tab === 'hostinger') {
                document.getElementById('user').value = 'u110596290_b22bsystem';
                document.getElementById('dbname').value = 'u110596290_b2bsystem';
                document.getElementById('user-hint').textContent = 'Hostinger default: u110596290_b2bsystem';
                document.getElementById('dbname-hint').textContent = 'Hostinger default: u110596290_b2bsystem';
            } else if (tab === 'xampp') {
                document.getElementById('user').value = 'root';
                document.getElementById('dbname').value = 'b2b_billing_system';
                document.getElementById('user-hint').textContent = 'XAMPP default: root';
                document.getElementById('dbname-hint').textContent = 'XAMPP default: b2b_billing_system';
            }
        }

        function showStatus(message, isError = false) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status ' + (isError ? 'error' : 'success');
            status.style.display = 'block';
        }

        function testConnection() {
            const loading = document.getElementById('loading');
            loading.style.display = 'block';

            const formData = new FormData();
            formData.append('action', 'test');
            formData.append('host', document.getElementById('host').value);
            formData.append('user', document.getElementById('user').value);
            formData.append('pass', document.getElementById('pass').value);
            formData.append('dbname', document.getElementById('dbname').value);

            fetch('config_api.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    showStatus('✅ Connection successful!', false);
                } else {
                    showStatus('❌ Connection failed: ' + data.error, true);
                }
            })
            .catch(e => {
                loading.style.display = 'none';
                showStatus('❌ Error: ' + e.message, true);
            });
        }

        document.getElementById('setupForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const loading = document.getElementById('loading');
            loading.style.display = 'block';

            const formData = new FormData(this);
            formData.append('action', 'save');

            fetch('config_api.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                if (data.success) {
                    showStatus('✅ Configuration saved! Redirecting...', false);
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    showStatus('❌ Error: ' + data.error, true);
                }
            })
            .catch(e => {
                loading.style.display = 'none';
                showStatus('❌ Error: ' + e.message, true);
            });
        });
    </script>
</body>
</html>
