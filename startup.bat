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

:: Check if --noconsole argument is passed
if "%~1"=="--noconsole" (
  :: Start glowberry-webserver.exe without console
  start /b "" glowberry-webserver.exe
) else (
  :: Start glowberry-webserver.exe normally
  start "" glowberry-webserver.exe
)

:: Open google.com in the browser
start "" https://google.com

endlocal
