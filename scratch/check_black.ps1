Add-Type -AssemblyName System.Drawing
$bmp = [System.Drawing.Bitmap]::FromFile("c:\xampp\htdocs\syncWithteam\scratch\m3.jpg")
$nonBlack = 0
for ($x = 0; $x -lt $bmp.Width; $x += 10) {
    for ($y = 0; $y -lt $bmp.Height; $y += 10) {
        $c = $bmp.GetPixel($x, $y)
        if ($c.R -gt 10 -or $c.G -gt 10 -or $c.B -gt 10) {
            $nonBlack++
        }
    }
}
$bmp.Dispose()
Write-Host "Non-black sample count: $nonBlack"
