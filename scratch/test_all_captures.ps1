Add-Type -AssemblyName System.Windows.Forms
Add-Type -AssemblyName System.Drawing

$code = @"
using System;
using System.Drawing;
using System.Drawing.Imaging;
using System.Windows.Forms;
using System.Runtime.InteropServices;

public class ScreenCap {
    [DllImport("user32.dll")]
    public static extern IntPtr GetDC(IntPtr hwnd);

    [DllImport("user32.dll")]
    public static extern int ReleaseDC(IntPtr hwnd, IntPtr hdc);

    [DllImport("gdi32.dll")]
    public static extern bool BitBlt(IntPtr hdcDest, int nXDest, int nYDest, int nWidth, int nHeight, IntPtr hdcSrc, int nXSrc, int nYSrc, int dwRop);

    [DllImport("user32.dll")]
    public static extern int GetSystemMetrics(int nIndex);

    public const int SM_XVIRTUALSCREEN = 76;
    public const int SM_YVIRTUALSCREEN = 77;
    public const int SM_CXVIRTUALSCREEN = 78;
    public const int SM_CYVIRTUALSCREEN = 79;
    public const int SRCCOPY = 0x00CC0020;

    public static Bitmap MethodA() {
        int x = GetSystemMetrics(SM_XVIRTUALSCREEN);
        int y = GetSystemMetrics(SM_YVIRTUALSCREEN);
        int w = GetSystemMetrics(SM_CXVIRTUALSCREEN);
        int h = GetSystemMetrics(SM_CYVIRTUALSCREEN);

        if (w == 0 || h == 0) {
            w = GetSystemMetrics(0); // SM_CXSCREEN
            h = GetSystemMetrics(1); // SM_CYSCREEN
        }

        IntPtr hdcSrc = GetDC(IntPtr.Zero);
        Bitmap bmp = new Bitmap(w, h, PixelFormat.Format32bppArgb);
        using (Graphics g = Graphics.FromImage(bmp)) {
            IntPtr hdcDest = g.GetHdc();
            BitBlt(hdcDest, 0, 0, w, h, hdcSrc, x, y, SRCCOPY);
            g.ReleaseHdc(hdcDest);
        }
        ReleaseDC(IntPtr.Zero, hdcSrc);
        return bmp;
    }
}
"@

Add-Type -TypeDefinition $code -ReferencedAssemblies System.Drawing, System.Windows.Forms

# Test Method A
$bmpA = [ScreenCap]::MethodA()
$bmpA.Save("c:\xampp\htdocs\syncWithteam\scratch\capA.jpg", [System.Drawing.Imaging.ImageFormat]::Jpeg)

# Count non-black
$nbA = 0
for ($x = 0; $x -lt $bmpA.Width; $x += 15) {
    for ($y = 0; $y -lt $bmpA.Height; $y += 15) {
        $c = $bmpA.GetPixel($x, $y)
        if ($c.R -gt 15 -or $c.G -gt 15 -or $c.B -gt 15) { $nbA++ }
    }
}
$bmpA.Dispose()
Write-Host "Method A Non-Black Count: $nbA"
