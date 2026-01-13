# Dallol Bingo - Project Documentation

This document provides a comprehensive overview of the **Dallol Bingo** application, its structure, technology stack, and core functionalities.

---

## 1. Project Overview
**Dallol Bingo** is a web-based Bingo management system designed for shops and agents. It features a premium real-time game interface, administrative dashboards, and automated balance management. The application supports multiple languages and audio feedback for a seamless gaming experience.

---

## 2. Technology Stack
- **Backend:** PHP 8.x
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla & jQuery)
- **Styling:** Custom CSS with [Boxicons](https://boxicons.com/) for iconography.
- **Animations:** Canvas-confetti for win celebrations.
- **Server:** Apache/Nginx (Production) or PHP Built-in Server (Development).

---

## 3. Project Structure

```text
Dallol_Bingo/
├── api/                    # Backend API Endpoints (JSON)
│   ├── block_card.php      # Marks a card as blocked/inactive
│   ├── cards.json          # Bingo card configuration/definitions
│   ├── check.php           # Validates winning patterns for a card
│   ├── finish_game.php     # Finalizes game and updates balances
│   ├── get_today_stats.php # Fetches earnings and game count for today
│   ├── start_game.php      # Initializes a new game session
│   └── update_settings.php # Updates game parameters (jackpot, etc.)
├── static/                 # Assets (CSS, JS, Audio, Images)
│   ├── account/            # Styles and scripts for user profiles
│   ├── agent/              # Styles and scripts for agent dashboard
│   ├── game/               # Core game assets (audio, icons, logic)
│   │   ├── audio/          # Voice call files (1-75) and shuffle sound
│   │   ├── css/            # Game-specific styling (base.css, styles.css)
│   │   ├── icon/           # Logos, money icons, etc.
│   │   └── js/             # Main game logic (script_v2.js)
├── admin.php               # Administrative control panel
├── config.php              # Global configuration (DB connection, Auth helpers)
├── dashboard.php           # User/Agent landing page with statistics
├── database.sql            # SQL schema and initial seed data
├── index.php               # The main Bingo game interface
├── login.php               # User authentication page
├── logout.php              # Session termination
├── new_game.php            # Game setup page (stake, players)
├── settings.php            # Application and game configuration
└── style.css               # Global application styles
```

---

## 4. Key Components Explained

### 核心 (Core Logic)
- **`index.php`**: The heart of the application. It renders the Bingo board, handles real-time game updates, and displays statistics like Current Stake and Win Price.
- **`script_v2.js`**: Managed by jQuery, this file handles the game loop, number calling, audio synchronization, and AJAX calls to the backend.
- **`config.php`**: Centralizes database connectivity using PDO and provides utility functions like `isLoggedIn()`, `isAdmin()`, and `redirect()`.

### API Layer (`/api`)
- All game-related interactions happen asynchronously through these endpoints. This ensures the UI remains responsive without page reloads.

### Data Management
- **`database.sql`**: Defines the structure for `users`, `shops`, `games`, and `transactions`. It also includes sample credentials (`admin`/`admin123` and `agent1`/`agent123`).

---

## 5. Database Schema

| Table | Description |
| :--- | :--- |
| **`shops`** | Stores shop details, commission percentages, and billing type (Prepaid/Postpaid). |
| **`users`** | Handles authentication, roles (`admin`, `agent`), and wallet balances. |
| **`games`** | Records every game session, including stake, pool, house cut, and winning card. |
| **`transactions`** | Logs all financial movements (deposits, stakes, wins). |

---

## 6. Setup & Execution

### Prerequisites
- PHP 7.4+ or 8.x
- MySQL / MariaDB Server
- A modern web browser

### Local Development Run
1. **Database Setup**: Import `database.sql` into your MySQL server.
2. **Configuration**: Ensure `config.php` has the correct database credentials.
3. **Start Server**: 
   ```powershell
   php -S localhost:8352
   ```
4. **Access**: Open `http://localhost:8352` in your browser.

---

## 7. Features
- **Auto-Play**: Configurable calling speeds (3s to 12s per number).
- **Audio Announcements**: Real-time voice calls for numbers.
- **Responsive Layout**: Designed to work on both desktop and tablet screens.
- **Jackpot System**: Customizable jackpot accumulation and triggering.
- **Dark Mode**: Premium dark-themed UI for better visibility in shop environments.

---
**Documentation generated on:** January 13, 2026.
