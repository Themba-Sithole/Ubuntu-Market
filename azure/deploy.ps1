# Ubuntu Market — Azure App Service + MySQL deployment helper
# Prerequisites: Azure CLI (az), logged in via `az login`
# Usage: .\azure\deploy.ps1 -ResourceGroup "ubuntu-market-rg" -AppName "ubuntu-market-app"

param(
    [string]$ResourceGroup = "ubuntu-market-rg",
    [string]$Location = "southafricanorth",
    [string]$AppName = "ubuntu-market-app",
    [string]$DbServerName = "ubuntu-market-mysql",
    [string]$DbName = "ubuntu_market",
    [string]$DbAdminUser = "ubuntumarket",
    [string]$DbAdminPassword = ""
)

$AzCli = Join-Path ${env:ProgramFiles} "Microsoft SDKs\Azure\CLI2\wbin\az.cmd"
if (-not (Test-Path $AzCli)) {
    throw "Azure CLI not found. Install from https://aka.ms/installazurecliwindows"
}
function az { & $AzCli @args }

if ($DbAdminPassword -eq "") {
    $DbAdminPassword = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 24 | ForEach-Object { [char]$_ })
    Write-Host "Generated DB admin password (save this): $DbAdminPassword"
}

Write-Host "Creating resource group..."
az group create --name $ResourceGroup --location $Location | Out-Null

Write-Host "Creating MySQL Flexible Server (this may take several minutes)..."
az mysql flexible-server create `
    --resource-group $ResourceGroup `
    --name $DbServerName `
    --location $Location `
    --admin-user $DbAdminUser `
    --admin-password $DbAdminPassword `
    --sku-name Standard_B1ms `
    --tier Burstable `
    --storage-size 20 `
    --version 8.0.21 `
    --public-access 0.0.0.0 `
    | Out-Null

Write-Host "Creating database..."
az mysql flexible-server db create `
    --resource-group $ResourceGroup `
    --server-name $DbServerName `
    --database-name $DbName `
    | Out-Null

Write-Host "Creating App Service plan (Linux, PHP)..."
az appservice plan create `
    --resource-group $ResourceGroup `
    --name "$AppName-plan" `
    --location $Location `
    --sku B1 `
    --is-linux `
    | Out-Null

Write-Host "Creating Web App..."
az webapp create `
    --resource-group $ResourceGroup `
    --plan "$AppName-plan" `
    --name $AppName `
    --runtime "PHP:8.2" `
    | Out-Null

$dbHost = "$DbServerName.mysql.database.azure.com"
$siteUrl = "https://$AppName.azurewebsites.net"

Write-Host "Configuring application settings..."
az webapp config appsettings set `
    --resource-group $ResourceGroup `
    --name $AppName `
    --settings `
        DB_HOST="$dbHost" `
        DB_NAME="$DbName" `
        DB_USER="$DbAdminUser" `
        DB_PASSWORD="$DbAdminPassword" `
        DB_SSL="true" `
        SITE_BASE_URL="$siteUrl" `
        SCM_DO_BUILD_DURING_DEPLOYMENT="false" `
    | Out-Null

Write-Host "Allowing App Service to reach MySQL..."
az mysql flexible-server firewall-rule create `
    --resource-group $ResourceGroup `
    --name $DbServerName `
    --rule-name AllowAzureServices `
    --start-ip-address 0.0.0.0 `
    --end-ip-address 0.0.0.0 `
    | Out-Null

Write-Host ""
Write-Host "Azure resources created."
Write-Host "  Site URL:  $siteUrl"
Write-Host "  DB host:   $dbHost"
Write-Host "  DB name:   $DbName"
Write-Host "  DB user:   $DbAdminUser"
Write-Host ""
Write-Host "Next steps:"
Write-Host "  1. Export your local DB: mysqldump -u root ubuntu_market > ubuntu_market.sql"
Write-Host "  2. Import to Azure:"
Write-Host "     mysql -h $dbHost -u $DbAdminUser -p --ssl-mode=REQUIRED $DbName < ubuntu_market.sql"
Write-Host "  3. Deploy site files:"
Write-Host "     Compress-Archive -Path * -DestinationPath deploy.zip"
Write-Host "     az webapp deploy --resource-group $ResourceGroup --name $AppName --src-path deploy.zip --type zip"
Write-Host "  4. Open $siteUrl and test login / payments"
