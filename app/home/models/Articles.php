<?php
namespace app\home\models;

use core\system\Model;
use core\extend\monolog\Log;
use Illuminate\Database\Capsule\Manager as DB;

class Articles extends Model {
    public $connection = 'slave';
    // 指定表名
    public $table = 'articles';
    // 指定表主键
    public $primaryKey = 'id';

    public $timestamps = false;

    public static function getFirst() {
        $logger = Log::getInstance();

        $article = new self();
        $article->getConnection()->enableQueryLog();
        $res = $article->where('id', '>=', 1)->update([
            'title' => 'nihao'
        ]);
        $logger->debug(json_encode($article->getConnection()->getQueryLog()));
        
        return $res;
    }

    // //这里是一种方式切换数据库的方式，还有一种就是设置上面的$connection属性，也可以切换数据库
    // public static function getOtherFirst() {
    //     $article = DB::connection('slave')->table('articles')->first();
    //     return get_object_vars($article);
    // }
}