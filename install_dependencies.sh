#!/bin/bash

echo "Installing TSPI Website dependencies..."
echo

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Composer not found. Please install Composer from https://getcomposer.org/download/"
    echo "and make sure it's in your PATH."
    echo
    echo "After installing Composer, run this script again."
    echo
    exit 1
fi

echo "Found Composer, installing dependencies..."
echo

# Run Composer install
composer install

echo
if [ $? -eq 0 ]; then
    echo "Dependencies installed successfully!"
    echo
    echo "You can now use the membership application PDF generation feature."
else
    echo "There was an error installing dependencies."
    echo "Please try running 'composer install' manually."
fi

echo
echo "If you're experiencing issues with TCPDF and PNG images, ensure your PHP has the GD extension enabled:"
echo "1. For Ubuntu/Debian: sudo apt-get install php-gd"
echo "2. For CentOS/RHEL: sudo yum install php-gd"
echo "3. For macOS with Homebrew: brew install php@7.4 (or your PHP version)"
echo "4. Restart your web server after installation"
echo
echo "If you're experiencing issues with PDF templates, make sure FPDI is installed:"
echo "1. Run 'composer require setasign/fpdi' in the terminal"
echo "2. Check that the setasign/fpdi library is in your vendor directory"
echo
echo "Installation completed!" 