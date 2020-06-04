<?php
namespace core\extend\phpoffice;
/********************************************************************************************
 Excel表格类，github地址： 
 https://github.com/PhpOffice/PhpSpreadsheet
*********************************************************************************************/
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;


class PhpExcel {

    private static $columnIndex = array(
        'A',  'B',  'C',  'D',  'E',  'F',  'G',  'H',  'I',  'J',  'K',  'L',  'M', 
        'N',  'O',  'P',  'Q',  'R',  'S',  'T',  'U',  'V',  'W',  'X',  'Y',  'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 
        'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ',
        'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 
        'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ',
        'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 
        'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ',
        'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG', 'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 
        'DN', 'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU', 'DV', 'DW', 'DX', 'DY', 'DZ',
    );

    /**
     * 使用PHPEXECL导入
     *
     * @param string $file      文件地址
     * @param int    $sheet     工作表sheet(传0则获取第一个sheet)
     * @param int    $columnCnt 列数(传0则自动获取最大列)
     * @param array  $options   操作选项
     *                          array mergeCells 合并单元格数组
     *                          array formula    公式数组
     *                          array format     单元格格式数组
     *
     * @return array
     * @throws Exception
     */
    public static function importExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = []) {
        try {
            /* 转码 */
            $file = iconv("utf-8", "gb2312", $file);

            if (empty($file) OR !file_exists($file)) {
                throw new \Exception('文件不存在!');
            }

            /** @var Xlsx $objRead */
            $objRead = IOFactory::createReader('Xlsx');

            if (!$objRead->canRead($file)) {
                /** @var Xls $objRead */
                $objRead = IOFactory::createReader('Xls');

                if (!$objRead->canRead($file)) {
                    throw new \Exception('只支持导入Excel文件！');
                }
            }

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);
            /* 建立excel对象 */
            $obj = $objRead->load($file);
            /* 获取指定的sheet表 */
            $currSheet = $obj->getSheet($sheet);

            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }

            if (0 == $columnCnt) {
                /* 取得最大的列号 */
                $columnH = $currSheet->getHighestColumn();
                /* 兼容原逻辑，循环时使用的是小于等于 */
                $columnCnt = Coordinate::columnIndexFromString($columnH);
            }

            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];

            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;

                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = Coordinate::stringFromColumnIndex($_column);
                    $cellId   = $cellName . $_row;
                    $cell     = $currSheet->getCell($cellId);

                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }

                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();

                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }

                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }

                    $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                    if (!empty($data[$_row][$cellName])) {
                        $isNull = false;
                    }
                }

                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row]);
                }
            }

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Excel导出
     *
     * @param array  $fields    字段名称，一维数组 ['ID','姓名','性别']
     * @param array  $datas     要导出的数据，二维数组, 如：
     *                          [
                                    ['id' => '1', 'name' => '李逵', 'sex' => '男'], 
                                    ['id' => '2', 'name' => '松江', 'sex' => '男']
                                ]
     * @param string $fileName  导出文件名称
     * @param array  $savePath  保存在服务器的路径
     */
    public static function exportExcel($fileName = 'system', $datas = [], $fields = [], $savePath = '') {
        Cell::setValueBinder(new AdvancedValueBinder());
     
        if (!is_array($datas) || empty($datas)) return false;
        set_time_limit(0);

        $retData = [];
        foreach ($datas as $key => $value) {
            $retData[] = array_values($value);
        }

        /* 设置默认文字样式 */
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'name' => '微软雅黑',
                'size' => 12
            ],
        ];
        $objSpreadsheet = new Spreadsheet();
        $objSpreadsheet->getDefaultStyle()->applyFromArray($styleArray);
        $activeSheet = $objSpreadsheet->setActiveSheetIndex(0);
        
        // 第一行设置标题
        $start = 1;
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $key => $value) {
                $activeSheet->getColumnDimension(self::$columnIndex[$key])->setWidth(30);
                $activeSheet->setCellValue(self::$columnIndex[$key] . $start, $value);
                $activeSheet->getStyle(self::$columnIndex[$key] . $start)->getFont()->setBold(true);
            }
            $start++;
        }

        // 设置表格数据
        if (!empty($retData)) {
            foreach($retData as $index => $record){
                $rowNum = $index + $start;
                foreach ($record as $column => $value) {
                    $activeSheet->setCellValue(self::$columnIndex[$column] . $rowNum, $value);
                } 
            }
        }

        if (!$savePath) {
            $fileName = $fileName . '_' . date('Ymd');
            $fileName = iconv("utf-8", "GB2312//TRANSLIT", $fileName);

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            header('Content-Disposition: attachment;filename=' . $fileName . '.xlsx');
            header("Content-Transfer-Encoding: binary");
            header('Cache-Control: max-age=0');
            $savePath = 'php://output';
        } else {
            $savePath = $options['savePath'];
        }

        ob_clean();
        ob_start();
        $objWriter = IOFactory::createWriter($objSpreadsheet, 'Xlsx');
        $objWriter->save($savePath);
        /* 释放内存 */
        $objSpreadsheet->disconnectWorksheets();
        unset($objSpreadsheet, $datas, $retData);
        ob_end_flush();

        return true;
    }
    
}

