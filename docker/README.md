# Docker Setup for TSPI Project

This directory contains Docker configuration files for the TSPI project.

## Files

- `apache.conf` - Apache configuration for the Docker container
- `custom-php.ini` - Custom PHP settings for the Docker container

## Environment Variables

Create a `.env` file in the root directory with the following variables:

```
# Database configuration
DB_HOST=crossover.proxy.rlwy.net
DB_USER=root
DB_PASS=mQXhlFdbZwNPUnyQBGWSBKPHOMajvArt
DB_NAME=railway
DB_PORT=50379

# Site URL - adjust based on Railway deployment URL
RAILWAY_STATIC_URL=https://your-app-name.up.railway.app

# SendGrid API Key (replace with your actual key)
SENDGRID_API_KEY=SG.your_sendgrid_api_key
```

## Local Development

For local development, use the following `.env` configuration:

```
# Database configuration
DB_HOST=db
DB_USER=root
DB_PASS=your_password_here
DB_NAME=railway
DB_PORT=3306

# Site URL
RAILWAY_STATIC_URL=http://localhost

# SendGrid API Key (replace with your actual key)
SENDGRID_API_KEY=SG.your_sendgrid_api_key
``` 