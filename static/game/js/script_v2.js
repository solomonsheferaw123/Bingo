// Wait for the page to fully load
const fullscreen = document.getElementById('full-screen');

const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item => {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

var numberElements = document.querySelectorAll('.number');
var calledNumbers = [];
var callednumberdisplay = document.getElementById('called-numbers');
var lastletter = document.getElementById('last-letter');
var lastnum = document.getElementById('last-num');
var callingSpeedRange = document.getElementById('callingSpeed');
var callingSpeedTxt = document.getElementById('callingSpeedTxt');
var callinginterval = 5000;
var autoIntervalId = null;
var autoPlaying = false;
var selectedLanguage = "am";
let speech = new SpeechSynthesisUtterance();
let voices = [];

function loadVoices() {
    voices = window.speechSynthesis.getVoices();
    speech.voice = voices[0]; // Set default voice
}

// Load voices immediately and on change
loadVoices();
if (window.speechSynthesis.onvoiceschanged !== undefined) {
    window.speechSynthesis.onvoiceschanged = loadVoices;
}
let bonus_c = document.getElementById('bonus_animation');
let bonus_t = document.getElementById('bonus_text');
let free_c = document.getElementById('free_hit');
let free_t = document.getElementById('free_hit_text');
let jackpot_c = document.getElementById('jackpot');
let jackpot_t = document.getElementById('jackpot_text');

menuBar.addEventListener('click', function () {
    sidebar.classList.toggle('hide');
})


if (window.innerWidth < 768) {
    sidebar.classList.add('hide');
}

const switchMode = document.getElementById('switch-mode');

switchMode.addEventListener('change', function () {
    deleteCookie("mode");
    setCookie("mode", this.checked, 7);
    if (this.checked) {
        document.body.classList.remove('dark');
    } else {
        document.body.classList.add('dark');
    }
})
window.addEventListener('load', function () {
    // Get the loader element
    var loader = document.getElementById('loader');

    // Hide the loader element
    loader.style.display = 'none';

    var footer = document.getElementById('footer');
    var bingo_con = document.getElementById('bingo-container');
    var index_a = this.document.getElementById('index');

    index_a.classList.add('active');
    footer.style.display = "block";
    bingo_con.style.display = "block";

    var cookieLanguage = getCookie("selectedLanguage");
    var cookieLanguage = getCookie("selectedLanguage");
    if (cookieLanguage != null) {
        // Check if the cookie value is a valid option in the select box
        let optionExists = false;
        for (let i = 0; i < callerLanguageSelect.options.length; i++) {
            if (callerLanguageSelect.options[i].value === cookieLanguage) {
                optionExists = true;
                break;
            }
        }

        if (optionExists) {
            callerLanguageSelect.value = cookieLanguage;
            selectedLanguage = cookieLanguage;
        } else {
            // Default to 'am' if generic/invalid
            callerLanguageSelect.value = 'am';
            selectedLanguage = 'am';
        }
    }
    var modeCookie = getCookie("mode");
    if (modeCookie != null) {
        if (modeCookie == 'true') {
            document.body.classList.remove('dark');
            switchMode.checked = true;
        } else {
            document.body.classList.add('dark');
            switchMode.checked = false;
        }
    }

    var speed = getCookie("speed");
    if (speed != null) {
        callingSpeedRange.value = speed;
        const invertedValue = 12 - (speed - 2);
        callinginterval = invertedValue * 1000;
        callingSpeedTxt.textContent = "Auto call " + invertedValue + " secounds";
    }

    // Clear cookies on logout
    const logoutBtn = document.querySelector('.logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function () {
            deleteCookie("selectedPlayers");
            deleteCookie("GameID");
            deleteCookie("WinAmount");
            deleteCookie("HouseCut");
            deleteCookie("Stake");
            deleteCookie("Bonus");
            deleteCookie("Free");
            deleteCookie("Patterns");
            deleteCookie("selectedLanguage"); // Optional, maybe keep language
            deleteCookie("calledNumbers");
        });
    }

    var gameInProgress = getCookie("GameID");
    if (gameInProgress != null && gameInProgress !== "") {
        document.getElementById('start-new-game').style.display = 'none';
        document.getElementById('start-auto-play').style.display = 'block';
        document.getElementById('call-next').style.display = 'block';
        document.getElementById('finsh').style.display = 'block';
        document.getElementById('shuffle').style.display = 'block'; // Keep shuffle visible

        // Ensure buttons are not stuck in inactive state on refresh
        callnextbtn.classList.remove('inactive');
        startbtn.classList.remove('inactive');
        finshbtn.classList.remove('inactive');
        check_btn.classList.remove('inactive');
        shuffle_btn.classList.remove('inactive');

        // Restore called numbers if game already in progress
        restoreGameState();
    } else {
        document.getElementById('start-new-game').style.display = 'block';
        document.getElementById('start-auto-play').style.display = 'none';
        document.getElementById('call-next').style.display = 'none';
        document.getElementById('finsh').style.display = 'none';
        document.getElementById('shuffle').style.display = 'block';
    }
});

// Function to toggle light mode and dark mode

// Function to toggle full screen mode
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
}

// Event listener for clicking on full screen image
fullscreen.addEventListener("click", toggleFullScreen);

var callerLanguageSelect = document.getElementById('lang');

function setCookie(cookieName, cookieValue, expirationDays) {
    localStorage.setItem(cookieName, cookieValue);
}

function getCookie(cookieName) {
    return localStorage.getItem(cookieName);
}

function deleteCookie(cookieName) {
    localStorage.removeItem(cookieName);
}

function getLanguage() {
    selectedLanguage = callerLanguageSelect.value;
    return selectedLanguage;
}

callerLanguageSelect.addEventListener('change', function () {
    // Update the language for call-out-loud
    const selectedLanguage = callerLanguageSelect.value;
    deleteCookie("selectedLanguage");
    setCookie("selectedLanguage", selectedLanguage, 7);

    if (!['am', 'mm', 'mm2'].includes(selectedLanguage)) {
        speech.voice = voices[0];
    }
});

callingSpeedRange.addEventListener('input', function () {
    var newSpeed = callingSpeedRange.value;
    const invertedValue = 12 - (newSpeed - 2);
    callinginterval = invertedValue * 1000;
    callingSpeedTxt.textContent = "Auto call " + invertedValue + " secounds";
    deleteCookie("speed");
    setCookie("speed", newSpeed, 7);
    // Convert speed to milliseconds
});

function updateTotalCalled() {
    var totalCalledClock = document.getElementById('total-called');
    totalCalledClock.textContent = calledNumbers.length + " CALLED";
}

// Helper function to play Bingo audio with fallback for inconsistent file naming
// Helper function to play Bingo audio with distinct voice sets
function playBingoAudio(letter, number, voiceType) {
    var basePath = "static/game/audio/";
    var fileName = "";

    if (voiceType === 2) {
        // Voice 2 (Male/M-am): uses "1.mp3", "2.mp3", etc.
        fileName = number + ".mp3";
    } else {
        // Voice 1 (Female/F-am) or default: uses "Voice 1.mp3", etc.
        fileName = "Voice " + number + ".mp3";
    }

    var audio = new Audio(basePath + fileName);
    audio.play().catch(function (e) {
        console.warn("Could not play audio for: " + fileName, e);
    });
}

function callNumber() {
    var remainingNumbers = getRemainingNumbers();
    callednumberdisplay.style.display = "block";

    if (remainingNumbers.length === 0) {
        alert("All numbers have been called!");
        return;
    }

    var randomIndex = Math.floor(Math.random() * remainingNumbers.length);
    var selectedNumber = remainingNumbers[randomIndex];

    calledNumbers.push(selectedNumber);
    setCookie("calledNumbers", encodeURIComponent(JSON.stringify(calledNumbers)), 7);

    var numberElement = document.querySelector('.number[data-number="' + selectedNumber + '"]');
    if (numberElement) {
        changeBlink(selectedNumber);
    }

    var rowIndex = Math.floor(selectedNumber / 15); // Calculate the row index
    var letter = 'B';
    if (selectedNumber <= 15) {
        letter = 'B';
    }
    else if (selectedNumber <= 30) {
        letter = 'I';
    }
    else if (selectedNumber <= 45) {
        letter = 'N';
    }
    else if (selectedNumber <= 60) {
        letter = 'G';
    }
    else if (selectedNumber <= 75) {
        letter = 'O';
    }// Get the corresponding letter
    var numStr = selectedNumber.toString();  // Convert the number to a string

    // Create the desired string format by inserting a space between characters
    var resultStr = numStr.split('').join(' ');
    var displayedNumber = letter + (selectedNumber) + ", " + letter + "," + resultStr; // Calculate the displayed number
    // This will log: "1 5"

    selectedLanguage = getLanguage();
    if (selectedLanguage == 'am') {
        // Voice 1 (Female Amharic) - uses original "Voice X.mp3"
        playBingoAudio(letter, selectedNumber, 1);
    } else if (selectedLanguage == 'mm' || selectedLanguage == 'mm2') {
        // Voice 2 (Male Amharic/Amharic 2) - uses "X.mp3"
        playBingoAudio(letter, selectedNumber, 2);
    } else if (selectedLanguage == 'om' || selectedLanguage == 'tg') {
        // Use AI speech synthesis for other languages
        speech.voice = voices[0];
        speech.text = displayedNumber;
        window.speechSynthesis.speak(speech);
    } else {
        // AI mode - use speech synthesis
        speech.voice = voices[0];
        speech.text = displayedNumber;
        window.speechSynthesis.speak(speech);
    }

    //var displayedNumber = letter + (selectedNumber) + ", " + letter + "," + resultStr; // Calculate the displayed number
    lastletter.textContent = letter;
    lastnum.textContent = selectedNumber;

    updateLastCalledNumbers();
    updateTotalCalled();

}

function changeBlink(number) {
    // Remove blink class from any previously blinking divs
    if (calledNumbers.length > 1) {
        const previousBlinkingDiv = document.querySelector('.number.blink');
        if (previousBlinkingDiv) {
            previousBlinkingDiv.classList.remove('blink');
            previousBlinkingDiv.classList.add('selected');
        }
    }

    // Find the div with the specified ID
    const divToBlink = document.querySelector('.number[data-number="' + number + '"]');
    if (divToBlink) {
        // Add the blink class to the specified div
        divToBlink.classList.add('blink');
    } else {
        console.warn(`No div found with ID: ${number}`);
    }
}

function updateLastCalledNumbers() {
    var lastCalledNumbersElement = document.getElementById('lastCalledNumbers');
    if (!lastCalledNumbersElement) return;
    lastCalledNumbersElement.innerHTML = '';

    for (var i = Math.max(0, calledNumbers.length - 4); i < calledNumbers.length; i++) {
        var number = calledNumbers[i];
        var numberElement = document.createElement('div');
        numberElement.classList.add('last-called-num');
        var letter = getLetterForNumber(number);
        numberElement.textContent = letter + " " + number;
        lastCalledNumbersElement.appendChild(numberElement);
    }
}

function getLetterForNumber(number) {
    if (number <= 15) return 'B';
    if (number <= 30) return 'I';
    if (number <= 45) return 'N';
    if (number <= 60) return 'G';
    if (number <= 75) return 'O';
    return '';
}

function restoreGameState() {
    var savedCalled = getCookie("calledNumbers");
    if (savedCalled) {
        try {
            calledNumbers = JSON.parse(decodeURIComponent(savedCalled));
        } catch (e) {
            calledNumbers = [];
        }

        if (calledNumbers.length > 0) {
            if (callednumberdisplay) callednumberdisplay.style.display = "block";

            calledNumbers.forEach((num, index) => {
                var numberElement = document.querySelector('.number[data-number="' + num + '"]');
                if (numberElement) {
                    if (index === calledNumbers.length - 1) {
                        numberElement.classList.add('blink');
                        if (lastletter) lastletter.textContent = getLetterForNumber(num);
                        if (lastnum) lastnum.textContent = num;
                    } else {
                        numberElement.classList.add('selected');
                    }
                }
            });
            updateLastCalledNumbers();
            updateTotalCalled();
        }
    }
}

var startbtn = document.getElementById('start-auto-play');
var callnextbtn = document.getElementById('call-next');
var finshbtn = document.getElementById('finsh');
var newgamebtn = document.getElementById('start-new-game');
var shuffle_btn = document.getElementById('shuffle');
var check_btn = document.getElementById('check-btn');
var game_id = document.getElementById("game-id");

startbtn.onclick = function () {
    selectedLanguage = getLanguage();
    if (autoPlaying) {
        startbtn.textContent = "START AUTO PLAY";
        if (selectedLanguage == 'am') {
            // Voice 1 - Stop sound
            var audio = new Audio("static/game/audio/stop.mp3");
            audio.play();
        } else if (selectedLanguage === 'mm' || selectedLanguage === 'mm2') {
            // Voice 2 - Stop sound
            var audio = new Audio("static/game/audio/stop2.mp3");
            audio.play();
        } else {
            speech.voice = voices[0];
            speech.text = "game paused";
            window.speechSynthesis.speak(speech);
        }
        stopAuto();
    } else {
        startbtn.textContent = "STOP AUTO PLAY";

        if (selectedLanguage == 'am') {
            // Voice 1 - Start sound
            var audio = new Audio("static/game/audio/start.mp3");
            audio.play();
        } else if (selectedLanguage === 'mm' || selectedLanguage === 'mm2') {
            // Voice 2 - Start sound
            var audio = new Audio("static/game/audio/start2.mp3");
            audio.play();
        } else {
            speech.voice = voices[0];
            speech.text = "game started";
            window.speechSynthesis.speak(speech);
        }
        startAuto();
    }
};

function startAuto() {
    callnextbtn.classList.add('inactive');
    shuffle_btn.classList.add('inactive');
    finshbtn.classList.add('inactive');
    check_btn.classList.add('inactive');

    if (autoIntervalId) {
        clearInterval(autoIntervalId);
        autoIntervalId = null;
        alert("Stopped");
        return;
    }

    autoIntervalId = setInterval(function () {
        callNumber();
        var remainingNumbers = getRemainingNumbers();
        if (remainingNumbers.length === 0) {
            clearInterval(autoIntervalId);
            autoIntervalId = null;
        }
    }, callinginterval);
    autoPlaying = true;
}

function stopAuto() {
    callnextbtn.classList.remove('inactive');
    shuffle_btn.classList.remove('inactive');
    finshbtn.classList.remove('inactive');
    check_btn.classList.remove('inactive');
    clearInterval(autoIntervalId);
    autoIntervalId = null;
    autoPlaying = false;
}

function getRemainingNumbers() {
    var allNumbers = Array.from(document.querySelectorAll('.number'));
    var remainingNumbers = allNumbers.filter(function (numberElement) {
        return !numberElement.classList.contains('selected') && !numberElement.classList.contains('blink');
    });

    return remainingNumbers.map(function (numberElement) {
        return parseInt(numberElement.getAttribute('data-number'));
    });
}


callnextbtn.onclick = function () {
    callNumber();
};

finshbtn.onclick = function () {
    let gameIdText = game_id.innerText.replace('#', '').trim();

    if (!gameIdText) {
        gameIdText = getCookie("GameID");
    }

    if (!gameIdText) {
        alert("Error: Game ID missing. Please ensure the game started correctly.");
        return;
    }

    // Call local PHP API to finish the game (no confirmation needed)
    fetch('api/finish_game.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'game_id': gameIdText,
            'win_card': document.getElementById('check-num').value || ''
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' || data.message === 'Game not found or already finished') {
                if (data.status !== 'success') {
                    console.log("Game already finished, resetting UI anyway.");
                }
                // Clear cookies and reset UI for new game
                deleteCookie("GameID");
                deleteCookie("calledNumbers");
                // Note: We KEEP selectedPlayers so they are ready for the next game

                // Reset the board - remove all selected/blink classes
                document.querySelectorAll('.number').forEach(el => {
                    el.classList.remove('selected', 'blink');
                });

                // Clear called numbers array
                calledNumbers.length = 0;

                // Hide called numbers display
                callednumberdisplay.style.display = 'none';

                // Reset total called counter
                document.getElementById('total-called').textContent = '0 CALLED';

                // Clear last called numbers display
                document.getElementById('lastCalledNumbers').innerHTML = '';
                document.getElementById('last-letter').textContent = '';
                document.getElementById('last-num').textContent = '';

                // Reset game info displays
                document.getElementById('game-id').textContent = '#' + Math.floor(Math.random() * 9000 + 1000);
                document.getElementById('stake-display').textContent = '0.00';
                document.getElementById('win-display').textContent = '0.00';
                document.getElementById('win_amount_display').textContent = '0.00 Birr';

                // Clear check number input
                document.getElementById('check-num').value = '';

                // Show START NEW GAME, hide game controls
                document.getElementById('start-new-game').style.display = 'block';
                document.getElementById('shuffle').style.display = 'block';
                document.getElementById('start-auto-play').style.display = 'none';
                document.getElementById('call-next').style.display = 'none';
                document.getElementById('finsh').style.display = 'none';


                // Reset auto play state
                if (autoPlaying) {
                    stopAuto();
                }
                startbtn.textContent = "START AUTO PLAY";
            } else {
                // Alert the error message if status is not success
                // alert(data.message || "Failed to finish game");
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
};

check_btn.onclick = function () {
    const check_num = document.getElementById('check-num').value;
    if (check_num.trim() === "") {
        // alert("Input field cannot be empty");
        return false; // Prevent form submission
    } else {
        checkBingo(check_num);
    }
}

function checkBingo(num) {
    let patterns = [];
    let selectedPlayers = [];

    try {
        const patternCookie = getCookie("Patterns");
        if (patternCookie) {
            patterns = JSON.parse(decodeURIComponent(patternCookie));
        }

        const playersCookie = getCookie("selectedPlayers");
        if (playersCookie) {
            selectedPlayers = JSON.parse(decodeURIComponent(playersCookie));
        }
    } catch (error) {
        console.error("Error parsing cookies:", error);
    }

    console.log("Checking Card:", num, "Patterns:", patterns, "Players:", selectedPlayers);

    $.ajax({
        url: "api/check.php",
        type: "GET",
        data: {
            card: num,
            called: JSON.stringify(calledNumbers),
            game: game_id.innerText,
            patterns: JSON.stringify(patterns),
            players: JSON.stringify(selectedPlayers)
        },
        success: function (response) {
            var result = response.result;

            if (result[0].message === "not a player") {
                alert("not a player");
                return;
            }

            if (result[0].message === "Good Bingo" || result[0].message === "Pass Bingo" || result[0].message === "No Bingo") {
                generateResultHTML(result[0], response.game);
            } else {
                alert(result[0].message);
            }
        },
        error: function (xhr, status, error) {
            console.error("Failed to check result", error);
        }
    });
}

// Function to generate the result HTML/Overlay for Bingo/No Bingo
function generateResultHTML(cardResult, game) {
    var resultContainer = document.getElementById("blur-background");
    resultContainer.innerHTML = ""; // Clear previous
    resultContainer.style.display = "block";

    var resultDiv = document.createElement("div");
    resultDiv.className = "result-container";

    // Title Section
    var title = document.createElement("div");
    title.className = "result-title";
    // Title format: "ID - Message" (e.g., "11 - No Bingo")
    title.textContent = cardResult.card_name + " - " + cardResult.message;
    resultDiv.appendChild(title);


    // Table Section
    var table = document.createElement("table");
    table.className = "bingo-result-table";

    // Header
    var tr = document.createElement("tr");
    ["B", "I", "N", "G", "O"].forEach(l => {
        var th = document.createElement("th");
        th.textContent = l;
        tr.appendChild(th);
    });
    table.appendChild(tr);

    // Grid Body
    var pos_counter = 1;
    cardResult.card.forEach(row => {
        var tr = document.createElement("tr");
        row.forEach(cell => {
            var td = document.createElement("td");
            td.textContent = (cell === 'FREE' || cell === 0) ? "★" : cell;

            // Highlight Logic
            if (cardResult.message === 'Good Bingo' || cardResult.message === 'Pass Bingo') {
                if (cardResult.winning_numbers.includes(pos_counter)) {
                    td.className = "matched";
                }
            } else if (cardResult.message === 'No Bingo') {
                if (calledNumbers.includes(cell) || cell === 'FREE' || cell === 0) {
                    td.className = "matched";
                }
            }

            tr.appendChild(td);
            pos_counter++;
        });
        table.appendChild(tr);
    });
    resultDiv.appendChild(table);

    // Buttons Section
    var actions = document.createElement("div");
    actions.className = "result-actions";

    var closeBtn = document.createElement("button");
    closeBtn.textContent = "Close";
    closeBtn.onclick = function () {
        resultContainer.style.display = "none";
        resultContainer.innerHTML = "";
    };
    actions.appendChild(closeBtn);

    if (cardResult.message === 'Pass Bingo' || cardResult.message === 'No Bingo') {
        var blockBtn = document.createElement("button");
        blockBtn.textContent = "Block";
        blockBtn.className = "block-btn";
        blockBtn.onclick = function () {
            blockCard(cardResult.card_name, game);
        };

        actions.appendChild(blockBtn);
    }
    resultDiv.appendChild(actions);
    resultContainer.appendChild(resultDiv);

    // --- AUDIO LOGIC ---
    let audioPath = "static/game/audio/";
    let isMale = (selectedLanguage === 'mm' || selectedLanguage === 'mm2');

    if (cardResult.message === 'Good Bingo') {
        audioPath += (isMale ? "yesbingo.mp3" : "yesbingo.mp3"); // Using yesbingo.mp3 as requested
    } else if (cardResult.message === 'Pass Bingo') {
        audioPath += (isMale ? "passbingo2.mp3" : "passbingo.mp3");
    } else if (cardResult.message === 'No Bingo') {
        audioPath += (isMale ? "nobingo2.mp3" : "nobingo.mp3");
    }

    if (audioPath !== "static/game/audio/") {
        var audio = new Audio(audioPath);
        audio.play().catch(() => {
            speech.text = cardResult.message;
            window.speechSynthesis.speak(speech);
        });
    }


    // Confetti for winners
    if (cardResult.message === 'Good Bingo') {
        launchConfetti();
    }
}

function blockCard(card_id, game) {
    $.ajax({
        url: "api/block_card.php",  // Updated to point to our PHP implementation
        type: "GET",
        data: {
            card: card_id,
            game: game,
            // Add more parameters as needed
        },
        success: function (response) {
            // Disable buttons based on the received list of selected numbers
            var resultContainer = document.getElementById("blur-background");
            while (resultContainer.firstChild) {
                resultContainer.removeChild(resultContainer.firstChild);
            }
            // Remove the tableContainer itself from its parent node
            resultContainer.style.display = "none";

        },
        error: function (xhr, status, error) {
            alert("Failed to  Block user");
        }
    });
}


let shuffleInterval;
// Variable to store the shuffle interval

// Function to shuffle the class of numbers visually
function shuffleNumbers() {
    // Remove the "selected" and "blink" class from all numbers
    numberElements.forEach((numberElement) => {
        numberElement.classList.remove('selected', 'blink');
    });

    // Randomly select 5 unique indices and add the "selected" class to them
    let randomIndices = [];
    while (randomIndices.length < 5) {
        let r = Math.floor(Math.random() * numberElements.length);
        if (!randomIndices.includes(r)) randomIndices.push(r);
    }

    randomIndices.forEach(idx => {
        if (numberElements[idx]) numberElements[idx].classList.add('selected');
    });
}

function shuffleBoard() {
    const audioPlayer = document.getElementById("audioPlayer");
    audioPlayer.loop = true; // Set audio to loop
    audioPlayer.play();

    shuffleInterval = setInterval(shuffleNumbers, 115); // Shuffle every 115 milliseconds
    calledNumbers.length = 0;
    deleteCookie("calledNumbers");
    updateLastCalledNumbers();
}

function stopShuffleBoard() {
    const audioPlayer = document.getElementById("audioPlayer");
    audioPlayer.pause();
    audioPlayer.currentTime = 0;
    clearInterval(shuffleInterval); // Clear the shuffle interval
    numberElements.forEach((numberElement) => {
        numberElement.classList.remove('selected');
    });
}

shuffle_btn.onclick = function () {
    const currentText = shuffle_btn.innerText.toUpperCase();
    if (currentText === "SHUFFLE") {
        shuffleBoard();
        shuffle_btn.innerText = "STOP";
        callnextbtn.classList.add('inactive');
        startbtn.classList.add('inactive');
        finshbtn.classList.add('inactive');
        check_btn.classList.add('inactive');
    } else {
        stopShuffleBoard();
        shuffle_btn.innerText = "SHUFFLE";
        callnextbtn.classList.remove('inactive');
        startbtn.classList.remove('inactive');
        finshbtn.classList.remove('inactive');
        check_btn.classList.remove('inactive');
    }
};
function launchConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    const myConfetti = confetti.create(canvas, {
        resize: true,
        useWorker: true
    });

    // Launch confetti from the left-bottom
    myConfetti({
        particleCount: 100,
        spread: 70,
        origin: { x: 0, y: 1 }
    });

    // Launch confetti from the right-bottom
    myConfetti({
        particleCount: 100,
        spread: 70,
        origin: { x: 1, y: 1 }
    });

    // Launch confetti from behind the div
    setTimeout(() => {
        myConfetti({
            particleCount: 100,
            spread: 70,
            origin: { y: 0.6 }
        });
    }, 500);

    // Repeat the confetti effect 3 times
    for (let i = 1; i < 3; i++) {
        setTimeout(() => {
            myConfetti({
                particleCount: 100,
                spread: 70,
                origin: { x: 0, y: 1 }
            });
            myConfetti({
                particleCount: 100,
                spread: 70,
                origin: { x: 1, y: 1 }
            });
            setTimeout(() => {
                myConfetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }, 500);
        }, i * 2000);
    }
}

var viewAllCalledButton = document.getElementById("viewAllCalledButton");
var viewAllCalledModal = document.getElementById("viewAllCalledModal");
var closeViewAllCalled = document.getElementById("closeViewAllCalled");
var recentlyCalledNumbers = document.getElementById("recentlyCalledNumbers");

// Function to display the recently called numbers in the pop-up modal
function displayRecentlyCalledNumbers() {
    if (!recentlyCalledNumbers) return;
    recentlyCalledNumbers.innerHTML = "";
    for (var i = 0; i < calledNumbers.length; i++) {
        var number = calledNumbers[i];
        var numberElement = document.createElement('div');
        numberElement.classList.add('last-called-num-view-all');
        var letter = 'B';
        if (number <= 15) {
            letter = 'B';
        }
        else if (number <= 30) {
            letter = 'I';
        }
        else if (number <= 45) {
            letter = 'N';
        }
        else if (number <= 60) {
            letter = 'G';
        }
        else if (number <= 75) {
            letter = 'O';
        }
        numberElement.textContent = letter + " " + number;
        numberElement.setAttribute('data-letter', letter);
        recentlyCalledNumbers.appendChild(numberElement);
    }
}

// Add a click event listener to the "View All Called" button (only if element exists)
if (viewAllCalledButton) {
    viewAllCalledButton.addEventListener("click", function () {
        // Display the pop-up modal
        if (viewAllCalledModal) viewAllCalledModal.style.display = "block";
        var resultContainer = document.getElementById("blur-background");
        if (resultContainer) resultContainer.style.display = "block";
        // Call the function to display recently called numbers
        displayRecentlyCalledNumbers();
    });
}

// Add a click event listener to close the pop-up modal (only if element exists)
if (closeViewAllCalled) {
    closeViewAllCalled.onclick = function () {
        if (viewAllCalledModal) viewAllCalledModal.style.display = "none";
        var resultContainer = document.getElementById("blur-background");
        if (resultContainer) resultContainer.style.display = "none";
    };
}
