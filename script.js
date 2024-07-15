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
    const value = parseInt(timeInput.value) || 600; // Default to 600 seconds (10 minutes)
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