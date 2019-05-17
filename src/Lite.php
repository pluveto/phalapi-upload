<?php
require __DIR__ . "/vender/autoload.php";
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
 * 依赖:
 *      gd库/cURL
 *      qcloud/cos-sdk-v5
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


    private $_imgWaterMarkType; //0: 无, 1: 文字, 2: 图片
    private $_imgWaterMarkPath;
    private $_imgWaterMarkText;
    private $_imgWaterMarkAlpha; //透明度
    private $_imgWaterMarkPosition; // 与数字小键盘相同

    private $_overwrite = false;

    private $_detectMime = false;


    private  $secretId = "COS_SECRETID"; //"云 API 密钥 SecretId";
    private $secretKey = "COS_SECRETKEY"; //"云 API 密钥 SecretKey";
    private $region = "ap-beijing"; //设置一个默认的存储桶地域
    private $cosClient = new Qcloud\Cos\Client(
        array(
            'region' => $region,
            'schema' => 'https', //协议头部，默认为http
            'credentials' => array(
                'secretId'  => $secretId,
                'secretKey' => $secretKey
            )
        )
    );
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
    public function uploadToCos()
    {
        try {
            $bucket = "examplebucket-1250000000"; //存储桶名称 格式：BucketName-APPID
            $key = "exampleobject";
            $srcPath = "F:/exampleobject"; //本地文件绝对路径
            $result = $cosClient->putObject(array(
                'Bucket' => $bucket,
                'Key' => $key,
                'Body' => fopen($srcPath, 'rb')
            ));
            print_r($result);
        } catch (\Exception $e) {
            echo "$e\n";
        }
    }
}
