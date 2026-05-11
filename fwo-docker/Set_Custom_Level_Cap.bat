@echo off
setlocal enabledelayedexpansion

echo ===========================================
echo   FWO Custom Level Cap Setter
echo ===========================================
echo.
echo This caps the maximum character level on your server.
echo Any characters currently above the cap will be reset
echo to exactly the cap level with the matching XP threshold.
echo.
echo Server must be restarted after for changes to take effect.
echo.

:input
set /p cap="Enter level cap (1-221): "

REM Validate: must be a number 1-221
echo %cap%| findstr /r "^[1-9][0-9]*$" >nul
if errorlevel 1 (
    echo Invalid input. Numbers only, no decimals.
    goto input
)
if %cap% lss 1 goto input
if %cap% gtr 221 (
    echo Maximum cap is 221.
    goto input
)

echo.
echo Setting level cap to %cap%...
docker exec fwo-db mysql -u root -pejair0xx fwworlddevdb -e "UPDATE pcharstats_all SET Experience = (SELECT XP FROM leveladv WHERE Level=%cap%), Level=%cap% WHERE Level > %cap%;"

if errorlevel 1 (
    echo.
    echo ERROR: Database update failed. Is the server running?
    pause
    exit /b 1
)

echo.
echo Level cap set to %cap% successfully.
echo.
set /p restart="Restart server now to apply? (Y/N): "
if /i "%restart%"=="Y" (
    docker-compose restart fwo-server
    echo.
    echo Server restarted. Changes are live.
)
pause
