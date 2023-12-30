@echo off
rd /S /Q .\bin\Debug\nginx\site
rd /S /Q .\bin\Debug\nginx\conf
xcopy /E /I /Y ".\nginx\site" ".\bin\Debug\nginx\site"
xcopy /E /I /Y ".\nginx\conf" ".\bin\Debug\nginx\conf"

del /F /Q ".\bin\Debug\startup.bat"
xcopy /Y "startup.bat" ".\bin\Debug\"

cd .\bin\Debug\
call startup.bat