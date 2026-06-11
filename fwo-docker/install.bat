@echo off
REM ============================================================================
REM FWO Offline - One-Shot Install (Distribution Edition)
REM ============================================================================
REM
REM This script gets a fresh FWO Offline server running from scratch:
REM   1. Verifies Docker Desktop is installed and running
REM   2. Loads pre-built Docker images from BACKUP_docker_images.tar (if present)
REM      OR builds locally from Dockerfiles (requires internet, may fail if
REM      vettadock/mysql-old:4.1 is no longer hosted)
REM   3. Brings up fwo-db
REM   4. Restores BACKUP_v15_complete.sql (the gold standard preservation snapshot)
REM   5. Brings up fwo-server
REM   6. Optionally prompts for initial XP rate and level cap
REM
REM Run from C:\fwo-docker\ (or wherever you extracted the project).
REM ============================================================================

setlocal enabledelayedexpansion

set DB_PASSWORD=ejair0xx
set CONTAINER=fwo-db
set BACKUP_FILE=%~dp0database\BACKUP_v15_complete.sql
set IMAGE_TAR=%~dp0BACKUP_docker_images.tar

echo.
echo ============================================
echo  FWO Offline Preservation - Install
echo ============================================
echo.

REM ----- 1. Verify Docker Desktop is installed -----
echo [1/6] Checking Docker Desktop...
docker --version >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: Docker Desktop is not installed or not in PATH.
    echo Download and install from: https://www.docker.com/products/docker-desktop/
    echo Then restart this script.
    echo.
    pause
    exit /b 1
)
docker info >nul 2>&1
if errorlevel 1 (
    echo.
    echo ERROR: Docker Desktop is installed but not running.
    echo Start Docker Desktop, wait for it to finish loading, then run this script again.
    echo.
    pause
    exit /b 1
)
echo       Docker Desktop is running.
echo.

REM ----- 2. Verify backup file exists -----
echo [2/6] Checking gold standard backup...
if not exist "%BACKUP_FILE%" (
    echo.
    echo ERROR: BACKUP_v15_complete.sql not found at:
    echo   %BACKUP_FILE%
    echo.
    echo Make sure the database\ folder is intact and contains the backup file.
    echo.
    pause
    exit /b 1
)
echo       Found: %BACKUP_FILE%
echo.

REM ----- 3. Load Docker images from tarball OR build -----
echo [3/6] Setting up Docker images...
if exist "%IMAGE_TAR%" (
    echo       Found pre-built image tarball, loading...
    docker load -i "%IMAGE_TAR%"
    if errorlevel 1 (
        echo       ERROR: docker load failed. Check the tarball is not corrupted.
        pause
        exit /b 1
    )
    echo       Images loaded successfully.
) else (
    echo       BACKUP_docker_images.tar not found.
    echo       Will attempt to build from Dockerfiles ^(requires internet^).
    echo       NOTE: If vettadock/mysql-old:4.1 is no longer available,
    echo       this will fail. Download BACKUP_docker_images.tar from:
    echo         ^<see README.md for download link^>
    echo       and place it next to install.bat.
    echo.
    pause
)
echo.

REM ----- 4. Bring up fwo-db -----
echo [4/6] Starting fwo-db...
docker-compose up -d fwo-db
if errorlevel 1 (
    echo.
    echo ERROR: docker-compose up failed for fwo-db.
    pause
    exit /b 1
)

echo       Waiting for fwo-db to become reachable...
set /a WAIT_COUNT=0
:wait_loop
docker exec %CONTAINER% mysql -e "SELECT 1" >nul 2>&1
if not errorlevel 1 goto db_ready
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% -e "SELECT 1" >nul 2>&1
if not errorlevel 1 goto db_ready
set /a WAIT_COUNT+=1
if %WAIT_COUNT% gtr 60 (
    echo.
    echo ERROR: fwo-db never became reachable after 120 seconds.
    echo Check container status with:  docker-compose logs fwo-db
    pause
    exit /b 1
)
timeout /t 2 /nobreak >nul
goto wait_loop
:db_ready
echo       fwo-db is up.
echo.

REM ----- 5. Restore BACKUP_v15_complete.sql -----
echo [5/6] Restoring gold standard database snapshot...
echo       (this takes about a minute on a typical machine)

REM First, make sure root password is set so the restore can authenticate.
REM This is idempotent: if already set, the command just fails silently.
docker exec %CONTAINER% mysqladmin -u root password %DB_PASSWORD% 2>nul

REM Restore. The dump uses --add-drop-database so it self-cleans.
docker exec -i %CONTAINER% mysql -uroot -p%DB_PASSWORD% < "%BACKUP_FILE%"
if errorlevel 1 (
    echo.
    echo ERROR: Database restore failed. Check the output above.
    pause
    exit /b 1
)

REM Show item count as proof the restore landed (just print, don't parse).
echo       Verifying restore:
docker exec %CONTAINER% mysql -uroot -p%DB_PASSWORD% fwworlddevdb -e "SELECT COUNT(*) AS item_count FROM item;"
echo.

REM ----- 6. Final clean restart with everything together -----
REM A clean down+up cycle here avoids a race condition where fwo-server
REM tries to start while fwo-db is still settling after the restore.
echo [6/6] Starting full server stack with clean cycle...
docker-compose down >nul 2>&1
docker-compose up -d
if errorlevel 1 (
    echo.
    echo ERROR: docker-compose up failed.
    pause
    exit /b 1
)

echo       Waiting for game server to come online ^(~30s^)...
timeout /t 30 /nobreak >nul

echo.
echo ============================================
echo  Install complete!
echo ============================================
echo.
echo Accounts:
echo   admin / fwopass        ^(GM character^)
echo   Account1..Account100 / fwopass
echo.
echo Next steps:
echo   - Optional: tune the server -^>  ServerConfiguration.bat
echo   - Launch the FWO client and connect to 127.0.0.1
echo.
echo Useful commands:
echo   docker-compose down      stop server (preserves data^)
echo   docker-compose up -d     start server
echo   recover.bat              run after a crash to unsuspend an account
echo.

pause
endlocal
