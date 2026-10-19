Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

# Enable DPI awareness safely
try {
    if (-not ([System.Management.Automation.PSTypeName]'Win32.User32Dpi').Type) {
        Add-Type -MemberDefinition '[DllImport("user32.dll")] public static extern bool SetProcessDPIAware();' -Name "User32Dpi" -Namespace "Win32" -ErrorAction SilentlyContinue
    }
    $null = [Win32.User32Dpi]::SetProcessDPIAware()
} catch {}

# Determine full screen bounds
$screen = [System.Windows.Forms.Screen]::PrimaryScreen
$width = $screen.Bounds.Width
$height = $screen.Bounds.Height
$left = $screen.Bounds.Left
$top = $screen.Bounds.Top

if ($width -le 0 -or $height -le 0) {
    $vScreen = [System.Windows.Forms.SystemInformation]::VirtualScreen
    $width = $vScreen.Width
    $height = $vScreen.Height
    $left = $vScreen.Left
    $top = $vScreen.Top
}

$bitmap = New-Object System.Drawing.Bitmap $width, $height
$graphics = [System.Drawing.Graphics]::FromImage($bitmap)

try {
    $graphics.CopyFromScreen($left, $top, 0, 0, (New-Object System.Drawing.Size $width, $height), [System.Drawing.CopyPixelOperation]::SourceCopy)
} catch {
    try {
        $graphics.CopyFromScreen(0, 0, 0, 0, (New-Object System.Drawing.Size $width, $height), [System.Drawing.CopyPixelOperation]::SourceCopy)
    } catch {}
}

$ms = New-Object System.IO.MemoryStream
$bitmap.Save($ms, [System.Drawing.Imaging.ImageFormat]::Jpeg)
$bytes = $ms.ToArray()
$base64 = [Convert]::ToBase64String($bytes)

$graphics.Dispose()
$bitmap.Dispose()
$ms.Dispose()

Write-Output $base64
