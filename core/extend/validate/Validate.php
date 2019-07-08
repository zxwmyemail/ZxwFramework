<?php
namespace core\extend\validate;
/********************************************************************************************
 参数校验类，github地址： 
 https://github.com/photondragon/webgeeker-validation
*********************************************************************************************/
use WebGeeker\Validation\Validation;

class Validate {

    /**
     * 参数校验
     * @param array  $params         校验参数
     * @param array   $validateRules 校验规则
     */
    public static function check($params, $validateRules) {
        try {
            Validation::validate($params, $validateRules);
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

}

?>
