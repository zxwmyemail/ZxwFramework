<?php
namespace core\extend\qrcode;
/********************************************************************************************
 二维码类，github地址： 
 https://github.com/endroid/qr-code
*********************************************************************************************/
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

class MyQrCode {

    /**
     * 获取二维码
     * @param string  $content  二维码信息
     */
    public static function get($content, $size = 300, $margin = 10, $logoPath = '', $saveToFile = false) {
        $qrCode = new QrCode($content);
        $qrCode->setSize($size);

        // Set advanced options
        $qrCode->setWriterByName('png');
        $qrCode->setMargin($margin);
        $qrCode->setEncoding('UTF-8');
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevel(ErrorCorrectionLevel::HIGH));
        $qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
        $qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
        if ($logoPath) {
            $qrCode->setLogoPath($logoPath);
            $qrCode->setLogoSize(120, 120);
        }
        $qrCode->setRoundBlockSize(true);
        $qrCode->setValidateResult(false);

        if ($saveToFile) {
            $fileName = 'qrcode_' . md5(time()) . '.png';
            $qrCode->writeFile(WEB_PATH . '/qrcode/' . $fileName);
            return $fileName;
        } else {
            // Directly output the QR code
            ob_clean();
            header('Content-Type: ' . $qrCode->getContentType());
            echo $qrCode->writeString();
        }
    }

}

?>
