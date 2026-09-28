Add-Type -AssemblyName System.Drawing
function Gradient($a,$b) { return [System.Drawing.Drawing2D.LinearGradientBrush]::new([System.Drawing.Rectangle]::new(0,0,128,128),[System.Drawing.ColorTranslator]::FromHtml($a),[System.Drawing.ColorTranslator]::FromHtml($b),65) }
foreach($kind in @('home','users','pill','chart','settings','alert')) {
 $bitmap=[System.Drawing.Bitmap]::new(128,128); $g=[System.Drawing.Graphics]::FromImage($bitmap); $g.SmoothingMode='AntiAlias'; $g.Clear([System.Drawing.Color]::Transparent)
 $blue=Gradient '#79d9ff' '#0766ba'; $green=Gradient '#89efd4' '#00947d'; $gold=Gradient '#ffe798' '#f08a0c'; $purple=Gradient '#c8b7ff' '#6542c7'; $white=Gradient '#ffffff' '#d8edf7'; $shadow=[System.Drawing.SolidBrush]::new([System.Drawing.Color]::FromArgb(35,18,60,83))
 $g.FillEllipse($shadow,18,108,94,12)
 switch($kind) {
 'home' { $g.FillRectangle($white,28,49,74,58); $g.FillPolygon($blue,[System.Drawing.Point[]]@([System.Drawing.Point]::new(11,57),[System.Drawing.Point]::new(64,13),[System.Drawing.Point]::new(117,57)));$g.FillRectangle($green,53,72,23,35);$g.FillRectangle($gold,33,66,13,14);$g.FillRectangle($gold,84,66,13,14) }
 'users' { $g.FillEllipse($blue,70,55,42,54);$g.FillEllipse($gold,76,21,31,34);$g.FillEllipse($green,15,52,65,63);$g.FillEllipse($gold,29,10,38,43);$g.FillPolygon($white,[System.Drawing.Point[]]@([System.Drawing.Point]::new(38,63),[System.Drawing.Point]::new(58,63),[System.Drawing.Point]::new(48,87))) }
 'pill' { $g.TranslateTransform(64,64);$g.RotateTransform(40);$g.TranslateTransform(-64,-64);$g.FillEllipse($blue,39,10,50,50);$g.FillRectangle($blue,39,35,50,29);$g.FillRectangle($white,39,64,50,27);$g.FillEllipse($white,39,66,50,50);$g.FillEllipse([System.Drawing.Brushes]::White,46,20,9,28);$g.ResetTransform() }
 'chart' { $g.FillRectangle($white,13,15,103,96);$g.FillRectangle($blue,25,64,20,35);$g.FillRectangle($green,54,43,20,56);$g.FillRectangle($gold,83,25,20,74) }
 'settings' { for($i=0;$i -lt 8;$i++){ $state=$g.Save();$g.TranslateTransform(64,64);$g.RotateTransform($i*45);$g.FillRectangle($purple,-12,-53,24,26);$g.Restore($state) };$g.FillEllipse($purple,22,22,84,84);$g.FillEllipse($white,42,42,44,44);$g.FillEllipse($blue,51,51,26,26) }
 'alert' { $g.FillPolygon($gold,[System.Drawing.Point[]]@([System.Drawing.Point]::new(64,9),[System.Drawing.Point]::new(120,107),[System.Drawing.Point]::new(8,107)));$g.FillRectangle($white,58,42,12,32);$g.FillEllipse($white,58,84,12,12) }
 }
 $bitmap.Save((Join-Path $PSScriptRoot ('../public/icons/'+$kind+'-color.png')),[System.Drawing.Imaging.ImageFormat]::Png)
 $g.Dispose();$bitmap.Dispose();$blue.Dispose();$green.Dispose();$gold.Dispose();$purple.Dispose();$white.Dispose();$shadow.Dispose()
}
