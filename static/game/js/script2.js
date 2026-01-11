const container = document.getElementById('container');
const startButton = document.getElementById('start-button');
const gameForm = document.getElementById('game-form');
const numbers = [];
let selectedNumbers = [];
var other_selected = [];
const moreNumber = document.getElementById('more');
const clearbtn = document.getElementById('clear');

const bonus = document.getElementById('bonus');
const free = document.getElementById('free');
const cashier = document.getElementById('cashier').innerText;
var patterns = [];
var selectedLanguage = "mm";
let speech = new SpeechSynthesisUtterance();
let voices = [];

function loadVoices() {
    voices = window.speechSynthesis.getVoices();
    speech.voice = voices[0];
}

loadVoices();
if (window.speechSynthesis.onvoiceschanged !== undefined) {
    window.speechSynthesis.onvoiceschanged = loadVoices;
}

function setCookie(cookieName, cookieValue, expirationDays) {
    localStorage.setItem(cookieName, cookieValue);
}

function getCookie(cookieName) {
    return localStorage.getItem(cookieName);
}

function deleteCookie(cookieName) {
    localStorage.removeItem(cookieName);
}

clearbtn.addEventListener('click', () => {
    deleteCookie("selectedPlayers");
    var divs = container.querySelectorAll(".box");
    var boundary = 100;
    var buttonText = moreNumber.textContent;
    if (buttonText == "1-100") {
        boundary = 200;
    }
    for (var i = 0; i < boundary; i++) {
        if (selectedNumbers.includes(i + 1)) {
            divs[i].classList.remove('selected');
            if (cashier == "True") {
                remove_player(i + 1);
            }
        }
    }
    selectedNumbers = [];
});

moreNumber.addEventListener('click', () => {
    var buttonText = moreNumber.textContent;
    if (buttonText == "100-200") {
        moreNumber.textContent = "1-100";
        add();
    } else {
        moreNumber.textContent = "100-200";
        remove();
    }

});

function add() {
    for (let i = 101; i <= 200; i++) {
        const box = document.createElement('div');
        box.textContent = i;
        box.classList.add('box');

        box.addEventListener('click', () => {
            if (selectedNumbers.includes(i)) {
                selectedNumbers = selectedNumbers.filter(num => num !== i);
                box.classList.remove('selected');
                if (cashier == "True") {
                    remove_player(i);
                }
            } else {
                selectedNumbers.push(i);
                box.classList.add('selected');
                if (cashier == "True") {
                    add_player(i);
                }
            }
            startButton.disabled = selectedNumbers.length === 0;
            updateTotalSelected();
        });

        container.appendChild(box);
    }
}
function remove() {
    var divs = container.querySelectorAll(".box");
    for (var i = 100; i < 200; i++) {
        container.removeChild(divs[i]);
    }
}


// Handle custom select dropdown
document.querySelector('.select-box').addEventListener('click', function () {
    const selectBox = this.parentElement;
    selectBox.classList.toggle('open'); // Toggle open class to show/hide options
});

// Handle selecting an option
document.querySelectorAll('.option').forEach(option => {
    option.addEventListener('click', function () {
        // Toggle 'selected' class on the clicked option
        this.classList.toggle('selected');

        // Update the displayed selected patterns
        const selectedPatterns = Array.from(document.querySelectorAll('.option.selected')).map(option => option.textContent).join(', ');
        document.getElementById('selectedPatterns').textContent = selectedPatterns || 'Choose Patterns';

        // Optionally save the selected values to cookies or send to server
        patterns = Array.from(document.querySelectorAll('.option.selected')).map(option => option.dataset.value);

    });
});

// Create number boxes


// ... Your existing JavaScript ...

// Inside the form submission event listener
const game_id = document.getElementById('game').innerText;
const submitBtn = document.getElementById('start-button');

gameForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (selectedNumbers.length === 0) return;

    if (submitBtn.innerText.trim() === "Confirm") {
        // Play sound
        // if (selectedLanguage=='am'){
        //     filePath = "static/game/audio/start.mp3";
        //     var audio = new Audio(filePath);
        //     audio.play();
        // }else 

        if (selectedLanguage == 'mm' || selectedLanguage == 'mm2' || selectedLanguage == 'am') {
            var audio = new Audio("static/game/audio/check.mp3");
            audio.play();
        } else {
            speech.text = "Confirm your Card Selection";
            window.speechSynthesis.speak(speech);
        }

        // Disable button to prevent re-click
        submitBtn.disabled = true;

        // Wait 5 seconds
        await new Promise(resolve => setTimeout(resolve, 5000));

        // Enable and change text to "Start Game"
        submitBtn.disabled = false;
        submitBtn.innerText = "Start Game";
        return;
    }

    // Continue if "Start Game"
    if (cashier === "True") {
        socket.send(JSON.stringify({
            type: 'start_game',
            stake: document.getElementById("stake").value,
            bonus: bonus.checked,
            free: free.checked,
        }));
    } else {
        // Send data to PHP API to record the game and deduct balance
        fetch('api/start_game.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'stake': document.getElementById("stake").value,
                'players': selectedNumbers.length
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Save game-related info in cookies for index.php
                    setCookie("GameID", data.game_id, 7);
                    setCookie("WinAmount", data.win_amount, 7);
                    setCookie("HouseCut", data.house_cut, 7);
                    setCookie("Stake", document.getElementById("stake").value, 7);
                    setCookie("Bonus", bonus.checked, 7);
                    setCookie("Free", free.checked, 7);
                    setCookie("Patterns", encodeURIComponent(JSON.stringify(patterns)), 7);
                    setCookie("selectedPlayers", encodeURIComponent(JSON.stringify(selectedNumbers)), 7);

                    // Redirect to index.php page
                    window.location.href = 'index.php?game_id=' + data.game_id;
                } else {
                    alert('Error: ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerText = "Confirm";
                }
            })
            .catch(error => {
                console.error('Error starting game:', error);
                alert('Failed to connect to the server.');
                submitBtn.disabled = false;
            });
    }
});

function updateTotalSelected() {
    deleteCookie("selectedPlayers");
    setCookie("selectedPlayers", encodeURIComponent(JSON.stringify(selectedNumbers)), 7);
    document.getElementById('noplayer').value = selectedNumbers.length;

    // Calculate and show exact win money
    const stake = parseFloat(document.getElementById('stake').value) || 0;
    const players = selectedNumbers.length;
    const totalPool = stake * players;

    // Get rules from DOM
    const cutBoundaryElement = document.getElementById('shop-cut-boundary');
    const cutPercentageElement = document.getElementById('shop-cut-percentage');

    const cutBoundary = cutBoundaryElement ? parseFloat(cutBoundaryElement.innerText) : 50;
    const cutRate = cutPercentageElement ? parseFloat(cutPercentageElement.innerText) : 0.20;

    let winAmount = totalPool;

    // Rule: Apply cut only if pool > boundary
    if (totalPool > cutBoundary) {
        const commissionPool = totalPool * cutRate;
        winAmount = totalPool - commissionPool;
    }

    document.getElementById('win').value = winAmount.toFixed(2);
}

// Update win amount when stake changes
document.getElementById('stake').addEventListener('input', updateTotalSelected);

function get_game_stat() {
    $.ajax({
        url: "/get_game_stat/",  // Replace with your Django view URL
        type: "GET",
        data: {
            game: game_id
        },
        success: function (response) {
            if (response.message === 'None') {

            } else {
                selectedNumbersStr = Array.isArray(response.main_selected) ? response.main_selected : [];
                selectedNumbers = selectedNumbersStr.map(str => parseInt(str, 10));
                update_view(response.selected_players);
            }
        },
        error: function (xhr, status, error) {
            alert("Failed to get data");
        }
    });
}

function update_view(players) {
    var boxes = document.querySelectorAll('.box');
    other_selected = [];

    boxes.forEach(function (box) {
        var innerTextStr = box.innerText.trim(); // Get inner text and trim whitespace
        var innerText = parseInt(innerTextStr, 10);
        // Check if inner text is in the numbersToMatch array
        if (selectedNumbers.includes(innerText)) {
            // Do something with the matching box element, e.g., add a class
            box.classList.add('selected');
        } else {
            box.className = "box";
        }

    });
    var colornum = 1;
    players.forEach(function (cashier) {
        var arrayStr = Array.isArray(cashier.selected_players) ? cashier.selected_players : [];
        var array = arrayStr.map(str => parseInt(str, 10));
        other_selected.push(...array);
        var color = "color" + colornum;
        colornum++;
        for (let element of array) {
            boxes[element - 1].classList.add('selected');
            boxes[element - 1].classList.add(color);
            boxes[element - 1].classList.add('blured');
        }

    });

    startButton.disabled = selectedNumbers.length === 0 && other_selected.length === 0;

}

window.onload = function () {
    for (let i = 1; i <= 100; i++) {
        const box = document.createElement('div');
        box.textContent = i;
        box.classList.add('box');

        box.addEventListener('click', () => {
            if (selectedNumbers.includes(i)) {
                selectedNumbers = selectedNumbers.filter(num => num !== i);
                box.classList.remove('selected');
                if (cashier == "True") {
                    remove_player(i);
                }
            } else {
                selectedNumbers.push(i);
                box.classList.add('selected');
                if (cashier == "True") {
                    add_player(i);
                }
            }
            startButton.disabled = selectedNumbers.length === 0;
            updateTotalSelected();
        });

        container.appendChild(box);
    }

    var stake = getCookie("Stake");
    var minStakeElement = document.getElementById('shop-min-stake');
    var minStake = minStakeElement ? parseFloat(minStakeElement.innerText) : 20;

    if (stake != null) {
        var stakeValue = parseFloat(stake);
        if (stakeValue >= minStake) {
            document.getElementById("stake").value = stake;
        } else {
            document.getElementById("stake").value = minStake;
        }
    }

    var b = getCookie("Bonus");
    console.log(b);
    if (b != null) {
        bonus.checked = (b === "true");;
        console.log(bonus.checked);
    }

    var f = getCookie("Free");
    console.log(f);
    if (f != null) {
        free.checked = (f === "true");;
        console.log(free.checked);
    }

    var cookieLanguage = getCookie("selectedLanguage");
    if (cookieLanguage != null) {
        if (cookieLanguage == "am") {
            selectedLanguage = "am";
        } else if (cookieLanguage == "mm") {
            selectedLanguage = "mm";
        } else {
            selectedLanguage = 0;
        }
    }

    const selectedPatterns = getCookie("Patterns");
    if (selectedPatterns) {
        let selectedValues = [];

        try {
            const raw = getCookie('Patterns');
            if (raw) {
                selectedValues = JSON.parse(decodeURIComponent(raw));
            }
        } catch (err) {
            console.error("Error parsing Patterns cookie:", err);
            selectedValues = [];
        }

        // Loop through all options and mark those that are selected
        document.querySelectorAll('.option').forEach(option => {
            if (selectedValues.includes(option.dataset.value)) {
                option.classList.add('selected'); // Add 'selected' class to pre-selected options
            }
        });

        // Update the displayed selected patterns
        const selected = selectedValues.map(value => {
            return document.querySelector(`.option[data-value="${value}"]`).textContent;
        }).join(', ');

        document.getElementById('selectedPatterns').textContent = selected || 'Choose Patterns';

        patterns = Array.from(document.querySelectorAll('.option.selected')).map(option => option.dataset.value);
    }

    // Restore selected players (cards)
    var selectedPlayersStr = getCookie("selectedPlayers");
    if (selectedPlayersStr != null && selectedPlayersStr !== "" && selectedPlayersStr !== "[]") {
        try {
            var savedPlayers = JSON.parse(decodeURIComponent(selectedPlayersStr));
            selectedNumbers = savedPlayers;

            const containsInRange = savedPlayers.some(num => num > 100 && num <= 200);
            if (containsInRange) {
                moreNumber.textContent = "1-100";
                add(); // This creates boxes 101-200
            }

            // Highlight all selected boxes
            const allBoxes = container.querySelectorAll(".box");
            allBoxes.forEach(box => {
                const num = parseInt(box.textContent);
                if (savedPlayers.includes(num)) {
                    box.classList.add('selected');
                }
            });

            startButton.disabled = selectedNumbers.length === 0;
            updateTotalSelected();
        } catch (e) {
            console.error("Error restoring selected players:", e);
        }
    }
    const inputElement = document.getElementById('stake');

    if (cashier == "True") {
        inputElement.addEventListener('input', (event) => {
            const currentValue = event.target.value;
            // Call your function here
            handleInputChange(currentValue);
        });

        setInterval(get_game_stat, 1000);
    }

};


function handleInputChange(value) {
    // Your logic here
    var game = document.getElementById('game').innerHTML;
    $.ajax({
        url: "/update_stake/",  // Replace with your Django view URL
        type: "GET",
        data: {
            stake: value,
            game: game,
            // Add more parameters as needed
        },
        success: function (response) {
            if (response.status === 'success') {
                console.log(response.message);
            } else if (response.status === 'failure' || response.status === 'error') {
                alert(response.message);
            }
        },
        error: function (xhr, status, error) {
            alert("Failed to get data");
        }
    });
}



function remove_player(card) {
    var game = document.getElementById('game').innerHTML;
    $.ajax({
        url: "/remove_player/",  // Replace with your Django view URL
        type: "GET",
        data: {
            card: card,
            game: game,
            // Add more parameters as needed
        },
        success: function (response) {
            if (response.status === 'success') {
                console.log(response.message);
            } else if (response.status === 'failure' || response.status === 'error') {
                alert(response.message);
            }
        },
        error: function (xhr, status, error) {
            alert("Failed to get data");
        }
    });
}

function add_player(card) {
    var game = document.getElementById('game').innerHTML;
    $.ajax({
        url: "/add_player/",  // Replace with your Django view URL
        type: "GET",
        data: {
            card: card,
            game: game,
            // Add more parameters as needed
        },
        success: function (response) {
            if (response.status === 'success') {
                console.log(response.message);
            } else if (response.status === 'failure' || response.status === 'error') {
                alert(response.message);
            }
        },
        error: function (xhr, status, error) {
            alert("Failed to get data");
        }
    });
}
