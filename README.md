Berikut versi **final**, **lengkap**, dan **terstruktur rapi** dalam **satu file README.md**—siap pakai di project GitHub-mu:

---

````markdown
# SQ - Game Dashboard Web Application

SQ is a lightweight PHP web application that allows users to manage personal game collections by integrating with the [RAWG.io](https://rawg.io/apidocs) public API. The system features user authentication, email verification, and folder-based organization of video games. It is designed as a practical implementation of PHP development, external API consumption, and UI structuring.

## Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Security Notes](#security-notes)
- [Planned Enhancements](#planned-enhancements)
- [License](#license)
- [Author](#author)

## Features

- User registration with email verification (SMTP via PHPMailer)
- Secure user login and logout
- User profile display (name, email, phone, address)
- Game search using RAWG.io public API
- Folder system for organizing personal game collections
- Clean and responsive UI with Bootstrap

## Technology Stack

**Frontend**  
- HTML5  
- CSS3 (Bootstrap)

**Backend**  
- PHP (Procedural)  
- MySQL  

**Third-Party Services**  
- [RAWG.io API](https://rawg.io/apidocs)  
- PHPMailer (for sending verification emails)

## Project Structure

```text
phpmailer/
├── api_helper.php            # General API request utility
├── dashboard.php             # Main dashboard view after login
├── db_connection.php         # Database connection settings
├── folder_details.php        # Handles user-created game folders
├── game_folder_details.php   # Displays game data inside folders
├── login.php                 # Login logic and session handling
├── logout.php                # Session destroy and logout
├── rawg_api_helper.php       # RAWG.io API request handler
├── register.php              # User registration and email sending
├── verify.php                # Email verification logic
└── README.md                 # Project documentation
````

## Installation

### Prerequisites

* PHP >= 7.4
* MySQL or MariaDB
* Web server (Apache, NGINX, or PHP built-in server)
* Composer (optional, for PHPMailer)

### Steps

1. **Clone the repository**

   ```bash
   git clone https://github.com/adrian-dev-art/sq.git
   ```

2. **Set up your local database**

   * Create a new MySQL database.
   * Import your own schema containing tables for:

     * `users`
     * `folders`
     * `games`

3. **Configure database connection**

   * Open `phpmailer/db_connection.php`.
   * Set your DB credentials (`host`, `username`, `password`, `database`).

4. **Set up PHPMailer**

   * Edit SMTP settings inside `register.php`:

     ```php
     $mail->isSMTP();
     $mail->Host = 'smtp.mailtrap.io'; // or smtp.gmail.com
     $mail->SMTPAuth = true;
     $mail->Username = 'your_smtp_username';
     $mail->Password = 'your_smtp_password';
     $mail->SMTPSecure = 'tls';
     $mail->Port = 587;
     ```
   * Use a valid SMTP provider: Mailtrap (for testing), Gmail (for personal use), or SendGrid (for production).

5. **Run the application**

   ```bash
   php -S localhost:8000
   ```

   Then open your browser and go to:

   ```
   http://localhost:8000/phpmailer/register.php
   ```

## Configuration

* Ensure your SMTP setup is working (test using Mailtrap or Gmail).
* Optionally register for a RAWG.io API key (free).
* You may update the RAWG API base URL in `rawg_api_helper.php` if needed.
* Replace any hardcoded config values with environment variables for production.

## Usage

1. Register a new user via the registration form.
2. Check your inbox for a verification code and activate the account.
3. Log in to access the user dashboard.
4. Use the RAWG.io-powered search bar to find video games.
5. Create game folders (e.g., "Favorites", "RPG", "Wishlist") and organize your selections.

## Security Notes

This app is for demonstration and educational purposes. It is **not production-ready**. Major security flaws exist, including:

* Vulnerable to SQL Injection (no prepared statements)
* No CSRF protection
* Minimal server-side input validation
* No rate limiting
* Sensitive config stored in plain PHP files

For production deployment, hardening is mandatory.

## Planned Enhancements

* Refactor to MVC pattern (or adopt a lightweight framework)
* Migrate all SQL to PDO with prepared statements
* Implement profile and folder editing/deletion
* Add game image thumbnails (via RAWG.io assets)
* Add search pagination
* Use `.env` and dotenv PHP support for configuration
* Secure routes and form submissions

## License

This project is licensed under the [MIT License](LICENSE).

## Author

**Adrian**
GitHub: [adrian-dev-art](https://github.com/adrian-dev-art)
Location: Bandung, Indonesia

```

---

Let me know if you want:
- `.env.example` with all relevant keys
- `schema.sql` file (users, folders, games table)
- Docker setup (`Dockerfile`, `docker-compose.yml`)

I’ll ship them fast.
```
