<?php

class DAOGenerator {

    public static function generate($pdo, $dbName, $tableName, $options = "CRUD+OJAS12345", $separateDAOAndDTOFiles = false) {
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
        $classStart .= "<?php" . DAOGenerator::lineBreak(2);

        $dao = "";
        $dto = "";

        if (strstr($options, "O")) {
            $dto .= DAOGenerator::propString($allCols) . DAOGenerator::lineBreak();
            $dto .= DAOGenerator::tab() . 'public function __construct(' . DAOGenerator::constructString($allCols) . ') {' . DAOGenerator::lineBreak();
            $dto .= DAOGenerator::feedString($allCols);
            $dto .= DAOGenerator::tab() . '}' . DAOGenerator::lineBreak(2);
            $dto .= DAOGenerator::getterString($allCols);
            $dto .= DAOGenerator::setterString($allCols);
            if (strstr($options, "J")) {
                $dto .= DAOGenerator::tab() . "public function toJSONString() {" . DAOGenerator::lineBreak();
                $dto .= DAOGenerator::tab(2) . "return(json_encode((array) \$this));" . DAOGenerator::lineBreak();
                $dto .= DAOGenerator::tab() . "}" . DAOGenerator::lineBreak(2);
            }
            if (strstr($options, "A")) {
                $dto .= DAOGenerator::tab() . "public function toArray() {" . DAOGenerator::lineBreak();
                $dto .= DAOGenerator::tab(2) . "return((array) \$this);" . DAOGenerator::lineBreak();
                $dto .= DAOGenerator::tab() . "}" . DAOGenerator::lineBreak(2);
            }
            if (strstr($options, "S")) {
                $dto .= DAOGenerator::toStringString($tableName, $allCols) . DAOGenerator::lineBreak(2);
            }
        }
        if (strstr($options, "C")) {
            if (strstr($options, "O") AND strstr($options, "1")) {
                $dao .= DAOGenerator::tab() . "public static function insert(\$pdo, $" . DAOGenerator::underscoreToCamelCase($tableName) . ") {" . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab() . "public static function insert(\$pdo, " . DAOGenerator::constructString($notAICols) . ") {" . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(2) . "try {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$sql = "INSERT INTO ' . $dbName . '.' . $tableName . ' (' . implode($notAICols, ', ') . ') values(' . DAOGenerator::questionMark(count($notAICols)) . ')";' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "1")) {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::argsString($tableName, $notAICols) . '));' . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::constructString($notAICols) . '));' . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(3) . 'return(1);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '} catch (PDOException $e) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'return($e);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(1) . '}' . DAOGenerator::lineBreak(2);
        }
        if (strstr($options, "R")) {
            $dao .= DAOGenerator::tab() . "public static function selectAll(\$pdo) {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . "try {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$sql = "SELECT * FROM ' . $dbName . '.' . $tableName . '";' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor = $pdo()->query($sql);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor->setFetchMode(PDO::FETCH_ASSOC);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$ret = $cursor->fetchAll();' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'if ($ret == false) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(4) . 'return null;' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '} else {' . DAOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "2")) {
                $dao .= DAOGenerator::tab(4) . '$max = count($ret);' . DAOGenerator::lineBreak();
                $dao .= DAOGenerator::tab(4) . '$o = [];' . DAOGenerator::lineBreak();
                $dao .= DAOGenerator::tab(4) . 'for ($i = 0; $i < $max; $i++) {' . DAOGenerator::lineBreak();
                $dao .= DAOGenerator::tab(5) . '$o[] = new ' . DAOGenerator::underscoreToCamelCase($tableName, true) . '(' . DAOGenerator::instantiateString($allCols) . ');' . DAOGenerator::lineBreak();
                $dao .= DAOGenerator::tab(4) . '}' . DAOGenerator::lineBreak();
                $dao .= DAOGenerator::tab(4) . 'return $o;' . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab(4) . 'return $ret;' . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(3) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '} catch (PDOException $e) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'return($e);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(1) . '}' . DAOGenerator::lineBreak(2);
        }
        if (strstr($options, "U")) {
            if (strstr($options, "O") AND strstr($options, "3")) {
                $dao .= DAOGenerator::tab() . "public static function update(\$pdo, $" . DAOGenerator::underscoreToCamelCase($tableName) . ") {" . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab() . "public static function update(\$pdo, " . DAOGenerator::constructString($allCols) . ") {" . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(2) . "try {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$sql = "UPDATE ' . $dbName . '.' . $tableName . ' SET ' . DAOGenerator::setString($notAICols) . ' WHERE ' . DAOGenerator::whereString($priCols) . '";' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "3")) {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::argsString($tableName, $notAICols) . ', ' . DAOGenerator::argsString($tableName, $priCols) . '));' . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::constructString($notAICols) . ', ' . DAOGenerator::constructString($priCols) . '));' . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(3) . 'return(1);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '} catch (PDOException $e) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'return($e);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(1) . '}' . DAOGenerator::lineBreak(2);
        }
        if (strstr($options, "D")) {
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAOGenerator::tab() . "public static function delete(\$pdo, $" . DAOGenerator::underscoreToCamelCase($tableName) . ") {" . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab() . "public static function delete(\$pdo, " . DAOGenerator::constructString($allCols) . ") {" . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(2) . "try {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$sql = "DELETE FROM ' . $dbName . '.' . $tableName . ' WHERE ' . DAOGenerator::whereString($priCols) . '";' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::argsString($tableName, $notAICols) . '));' . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::constructString($allCols) . '));' . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(3) . 'return(1);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '} catch (PDOException $e) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'return($e);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(1) . '}' . DAOGenerator::lineBreak(2);
        }
        if (strstr($options, "+")) {
            $dao .= DAOGenerator::tab() . "public static function selectOne(\$pdo, " . DAOGenerator::priParamString($priCols) . ") {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . "try {" . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$sql = "DELETE FROM ' . $dbName . '.' . $tableName . ' WHERE ' . DAOGenerator::whereString($priCols) . '";' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor = $pdo->prepare($sql);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor->execute(array(' . DAOGenerator::priParamString($priCols) . '));' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$cursor->setFetchMode(PDO::FETCH_ASSOC);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '$ret = $cursor->fetch();' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'if ($ret == false) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(4) . 'return null;' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . '} else {' . DAOGenerator::lineBreak();
            if (strstr($options, "O") AND strstr($options, "5")) {
                $dao .= DAOGenerator::tab(4) . 'return new ' . DAOGenerator::underscoreToCamelCase($tableName, true) . '(' . DAOGenerator::instantiateString($allCols) . ');' . DAOGenerator::lineBreak();
            } else {
                $dao .= DAOGenerator::tab(4) . 'return $ret;' . DAOGenerator::lineBreak();
            }
            $dao .= DAOGenerator::tab(3) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '} catch (PDOException $e) {' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(3) . 'return($e);' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(2) . '}' . DAOGenerator::lineBreak();
            $dao .= DAOGenerator::tab(1) . '}' . DAOGenerator::lineBreak(2);
        }
        if ($separateDAOAndDTOFiles) {
            if ((strstr($options,"C") || strstr($options,"R") || strstr($options,"U") || strstr($options,"D") || strstr($options,"+")) && strstr($options, "O")){
                return([$classStart."class " . DAOGenerator::underscoreToCamelCase($tableName, true) . "DAO {" . DAOGenerator::lineBreak(2).$dao."}" . DAOGenerator::lineBreak(2)."?>",$classStart."class " . DAOGenerator::underscoreToCamelCase($tableName, true) . "DTO {" . DAOGenerator::lineBreak(2).$dto."}" . DAOGenerator::lineBreak(2)."?>"]);
            } else if(strstr($options, "O")){
                return($classStart."class " . DAOGenerator::underscoreToCamelCase($tableName, true) . "DTO {" . DAOGenerator::lineBreak(2).$dto."}" . DAOGenerator::lineBreak(2)."?>");
            } else if(strstr($options,"C") || strstr($options,"R") || strstr($options,"U") || strstr($options,"D") || strstr($options,"+")){
                return($classStart."class " . DAOGenerator::underscoreToCamelCase($tableName, true) . "DAO {" . DAOGenerator::lineBreak(2).$dao."}" . DAOGenerator::lineBreak(2)."?>");
            } else {
                return("");
            }
        }
        else {
        return($classStart."class " . DAOGenerator::underscoreToCamelCase($tableName, true) . " {" . DAOGenerator::lineBreak(2).$dto.$dao."}" . DAOGenerator::lineBreak(2)."?>");
        }
    }

    private static function toJSONString($tableName, $cols) {
        $str = "";
        $str .= DAOGenerator::tab() . "public function toString() {" . DAOGenerator::lineBreak() . DAOGenerator::tab(2) . "return('" . DAOGenerator::underscoreToCamelCase($tableName, true) . " : {";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::underscoreToCamelCase($cols[0]) . ":' . \$this->" . DAOGenerator::underscoreToCamelCase($cols[0]) . " . ',";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $str .= "}');" . DAOGenerator::lineBreak() . DAOGenerator::tab(1) . "}";
        return($str);
    }

    private static function toStringString($tableName, $cols) {
        $str = "";
        $str .= DAOGenerator::tab() . "public function toString() {" . DAOGenerator::lineBreak() . DAOGenerator::tab(2) . "return('" . DAOGenerator::underscoreToCamelCase($tableName, true) . " : {";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::underscoreToCamelCase($cols[$i]) . ":' . \$this->" . DAOGenerator::underscoreToCamelCase($cols[$i]) . " . ',";
        }
        $str = substr($str, 0, strlen($str) - 1);
        $str .= "}');" . DAOGenerator::lineBreak() . DAOGenerator::tab(1) . "}";
        return($str);
    }

    private static function instantiateString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= '$ret["' . DAOGenerator::underscoreToCamelCase($cols[$i]) . '"], ';
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function getterString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::tab(1) . 'public function get' . DAOGenerator::underscoreToCamelCase($cols[$i], true) . '() {' . DAOGenerator::lineBreak();
            $str .= DAOGenerator::tab(2) . 'return($this->' . DAOGenerator::underscoreToCamelCase($cols[$i]) . ');' . DAOGenerator::lineBreak();
            $str .= DAOGenerator::tab(1) . "}" . DAOGenerator::lineBreak(2);
        }
        return($str);
    }

    private static function setterString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::tab(1) . 'public function set' . DAOGenerator::underscoreToCamelCase($cols[$i], true) . '($' . DAOGenerator::underscoreToCamelCase($cols[$i]) . ') {' . DAOGenerator::lineBreak();
            $str .= DAOGenerator::tab(2) . "\$this->" . DAOGenerator::underscoreToCamelCase($cols[$i]) . " = $" . DAOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAOGenerator::lineBreak();
            $str .= DAOGenerator::tab(1) . "}" . DAOGenerator::lineBreak(2);
        }
        return($str);
    }

    private static function feedString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::tab(2) . "\$this->" . DAOGenerator::underscoreToCamelCase($cols[$i]) . " = $" . DAOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAOGenerator::lineBreak();
        }
        return($str);
    }

    private static function constructString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= '$' . DAOGenerator::underscoreToCamelCase($cols[$i]) . ", ";
        }
        $str = substr($str, 0, strlen($str) - 2);
        return($str);
    }

    private static function propString($cols) {
        $str = "";
        $max = count($cols);
        for ($i = 0; $i < $max; $i++) {
            $str .= DAOGenerator::tab() . "private $" . DAOGenerator::underscoreToCamelCase($cols[$i]) . ";" . DAOGenerator::lineBreak();
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
            $str .= "$" . DAOGenerator::underscoreToCamelCase($objectName) . "->get" . DAOGenerator::underscoreToCamelCase($cols[$i], true) . "(), ";
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
