Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

# Method 1: Standard CopyFromScreen with System.Drawing.Point
try {
    $bounds = [System.Windows.Forms.Screen]::PrimaryScreen.Bounds
    $bitmap1 = New-Object System.Drawing.Bitmap $bounds.Width, $bounds.Height
    $g1 = [System.Drawing.Graphics]::FromImage($bitmap1)
    $g1.CopyFromScreen($bounds.Location, [System.Drawing.Point]::Empty, $bounds.Size)
    $bitmap1.Save("c:\xampp\htdocs\syncWithteam\scratch\m1.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)
    $g1.Dispose()
    $bitmap1.Dispose()
    Write-Host "Method 1 Success"
} catch {
    Write-Host "Method 1 Failed: $_"
}

# Method 2: CopyFromScreen with (0,0,0,0) coordinates
try {
    $bounds = [System.Windows.Forms.Screen]::PrimaryScreen.Bounds
    $bitmap2 = New-Object System.Drawing.Bitmap $bounds.Width, $bounds.Height
    $g2 = [System.Drawing.Graphics]::FromImage($bitmap2)
    $g2.CopyFromScreen(0, 0, 0, 0, $bounds.Size)
    $bitmap2.Save("c:\xampp\htdocs\syncWithteam\scratch\m2.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)
    $g2.Dispose()
    $bitmap2.Dispose()
    Write-Host "Method 2 Success"
} catch {
    Write-Host "Method 2 Failed: $_"
}

# Method 3: User32 BitBlt via P/Invoke (Direct Windows GDI)
$code = @"
using System;
using System.Drawing;
using System.Runtime.InteropServices;

public class Win32Capture {
    [DllImport("user32.dll")]
    public static extern IntPtr GetDesktopWindow();

    [DllImport("user32.dll")]
    public static extern IntPtr GetWindowDC(IntPtr hWnd);

    [DllImport("user32.dll")]
    public static extern IntPtr ReleaseDC(IntPtr hWnd, IntPtr hDC);

    [DllImport("gdi32.dll")]
    public static extern bool BitBlt(IntPtr hdcDest, int nXDest, int nYDest, int nWidth, int nHeight, IntPtr hdcSrc, int nXSrc, int nYSrc, int dwRop);

    public const int SRCCOPY = 0x00CC0020;

    public static Bitmap CaptureDesktop() {
        IntPtr hWnd = GetDesktopWindow();
        IntPtr hdcSrc = GetWindowDC(hWnd);
        int width = GetSystemMetrics(0); // SM_CXSCREEN
        int height = GetSystemMetrics(1); // SM_CYSCREEN

        Bitmap bmp = new Bitmap(width, height);
        using (Graphics g = Graphics.FromImage(bmp)) {
            IntPtr hdcDest = g.GetHdc();
            BitBlt(hdcDest, 0, 0, width, height, hdcSrc, 0, 0, SRCCOPY);
            g.ReleaseHdc(hdcDest);
        }
        ReleaseDC(hWnd, hdcSrc);
        return bmp;
    }

    [DllImport("user32.dll")]
    public static extern int GetSystemMetrics(int nIndex);
}
"@

try {
    Add-Type -TypeDefinition $code -ReferencedAssemblies System.Drawing
    $bmp3 = [Win32Capture]::CaptureDesktop()
    $bmp3.Save("c:\xampp\htdocs\syncWithteam\scratch\m3.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)
    $bmp3.Dispose()
    Write-Host "Method 3 (Win32 BitBlt) Success"
} catch {
    Write-Host "Method 3 Failed: $_"
}
