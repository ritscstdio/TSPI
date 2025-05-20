@echo off
echo Installing PHP dependencies...
composer install
echo.
echo If you're experiencing issues with TCPDF and PNG images, ensure your PHP has the GD extension enabled:
echo 1. Open your php.ini file (usually in your XAMPP/PHP directory)
echo 2. Uncomment the line ";extension=gd" by removing the semicolon
echo 3. Restart your web server
echo.
echo If you're experiencing issues with PDF templates, make sure FPDI is installed:
echo 1. Run "composer require setasign/fpdi" in the command line
echo 2. Check that the setasign/fpdi library is in your vendor directory
echo.
echo Installation completed!
pause 