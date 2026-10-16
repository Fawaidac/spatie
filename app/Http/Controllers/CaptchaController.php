<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CaptchaController extends Controller
{
    /**
     * Generate dynamic captcha image and store code in session.
     */
    public function generate(Request $request): Response
    {
        // Allowed characters (clean, unambiguous)
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $length = 5;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        // Store in session (lowercase for easy matching)
        session(['captcha_code' => strtolower($code)]);

        // Dimensions
        $width = 140;
        $height = 42;
        $image = imagecreatetruecolor($width, $height);

        // Colors (matching Spark Pink theme)
        $bg = imagecolorallocate($image, 252, 231, 243); // soft pink #FCE7F3
        $textColor = imagecolorallocate($image, 190, 24, 93); // deep berry pink #BE185D
        $lineColor = imagecolorallocate($image, 255, 128, 191); // light pink
        $dotColor = imagecolorallocate($image, 244, 114, 182); // pink dots

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        // Add random noise dots
        for ($i = 0; $i < 60; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $dotColor);
        }

        // Add decorative curves / noise lines
        for ($i = 0; $i < 4; $i++) {
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $lineColor
            );
        }

        // Draw text with built-in font
        $font = 5; // Built-in GD font (highest size)
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);

        $totalTextWidth = $length * ($charWidth + 6);
        $startX = (int) (($width - $totalTextWidth) / 2);

        for ($i = 0; $i < $length; $i++) {
            $y = (int) (($height - $charHeight) / 2) + random_int(-3, 3);
            $x = $startX + ($i * ($charWidth + 7));
            imagechar($image, $font, $x, $y, $code[$i], $textColor);
        }

        // Output buffer
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return response($imageData, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}
