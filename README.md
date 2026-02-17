# Ikwerre Development Association (IDA) Website

Official website for the Ikwerre Development Association.

## Features
- **Public Information:** History, Mission, Vision, Branches, and Executives.
- **Digital Library:** Support for PDF documents, Images, and Video (YouTube) embeds.
- **Membership System:** Online application, login, and member profile.
- **Admin Dashboard:** Full management of members, library items, and executives.

## Tech Stack
- Frontend: HTML5, CSS3, Bootstrap 5 (CDN), JavaScript.
- Backend: PHP.
- Database: MySQL.

## Installation
1. **Database Setup:**
   - Create a MySQL database named `ida_db`.
   - Import the `schema.sql` file into your database.
2. **Configuration:**
   - Copy `.env.example` to `.env` and update your database credentials and app settings.
   - If you skip `.env`, defaults are used from `includes/config.php`.
3. **Web Server:**
   - Place the files in your web server's root directory (e.g., `public_html` or `www`).
   - Ensure the `uploads/` directory and its subdirectories are writable by the web server.

## Admin Access
- URL: `yourdomain.com/admin/login.php`
- Default Username: `admin`
- Default Password: `admin123` (Please change this after login!)

## Directory Structure
- `admin/`: Administrative dashboard files.
- `css/`: Custom stylesheets.
- `img/`: Static images and logo.
- `includes/`: Common PHP components and utilities.
- `js/`: Frontend JavaScript.
- `uploads/`: Dynamic content uploads (Members, Library, Executives).
- `test/`: CLI scripts for quick DB actions.
- `tests/`: Minimal CLI test harness.
