@echo off
rd /S /Q .\bin\Debug\nginx\site
xcopy /E /I /Y ".\nginx\site" ".\bin\Debug\nginx\site"
cd .\bin\Debug\
call startup.bat