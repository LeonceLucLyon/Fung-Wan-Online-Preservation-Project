@echo off
setlocal enabledelayedexpansion

REM ============================================================================
REM  FWO Server Configuration - loot / economy / progression dials
REM  Changes are written to the DB immediately but take effect in-game only
REM  after you choose [S] Save & Restart (the engine caches data at boot).
REM  Run this from your C:\fwo-docker folder (same as your other bats).
REM
REM  Level cap lives in the `Caps` table (CapType rows). The engine enforces the
REM  lower row, so this tool sets every CapType row to your chosen value. The XP
REM  curve in `leveladv` already runs to 221, so raising the cap lets players
REM  actually grind to it.
REM ============================================================================

:menu
cls
echo ==================================================================
echo            FWO Server Configuration   (Fung Wan Online)
echo ==================================================================
echo.
echo   --- current settings ---
call :show
echo.
echo   1.  Hero point rate    ( mob level x N hero points )
echo   2.  Gold modifier      ( adds mob level x N gold per kill )
echo   3.  Slot 2 rare %%      Armor / Bracers / Greaves
echo   4.  Slot 3 rare %%      Staff / Bow / Amulets
echo   5.  Slot 4 rare %%      Saber / Sword / Rings / Shoulderpads / Masks
echo   6.  Slot 5 rare %%      Rare components
echo   7.  Slot 6 rare %%      Heavenly components
echo   8.  XP rate            ( NPC xp drops, 1 = original )
echo   9.  Level cap          ( max character level )
echo.
echo   R.  Refresh
echo   S.  Save ^& Restart server   (apply all changes)
echo   Q.  Quit without restarting
echo.
set /p choice="Choose: "

set "EID="
if "%choice%"=="1" ( set "EID=999" & set "ELBL=Hero point rate (mob lvl x N)" )
if "%choice%"=="2" ( set "EID=998" & set "ELBL=Gold modifier (mob lvl x N gold)" )
if "%choice%"=="3" ( set "EID=993" & set "ELBL=Slot 2 - Armor/Bracers/Greaves" )
if "%choice%"=="4" ( set "EID=992" & set "ELBL=Slot 3 - Staff/Bow/Amulets" )
if "%choice%"=="5" ( set "EID=991" & set "ELBL=Slot 4 - Saber/Sword/Rings/Shoulders/Masks" )
if "%choice%"=="6" ( set "EID=990" & set "ELBL=Slot 5 - Rare components" )
if "%choice%"=="7" ( set "EID=989" & set "ELBL=Slot 6 - Heavenly components" )
if defined EID ( call :setdial & goto menu )

if "%choice%"=="8" ( call :setxp & goto menu )
if "%choice%"=="9" ( call :setcap & goto menu )
if /i "%choice%"=="R" goto menu
if /i "%choice%"=="S" goto restart
if /i "%choice%"=="Q" exit /b 0
goto menu

:setdial
echo.
set /p val="Set !ELBL! to (0-100, 0 = off): "
echo !val!| findstr /r "^[0-9][0-9]*$" >nul
if errorlevel 1 ( echo   Invalid - whole numbers 0-100 only. & pause & goto :eof )
if !val! gtr 100 ( echo   Maximum is 100. & pause & goto :eof )
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -e "UPDATE gameevent SET Status=!val! WHERE ID=!EID!;"
if errorlevel 1 ( echo   DB update FAILED - is the fwo-db container running? & pause & goto :eof )
echo   [!EID!] set to !val!.   ( applies after Save ^& Restart )
pause
goto :eof

:setxp
echo.
set /p rate="Enter XP rate (1-10000, 1 = original): "
echo !rate!| findstr /r "^[1-9][0-9]*$" >nul
if errorlevel 1 ( echo   Invalid - whole numbers 1-10000 only. & pause & goto :eof )
if !rate! gtr 10000 ( echo   Maximum is 10000. & pause & goto :eof )
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -e "UPDATE npcattrib n JOIN xp_baseline b ON n.AttribID=b.AttribID SET n.XPperHP = b.XPperHP * !rate!, n.XPValue = b.XPValue * !rate!;"
if errorlevel 1 ( echo   DB update FAILED - is the server running? & pause & goto :eof )
echo   XP rate set to !rate!x.   ( applies after Save ^& Restart )
pause
goto :eof

:setcap
echo.
echo   Sets the server level cap in the Caps table. leveladv has XP data up to 221.
echo   NOTE: any character ABOVE the new cap is pulled down to exactly the cap.
set /p cap="Enter level cap (1-221): "
echo !cap!| findstr /r "^[1-9][0-9]*$" >nul
if errorlevel 1 ( echo   Invalid - whole numbers 1-221 only. & pause & goto :eof )
if !cap! gtr 221 ( echo   Maximum is 221 ^(leveladv max^). & pause & goto :eof )
REM Set the real enforced cap. Every CapType row is set to the same value so the
REM cap applies no matter which row the engine reads (it enforces the lower one).
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -e "UPDATE Caps SET Level=!cap!;"
if errorlevel 1 ( echo   DB update FAILED - is the fwo-db container running? & pause & goto :eof )
REM Pull any characters above the new cap down to it (a no-op when raising the cap).
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -e "UPDATE pcharstats_all SET Experience = (SELECT XP FROM leveladv WHERE Level=!cap!), Level=!cap! WHERE Level > !cap!;"
echo   Level cap set to !cap!.   ( applies after Save ^& Restart )
pause
goto :eof

:show
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -N -e "SELECT CONCAT('   [',ID,'] ',Description,' = ',Status) FROM gameevent WHERE ID IN (999,998,993,992,991,990,989) ORDER BY ID DESC;" 2>nul
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -N -e "SELECT CONCAT('   [XP ] NPC xp rate = ',ROUND(n.XPValue/b.XPValue),'x') FROM npcattrib n JOIN xp_baseline b ON n.AttribID=b.AttribID WHERE b.XPValue>0 LIMIT 1;" 2>nul
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -N -e "SELECT CONCAT('   [LVL] level cap = ',MIN(Level)) FROM Caps;" 2>nul
docker exec fwo-db mysql -uroot -pejair0xx fwworlddevdb -N -e "SELECT CONCAT('   [chr] highest character = ',IFNULL(MAX(Level),0)) FROM pcharstats_all;" 2>nul
goto :eof

:restart
echo.
echo Restarting fwo-server to apply changes...
docker-compose restart fwo-server
echo.
echo Done. All changes are now live.
pause
exit /b 0
