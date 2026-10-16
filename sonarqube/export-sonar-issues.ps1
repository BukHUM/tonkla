#Requires -Version 5.1
param(
    [Parameter(Mandatory = $true)][string]$SonarHost,
    [Parameter(Mandatory = $true)][string]$ProjectKey,
    [Parameter(Mandatory = $true)][string]$Token,
    [string]$OutDir = ""
)

$ErrorActionPreference = "Stop"
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12

$SonarHost = $SonarHost.TrimEnd("/")
if (-not $OutDir) {
    $stamp = Get-Date -Format "yyyyMMdd-HHmmss"
    $OutDir = Join-Path (Split-Path $PSScriptRoot -Parent) ("sonar-reports\" + $stamp)
}
New-Item -ItemType Directory -Force -Path $OutDir | Out-Null

$authHeader = @{
    Authorization = "Basic " + [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes(($Token + ":")))
}

function Invoke-SonarApi {
    param([string]$PathAndQuery)
    $uri = $SonarHost + $PathAndQuery
    try {
        return Invoke-RestMethod -Uri $uri -Headers $authHeader -Method Get -TimeoutSec 120
    }
    catch {
        $status = $null
        if ($_.Exception.Response) {
            $status = [int]$_.Exception.Response.StatusCode
        }
        throw ("Sonar API failed (" + $status + "): " + $uri + "`n" + $_.Exception.Message)
    }
}

function Get-AllIssues {
    param(
        [string]$ExtraQuery = "",
        [int]$PageSize = 500
    )

    $page = 1
    $all = New-Object System.Collections.Generic.List[object]
    $total = -1

    while ($true) {
        $q = 'componentKeys={0}&resolved=false&ps={1}&p={2}' -f [uri]::EscapeDataString($ProjectKey), $PageSize, $page
        if ($ExtraQuery) {
            $q = '{0}&{1}' -f $q, $ExtraQuery
        }
        $resp = Invoke-SonarApi ("/api/issues/search?" + $q)

        if ($total -lt 0) {
            $total = [int]$resp.paging.total
            Write-Host ("  total matching: " + $total)
            if ($total -gt 10000) {
                Write-Warning "API hard-caps around 10,000 results for one query. Narrow filters if count looks truncated."
            }
        }

        $batch = @($resp.issues)
        foreach ($issue in $batch) {
            [void]$all.Add($issue)
        }

        $fetched = $all.Count
        Write-Host ("  page " + $page + " - fetched " + $fetched + " / " + $total)
        $page++

        if (($fetched -ge $total) -or ($batch.Count -eq 0) -or ($fetched -ge 10000)) {
            break
        }
    }

    # Materialize as Object[] so callers can count rows correctly.
    return @($all.ToArray())
}

function Convert-IssueRow {
    param($Issue)
    $msg = ""
    if ($Issue.message) {
        $msg = (($Issue.message -replace "[\r\n]+", " ").Trim())
    }
    $file = (($Issue.component -split ":")[-1])
    [PSCustomObject]@{
        key          = $Issue.key
        type         = $Issue.type
        severity     = $Issue.severity
        status       = $Issue.status
        rule         = $Issue.rule
        component    = $Issue.component
        file         = $file
        line         = $Issue.line
        message      = $msg
        effort       = $Issue.effort
        debt         = $Issue.debt
        creationDate = $Issue.creationDate
        updateDate   = $Issue.updateDate
        tags         = ((@($Issue.tags) -join "|"))
    }
}

function Export-IssueSet {
    param(
        [string]$Name,
        [string]$ExtraQuery,
        [string]$CsvPath,
        [string]$JsonPath
    )

    Write-Host ""
    Write-Host ("=== " + $Name + " ===") -ForegroundColor Cyan
    $raw = Get-AllIssues -ExtraQuery $ExtraQuery
    $issues = @($raw)
    # If PowerShell collapsed a nested array into one element, flatten one level.
    if ($issues.Count -eq 1 -and $issues[0] -is [System.Array]) {
        $issues = @($issues[0])
    }
    $rows = New-Object System.Collections.Generic.List[object]
    foreach ($issue in $issues) {
        [void]$rows.Add((Convert-IssueRow $issue))
    }

    @($rows.ToArray()) | Export-Csv -Path $CsvPath -NoTypeInformation -Encoding UTF8
    ($issues | ConvertTo-Json -Depth 8) | Set-Content -Path $JsonPath -Encoding UTF8
    Write-Host ("  wrote " + $rows.Count + " rows -> " + $CsvPath)
    return $rows.Count
}

Write-Host "Fetching project measures..." -ForegroundColor Cyan
$metricKeys = @(
    "bugs", "vulnerabilities", "code_smells",
    "reliability_rating", "security_rating", "sqale_rating",
    "coverage", "duplicated_lines_density", "ncloc", "security_hotspots"
) -join ","

$measuresQuery = 'component={0}&metricKeys={1}' -f [uri]::EscapeDataString($ProjectKey), $metricKeys
$measuresResp = Invoke-SonarApi ("/api/measures/component?" + $measuresQuery)
$measureMap = @{}
foreach ($m in @($measuresResp.component.measures)) {
    $measureMap[$m.metric] = $m.value
}

$summaryPath = Join-Path $OutDir "summary.json"
$counts = @{}

$counts["vulnerabilities"] = Export-IssueSet `
    -Name "Vulnerabilities (Security)" `
    -ExtraQuery "types=VULNERABILITY" `
    -CsvPath (Join-Path $OutDir "vulnerabilities.csv") `
    -JsonPath (Join-Path $OutDir "vulnerabilities.json")

$counts["bugs"] = Export-IssueSet `
    -Name "Bugs (Reliability)" `
    -ExtraQuery "types=BUG" `
    -CsvPath (Join-Path $OutDir "bugs.csv") `
    -JsonPath (Join-Path $OutDir "bugs.json")

$smellSeverities = @("BLOCKER", "CRITICAL", "MAJOR", "MINOR", "INFO")
$smellCombined = New-Object System.Collections.Generic.List[object]
Write-Host ""
Write-Host "=== Code Smells (Maintainability) by severity ===" -ForegroundColor Cyan
foreach ($sev in $smellSeverities) {
    Write-Host ("--- CODE_SMELL severity=" + $sev + " ---")
    $extra = 'types=CODE_SMELL&severities=' + $sev
    $part = @(Get-AllIssues -ExtraQuery $extra)
    if ($part.Count -eq 1 -and $part[0] -is [System.Array]) { $part = @($part[0]) }
    foreach ($i in $part) {
        [void]$smellCombined.Add($i)
    }
}

$smellRows = @($smellCombined.ToArray() | ForEach-Object { Convert-IssueRow $_ })
$smellCsv = Join-Path $OutDir "code_smells.csv"
$smellJson = Join-Path $OutDir "code_smells.json"
$smellRows | Export-Csv -Path $smellCsv -NoTypeInformation -Encoding UTF8
($smellCombined.ToArray() | ConvertTo-Json -Depth 8) | Set-Content -Path $smellJson -Encoding UTF8
Write-Host ("  wrote " + $smellRows.Count + " rows -> " + $smellCsv)
$counts["code_smells"] = $smellRows.Count

Write-Host ""
Write-Host "=== Priority queue (VULNERABILITY + BUG BLOCKER/CRITICAL) ===" -ForegroundColor Cyan
$prioBugs = @(Get-AllIssues -ExtraQuery 'types=BUG&severities=BLOCKER,CRITICAL')
if ($prioBugs.Count -eq 1 -and $prioBugs[0] -is [System.Array]) { $prioBugs = @($prioBugs[0]) }
$prioRows = New-Object System.Collections.Generic.List[object]
$prioVulnPath = Join-Path $OutDir "vulnerabilities.csv"
if (Test-Path $prioVulnPath) {
    foreach ($row in @(Import-Csv $prioVulnPath)) {
        [void]$prioRows.Add($row)
    }
}
foreach ($i in $prioBugs) {
    [void]$prioRows.Add((Convert-IssueRow $i))
}
$prioCsv = Join-Path $OutDir "priority-fix-first.csv"
@($prioRows.ToArray()) | Export-Csv -Path $prioCsv -NoTypeInformation -Encoding UTF8
Write-Host ("  wrote " + $prioRows.Count + " rows -> " + $prioCsv)

$summary = [ordered]@{
    host         = $SonarHost
    projectKey   = $ProjectKey
    exportedAt   = (Get-Date).ToString("o")
    measures     = $measureMap
    issueCounts  = $counts
    files        = [ordered]@{
        vulnerabilities = "vulnerabilities.csv"
        bugs            = "bugs.csv"
        code_smells     = "code_smells.csv"
        priority        = "priority-fix-first.csv"
    }
}
($summary | ConvertTo-Json -Depth 6) | Set-Content -Path $summaryPath -Encoding UTF8

Write-Host ""
Write-Host "Done. Output folder:" -ForegroundColor Green
Write-Host ("  " + $OutDir)
Write-Host "  summary.json | vulnerabilities.csv | bugs.csv | code_smells.csv | priority-fix-first.csv"
Write-Host ("Counts: vuln=" + $counts["vulnerabilities"] + " bugs=" + $counts["bugs"] + " smells=" + $counts["code_smells"] + " priority=" + $prioRows.Count)
