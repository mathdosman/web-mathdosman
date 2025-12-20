param(
    [switch]$Clean
)

$ErrorActionPreference = 'Stop'

Write-Host "Reset ke snapshot 'stabil1'..." -ForegroundColor Cyan

# Ensure we are in the repo root (this script lives in repo root)
Set-Location -LiteralPath $PSScriptRoot

# Basic checks
$null = git rev-parse --is-inside-work-tree 2>$null
$null = git rev-parse --verify stabil1 2>$null

# Restore tracked files to the stabil1 snapshot
git reset --hard stabil1

if ($Clean) {
    # Optional: remove untracked files, but keep common upload dirs
    git clean -fd -e gambar -e gambarsoal
}

Write-Host "OK. Workspace sudah kembali ke kondisi stabil1." -ForegroundColor Green
if (-not $Clean) {
    Write-Host "Catatan: file untracked tidak dihapus. Jalankan: .\\stabil1.ps1 -Clean (opsional)" -ForegroundColor DarkGray
}
