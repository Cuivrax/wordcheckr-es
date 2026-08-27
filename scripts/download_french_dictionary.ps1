$ErrorActionPreference = "Stop"

$RootDir = Split-Path -Parent $PSScriptRoot
$DestDir = Join-Path $RootDir "data\raw"
$DestFile = Join-Path $DestDir "french_dict.db"
$TempFile = "$DestFile.part"
$Url = "https://huggingface.co/datasets/Kartmaan/french-dictionary/resolve/main/french_dict.db?download=true"

New-Item -ItemType Directory -Path $DestDir -Force | Out-Null

Write-Host "Téléchargement de french_dict.db..."
Invoke-WebRequest -Uri $Url -OutFile $TempFile
Move-Item -Path $TempFile -Destination $DestFile -Force

$Item = Get-Item $DestFile
Write-Host "Fichier téléchargé : $($Item.FullName)"
Write-Host "Taille : $($Item.Length) octets"

$Hash = Get-FileHash -Algorithm SHA256 -Path $DestFile
"$($Hash.Hash.ToLower())  french_dict.db" | Set-Content "$DestFile.sha256"
Write-Host "SHA-256 écrit dans $DestFile.sha256"
