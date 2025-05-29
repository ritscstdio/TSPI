@echo off
echo Setting up local development environment...

REM Create docker directory if it doesn't exist
if not exist docker mkdir docker

REM Create .env file if it doesn't exist
if not exist .env (
  echo Creating .env file from .env.example
  copy .env.example .env
  echo Please update your .env file with your actual credentials
)

REM Create necessary directories
if not exist uploads mkdir uploads
if not exist uploads\profile_pics mkdir uploads\profile_pics
if not exist temp mkdir temp
if not exist logs mkdir logs

echo Starting Docker containers...
docker-compose up -d

echo Local development environment setup complete!
echo Your application should be accessible at http://localhost 