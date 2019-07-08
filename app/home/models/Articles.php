<?php
namespace app\home\models;

use core\system\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Articles extends Model {
    // 对应配置文件database.php中的数据库连接主键
    public $connection = 'slave';

    // 指定表名
    public $table = 'articles';

    // 指定表主键
    public $primaryKey = 'id';

    public static function getFirst() {
        $article = self::first();
        return $article->toArray();
    }

    // //这里是一种方式切换数据库的方式，还有一种就是设置上面的$connection属性，也可以切换数据库
    // public static function getOtherFirst() {
    //     $article = DB::connection('slave')->table('articles')->first();
    //     return get_object_vars($article);
    // }
}