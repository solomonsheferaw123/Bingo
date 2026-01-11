// --- SESSION MANAGEMENT ---
const currentUser = localStorage.getItem('currentUser') || 'Guest';
document.querySelector('.user-info').innerHTML = `Hello, ${currentUser}`;

// --- DATA PERSISTENCE ---
// Load shops from localStorage or use defaults
let shops = JSON.parse(localStorage.getItem('dallol_shops')) || [
    { name: 'Shop Alpha', percent: '20%', prepaid: 'Yes', balance: '430.00', totalEarning: '2,480.00' }
];

// Function to save shops to localStorage
function saveShops() {
    localStorage.setItem('dallol_shops', JSON.stringify(shops));
}

// Function to render table
function renderTable() {
    const tableBody = document.querySelector('#gameTable tbody');
    tableBody.innerHTML = ''; // Clear existing

    shops.forEach((shop, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${shop.name}</td>
            <td>${shop.percent || '20%'}</td>
            <td><span class="status-yes">${shop.prepaid || 'No'}</span></td>
            <td>${shop.balance || '0.00'}</td>
            <td>${shop.totalGames || '0'}</td>
            <td>${shop.totalEarning || '0.00'}</td>
            <td>0</td>
            <td>0.00</td>
            <td>
                <button class="action-btn edit" onclick="editShop(${index})">Edit</button>
                <button class="action-btn deposit" onclick="openDeposit(${index})">Deposit</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });
}

// --- ACTIONS ---

// Add New Shop
document.getElementById('add-new-shop-submit').onclick = function (e) {
    e.preventDefault();
    const form = document.getElementById('add-new-shop-form');
    const name = form.querySelector('input[name="name"]').value;

    if (!name) { alert("Enter shop name"); return; }

    const newShop = {
        name: name,
        percent: form.querySelector('input[name="percentage"]').value + '%',
        prepaid: form.querySelector('input[name="prepaid"]').checked ? 'Yes' : 'No',
        balance: '0.00',
        totalEarning: '0.00',
        totalGames: '0'
    };

    shops.push(newShop);
    saveShops();
    renderTable();

    alert(`Shop "${name}" created! You can now login using this name.`);
    document.getElementById('blur-background').style.display = 'none';
    form.reset();
};

// Open Deposit Modal
let currentEditIndex = -1;
window.openDeposit = (index) => {
    currentEditIndex = index;
    document.getElementById('blur-background').style.display = 'block';
    document.getElementById('add-balance').style.display = 'block';
    document.getElementById('add-new-shop').style.display = 'none';
    document.getElementById('add-balance-name').value = shops[index].name;
};

// Submit Deposit
document.getElementById('add-balance-submit').onclick = function (e) {
    e.preventDefault();
    const amount = parseFloat(document.getElementById('amount').value);

    if (amount > 0 && currentEditIndex > -1) {
        let currentBalance = parseFloat(shops[currentEditIndex].balance.replace(/,/g, ''));
        shops[currentEditIndex].balance = (currentBalance + amount).toFixed(2);
        saveShops();
        renderTable();
        alert("Balance Updated!");
        document.getElementById('blur-background').style.display = 'none';
    }
};

// Initial Render
renderTable();

// Table Filter (Search)
window.filterTable = function () {
    const input = document.getElementById("searchBox");
    const filter = input.value.toUpperCase();
    const rows = document.querySelectorAll("#gameTable tbody tr");

    rows.forEach(row => {
        const text = row.innerText.toUpperCase();
        row.style.display = text.indexOf(filter) > -1 ? "" : "none";
    });
}
