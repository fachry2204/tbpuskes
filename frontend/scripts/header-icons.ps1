Add-Type -AssemblyName System.Drawing
$iconDirectory = Join-Path $PSScriptRoot '../public/icons'
foreach ($kind in @('calendar', 'notification')) {
  $bitmap = New-Object System.Drawing.Bitmap 128,128
  $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
  $graphics.SmoothingMode = 'AntiAlias'
  $graphics.Clear([System.Drawing.Color]::Transparent)
  $blue = New-Object System.Drawing.SolidBrush ([System.Drawing.ColorTranslator]::FromHtml('#168fb5'))
  $light = New-Object System.Drawing.SolidBrush ([System.Drawing.ColorTranslator]::FromHtml('#e7faff'))
  $gold = New-Object System.Drawing.SolidBrush ([System.Drawing.ColorTranslator]::FromHtml('#ffbd36'))
  $shadow = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb(35,12,89,112))
  $graphics.FillEllipse($shadow,19,104,92,15)
  if ($kind -eq 'calendar') {
    $graphics.FillRectangle($blue,20,23,88,85)
    $graphics.FillRectangle($light,26,46,76,55)
    $pen = New-Object System.Drawing.Pen ([System.Drawing.ColorTranslator]::FromHtml('#07647c')),8
    $pen.StartCap='Round'; $pen.EndCap='Round'
    $graphics.DrawLine($pen,40,15,40,31); $graphics.DrawLine($pen,88,15,88,31)
    foreach ($x in @(36,58,80)) { foreach ($y in @(57,77)) { $graphics.FillEllipse($blue,$x,$y,12,12) } }
    $graphics.FillEllipse($gold,78,75,17,17)
    $pen.Dispose()
  } else {
    $graphics.FillEllipse($blue,53,17,22,22)
    $graphics.FillEllipse($gold,31,29,66,72)
    $graphics.FillRectangle($gold,31,65,66,30)
    $graphics.FillEllipse($blue,52,91,24,23)
    $graphics.FillEllipse($gold,20,82,88,24)
    $graphics.FillEllipse([System.Drawing.Brushes]::White,42,40,10,25)
    $graphics.FillEllipse([System.Drawing.Brushes]::Tomato,87,15,25,25)
  }
  $bitmap.Save((Join-Path $iconDirectory ($kind+'-color.png')), [System.Drawing.Imaging.ImageFormat]::Png)
  $graphics.Dispose(); $bitmap.Dispose(); $blue.Dispose(); $light.Dispose(); $gold.Dispose(); $shadow.Dispose()
}
