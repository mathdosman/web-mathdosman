@echo off
REM Reset workspace to the 'stabil1' git tag.
REM Usage:
REM   stabil1
REM   stabil1 clean

setlocal
set SCRIPT_DIR=%~dp0
if /I "%~1"=="clean" (
  powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%stabil1.ps1" -Clean
) else (
  powershell -NoProfile -ExecutionPolicy Bypass -File "%SCRIPT_DIR%stabil1.ps1"
)
endlocal
