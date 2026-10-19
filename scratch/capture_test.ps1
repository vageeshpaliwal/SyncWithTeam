Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$screen = [System.Windows.Forms.SystemInformation]::VirtualScreen
$bitmap = New-Object System.Drawing.Bitmap $screen.Width, $screen.Height
$graphics = [System.Drawing.Graphics]::FromImage($bitmap)
$graphics.CopyFromScreen($screen.Left, $screen.Top, 0, 0, $screen.Size, [System.Drawing.CopyPixelOperation]::SourceCopy)

$outputPath = "c:\xampp\htdocs\syncWithteam\scratch\test_screen.jpg"
$bitmap.Save($outputPath, [System.Drawing.Imaging.ImageFormat]::Jpeg)

$graphics.Dispose()
$bitmap.Dispose()

Write-Host "Saved to $outputPath"
