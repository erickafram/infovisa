Get-Service | Where-Object { $_.Name -like '*postgres*' -or $_.DisplayName -like '*postgres*' }
