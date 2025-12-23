@echo off
REM Reset workspace to the 'stabil2' git tag.
REM Usage:
REM   stabil2
REM   stabil2 clean

setlocal
set SCRIPT_DIR=%~dp0
if /I "%~1"=="clean" (
  powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%stabil2.ps1" -Clean
) else (
  powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%stabil2.ps1"
)
endlocal
