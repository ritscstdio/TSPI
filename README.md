# TSPI Web Application

This is the web application for Tulay sa Pag-unlad, Inc., a financial institution focused on empowering communities through financial inclusion and sustainable development.

## Local Development Setup

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache web server (XAMPP is recommended for Windows users)
- Composer (for dependency management)

### Installation with XAMPP
1. Clone this repository into your XAMPP htdocs folder:
   ```
   git clone https://github.com/ritscstdio/TSPI.git /path/to/xampp/htdocs/TSPI
   ```

2. Create a database named `railway` in your MySQL server

3. Import the database schema from the SQL file provided (contact administrator for access)

4. Start the Apache and MySQL services in XAMPP

5. Access the application at http://localhost/TSPI

### Installation with Docker
1. Clone this repository:
   ```
   git clone https://github.com/ritscstdio/TSPI.git
   cd TSPI
   ```

2. Create a `.env` file with the following variables:
   ```
   DB_HOST=db
   DB_USER=root
   DB_PASS=your_password
   DB_NAME=railway
   DB_PORT=3306
   ```

3. Run the Docker containers:
   ```
   docker-compose up -d
   ```

4. Access the application at http://localhost

## Railway Deployment

This application is configured for easy deployment to Railway.

### Deployment Steps
1. Fork this repository to your GitHub account

2. Sign up for Railway at https://railway.app/

3. Create a new project in Railway

4. Add a MySQL database service to your project

5. Add a web service and connect it to your GitHub repository
   - Railway will automatically detect the Dockerfile and use it

6. Set up the environment variables in the Railway dashboard with your database credentials

7. Railway will build and deploy your application automatically

8. Access your application at the URL provided by Railway

### Environment Variables for Railway
Set these variables in the Railway dashboard:
- `DB_HOST` - Database hostname (provided by Railway)
- `DB_USER` - Database username (provided by Railway)
- `DB_PASS` - Database password (provided by Railway)
- `DB_NAME` - Database name (provided by Railway)
- `DB_PORT` - Database port (provided by Railway)

## Features
- Blog content management
- Responsive design
- User authentication and authorization
- Media uploads
- And more...

## Media Handling

The application handles two types of media content:
- Static assets (`src/assets/`) - Part of the codebase
- User-uploaded media (`uploads/media/`) - Dynamically uploaded content

### Troubleshooting Media Issues

If you encounter issues with media not loading properly on your deployment:

1. First, verify that all files exist in the correct location on your server
2. Use the admin tools to diagnose and fix issues:
   - `fix-missing-media.php` - Check which media files are missing and update database references
   - `upload-missing-media.php` - Download missing media files from GitHub to your server

### GitHub Integration

The system has a fallback mechanism that attempts to load media from the GitHub repository when it's not available on the server. This is particularly useful for live deployments where media uploads from local development might not have been transferred.

To configure this fallback mechanism:
1. Make sure your GitHub repository is public
2. Update the GitHub URL pattern in `includes/header.php` to match your repository

## Documentation
Additional documentation can be found in the `documentation` folder.

## Contact
For more information, contact partners@tspi.org or tspicustomercare@tspi.org.
