@echo off
echo Starting Laravel Server...
echo ---------------------------------------
echo JANGAN TUTUP JENDELA INI (DO NOT CLOSE THIS WINDOW)
echo ---------------------------------------
start http://127.0.0.1:8000
C:\xampp\php\php.exe artisan serve
pause
