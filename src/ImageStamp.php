<?php

/**
 * 图片水印类
 * 支持文字水印和图片水印, 支持水印透明度设置
 * https://blog.csdn.net/luoluozlb/article/details/73351184
 * 支持的图片类型: 'jpg', 'png', 'gif'
 * @author luoluolzb 2017/6/17
 */
class ImageStamp
{
    /**
     * 确定水印位置的常量
     * @var const
     */
    const
        STAMP_TOP = 1,
        STAMP_BOTTOM = 2,
        STAMP_LEFT = 4,
        STAMP_RIGHT = 8,
        STAMP_CENTER = 16;

    /**
     * 水印距离图片边缘的距离(非居中时)常量
     * @var const
     */
    const STAMP_SIDE = 10;

    /**
     * 水印默认位置
     * @var const
     */
    const STAMP_DEFAULT_LOC = 10;    //STAMP_BOTTOM | STAMP_RIGHT

    /**
     * @var private
     */
    private $fileName, $mimeString, $imgRes;
    private $imgWidth, $imgHeight;

    /**
     * 打开需要加水印的图片
     * 此函数可以多次使用, 第二次会覆盖第一次
     * @param   string $fileName 图片文件
     * @return  bool   操作结果
     */
    public function Open($fileName)
    {
        if (!file_exists($fileName)) {
            return false;
        }
        if ($this->imgRes) {
            imagedestroy($this->imgRes);
        }

        $this->fileName = $fileName;
        $this->mimeString = self::GetImageMime($fileName);
        $this->imgRes = self::ImageCreate($fileName, $this->mimeString);

        if (!$this->imgRes) {
            return false;
        }
        $this->imgWidth = imagesx($this->imgRes);
        $this->imgHeight = imagesy($this->imgRes);
        return true;
    }

    function __destruct()
    {
        if ($this->imgRes) {
            imagedestroy($this->imgRes);
        }
    }

    /**
     * 发送图片到浏览器
     * @return  bool   操作结果
     */
    public function Send()
    {
        if (!$this->imgRes) {
            return false;
        }
        header('Content-Type: ' . $this->mimeString);
        if ($this->mimeString === 'image/jpeg') {
            return imagejpeg($this->imgRes);
        } else if ($this->mimeString === 'image/png') {
            return imagepng($this->imgRes);
        } else if ($this->mimeString === 'image/gif') {
            return imagegif($this->imgRes);
        }
        return false;
    }

    /**
     * 保存图片(文件后缀名决定保存类型, 只能是：jpg, png, gif)
     * @return  bool   操作结果
     */
    public function Save($fileName)
    {
        if (!$this->imgRes) {
            return false;
        }
        $t = explode('.', $fileName);
        $ext = end($t);
        if ($ext === 'jpg') {
            return imagejpeg($this->imgRes, $fileName);
        } else if ($ext === 'png') {
            return imagepng($this->imgRes, $fileName);
        } else if ($ext === 'gif') {
            return imagegif($this->imgRes, $fileName);
        }
        return false;
    }

    /**
     * 给图片添加文字水印
     * @param   string  $text     文字
     * @param   integer $fontSize 字体大小
     * @param   string  $fontFile 字体文件
     * @param   array   $rgba     颜色(rgb+alpha格式, alpha:0-127, 0完全不透明, 127完全透明)
     * @param   integer $location 水印位置（水印位置常量组合而成，使用'|'运算符）
     * @return  bool   操作结果
     */
    public function AddTextStamp($text, $fontSize, $fontFile, $rgba, $location = self::STAMP_DEFAULT_LOC)
    {
        if (!$this->imgRes) {
            return false;
        }

        $color = imagecolorallocatealpha($this->imgRes, $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
        $box = imagettfbbox($fontSize, 0, $fontFile, $text);
        $stampWidth = abs($box[2] - $box[0]);
        $stampHeight = abs($box[7] - $box[1]);

        if ($stampWidth >= $this->imgWidth || $stampHeight >= $this->imgHeight) {
            return false;
        }
        $loc = $this->StampLocation(true, $stampWidth, $stampHeight, $location);
        return imagettftext($this->imgRes, $fontSize, 0, $loc['x'], $loc['y'], $color, $fontFile, $text);
    }

    /**
     * 给图片添加图片水印
     * @param   string   $stampFile 图片文件
     * @param   $integer $alpha     水印图的透明度(0~100, 0完全透明, 100完全不透明)
     * @param   string   $location  水印位置（水印位置常量组合而成，使用'|'运算符）
     * @return  bool     操作结果
     */
    public function AddImageStamp($stampFile, $alpha, $location = self::STAMP_DEFAULT_LOC)
    {
        if (!$this->imgRes) {
            return false;
        }

        $stampMimeString = self::GetImageMime($stampFile);
        $stampRes = self::ImageCreate($stampFile, $stampMimeString);
        if (!$stampRes) {
            return false;
        }
        $stampWidth = imagesx($stampRes);
        $stampHeight = imagesy($stampRes);

        if ($stampWidth >= $this->imgWidth || $stampHeight >= $this->imgHeight) {
            return false;
        }
        $loc = $this->StampLocation(false, imagesx($stampRes), imagesy($stampRes), $location);
        return self::imagecopymerge_alpha($this->imgRes, $stampRes, $loc['x'], $loc['y'], 0, 0, $stampWidth, $stampHeight, $alpha);
    }

    /**
     * 私有工具函数: 计算水印位置
     * @return  array ['x' => , 'y' => ]
     */
    private function StampLocation($isTextStamp, $stampWidth, $stampHeight, $location)
    {
        $ret = array(
            'x' => self::STAMP_SIDE,
            'y' => self::STAMP_SIDE
        );

        if ($location & self::STAMP_LEFT) {
            $ret['x'] = self::STAMP_SIDE;
        } else if ($location & self::STAMP_RIGHT) {
            $ret['x'] = $this->imgWidth - $stampWidth - self::STAMP_SIDE;
        }

        if ($location & self::STAMP_TOP) {
            if ($isTextStamp) {
                $ret['y'] = $stampHeight + self::STAMP_SIDE;
            } else {
                $ret['y'] = self::STAMP_SIDE;
            }
        } else if ($location & self::STAMP_BOTTOM) {
            if ($isTextStamp) {
                $ret['y'] = $this->imgHeight - self::STAMP_SIDE;
            } else {
                $ret['y'] = $this->imgHeight - $stampHeight - self::STAMP_SIDE;
            }
        }

        if ($location & self::STAMP_CENTER) {
            /*竖直方向上的居中*/
            if (($location & self::STAMP_LEFT) || ($location & self::STAMP_RIGHT)) {
                if ($isTextStamp) {
                    $ret['y'] = ($this->imgHeight - $stampHeight) / 2 + $stampHeight;
                } else {
                    $ret['y'] = ($this->imgHeight - $stampHeight) / 2;
                }
            }

            /*水平方向上的居中*/ else if (($location & self::STAMP_TOP) || ($location & self::STAMP_BOTTOM)) {
                $ret['x'] = ($this->imgWidth - $stampWidth) / 2;
            }

            /*水平和竖直都居中*/ else {
                if ($isTextStamp) {
                    $ret['y'] = ($this->imgHeight - $stampHeight) / 2 + $stampHeight;
                } else {
                    $ret['y'] = ($this->imgHeight - $stampHeight) / 2;
                }
                $ret['x'] = ($this->imgWidth - $stampWidth) / 2;
            }
        }

        return $ret;
    }

    /**
     * 通用工具函数: 贴图函数, 并可以设置贴图的alpha透明
     * 参数同 imagecopymerge
     */
    public static function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
    {
        $cut = imagecreatetruecolor($src_w, $src_h);
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

    /**
     * 通用工具函数: 获取图片mime类型
     * @param string $fileName 图片文件
     * @return false 失败, string mime串
     */
    public static function GetImageMime($fileName)
    {
        $fhanle = fopen($fileName, 'rb');
        if ($fhanle) {
            $bytes6 = fread($fhanle, 6);
            fclose($fhanle);
            if ($bytes6 === false) return false;
            if (substr($bytes6, 0, 3) == "\xff\xd8\xff") return 'image/jpeg';
            if ($bytes6 == "\x89PNG\x0d\x0a") return 'image/png';
            if ($bytes6 == "GIF87a" || $bytes6 == "GIF89a") return 'image/gif';
        }
        return false;
    }

    /**
     * 通用工具函数: 使用gd库打开图片
     * @param  string $fileName   图片文件
     * @return false 操作失败, string $mimeString 图片mime串
     */
    public static function ImageCreate($fileName, $mimeString)
    {
        switch ($mimeString) {
            case 'image/jpeg':
                return imagecreatefromjpeg($fileName);
                break;

            case 'image/png':
                return imagecreatefrompng($fileName);
                break;

            case 'image/gif':
                return imagecreatefromgif($fileName);
                break;

            default:
                return false;
                break;
        }
    }
}
