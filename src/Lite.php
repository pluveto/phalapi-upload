<?php
namespace PhalApi\Upload;

/**
 * 使用方法:
 *      ```
 *      // 载入文件上传插件
 *      $di->upload = new \PhalApi\Upload\Lite();
 *      // 重写请求处理器     
 *      $di->upload->saveImage(file,config)
 *      $di->upload->saveFile(file,config)
 *      
 *      ```
 * 
 * @author ZhangZijing <i@pluvet.com>
 */
class Upload
{
    /**
     * 设置:
     *      上传目录
     *      
     *
     */

    /**
     * 检测文件的 MIME 类型
     * https://www.php.net/manual/zh/function.mime-content-type.php
     */

    private $_uploadPath;
    private $_allowTypes;

    private $_imgMinWidth;
    private $_imgMinHeight;
    private $_imgMaxWidth;
    private $_imgMaxHeight;

    private $_imgWaterMarkPath;
    private $_imgWaterMarkPosition; // 与数字小键盘相同

    private $_overwrite = false;

    private $_detectMime = false;

    /**
     * 处理单个图片上传
     *
     * @param array $file 文件信息数组
     * @return void
     */
    public function uploadImage($file)
    {
        // 
    }
    /**
     * 获得随机文件名
     *
     * @param 扩展名 $ext
     * @return string
     */
    public function getRandomFilename($ext = '')
    { }
}
