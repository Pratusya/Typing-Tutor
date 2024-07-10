<?php
session_start();
if (!isset($_SESSION['username'])) {
    // User is not logged in
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Speed Typing Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Source+Code+Pro&display=swap');

:root {
    --primary-color: #3498db;
    --secondary-color: #2ecc71;
    --background-color: #f0f3f5;
    --text-color: #34495e;
    --error-color: #e74c3c;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.6;
    height: 100vh;
    overflow: hidden;
}

.main-header {
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 1rem 2rem;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

    .logo {
        display: flex;
        align-items: center;
    }

    .logo img {
        height: 40px;
        margin-right: 10px;
    }

    .logo h1 {
        font-size: 1.5rem;
        margin: 0;
    }

.main-header nav {
        display: flex;
        align-items: center;
    }

    .main-header nav ul {
        list-style-type: none;
        display: flex;
        gap: 1.5rem;
        margin: 0;
        padding: 0;
    }

    .main-header nav a {
        text-decoration: none;
        color: var(--text-color);
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .main-header nav a:hover {
        color: var(--primary-color);
    }

.login-btn {
    display: inline-block;
    text-decoration: none;
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.login-btn:hover {
    background-color: #2980b9;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-info span {
    font-weight: 500;
}

.container {
    display: flex;
    height: calc(100vh - 70px);
    overflow-y: auto;
}

.typing-area {
    flex: 1;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    overflow-y: auto;
}

#time-input {
    width: 70px;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

#time-unit {
    margin-left: 0.5rem;
}

header {
    margin-bottom: 2rem;
}

.test-config {
    display: flex;
    gap: 1rem;
}

.config-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

select, input[type="checkbox"] {
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
}

.text-display {
    text-align: left;
    font-family: 'Source Code Pro', monospace;
    font-size: 1.2rem;
    background-color: #fff;
    padding: 1.5rem;
    border-radius: 5px;
    margin-bottom: 1rem;
    flex-grow: 1;
    overflow-y: scroll;
    outline: none;
    position: relative;
    line-height: 1.8;
    max-height: 60vh;
}

.text-content {
    white-space: pre-wrap;
    word-break: break-word;
}

.text-content p {
    margin-bottom: 1.5em;
    text-indent: 0;
}

.cursor {
    position: absolute;
    width: 2px;
    height: 1.5em;
    background-color: var(--primary-color);
    animation: blink 0.7s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 0; }
    50% { opacity: 1; }
}

.text-content span {
    position: relative;
}

.text-content span.active {
    background-color: rgba(52, 152, 219, 0.2);
}

.text-content span.correct {
    color: green;
}

.text-content span.incorrect {
    color: var(--error-color);
    text-decoration: underline;
}

.reset-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15%;
    padding: 0.8rem 1.5rem;
    background-color: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.reset-btn:hover {
    background-color: #29b93ce5;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.reset-btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.reset-btn i {
    margin-right: 8px;
    font-size: 1.1rem;
}

.sidebar {
    width: 400px;
    background-color: #fff;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
    overflow-y: auto;
}

.stats-panel {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
    font-size: 1.2rem;
    padding: 1rem;
    background-color: var(--background-color);
    border-radius: 5px;
}

.stat-item i {
    color: var(--primary-color);
    margin-right: 0.5rem;
}

.history-panel {
    flex-grow: 1;
    overflow-y: auto;
}

.history-panel h2 {
    font-size: 1.3rem;
    margin-bottom: 0.8rem;
}

#history-list {
    list-style-type: none;
}

#history-list li {
    background-color: var(--background-color);
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 5px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fefefe;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    max-width: 80%;
    max-height: 80%;
    overflow-y: auto;
}

.modal-content h2 {
    margin-top: 0;
    color: var(--primary-color);
}

.modal-content p {
    margin: 10px 0;
}

#close-modal {
    margin-top: 15px;
    padding: 10px 20px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

#close-modal:hover {
    background-color: #2980b9;
}

#statsChart {
    margin-top: 20px;
    max-width: 100%;
    height: auto;
}

/* Slider Toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
}

input:checked + .slider {
    background-color: var(--primary-color);
}

input:focus + .slider {
    box-shadow: 0 0 1px var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

.slider.round {
    border-radius: 34px;
}

.slider.round:before {
    border-radius: 50%;
}

.hamburger-menu {
    display: none;
    flex-direction: column;
    justify-content: space-around;
    width: 2rem;
    height: 2rem;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    z-index: 10;
}

.hamburger-menu span {
    width: 2rem;
    height: 0.25rem;
    background: var(--text-color);
    border-radius: 10px;
    transition: all 0.3s linear;
    position: relative;
    transform-origin: 1px;
}

.hamburger-menu.open span:first-child {
    transform: rotate(45deg);
}

.hamburger-menu.open span:nth-child(2) {
    opacity: 0;
}

.hamburger-menu.open span:nth-child(3) {
    transform: rotate(-45deg);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.responsive-user-info {
    display: none;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    border-top: 1px solid #e0e0e0;
}

.responsive-user-info span {
    margin-bottom: 0.5rem;
}

@media (max-width: 1200px) {
    .container {
        flex-direction: column;
        height: auto;
        overflow-y: visible;
    }

    .sidebar {
        width: 100%;
        order: -1;
        height: auto;
    }

    .typing-area {
        height: auto;
        min-height: 60vh;
    }

    body {
        overflow-y: auto;
    }
}

@media (max-width: 768px) {
    body {
        font-size: 14px;
    }

    .header-content {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .logo {
        flex: 1;
    }

    .main-header nav {
        margin: 1rem 0;
    }

    .login-btn {
        margin-top: 1rem;
    }

    .typing-area {
        padding: 1rem;
    }

    .text-display {
        font-size: 1rem;
        padding: 1rem;
    }

    .test-config {
        flex-direction: column;
        gap: 0.5rem;
    }

    .config-item {
        width: 100%;
        justify-content: space-between;
    }

    .stats-panel {
        flex-direction: row;
        grid-template-rows: 2fr;
    }
    .stat-item {
        font-size: 1rem;
        padding: 0.5rem;
    }

    .hamburger-menu {
        display: flex;
    }

    .main-header nav {
        position: fixed;
        top: 0;
        right: -300px;
        height: 100vh;
        width: 300px;
        background-color: #fff;
        box-shadow: -2px 0 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease-in-out;
        flex-direction: column;
        justify-content: flex-start;
        padding-top: 60px;
    }

    .main-header nav.open {
        transform: translateX(-300px);
    }

    .main-header nav ul {
        flex-direction: column;
        align-items: center;
    }

    .main-header nav ul li {
        margin: 1rem 0;
    }

    .user-info {
        display: none;
    }

    .responsive-user-info {
        display: flex;
    }
}

@media (max-width: 480px) {
    .container {
        height: auto;
    }

    .typing-area, .sidebar {
        max-height: none;
    }

    .text-display {
        max-height: 40vh;
    }

    .typing-area {
        padding: 0.5rem;
    }

    .text-display {
        font-size: 0.9rem;
        padding: 0.5rem;
    }

    .reset-btn {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }

    .reset-btn i {
        font-size: 1rem;
    }

    .sidebar {
        padding: 1rem;
    }

    .main-header {
        padding: 1rem;
    }

    .main-header nav ul {
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
}

@media (max-width: 360px) {
    .main-header {
        padding: 0.5rem;
    }

    .logo img {
        height: 30px;
    }

    .logo h1 {
        font-size: 1.2rem;
    }

    .main-header nav ul {
        flex-wrap: wrap;
        justify-content: center;
    }

    .login-btn {
        width: 100%;
        margin-top: 0.5rem;
    }

    .typing-area {
        padding: 0.5rem;
    }

    .text-display {
        font-size: 0.8rem;
        padding: 0.5rem;
    }

    .reset-btn {
        padding: 0.5rem 0.8rem;
        font-size: 0.8rem;
    }

    .reset-btn i {
        font-size: 0.9rem;
    }

    .stat-item {
        font-size: 0.9rem;
        padding: 0.4rem;
    }

    .history-panel h2 {
        font-size: 1.1rem;
    }

    #history-list li {
        font-size: 0.8rem;
        padding: 0.8rem;
    }
}
    </style>
</head>
<body>
<header class="main-header">
        <div class="header-content">
            <div class="logo">
                <img src="logo.png" alt="">
                <h1>TypeMaster</h1>
            </div>
            <div class="hamburger">
                <button class="hamburger-menu" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
            <nav>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Lessons</a></li>
                    <li><a href="#">Statistics</a></li>
                    <li><a href="#">About</a></li>
                </ul>
                <div class="responsive-user-info">
                    <?php if (isset($_SESSION['username'])): ?>
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="logout.php" class="login-btn">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="login-btn">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
            <?php if (isset($_SESSION['username'])): ?>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="login-btn">Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (isset($_SESSION['username'])): ?>
    <div class="container">
        <main class="typing-area">
            <header class="test-header">
                <div class="test-config">
                    <div class="config-item">
                        <label for="time-input">Duration:</label>
                        <input type="number" id="time-input" min="10" max="3600" value="60">
                        <span id="time-unit">seconds</span>
                    </div>
                    <div class="config-item">
                        <label for="lesson-select">Lesson:</label>
                        <select id="lesson-select">
                            <option value="general">General</option>
                            <option value="punctuation">Punctuation</option>
                            <option value="numbers">Numbers</option>
                        <option value="code">Code</option>
                        </select>
                    </div>
                    <div class="config-item">
                        <label for="backspace-toggle">Backspace:</label>
                        <label class="switch">
                            <input type="checkbox" id="backspace-toggle" checked>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </header>
            <div id="text-display" class="text-display" tabindex="0">
                <div class="text-content"></div>
                <div class="cursor"></div>
            </div>
            <button id="reset-btn" class="reset-btn">
                <i class="fas fa-redo-alt"></i>
                <span>Reset Test</span>
            </button>
        </main>
        <aside class="sidebar">
            <div class="stats-panel">
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <span id="time-left">60</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span id="wpm">0</span> WPM
                </div>
                <div class="stat-item">
                    <i class="fas fa-keyboard"></i>
                    <span id="cpm">0</span> CPM
                </div>
                <div class="stat-item">
                    <i class="fas fa-times-circle"></i>
                    <span id="errors">0</span> Errors
                </div>
                <div class="stat-item">
                    <i class="fas fa-backspace"></i>
                    <span id="backspace-count">0</span> Backspaces
                </div>
                <div class="stat-item">
                    <i class="fas fa-bullseye"></i>
                    <span id="accuracy">100</span>% Accuracy
                </div>
            </div>
            <div class="history-panel">
                <h2>Test History</h2>
                <ul id="history-list"></ul>
            </div>
        </aside>
    </div>
    <?php else: ?>
    <div class="container">
        <p>Please <a href="login.php">login</a> to use TypeMaster.</p>
    </div>
    <?php endif; ?>

    <script src="script.js"></script>
</body>
</html>