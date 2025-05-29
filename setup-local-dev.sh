#!/bin/bash

# Create docker directory if it doesn't exist
mkdir -p docker

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
  echo "Creating .env file from .env.example"
  cp .env.example .env
  echo "Please update your .env file with your actual credentials"
fi

# Create necessary directories with proper permissions
mkdir -p uploads
mkdir -p uploads/profile_pics
mkdir -p temp
mkdir -p logs

# Set proper permissions
chmod -R 775 uploads
chmod -R 775 temp
chmod -R 775 logs

echo "Starting Docker containers..."
docker-compose up -d

echo "Local development environment setup complete!"
echo "Your application should be accessible at http://localhost" 