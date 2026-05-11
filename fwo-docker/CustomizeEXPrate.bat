@echo off
setlocal enabledelayedexpansion

echo ===========================================
echo   FWO Custom XP Rate Setter
echo ===========================================
echo.
echo This adjusts NPC XP drops. 1 = original rate. 40 = 40x.
echo Server must be restarted after for changes to take effect.
echo.

:input
set /p rate="Enter XP rate (1-100): "

REM Validate: must be a number 1-100
echo %rate%| findstr /r "^[1-9][0-9]*$" >nul
if errorlevel 1 (
    echo Invalid input. Numbers only.
    goto input
)
if %rate% lss 1 goto input
if %rate% gtr 100 (
    echo Maximum rate is 100.
    goto input
)

echo.
echo Setting XP rate to %rate%x...
docker exec fwo-db mysql -u root -pejair0xx fwworlddevdb -e "UPDATE npcattrib n JOIN xp_baseline b ON n.AttribID=b.AttribID SET n.XPperHP = b.XPperHP * %rate%, n.XPValue = b.XPValue * %rate%;"

if errorlevel 1 (
    echo ERROR: Database update failed. Is the server running?
    pause
    exit /b 1
)

echo.
echo XP rate set to %rate%x successfully.
echo.
set /p restart="Restart server now to apply? (Y/N): "
if /i "%restart%"=="Y" (
    docker-compose restart fwo-server
    echo Server restarted. Changes are live.
)
pause