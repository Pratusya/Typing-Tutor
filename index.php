<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require('db.php');

function getTypingHistory($username) {
    global $con;
    $stmt = $con->prepare("SELECT wpm, accuracy, timestamp FROM typing_history WHERE username = ? ORDER BY timestamp DESC LIMIT 10");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    return $history;
}

$typing_history = getTypingHistory($_SESSION['username']);
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
    height: calc(100vh - 70px); /* Subtract header height */
    overflow: hidden; /* Hide overflow */
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
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.history-panel h2 {
    font-size: 1.3rem;
    margin-bottom: 0.8rem;
}

#history-list {
    list-style-type: none;
    overflow-y: auto;
    flex-grow: 1;
    padding-right: 10px;
    margin: 0;
    padding: 0;
}

#history-list li {
    background-color: var(--background-color);
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 5px;
}

/* Add custom scrollbar styles for webkit browsers */
#history-list::-webkit-scrollbar {
    width: 8px;
}

#history-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#history-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#history-list::-webkit-scrollbar-thumb:hover {
    background: #555;
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
        height: auto;
        max-height: 50vh;
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

    .sidebar {
        max-height: none;
    }

    .history-panel {
        max-height: 30vh;
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
                        <input type="number" id="time-input" min="10" max="3600" value="300">
                        <span id="time-unit">seconds</span>
                    </div>
                    <div class="config-item">
                        <label for="lesson-select">Lesson:</label>
                        <select id="lesson-select">
                            <option value="general">General</option>
                            <!-- <option value="punctuation">Punctuation</option>
                            <option value="numbers">Numbers</option>
                            <option value="code">Code</option> -->
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
                <ul id="history-list">
                    <?php foreach ($typing_history as $entry): ?>
                        <li>WPM: <?php echo $entry['wpm']; ?>, Accuracy: <?php echo $entry['accuracy']; ?>%, Time: <?php echo $entry['timestamp']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>
    </div>
    <?php else: ?>
    <div class="container">
        <p>Please <a href="login.php">login</a> to use TypeMaster.</p>
    </div>
    <?php endif; ?>

    <!-- <script src="script.js"></script> -->

    <script>
        const generalParagraphs = [
    "Their politician was, in this moment, a notour paperback. The first armless grouse is, in its own way, a gear. The coat is a wash. However, a cake is the llama of a caravan. Snakelike armies show us how playgrounds can be viscoses. Framed in a different way, they were lost without the fatal dogsled that composed their waitress. Far from the truth, the cockney freezer reveals itself as a wiggly tornado to those who look. The first hawklike sack.",
    // "Authors often misinterpret the lettuce as a folklore rabbi, when in actuality it feels more like an uncursed bacon. Pursued distances show us how mother-in-laws can be charleses. Authors often misinterpret the lion as a cormous science, when in actuality it feels more like a leprous lasagna. Recent controversy aside, their band was, in this moment, a racemed suit. The clutch of a joke becomes a togaed chair. The first pickled chess is.",
    // "In modern times the first scrawn kitten is, in its own way, an input. An ostrich is the beginner of a roast. An appressed exhaust is a gun of the mind. A recorder is a grade from the right perspective. A hygienic is the cowbell of a skin. Few can name a dun brazil that isn't a highbrow playroom. The unwished beast comes from a thorny oxygen. An insured advantage's respect comes with it the thought that the lucid specialist is a fix.",
    // "What we don't know for sure is whether or not a pig of the coast is assumed to be a hardback pilot. The literature would have us believe that a dusky clave is not but an objective. Few can name a limbate leo that isn't a sunlit silver. The bow is a mitten. However, the drawer is a bay. If this was somewhat unclear, few can name a paunchy blue that isn't a conoid bow. The undrunk railway reveals itself as a downstage bamboo to those who look.",
    // "An aunt is a bassoon from the right perspective. As far as we can estimate, some posit the melic myanmar to be less than kutcha. One cannot separate foods from blowzy bows. The scampish closet reveals itself as a sclerous llama to those who look. A hip is the skirt of a peak. Some hempy laundries are thought of simply as orchids. A gum is a trumpet from the right perspective. A freebie flight is a wrench of the mind. Some posit the croupy.",
    // "A baby is a shingle from the right perspective. Before defenses, collars were only operations. Bails are gleesome relatives. An alloy is a streetcar's debt. A fighter of the scarecrow is assumed to be a leisured laundry. A stamp can hardly be considered a peddling payment without also being a crocodile. A skill is a meteorology's fan. Their scent was, in this moment, a hidden feeling. The competitor of a bacon becomes a boxlike cougar.",
];

// const punctuationParagraphs = [
//     "Wow! That's amazing. Have you ever thought, 'Is this real?' It's hard to believe, isn't it?",
//     "He said, 'I'm going to the store.' Then he asked, 'Do you need anything?'",
//     "The company's profits increased by 15%; however, expenses rose by 20%.",
//     "Dear Sir/Madam, I hope this email finds you well. Sincerely, John Doe.",
//     "She exclaimed, 'Oh no!' when she realized she'd forgotten her keys."
// ];

// const numbersParagraphs = [
//     "In 2023, the population reached 8,045,311 with a growth rate of 1.2% per annum.",
//     "The company's revenue was $1,234,567.89 in Q1, $2,345,678.90 in Q2, and $3,456,789.01 in Q3.",
//     "Pi is approximately equal to 3.14159, while e is about 2.71828.",
//     "The distance to the moon is roughly 384,400 kilometers or 238,855 miles.",
//     "In binary, 42 is represented as 101010, and in hexadecimal, it's 2A."
// ];

// const codeParagraphs = [
//     "function factorial(n) { return n <= 1 ? 1 : n * factorial(n - 1); }",
//     "for (let i = 0; i < array.length; i++) { console.log(array[i]); }",
//     "const sum = numbers.reduce((acc, curr) => acc + curr, 0);",
//     "if (condition) { doSomething(); } else { doSomethingElse(); }",
//     "class Rectangle { constructor(width, height) { this.width = width; this.height = height; } }"
// ];

const textDisplay = document.getElementById('text-display');
const timeLeft = document.getElementById('time-left');
const wpmDisplay = document.getElementById('wpm');
const cpmDisplay = document.getElementById('cpm');
const errorsDisplay = document.getElementById('errors');
const resetBtn = document.getElementById('reset-btn');
const historyList = document.getElementById('history-list');
const timeInput = document.getElementById('time-input');
const timeUnit = document.getElementById('time-unit');
const backspaceToggle = document.getElementById('backspace-toggle');
const lessonSelect = document.getElementById('lesson-select');

const modal = document.createElement('div');
modal.className = 'modal';
document.body.appendChild(modal);

let timer;
let timeRemaining;
let currentText = '';
let typedCharacters = 0;
let errors = 0;
let startTime;
let currentIndex = 0;
let isTestActive = false;
let backspaceCount = 0;
let hasStartedTyping = false;

let wpmData = [];
let cpmData = [];
let accuracyData = [];
let backspaceData = [];
let errorData = [];
let timeData = [];

function disableTextSelection(element) {
    element.style.userSelect = 'none';
    element.style.webkitUserSelect = 'none';
    element.style.msUserSelect = 'none';
    element.style.mozUserSelect = 'none';
}

function disableContextMenu(element) {
    element.addEventListener('contextmenu', (e) => {
        e.preventDefault();
    });
}

function updateTimeUnit() {
    const value = parseInt(timeInput.value) || 300; // Default to 600 seconds (10 minutes)
    if (value >= 3600) {
        timeUnit.textContent = 'hour';
        timeInput.step = '1';
        timeInput.max = '24';
    } else {
        timeUnit.textContent = 'seconds';
        timeInput.step = '10';
        timeInput.max = '3590';
    }
    timeInput.value = Math.min(Math.max(value, 10), parseInt(timeInput.max));
    initTest();
}

timeInput.addEventListener('change', () => {
    let value = parseInt(timeInput.value) || 600; // Default to 600 seconds (10 minutes)
    if (timeUnit.textContent === 'hour') {
        value = Math.min(Math.max(value, 1), 24);
    } else {
        value = Math.min(Math.max(value, 10), 3590);
    }
    timeInput.value = value;
    updateTimeUnit();
    initTest();
});

function disableControls() {
    timeInput.disabled = true;
    lessonSelect.disabled = true;
    backspaceToggle.disabled = true;
}

function enableControls() {
    timeInput.disabled = false;
    lessonSelect.disabled = false;
    backspaceToggle.disabled = false;
}

function getRandomParagraph() {
    const lesson = lessonSelect.value;
    let selectedParagraphs;

    switch (lesson) {
        case 'punctuation':
            selectedParagraphs = punctuationParagraphs;
            break;
        case 'numbers':
            selectedParagraphs = numbersParagraphs;
            break;
        case 'code':
            selectedParagraphs = codeParagraphs;
            break;
        default:
            selectedParagraphs = generalParagraphs;
    }

    const randomIndex = Math.floor(Math.random() * selectedParagraphs.length);
    return selectedParagraphs[randomIndex];
}

function initTest() {
    clearInterval(timer);
    isTestActive = false;
    hasStartedTyping = false;
    currentText = getRandomParagraph();
    
    const textContent = document.createElement('div');
    textContent.className = 'text-content';
    textContent.innerHTML = currentText.split('').map(char => `<span>${char}</span>`).join('');
    
    disableTextSelection(textContent);
    disableContextMenu(textContent);

    const cursor = document.createElement('div');
    cursor.className = 'cursor';

    textDisplay.innerHTML = '';
    textDisplay.appendChild(textContent);
    textDisplay.appendChild(cursor);

    const firstChar = textContent.querySelector('span');
    if (firstChar) {
        const rect = firstChar.getBoundingClientRect();
        const containerRect = textDisplay.getBoundingClientRect();
        cursor.style.top = `${rect.top - containerRect.top}px`;
        cursor.style.left = `${rect.left - containerRect.left}px`;
    }

    let inputTime = parseInt(timeInput.value) || 600; // Default to 600 seconds (10 minutes)
    if (timeUnit.textContent === 'hour') {
        inputTime *= 3600;
    }
    timeRemaining = inputTime;
    
    updateTimerDisplay();
    typedCharacters = 0;
    errors = 0;
    currentIndex = 0;
    backspaceCount = 0;
    wpmDisplay.textContent = '0';
    cpmDisplay.textContent = '0';
    errorsDisplay.textContent = '0';
    document.getElementById('backspace-count').textContent = '0';
    document.getElementById('accuracy').textContent = '100';
    textDisplay.focus();
    
    wpmData = [];
    cpmData = [];
    accuracyData = [];
    backspaceData = [];
    errorData = [];
    timeData = [];
    
    textDisplay.removeEventListener('keydown', handleKeyDown);
    textDisplay.addEventListener('keydown', handleKeyDown);

    textContent.querySelector('span').classList.add('active');

    enableControls();
    handleResponsiveLayout();
}

function updateTimerDisplay() {
    if (timeRemaining >= 3600) {
        const hours = Math.floor(timeRemaining / 3600);
        const minutes = Math.floor((timeRemaining % 3600) / 60);
        const seconds = timeRemaining % 60;
        timeLeft.textContent = `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else if (timeRemaining >= 60) {
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        timeLeft.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    } else {
        timeLeft.textContent = timeRemaining;
    }
}

function startTimer() {
    startTime = new Date();
    timer = setInterval(() => {
        if (timeRemaining > 0) {
            timeRemaining--;
            updateTimerDisplay();
            updateStats();
        } else {
            endTest();
        }
    }, 1000);
}

function calculateAccuracy() {
    if (!hasStartedTyping) return 100;
    return typedCharacters > 0 ? Math.round(((typedCharacters - errors) / typedCharacters) * 100) : 100;
}

function updateStats() {
    const timeElapsed = Math.max((new Date() - startTime) / 1000 / 60, 0.001);
    const wpm = Math.round((typedCharacters / 5) / timeElapsed);
    const cpm = Math.round(typedCharacters / timeElapsed);
    const accuracy = calculateAccuracy();
    
    wpmDisplay.textContent = wpm > 0 ? wpm : 0;
    cpmDisplay.textContent = cpm > 0 ? cpm : 0;
    errorsDisplay.textContent = errors;
    document.getElementById('backspace-count').textContent = backspaceCount;
    document.getElementById('accuracy').textContent = accuracy;

    wpmData.push(wpm);
    cpmData.push(cpm);
    accuracyData.push(accuracy);
    backspaceData.push(backspaceCount);
    errorData.push(errors);
    timeData.push(Math.round(timeElapsed * 60));
}

function showResultPopup(wpm, cpm, errors, backspaces, accuracy) {
    modal.innerHTML = `
        <div class="modal-content">
            <h2>Test Results</h2>
            <p>Words per minute: ${wpm}</p>
            <p>Characters per minute: ${cpm}</p>
            <p>Accuracy: ${accuracy}%</p>
            <p>Errors: ${errors}</p>
            <p>Backspaces: ${backspaces}</p>
            <canvas id="statsChart" width="400" height="200"></canvas>
            <button id="close-modal">Close</button>
        </div> `;
    modal.style.display = 'flex';
    
    createChart();

    document.getElementById('close-modal').addEventListener('click', () => {
        modal.style.display = 'none';
    });
}

function createChart() {
    const ctx = document.getElementById('statsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeData,
            datasets: [
                {
                    label: 'WPM',
                    data: wpmData,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                },
                {
                    label: 'CPM',
                    data: cpmData,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1
                },
                {
                    label: 'Accuracy',
                    data: accuracyData,
                    borderColor: 'rgb(54, 162, 235)',
                    tension: 0.1
                },
                {
                    label: 'Backspaces',
                    data: backspaceData,
                    borderColor: 'rgb(255, 206, 86)',
                    tension: 0.1
                },
                {
                    label: 'Errors',
                    data: errorData,
                    borderColor: 'rgb(153, 102, 255)',
                    tension: 0.1
                }
            ]
        },
    options: {
        responsive: true,
        scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Time (seconds)'
                    }
                },
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function addToHistory(wpm, cpm, accuracy, errors, backspaces) {
    const li = document.createElement('li');
    const now = new Date();
    li.innerHTML = `WPM: ${wpm} | CPM: ${cpm} | Accuracy: ${accuracy}% | Errors: ${errors} | Backspaces: ${backspaces} | Time: ${now.toLocaleString()}`;
    historyList.insertBefore(li, historyList.firstChild);
    if (historyList.children.length > 10) {
        historyList.removeChild(historyList.lastChild);
    }
    saveHistoryToDatabase(wpm, cpm, accuracy, errors, backspaces);
}

function updateHistory(wpm, cpm, accuracy, errors, backspaces) {
    const data = new FormData();
    data.append('wpm', wpm);
    data.append('cpm', cpm);
    data.append('accuracy', accuracy);
    data.append('errors', errors);
    data.append('backspaces', backspaces);

    fetch('save_history.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const historyList = document.getElementById('history-list');
            historyList.innerHTML = ''; // Clear existing history
            data.history.forEach(entry => {
                const li = document.createElement('li');
                li.textContent = `WPM: ${entry.wpm}, Accuracy: ${entry.accuracy}%, Time: ${entry.timestamp}`;
                historyList.appendChild(li);
            });
        } else {
            console.error('Error saving history:', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function endTest() {
    isTestActive = false;
    clearInterval(timer);
    updateStats();
    const finalWpm = parseInt(wpmDisplay.textContent);
    const finalCpm = parseInt(cpmDisplay.textContent);
    const accuracy = calculateAccuracy();
    addToHistory(finalWpm, finalCpm, accuracy, errors, backspaceCount);
    showResultPopup(finalWpm, finalCpm, errors, backspaceCount, accuracy);
    saveHistoryToDatabase(finalWpm, finalCpm, accuracy, errors, backspaceCount);
    textDisplay.removeEventListener('keydown', handleKeyDown);
    enableControls();
}

function saveHistoryToDatabase(wpm, cpm, accuracy, errors, backspaces) {
    const data = new URLSearchParams();
    data.append('wpm', wpm);
    data.append('cpm', cpm);
    data.append('accuracy', accuracy);
    data.append('errors', errors);
    data.append('backspaces', backspaces);
    
    const now = new Date();
    data.append('timestamp', now.toISOString());

    fetch('save_history.php', {
        method: 'POST',
        body: data,
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            console.log('History saved successfully');
        } else {
            console.error('Error saving history:', result.error);
        }
    })
    .catch(error => {
        console.error('Error saving history:', error);
    });
}

function loadTypingHistory() {
    fetch('get_history.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            historyList.innerHTML = ''; // Clear existing history
            data.history.forEach(entry => {
                const li = document.createElement('li');
                li.innerHTML = `WPM: ${entry.wpm} | CPM: ${entry.cpm} | Accuracy: ${entry.accuracy}% | Errors: ${entry.errors} | Backspaces: ${entry.backspaces} | Time: ${new Date(entry.timestamp).toLocaleString()}`;
                historyList.appendChild(li);
            });
        } else {
            console.error('Error loading history:', data.error);
        }
    })
    .catch(error => {
        console.error('Error loading history:', error);
    });
}

function handleKeyDown(e) {
    if (!isTestActive && timeRemaining > 0) {
        isTestActive = true;
        startTime = new Date();
        startTimer();
        disableControls();
    }

    if (isTestActive && timeRemaining > 0) {
        const key = e.key;
        const currentChar = currentText[currentIndex];
        const spans = textDisplay.querySelectorAll('.text-content span');

        spans[currentIndex].classList.remove('active');

        if (key === currentChar || (key.length === 1 && key !== currentChar)) {
            hasStartedTyping = true;
        }

        if (key === currentChar) {
            spans[currentIndex].classList.add('correct');
            currentIndex++;
            typedCharacters++;
        } else if (key === 'Backspace' && backspaceToggle.checked && currentIndex > 0) {
            currentIndex--;
            spans[currentIndex].classList.remove('correct', 'incorrect');
            if (spans[currentIndex].classList.contains('incorrect')) {
                errors--;
            }
            typedCharacters--;
            backspaceCount++;
        } else if (key.length === 1) {
            spans[currentIndex].classList.add('incorrect');
            errors++;
            currentIndex++;
            typedCharacters++;
        }

        if (currentIndex < currentText.length) {
            spans[currentIndex].classList.add('active');
            const rect = spans[currentIndex].getBoundingClientRect();
            const cursorTop = rect.top - textDisplay.getBoundingClientRect().top;
            const cursorLeft = rect.left - textDisplay.getBoundingClientRect().left;
            textDisplay.querySelector('.cursor').style.top = `${cursorTop}px`;
            textDisplay.querySelector('.cursor').style.left = `${cursorLeft}px`;
        }
        if (currentIndex === currentText.length) {
            endTest();
        }

        updateStats();
        e.preventDefault();
    }
}

function handleResponsiveLayout() {
    const container = document.querySelector('.container');
    const typingArea = document.querySelector('.typing-area');
    const sidebar = document.querySelector('.sidebar');

    if (window.innerWidth <= 1200) {
        container.style.height = 'auto';
        typingArea.style.height = 'auto';
        sidebar.style.height = 'auto';
    } else {
        container.style.height = '100vh';
        typingArea.style.height = '100%';
        sidebar.style.height = '100vh';
    }
}

resetBtn.addEventListener('click', initTest);
lessonSelect.addEventListener('change', initTest);

window.addEventListener('load', handleResponsiveLayout);
window.addEventListener('resize', handleResponsiveLayout);

document.addEventListener('copy', (e) => {
    if (isTestActive) {
        e.preventDefault();
    }
});

// Initialize the test when the page loads
updateTimeUnit();
initTest();

document.addEventListener('DOMContentLoaded', loadTypingHistory);

// Hamburger menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const nav = document.querySelector('.main-header nav');
    
    hamburger.addEventListener('click', function() {
        hamburger.classList.toggle('open');
        nav.classList.toggle('open');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInsideNav = nav.contains(event.target);
        const isClickOnHamburger = hamburger.contains(event.target);
        
        if (!isClickInsideNav && !isClickOnHamburger && nav.classList.contains('open')) {
            nav.classList.remove('open');
            hamburger.classList.remove('open');
        }
    });
    
    // Close menu when window is resized to larger than mobile breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            nav.classList.remove('open');
            hamburger.classList.remove('open');
        }
    });
});
    </script>
</body>
</html>