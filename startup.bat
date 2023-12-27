@echo off
setlocal

:: Check if glowberry-webserver.exe exists
if not exist glowberry-webserver.exe (
   echo Could not find 'glowberry-webserver.exe'. Please verify the file integrity and make sure that everything is in order.
   exit /b
)

:: Check if gbhelper.exe exists
if not exist gbhelper.exe (
   echo Could not find 'gbhelper.exe'. Please verify the file integrity and make sure that everything is in order.
   exit /b
)

:: Check if the php cgi exists
if not exist .\nginx\php\php-cgi.exe (
   echo Could not find 'php-cgi.exe'. Please verify the file integrity and make sure that everything is in order.
   exit /b
)

:: Check if --noconsole argument is passed
if "%~1"=="--noconsole" (
  :: Start glowberry-webserver.exe without console
  start /B "" glowberry-webserver.exe
) else (
  :: Start glowberry-webserver.exe normally
  start /B "" glowberry-webserver.exe
)

:: Open google.com in the browser
tasklist /fi "ImageName eq nginx.exe" /fo csv 2>NUL | find /I "nginx.exe">NUL
if not "%ERRORLEVEL%"=="0" (
	start /B "" php-fcgi.bat
	start /B /D ".\nginx" .\nginx.exe

)

endlocal
