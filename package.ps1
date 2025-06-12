# PowerShell script to package the Todo application
$sourceDir = $PSScriptRoot
$packageDir = "$sourceDir\package"
$zipFile = "$sourceDir\todo_app_package_$(Get-Date -Format 'yyyyMMdd_HHmmss').zip"

# Create package directory if it doesn't exist
if (Test-Path $packageDir) {
    Remove-Item -Path $packageDir -Recurse -Force
}
New-Item -ItemType Directory -Path $packageDir | Out-Null

# List of files/directories to include (adjust as needed)
$include = @(
    "assets/**/*",
    "config/*.php",
    "controllers/**/*",
    "includes/**/*",
    "models/**/*",
    "views/**/*",
    "index.php",
    ".htaccess",
    "README.md"
)

# Copy files to package directory
foreach ($item in $include) {
    $source = Join-Path $sourceDir $item
    $destination = Join-Path $packageDir $item
    
    if (Test-Path $source) {
        $destDir = Split-Path $destination -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Copy-Item -Path $source -Destination $destination -Recurse -Force
    }
}

# Create a README file with installation instructions
$readmeContent = @"
# TODO Aplikacija - Upute za instalaciju

## Zahtjevi
- PHP 7.4 ili noviji
- MySQL 5.7 ili noviji
- Web poslužitelj (npr. Apache, Nginx)

## Instalacija

1. Kopirajte sve datoteke na vaš web poslužitelj
2. Kreirajte bazu podataka i importujte datoteku `database/schema.sql`
3. Konfigurirajte pristup bazi podataka u datoteci `config/database.php`
4. Postavite prava pristupa za mapu `uploads` (ako postoji) na 755
5. Otvorite aplikaciju u web pregledniku

## Prva prijava
- Email: admin@example.com
- Lozinka: admin123 (preporuča se promjena pri prvoj prijavi)

## Sigurnosne napomene
- Promijenite zadane vjerodajnice prilikom prve prijave
- Osigurajte da je konfiguracijska datoteka baze podataka zaštićena od javnog pristupa
- Redovno pravite sigurnosne kopije baze podataka
"@

Set-Content -Path "$packageDir\README.md" -Value $readmeContent

# Create ZIP archive
Compress-Archive -Path "$packageDir\*" -DestinationPath $zipFile -Force

# Clean up
Remove-Item -Path $packageDir -Recurse -Force

Write-Host "\nAplikacija je uspješno spremljena u datoteku:" -ForegroundColor Green
Write-Host $zipFile -ForegroundColor Cyan
Write-Host "\nSada možete podijeliti ovu datoteku s drugima." -ForegroundColor Green
