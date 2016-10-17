<?php
// 需要php5.6以上版本才支持64位整形 
class BinaryBytes {
        
        // 转换字符串为j二进制流，$type的值有a和A两种
        public static function toString($string, $length, $ispack = true, $type = 'a'){ 
            return $ispack ? pack($type.$length, $string) : unpack($type.$length, $string);  
        } 
    
        public static function int8($i) {
            return is_numeric($i) ? pack("c", $i) : unpack("c", $i)[1];
        }

        public static function uInt8($i) {
            return is_numeric($i) ? pack("C", $i) : unpack("C", $i)[1];
        }

        public static function int16($i) {
            return is_numeric($i) ? pack("s", $i) : unpack("s", $i)[1];
        }

        public static function uInt16($i, $endianness=false) {
            $f = is_numeric($i) ? "pack" : "unpack";

            if ($endianness === true) {  // big-endian
                $i = $f("n", $i);
            }
            else if ($endianness === false) {  // little-endian
                $i = $f("v", $i);
            }
            else if ($endianness === null) {  // machine byte order
                $i = $f("S", $i);
            }

            return is_array($i) ? $i[1] : $i;
        }

        public static function int32($i) {
            return is_numeric($i) ? pack("l", $i) : unpack("l", $i)[1];
        }

        public static function uInt32($i, $endianness=false) {
            $f = is_numeric($i) ? "pack" : "unpack";

            if ($endianness === true) {  // big-endian
                $i = $f("N", $i);
            }
            else if ($endianness === false) {  // little-endian
                $i = $f("V", $i);
            }
            else if ($endianness === null) {  // machine byte order
                $i = $f("L", $i);
            }

            return is_array($i) ? $i[1] : $i;
        }

        public static function int64($i) {
            return is_numeric($i) ? pack("q", $i) : unpack("q", $i)[1];
        }

        public static function uInt64($i, $endianness=false) {
            $f = is_numeric($i) ? "pack" : "unpack";

            if ($endianness === true) {  // big-endian
                $i = $f("J", $i);
            }
            else if ($endianness === false) {  // little-endian
                $i = $f("P", $i);
            }
            else if ($endianness === null) {  // machine byte order
                $i = $f("Q", $i);
            }

            return is_array($i) ? $i[1] : $i;
        }
    }
?>
