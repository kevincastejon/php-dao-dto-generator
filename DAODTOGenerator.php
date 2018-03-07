<?php

class DAODTOGenerator {

    public static function generate($pdo, $dbName, $tableName, $options = "CRUD+OJAS12345", $activeRecord = true) {
        $tableListRequest = 'SELECT COLUMN_NAME, COLUMN_KEY, EXTRA FROM information_schema.COLUMNS WHERE information_schema.COLUMNS.TABLE_SCHEMA = "' . $dbName . '" AND information_schema.COLUMNS.TABLE_NAME = "' . $tableName . '"';
        $result = $pdo->query($tableListRequest);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        $allCols = [];
        $priCols = [];
        $aiCols = [];
        $notPRICols = [];
        $notAICols = [];
        foreach ($result as $v) {
            $allCols[] = $v["COLUMN_NAME"];
            if ($v["COLUMN_KEY"] == "PRI") {
                $priCols[] = $v["COLUMN_NAME"];
            } else {
                $notPRICols[] = $v["COLUMN_NAME"];
            }
            if ($v["EXTRA"] == "auto_increment") {
                $aiCols[] = $v["COLUMN_NAME"];
            } else {
                $notAICols[] = $v["COLUMN_NAME"];
            }
        }
        $classStart = "";
        $classStart .= "<?php" . DAODTOGenerator::lineBreak(2);

        $dao = "";
        $dto = "";

        if (strstr($options, "O")) {
            $dto .= DAODTOGenerator::propString($allCols) . DAODTOGenerator::lineBreak();
            $dto .= DAODTOGenerator::tab() . 'public function __construct(' . DAODTOGenerator::constructString($allCols) . ') {' . DAODTOGenerator::lineBreak();
            $dto .= DAODTOGenerator::feedString($allCols);
            $dto .= DAODTOGenerator::tab() . '}' . DAODTOGenerator::lineBreak(2);
            $dto .= DAODTOGenerator::getterString($allCols);
            $dto .= DAODTOGenerator::setterString($allCols);
            if (strstr($options, "J")) {
                $dto .= DAODTOGenerator::tab() . "public function toJSONString() {" . DAODTOGenerator::lineBreak();
                $dto .= DAODTOGenerator::tab(2) . "return(json_encode((array) \$this));" . DAODTOGenerator::lineBreak();
                $dto .= DAODTOGenerator::tab() . "}" . DAODTOGenerator::lineBreak(2);
            }
            if (strstr($options, "A")) {
                $dto .= DAODTOGenerator::tab() . "public function toArray() {" . DAODTOGenerator::lineBreak();
                $dto .= DAODTOGenerator::tab(2) . "return((array) \$this);" . DAODTOGenerator::lineBreak();
                $dto .= DAODTOGenerator::tab() . "}" . DAODTOGenerator::lineBreak(2);
            }
            if (strstr($options, "S")) {
                $dto .= DAODTOGenerator::toStringString($tableName, $allCols) . DAODTOGenerator::lineBreak(2);
            }
        }
        if (strstr($options, "C")) {
            if (strstr($options, "O") AND strstr($options, "1")) {
                $dao .= DAODTOGenerator::tab() . "public static function insert(\$pdo, $" . DAODTOGenerator::underscoreToCamelCase($tableName) . ") {" . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab() . "public static function insert(\$pdo, " . DAODTOGenerator::constructString($notAICols) . ") {" . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(2) . "try {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$sql = "INSERT INTO ' . $dbName . '.' . $tableName . ' (' . implode($notAICols, ', ') . ') values(' . DAODTOGenerator::questionMark(count($notAICols)) . ')";' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAODTOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "1")) {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::argsString($tableName, $notAICols) . '));' . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::constructString($notAICols) . '));' . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(3) . 'return(1);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '} catch (PDOException $e) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'return($e);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(1) . '}' . DAODTOGenerator::lineBreak(2);
        }
        if (strstr($options, "R")) {
            $dao .= DAODTOGenerator::tab() . "public static function selectAll(\$pdo) {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . "try {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$sql = "SELECT * FROM ' . $dbName . '.' . $tableName . '";' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor = $pdo->query($sql);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor->setFetchMode(PDO::FETCH_ASSOC);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$ret = $cursor->fetchAll();' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'if ($ret == false) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(4) . 'return null;' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '} else {' . DAODTOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "2")) {
                $dao .= DAODTOGenerator::tab(4) . '$max = count($ret);' . DAODTOGenerator::lineBreak();
                $dao .= DAODTOGenerator::tab(4) . '$o = [];' . DAODTOGenerator::lineBreak();
                $dao .= DAODTOGenerator::tab(4) . 'for ($i = 0; $i < $max; $i++) {' . DAODTOGenerator::lineBreak();
                $dao .= DAODTOGenerator::tab(5) . '$o[] = new ' . DAODTOGenerator::underscoreToCamelCase($tableName, true) . '(' . DAODTOGenerator::instantiateString($allCols) . ');' . DAODTOGenerator::lineBreak();
                $dao .= DAODTOGenerator::tab(4) . '}' . DAODTOGenerator::lineBreak();
                $dao .= DAODTOGenerator::tab(4) . 'return $o;' . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab(4) . 'return $ret;' . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(3) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '} catch (PDOException $e) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'return($e);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(1) . '}' . DAODTOGenerator::lineBreak(2);
        }
        if (strstr($options, "U")) {
            if (strstr($options, "O") AND strstr($options, "3")) {
                $dao .= DAODTOGenerator::tab() . "public static function update(\$pdo, $" . DAODTOGenerator::underscoreToCamelCase($tableName) . ") {" . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab() . "public static function update(\$pdo, " . DAODTOGenerator::constructString($allCols) . ") {" . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(2) . "try {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$sql = "UPDATE ' . $dbName . '.' . $tableName . ' SET ' . DAODTOGenerator::setString($notAICols) . ' WHERE ' . DAODTOGenerator::whereString($priCols) . '";' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAODTOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "3")) {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::argsString($tableName, $notAICols) . ', ' . DAODTOGenerator::argsString($tableName, $priCols) . '));' . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::constructString($notAICols) . ', ' . DAODTOGenerator::constructString($priCols) . '));' . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(3) . 'return(1);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '} catch (PDOException $e) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'return($e);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(1) . '}' . DAODTOGenerator::lineBreak(2);
        }
        if (strstr($options, "D")) {
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAODTOGenerator::tab() . "public static function delete(\$pdo, $" . DAODTOGenerator::underscoreToCamelCase($tableName) . ") {" . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab() . "public static function delete(\$pdo, " . DAODTOGenerator::constructString($allCols) . ") {" . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(2) . "try {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$sql = "DELETE FROM ' . $dbName . '.' . $tableName . ' WHERE ' . DAODTOGenerator::whereString($priCols) . '";' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAODTOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::argsString($tableName, $notAICols) . '));' . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::constructString($allCols) . '));' . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(3) . 'return(1);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '} catch (PDOException $e) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'return($e);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(1) . '}' . DAODTOGenerator::lineBreak(2);
        }
        if (strstr($options, "+")) {
            $dao .= DAODTOGenerator::tab() . "public static function selectOne(\$pdo, " . DAODTOGenerator::priParamString($priCols) . ") {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . "try {" . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$sql = "DELETE FROM ' . $dbName . '.' . $tableName . ' WHERE ' . DAODTOGenerator::whereString($priCols) . '";' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor->execute(array(' . DAODTOGenerator::priParamString($priCols) . '));' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$cursor->setFetchMode(PDO::FETCH_ASSOC);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '$ret = $cursor->fetch();' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'if ($ret == false) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(4) . 'return null;' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . '} else {' . DAODTOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAODTOGenerator::tab(4) . 'return new ' . DAODTOGenerator::underscoreToCamelCase($tableName, true) . '(' . DAODTOGenerator::instantiateString($allCols) . ');' . DAODTOGenerator::lineBreak();
            } else {
                $dao .= DAODTOGenerator::tab(4) . 'return $ret;' . DAODTOGenerator::lineBreak();
            }
            $dao .= DAODTOGenerator::tab(3) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '} catch (PDOException $e) {' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(3) . 'return($e);' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(2) . '}' . DAODTOGenerator::lineBreak();
            $dao .= DAODTOGenerator::tab(1) . '}' . DAODTOGenerator::lineBreak(2);
        }
        if (!$activeRecord) {
            if ((strstr($options,"C") || strstr($options,"R") || strstr($options,"U") || strstr($options,"D") || strstr($options,"+")) && strstr($options, "O")){
                return([$classStart."class " . DAODTOGenerator::underscoreToCamelCase($tableName, true) . "DAO {" . DAODTOGenerator::lineBreak(2).$dao."}" . DAODTOGenerator::lineBreak(2)."?>",$classStart."class " . DAODTOGenerator::underscoreToCamelCase($tableName, true) . "DTO {" . DAODTOGenerator::lineBreak(2).$dto."}" . DAODTOGenerator::lineBreak(2)."?>"]);
            } else if(strstr($options, "O")){
                return($classStart."class " . DAODTOGenerator::underscoreToCamelCase($tableName, true) . "DTO {" . DAODTOGenerator::lineBreak(2).$dto."}" . DAODTOGenerator::lineBreak(2)."?>");
            } else if(strstr($options,"C") || strstr($options,"R") || strstr($options,"U") || strstr($options,"D") || strstr($options,"+")){
                return($classStart."class " . DAODTOGenerator::underscoreToCamelCase($tableName, true) . "DAO {" . DAODTOGenerator::lineBreak(2).$dao."}" . DAODTOGenerator::lineBreak(2)."?>");
            } else {
                return("");
            }
        }
        else {
        return($classStart."class " . DAODTOGenerator::underscoreToCamelCase($tableName, true) . " {" . DAODTOGenerator::lineBreak(2).$dto.$dao."}" . DAODTOGenerator::lineBreak(2)."?>");
        }
    }

    private static function toJSONString($tableName, $cols) {
        $str = "";
        $str .= DAODTOGenerator::tab() . "public function toString() {" . DAODTOGenerator::lineBreak() . DAODTOGenerator::tab(2) . "return('" . DAODTOGenerator::underscoreToCamelCase($tableName, true) . " : {";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::underscoreToCamelCase($cols[0]) . ":' . \$this->" . DAODTOGenerator::underscoreToCamelCase($cols[0]) . " . ',";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $str .= "}');" . DAODTOGenerator::lineBreak() . DAODTOGenerator::tab(1) . "}";
        return($str);
    }

    private static function toStringString($tableName, $cols) {
        $str = "";
        $str .= DAODTOGenerator::tab() . "public function toString() {" . DAODTOGenerator::lineBreak() . DAODTOGenerator::tab(2) . "return('" . DAODTOGenerator::underscoreToCamelCase($tableName, true) . " : {";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ":' . \$this->" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . " . ',";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $str .= "}');" . DAODTOGenerator::lineBreak() . DAODTOGenerator::tab(1) . "}";
        return($str);
    }

    private static function instantiateString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= '$ret["' . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . '"], ';
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function getterString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::tab(1) . 'public function get' . DAODTOGenerator::underscoreToCamelCase($cols[$i], true) . '() {' . DAODTOGenerator::lineBreak();
            $str .= DAODTOGenerator::tab(2) . 'return($this->' . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ');' . DAODTOGenerator::lineBreak();
            $str .= DAODTOGenerator::tab(1) . "}" . DAODTOGenerator::lineBreak(2);
        }
        return($str);
    }

    private static function setterString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::tab(1) . 'public function set' . DAODTOGenerator::underscoreToCamelCase($cols[$i], true) . '($' . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ') {' . DAODTOGenerator::lineBreak();
            $str .= DAODTOGenerator::tab(2) . "\$this->" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . " = $" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAODTOGenerator::lineBreak();
            $str .= DAODTOGenerator::tab(1) . "}" . DAODTOGenerator::lineBreak(2);
        }
        return($str);
    }

    private static function feedString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::tab(2) . "\$this->" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . " = $" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAODTOGenerator::lineBreak();
        }
        return($str);
    }

    private static function constructString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= '$' . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ", ";
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function propString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAODTOGenerator::tab() . "private $" . DAODTOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAODTOGenerator::lineBreak();
        }

        return($str);
    }

    private static function priParamString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= "$" . $cols[$i] . ", ";
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function setString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= $cols[$i] . " = ?, ";
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function whereString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= $cols[$i] . " = ? AND ";
        }
        $str = substr($str, 0, strlen($str) - 5);
        return($str);
    }

    private static function argsString($objectName, $cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= "$" . DAODTOGenerator::underscoreToCamelCase($objectName) . "->get" . DAODTOGenerator::underscoreToCamelCase($cols[$i], true) . "(), ";
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function underscoreToCamelCase($string, $capitalizeFirstCharacter = false) {

        $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }
        return $str;
    }

    private static function tab($num = 1) {
        if ($num < 1) {
            $num = 1;
        }
        $ar = "";
        for ($i = 0; $i < $num; $i++) {
            $ar .= "    ";
        }
        return($ar);
    }

    private static function lineBreak($num = 1) {
        if ($num < 1) {
            $num = 1;
        }
        $ar = "";
        for ($i = 0; $i < $num; $i++) {
            $ar .= "\n";
        }
        return($ar);
    }

    private static function questionMark($num = 1) {
        if ($num < 1) {
            $num = 1;
        }
        $ar = "";
        for ($i = 0; $i < $num; $i++) {
            $ar .= "?,";
        }
        $ar = substr($ar, 0, strlen($ar) - 1);
        return($ar);
    }

}
