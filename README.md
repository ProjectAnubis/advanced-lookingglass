# Advanced Looking Glass

[Click here for the Turkish version](README.tr.md)

Advanced Looking Glass is a production-grade, modular, and extensible platform that integrates various advanced network testing, analysis, control, and monitoring functionalities into a single solution. This project features real-time data streaming, advanced DNS operations, port scanning, speed testing, live output streaming via WebSocket, RESTful API support, JWT/OAuth2-based security, automatic alerting, and distributed architecture support.

Advanced Looking Glass is a comprehensive network testing platform that integrates cutting-edge features into a single, production-ready solution. Accessible via a modern web interface and a RESTful API, it supports real-time data streaming, advanced analytics, automated alerting, and distributed processing to meet large-scale network monitoring and analysis needs.

## Features

### Network Testing Methods
- **Ping Tests:** IPv4 and IPv6 ping tests.
- **Advanced MTR (My Traceroute):** Performs detailed MTR tests with per-hop statistics (average, best, worst, and standard deviation), DNS cache optimization, and asynchronous reverse DNS lookups.
- **Traceroute:** Supports both IPv4 and IPv6 traceroute tests.
- **Port Scanning:** Uses nmap for comprehensive port scans.
- **Speed Testing:** Conducts both incoming and outgoing speed tests using iperf3.

### Security
- **CSRF Protection & Input Validation:** Uses CSRF tokens and `escapeshellarg()` to prevent command injection.
- **SQL Injection Protection:** Utilizes PDO prepared statements.
- **API Security:** Provides JWT/OAuth2-based authentication (via `auth.php`).
- **Optional 2-Factor Authentication (2FA):** Can be integrated for additional security.

### Real-Time Output & Communication
- **WebSocket Streaming:** Implements a Ratchet-based WebSocket server (`websocket.php`) for low-latency, continuous data streaming.
- **RESTful API:** Offers API endpoints (`api.php`) that return JSON-formatted output.

### Dashboard & Analytics
- **Graphical Reporting:** Uses Chart.js (via `dashboard.php`) to display graphs and analytical reports.
- **Trend Analysis:** Provides summary reports and trend analysis for network performance.

### Distributed Architecture & Automated Alerts
- **Message Queue Integration:** Supports RabbitMQ (see `distributed.php` example) for centralized log management and asynchronous task processing.
- **Automated Alerts:** Utilizes PHPMailer (via `alerts.php`) to send email/SMS notifications in critical situations.

## Installation

1. **Server Requirements:**
   - PHP 7.4+ (recommended)
   - MySQL or a compatible database (with PDO support)
   - Required system tools: iperf3, nmap, mtr, traceroute, etc.
   - Composer (for PHP dependency management)

2. **Database Setup:**
   - Run the `create_table.sql` script in your database to create the `lg_logs` table.
   - Update the database connection settings in `config.php` (DB_DSN, DB_USER, DB_PASS).

3. **Composer Dependencies:**
   - In your project directory, run the following commands:
     ```bash
     composer require react/dns
     composer require cboden/ratchet
     composer require firebase/php-jwt
     composer require phpmailer/phpmailer
     composer require php-amqplib/php-amqplib
     ```
   - This will install the necessary packages into the `vendor/` directory.

4. **Web Server Configuration:**
   - Configure your web server (Apache, Nginx, etc.) to use the project’s root directory.
   - Set up any required .htaccess or server configuration files.

5. **Running the WebSocket Server:**
   - Start the WebSocket server by running:
     ```bash
     php websocket.php
     ```
   - Clients can then connect to the WebSocket server on the defined port (e.g., 8080).

## Usage

- **Web Interface:**
  - Open `index.php` in your browser to start network tests.
  - Enter the target (IP address or hostname) and select the desired test method.
  - Results will be displayed in real-time using WebSocket or fetch streaming.

- **RESTful API:**
  - Send GET or POST requests to `api.php` with your API key (LG_API_KEY).
  - Example request:
    ```
    GET /api.php?api_key=YOUR_SECURE_API_KEY&method=ping&target=8.8.8.8
    ```
  - You will receive a JSON response with the test output.

- **Dashboard & Analytics:**
  - Access `dashboard.php` to view graphical reports and trend analyses of your network tests.

- **Distributed Architecture:**
  - For multi-server environments, refer to the `distributed.php` example to integrate RabbitMQ for centralized log management and asynchronous processing.

## Advanced Features & Notes

- **WebSocket Integration:**  
  The Ratchet-based WebSocket server enables low-latency live data streaming compared to fetch-based methods.

- **Enhanced DNS Operations:**  
  The parser includes asynchronous reverse DNS lookups using AsyncDNSResolver for performance improvements and error handling.

- **Speed Testing:**  
  Iperf3 commands safely substitute the `{target}` placeholder to run separate tests for incoming and outgoing traffic.

- **Security:**  
  All inputs are validated, CSRF tokens are used, and command injections are prevented with `escapeshellarg()`. JWT-based API authentication is also implemented.

- **Automated Alerts:**  
  The system can be configured via `alerts.php` to send email/SMS notifications when critical network anomalies occur.

- **Distributed Architecture:**  
  The `distributed.php` example demonstrates how to use RabbitMQ for centralized logging and asynchronous task handling.

## Project Status & Verification

- **Code Structure:**  
  Modules are organized into separate files with proper namespace usage. Dependencies are managed via Composer.

- **Security:**  
  The project employs CSRF protection, input validation, prepared statements, and JWT authentication for secure operation.

- **Real-Time Streaming:**  
  Both WebSocket and fetch-based streaming are implemented for live output.

- **Asynchronous Operations:**  
  AsyncDNSResolver provides an example of asynchronous reverse DNS lookups, and the system can be expanded for full asynchronous processing.

- **System Requirements:**  
  Ensure that external commands (iperf3, nmap, mtr, traceroute, etc.) are installed on your system.

**Note:** This project is still under development. Unexpected errors may occur, and some features might not yet be fully stable.