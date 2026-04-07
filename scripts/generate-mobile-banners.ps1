$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.Drawing

function New-Color([string]$hex, [int]$alpha = 255) {
    $value = $hex.TrimStart('#')
    if ($value.Length -ne 6) {
        throw "Invalid hex color: $hex"
    }

    return [System.Drawing.Color]::FromArgb(
        $alpha,
        [Convert]::ToInt32($value.Substring(0, 2), 16),
        [Convert]::ToInt32($value.Substring(2, 2), 16),
        [Convert]::ToInt32($value.Substring(4, 2), 16)
    )
}

function New-RoundedPath([float]$x, [float]$y, [float]$width, [float]$height, [float]$radius) {
    $diameter = [Math]::Min($radius * 2, [Math]::Min($width, $height))
    $path = New-Object System.Drawing.Drawing2D.GraphicsPath

    if ($diameter -le 0) {
        $path.AddRectangle([System.Drawing.RectangleF]::new($x, $y, $width, $height))
        return $path
    }

    $path.AddArc($x, $y, $diameter, $diameter, 180, 90)
    $path.AddArc($x + $width - $diameter, $y, $diameter, $diameter, 270, 90)
    $path.AddArc($x + $width - $diameter, $y + $height - $diameter, $diameter, $diameter, 0, 90)
    $path.AddArc($x, $y + $height - $diameter, $diameter, $diameter, 90, 90)
    $path.CloseFigure()

    return $path
}

function Draw-ShadowedPanel($graphics, $x, $y, $width, $height, $radius, $fillColor, $shadowAlpha = 90) {
    $shadowPath = New-RoundedPath ($x + 12) ($y + 18) $width $height $radius
    $shadowBrush = New-Object System.Drawing.SolidBrush ([System.Drawing.Color]::FromArgb($shadowAlpha, 0, 0, 0))
    $graphics.FillPath($shadowBrush, $shadowPath)
    $shadowBrush.Dispose()
    $shadowPath.Dispose()

    $panelPath = New-RoundedPath $x $y $width $height $radius
    $panelBrush = New-Object System.Drawing.SolidBrush $fillColor
    $graphics.FillPath($panelBrush, $panelPath)
    $panelBrush.Dispose()
    $panelPath.Dispose()
}

function Draw-Glow($graphics, $x, $y, $width, $height, $innerHex, $alpha) {
    $ellipsePath = New-Object System.Drawing.Drawing2D.GraphicsPath
    $ellipsePath.AddEllipse($x, $y, $width, $height)

    $glowBrush = New-Object System.Drawing.Drawing2D.PathGradientBrush $ellipsePath
    $glowBrush.CenterColor = New-Color $innerHex $alpha
    $glowBrush.SurroundColors = [System.Drawing.Color[]]@([System.Drawing.Color]::FromArgb(0, 0, 0, 0))
    $graphics.FillPath($glowBrush, $ellipsePath)

    $glowBrush.Dispose()
    $ellipsePath.Dispose()
}

function Draw-OutlineArc($graphics, $x, $y, $width, $height, $penHex, $alpha, $penWidth) {
    $pen = New-Object System.Drawing.Pen (New-Color $penHex $alpha), $penWidth
    $graphics.DrawArc($pen, $x, $y, $width, $height, 210, 220)
    $pen.Dispose()
}

function Draw-CoverImage($graphics, $imagePath, $x, $y, $width, $height, $radius, $opacity = 1.0) {
    $bitmap = [System.Drawing.Image]::FromFile($imagePath)
    try {
        $sourceRatio = $bitmap.Width / $bitmap.Height
        $destRatio = $width / $height

        if ($sourceRatio -gt $destRatio) {
            $srcHeight = $bitmap.Height
            $srcWidth = [int]($bitmap.Height * $destRatio)
            $srcX = [int](($bitmap.Width - $srcWidth) / 2)
            $srcY = 0
        } else {
            $srcWidth = $bitmap.Width
            $srcHeight = [int]($bitmap.Width / $destRatio)
            $srcX = 0
            $srcY = [int](($bitmap.Height - $srcHeight) / 2)
        }

        $clipPath = New-RoundedPath $x $y $width $height $radius
        $state = $graphics.Save()
        $graphics.SetClip($clipPath)

        if ($opacity -lt 1.0) {
            $matrix = New-Object System.Drawing.Imaging.ColorMatrix
            $matrix.Matrix33 = [single]$opacity
            $attributes = New-Object System.Drawing.Imaging.ImageAttributes
            $attributes.SetColorMatrix($matrix)
            $graphics.DrawImage(
                $bitmap,
                [System.Drawing.Rectangle]::new([int]$x, [int]$y, [int]$width, [int]$height),
                $srcX, $srcY, $srcWidth, $srcHeight,
                [System.Drawing.GraphicsUnit]::Pixel,
                $attributes
            )
            $attributes.Dispose()
        } else {
            $graphics.DrawImage(
                $bitmap,
                [System.Drawing.Rectangle]::new([int]$x, [int]$y, [int]$width, [int]$height),
                $srcX, $srcY, $srcWidth, $srcHeight,
                [System.Drawing.GraphicsUnit]::Pixel
            )
        }

        $graphics.Restore($state)

        $borderPen = New-Object System.Drawing.Pen ([System.Drawing.Color]::FromArgb(48, 255, 255, 255)), 3
        $graphics.DrawPath($borderPen, $clipPath)
        $borderPen.Dispose()
        $clipPath.Dispose()
    }
    finally {
        $bitmap.Dispose()
    }
}

function Draw-Texture($graphics, $texturePath, $width, $height, $opacity) {
    $texture = [System.Drawing.Image]::FromFile($texturePath)
    try {
        $matrix = New-Object System.Drawing.Imaging.ColorMatrix
        $matrix.Matrix33 = [single]$opacity
        $attributes = New-Object System.Drawing.Imaging.ImageAttributes
        $attributes.SetColorMatrix($matrix)
        $graphics.DrawImage(
            $texture,
            [System.Drawing.Rectangle]::new(0, 0, $width, $height),
            0, 0, $texture.Width, $texture.Height,
            [System.Drawing.GraphicsUnit]::Pixel,
            $attributes
        )
        $attributes.Dispose()
    }
    finally {
        $texture.Dispose()
    }
}

function Draw-Logo($graphics, $logoPath, $x, $y, $width, $height, $opacity) {
    $logo = [System.Drawing.Image]::FromFile($logoPath)
    try {
        $matrix = New-Object System.Drawing.Imaging.ColorMatrix
        $matrix.Matrix33 = [single]$opacity
        $attributes = New-Object System.Drawing.Imaging.ImageAttributes
        $attributes.SetColorMatrix($matrix)
        $graphics.DrawImage(
            $logo,
            [System.Drawing.Rectangle]::new([int]$x, [int]$y, [int]$width, [int]$height),
            0, 0, $logo.Width, $logo.Height,
            [System.Drawing.GraphicsUnit]::Pixel,
            $attributes
        )
        $attributes.Dispose()
    }
    finally {
        $logo.Dispose()
    }
}

function Draw-DotCluster($graphics, $startX, $startY, $spacing, $rows, $cols, $hex, $alpha) {
    $brush = New-Object System.Drawing.SolidBrush (New-Color $hex $alpha)
    for ($row = 0; $row -lt $rows; $row++) {
        for ($col = 0; $col -lt $cols; $col++) {
            $size = if ((($row + $col) % 3) -eq 0) { 10 } elseif ((($row + $col) % 3) -eq 1) { 7 } else { 5 }
            $graphics.FillEllipse($brush, $startX + ($col * $spacing), $startY + ($row * $spacing), $size, $size)
        }
    }
    $brush.Dispose()
}

$root = Split-Path -Parent $PSScriptRoot
$bannerDir = Join-Path $root 'public\images\banner'
$productDir = Join-Path $root 'public\images\product'
$bgDir = Join-Path $root 'public\images\background'
$logoPath = Join-Path $root 'public\images\logo\logo-user.png'

New-Item -ItemType Directory -Force -Path $bannerDir | Out-Null

$items = @(
    @{
        Output = Join-Path $bannerDir 'banner-mobile-1.png'
        Start = '#0E281B'
        End = '#163C29'
        Glow = '#FFAA2C'
        Accent = '#F3C56A'
        Main = Join-Path $productDir 'lau-bo-nhung-dam.jpg'
        MainRect = @(320, 160, 700, 780, 100)
        Secondary = Join-Path $productDir 'tom-nuong-moi.jpg'
        SecondaryRect = @(80, 850, 340, 300, 56)
        Outline = @(250, 120, 780, 980)
    },
    @{
        Output = Join-Path $bannerDir 'banner-mobile-2.png'
        Start = '#2D180B'
        End = '#9A4C15'
        Glow = '#FFD16E'
        Accent = '#FFF0C7'
        Main = Join-Path $productDir 'lau-thai-tomyum.jpg'
        MainRect = @(360, 150, 650, 820, 100)
        Secondary = Join-Path $productDir 'bach-tuoc-hap-sa.jpg'
        SecondaryRect = @(70, 220, 320, 360, 56)
        Outline = @(280, 120, 760, 1020)
    },
    @{
        Output = Join-Path $bannerDir 'banner-mobile-3.png'
        Start = '#3A0E12'
        End = '#7F2516'
        Glow = '#FFAE5B'
        Accent = '#FFDDA6'
        Main = Join-Path $productDir 'mi-xao-hai-san.jpg'
        MainRect = @(300, 120, 730, 860, 100)
        Secondary = Join-Path $productDir 'lau-rieu-cua.jpg'
        SecondaryRect = @(70, 860, 360, 280, 56)
        Outline = @(220, 80, 820, 1030)
    }
)

$texturePath = Join-Path $bgDir 'header-bg.jpg'
$canvasWidth = 1080
$canvasHeight = 1520

foreach ($item in $items) {
    $bitmap = New-Object System.Drawing.Bitmap $canvasWidth, $canvasHeight
    $graphics = [System.Drawing.Graphics]::FromImage($bitmap)

    try {
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::AntiAlias
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
        $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
        $graphics.TextRenderingHint = [System.Drawing.Text.TextRenderingHint]::AntiAliasGridFit

        $backgroundRect = [System.Drawing.Rectangle]::new(0, 0, $canvasWidth, $canvasHeight)
        $backgroundBrush = New-Object System.Drawing.Drawing2D.LinearGradientBrush (
            $backgroundRect,
            (New-Color $item.Start),
            (New-Color $item.End),
            90
        )
        $graphics.FillRectangle($backgroundBrush, $backgroundRect)
        $backgroundBrush.Dispose()

        Draw-Texture $graphics $texturePath $canvasWidth $canvasHeight 0.14
        Draw-Glow $graphics -120 920 760 500 $item.Glow 110
        Draw-Glow $graphics 420 -140 780 720 $item.Glow 90
        Draw-Glow $graphics 620 520 520 520 '#FFFFFF' 30

        $outline = $item.Outline
        Draw-OutlineArc $graphics $outline[0] $outline[1] $outline[2] $outline[3] $item.Accent 62 7
        Draw-OutlineArc $graphics ($outline[0] + 90) ($outline[1] + 120) ($outline[2] - 170) ($outline[3] - 220) $item.Accent 28 3

        Draw-DotCluster $graphics 84 122 28 3 5 $item.Accent 85

        Draw-Logo $graphics $logoPath 74 56 220 160 0.34

        $mainRect = $item.MainRect
        Draw-ShadowedPanel $graphics ($mainRect[0] - 8) ($mainRect[1] - 8) ($mainRect[2] + 16) ($mainRect[3] + 16) ($mainRect[4] + 8) ([System.Drawing.Color]::FromArgb(16, 255, 255, 255)) 0
        Draw-CoverImage $graphics $item.Main $mainRect[0] $mainRect[1] $mainRect[2] $mainRect[3] $mainRect[4] 1.0

        $secondaryRect = $item.SecondaryRect
        Draw-ShadowedPanel $graphics ($secondaryRect[0] - 6) ($secondaryRect[1] - 6) ($secondaryRect[2] + 12) ($secondaryRect[3] + 12) ($secondaryRect[4] + 6) ([System.Drawing.Color]::FromArgb(18, 255, 255, 255)) 0
        Draw-CoverImage $graphics $item.Secondary $secondaryRect[0] $secondaryRect[1] $secondaryRect[2] $secondaryRect[3] $secondaryRect[4] 1.0

        $ringPen = New-Object System.Drawing.Pen (New-Color $item.Accent 58), 5
        $graphics.DrawEllipse($ringPen, 92, 1260, 180, 180)
        $graphics.DrawEllipse($ringPen, 840, 1070, 120, 120)
        $ringPen.Dispose()

        $outputDir = Split-Path -Parent $item.Output
        New-Item -ItemType Directory -Force -Path $outputDir | Out-Null
        $bitmap.Save($item.Output, [System.Drawing.Imaging.ImageFormat]::Png)
    }
    finally {
        $graphics.Dispose()
        $bitmap.Dispose()
    }
}

Write-Host 'Generated mobile banners:'
$items | ForEach-Object { Write-Host $_.Output }
