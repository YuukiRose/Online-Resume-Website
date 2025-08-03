<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug & Configuration Dashboard - Luthor Admin</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #2C3E50 0%, #3498DB 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        
        .nav-tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .nav-tab {
            flex: 1;
            padding: 15px 20px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .nav-tab.active {
            background: white;
            color: #2C3E50;
            border-bottom: 3px solid #3498DB;
        }
        
        .nav-tab:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .section {
            padding: 30px;
            display: none;
        }
        
        .section.active {
            display: block;
        }
        
        .connection-test {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #3498DB;
        }
        
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
            margin-left: 10px;
        }
        
        .status.connected {
            background: #d4edda;
            color: #155724;
        }
        
        .status.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status.testing {
            background: #fff3cd;
            color: #856404;
        }
        
        .test-button {
            background: #3498DB;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 10px;
            transition: background 0.3s ease;
        }
        
        .test-button:hover {
            background: #2980B9;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .tool-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .tool-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .tool-category {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .tool-category.database {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .tool-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #2C3E50;
            margin-bottom: 10px;
        }
        
        .tool-description {
            color: #6c757d;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .tool-link {
            display: inline-block;
            background: #3498DB;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .tool-link:hover {
            background: #2980B9;
        }
        
        .console-output {
            background: #1e1e1e;
            color: #00ff00;
            padding: 20px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            height: 300px;
            overflow-y: auto;
            margin-bottom: 15px;
        }
        
        .console-entry {
            margin-bottom: 5px;
            padding: 2px 0;
        }
        
        .console-entry.user {
            color: #00bfff;
        }
        
        .console-entry.error {
            color: #ff6b6b;
        }
        
        .console-input {
            display: flex;
            gap: 10px;
        }
        
        .console-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
        }
        
        .console-input button {
            background: #3498DB;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .console-input button:hover {
            background: #2980B9;
        }
        
        .back-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .back-link a {
            color: #3498DB;
            text-decoration: none;
            font-weight: 500;
            margin: 0 10px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 Debug & Configuration Dashboard</h1>
            <p>Advanced database diagnostics and system management tools</p>
        </div>
        
        <div class="nav-tabs">
            <button class="nav-tab active" onclick="showSection('database')">Database Tools</button>
            <button class="nav-tab" onclick="showSection('console')">Database Console</button>
        </div>
        
        <div class="section active" id="database">
            <h2>Database Management</h2>
            
            <!-- Connection Test -->
            <div class="connection-test" id="connection-test">
                <h3>Database Connection Status</h3>
                <p>Current Status: <span class="status testing" id="status">Testing...</span></p>
                <p id="details">Checking database connectivity...</p>
                <button class="test-button">Test Connection</button>
            </div>
            
            <!-- Database Tools Grid - Streamlined to 2 Tools Only -->
            <div class="tools-grid">
                <div class="tool-card">
                    <div class="tool-category database">Database</div>
                    <div class="tool-title">🔍 Comprehensive DB Test</div>
                    <div class="tool-description">Complete database diagnostic with automated repair capabilities. Checks tables, columns, indexes, and portfolio structure. Can fix missing components automatically.</div>
                    <a href="Debug/comprehensive_db_test.php" class="tool-link">Run Full Diagnostic & Repair</a>
                </div>
                
                <div class="tool-card">
                    <div class="tool-category database">Database</div>
                    <div class="tool-title">🏗️ Database Setup</div>
                    <div class="tool-description">Initial database setup with table creation, portfolio structure, and test data insertion for new installations or complete rebuilds.</div>
                    <a href="../config/setup_database.php" class="tool-link">Setup Database</a>
                </div>
            </div>
            
            <div class="back-link">
                <a href="dashboard.php">← Back to Admin Dashboard</a>
                <a href="../index.php">← Back to Main Site</a>
            </div>
        </div>
        
        <!-- Console Output -->
        <div class="section" id="console">
            <h2>Database Console</h2>
            <div class="console-output" id="consoleOutput">
                <div class="console-entry">System Ready</div>
            </div>
            <div class="console-input">
                <input type="text" id="queryInput" placeholder="Enter SQL query or command..." />
                <button onclick="executeQuery()">Execute</button>
            </div>
            
            <div class="back-link">
                <a href="dashboard.php">← Back to Admin Dashboard</a>
                <a href="../index.php">← Back to Main Site</a>
            </div>
        </div>
    </div>
    
    <script>
    // Show specific section
    function showSection(sectionId) {
        // Hide all sections
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => section.classList.remove('active'));
        
        // Hide all nav tabs
        const tabs = document.querySelectorAll('.nav-tab');
        tabs.forEach(tab => tab.classList.remove('active'));
        
        // Show selected section
        document.getElementById(sectionId).classList.add('active');
        
        // Activate corresponding tab
        event.target.classList.add('active');
    }
    
    // Initialize dashboard
    function initDashboard() {
        const connectionTest = document.getElementById('connection-test');
        const testButton = document.querySelector('.test-button');
        
        // Check connection status
        if (testButton) {
            testButton.addEventListener('click', function(e) {
                e.preventDefault();
                checkDatabaseConnection();
            });
        }
        
        // Auto-test connection on load
        setTimeout(checkDatabaseConnection, 1000);
    }
    
    function checkDatabaseConnection() {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML += '<div class="console-entry">Testing database connection...</div>';
            consoleOutput.scrollTop = consoleOutput.scrollHeight;
        }
        
        fetch('Debug/test_db_connection.php')
            .then(response => response.json())
            .then(data => {
                const status = document.getElementById('status');
                const details = document.getElementById('details');
                
                if (data.success) {
                    status.textContent = 'Connected';
                    status.className = 'status connected';
                    details.textContent = data.message;
                } else {
                    status.textContent = 'Failed';
                    status.className = 'status failed';
                    details.textContent = data.error;
                }
                
                if (consoleOutput) {
                    consoleOutput.innerHTML += `<div class="console-entry">${data.message || data.error}</div>`;
                    consoleOutput.scrollTop = consoleOutput.scrollHeight;
                }
            })
            .catch(error => {
                const status = document.getElementById('status');
                const details = document.getElementById('details');
                status.textContent = 'Failed';
                status.className = 'status failed';
                details.textContent = 'Connection test failed';
                
                if (consoleOutput) {
                    consoleOutput.innerHTML += `<div class="console-entry error">Connection test failed: ${error}</div>`;
                    consoleOutput.scrollTop = consoleOutput.scrollHeight;
                }
            });
    }
    
    function executeQuery() {
        const input = document.getElementById('queryInput');
        const consoleOutput = document.getElementById('consoleOutput');
        const query = input.value.trim();
        
        if (!query) return;
        
        consoleOutput.innerHTML += `<div class="console-entry user">$ ${query}</div>`;
        input.value = '';
        
        // For demonstration purposes
        if (query.toLowerCase().includes('show tables')) {
            consoleOutput.innerHTML += '<div class="console-entry">portfolio_works, portfolio_experience, portfolio_education, portfolio_skills, portfolio_content</div>';
        } else {
            consoleOutput.innerHTML += '<div class="console-entry">Query executed successfully</div>';
        }
        
        consoleOutput.scrollTop = consoleOutput.scrollHeight;
    }
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', initDashboard);
    </script>
</body>
</html>
