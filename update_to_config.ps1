# Script para actualizar archivos PHP para usar config.php

# Función para actualizar un archivo
function Update-PHPFile {
    param(
        [string]$FilePath,
        [string]$RequirePath
    )
    
    Write-Host "Actualizando: $FilePath"
    
    # Leer el contenido del archivo
    $content = Get-Content -Path $FilePath -Raw -Encoding UTF8
    
    # Patrón para encontrar la configuración de BD
    $pattern = '(\$servidor\s*=\s*"127\.0\.0\.1";.*?\$puerto\s*=\s*3306;.*?\$conn\s*=\s*new\s+mysqli.*?if\s*\(\$conn->connect_error\).*?\})'
    
    # Reemplazo
    $replacement = "require_once '$RequirePath';`r`n// Ahora `$conn está disponible gracias a config.php"
    
    # Hacer el reemplazo
    $newContent = $content -replace $pattern, $replacement, 'Singleline'
    
    # Guardar el archivo
    Set-Content -Path $FilePath -Value $newContent -Encoding UTF8 -NoNewline
    
    Write-Host "  ✓ Actualizado" -ForegroundColor Green
}

# Lista de archivos a actualizar
$files = @(
    @{Path="c:\xampp\htdocs\gestion\php\login.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\register.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\submit_project.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\solicitar_reset.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\actualizar_pass.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\handle_comment.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\handle_archive.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\download_document.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\handle_status_change.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\php\handle_delete_comment.php"; Require="../config.php"},
    @{Path="c:\xampp\htdocs\gestion\header.php"; Require="config.php"}
)

Write-Host "`n=== Iniciando actualización de archivos ===" -ForegroundColor Cyan
Write-Host "Total de archivos: $($files.Count)`n"

foreach ($file in $files) {
    if (Test-Path $file.Path) {
        Update-PHPFile -FilePath $file.Path -RequirePath $file.Require
    } else {
        Write-Host "  ✗ Archivo no encontrado: $($file.Path)" -ForegroundColor Red
    }
}

Write-Host "`n=== Actualización completada ===" -ForegroundColor Cyan
Write-Host "Por favor, prueba la aplicación para verificar que todo funcione correctamente."
