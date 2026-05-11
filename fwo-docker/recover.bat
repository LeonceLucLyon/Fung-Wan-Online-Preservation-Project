@echo off
REM ============================================================================
REM FWO Offline - Crash Recovery
REM ============================================================================
REM Run this when zoneserver crashes mid-session and your account auto-suspends.
REM The original FWO had this as a cron job (unsuspend_ac.sh) on prod.
REM
REM What it does:
REM   1. Restarts fwo-server (relaunches zoneserver, wctrlr, authsys, logserver)
REM   2. Clears stale auth state from authorized + authenticated tables
REM   3. Resets ejai's subscription to active state
REM
REM Usage:
REM   recover.bat                  resets ejai (default test account)
REM   recover.bat admin            resets a different username
REM ============================================================================

setlocal

set DB_PASSWORD=ejair0xx
set CONTAINER=fwo-db

if "%~1"=="" (
    set "USERNAME=ejai"
) else (
    set "USERNAME=%~1"
)

echo.
echo Recovering %USERNAME%...

echo [1/4] Restarting fwo-server...
docker-compose restart fwo-server

echo [2/4] Clearing authorized table...
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% fwsubdevdb -e "DELETE FROM authorized"

echo [3/4] Clearing authenticated table...
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% fwworlddevdb -e "DELETE FROM authenticated"

echo [4/4] Resetting subscription for %USERNAME%...
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% fwsubdevdb -e "UPDATE subscription SET SvcLevel=1, LockOutTime=0 WHERE Username='%USERNAME%'"

echo.
echo Verifying subscription state...
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% fwsubdevdb -e "SELECT Username, SvcLevel, LockOutTime FROM subscription WHERE Username='%USERNAME%'"

echo.
echo Waiting for zoneserver to come back up...
timeout /t 20 /nobreak >nul

echo Server status:
docker exec fwo-server bash -c "ps aux | grep '/FWO13/zoneserver/zoneserver' | grep -v grep"
echo.
echo Recovery complete. Try logging in again.
echo.

endlocal
