<?php
$typeForm = filter_input(INPUT_POST, "typeForm");
$host = filter_input(INPUT_POST, "host");
$login = filter_input(INPUT_POST, "login");
$pwd = filter_input(INPUT_POST, "pwd");
$selectedDb = filter_input(INPUT_POST, "selectedDb");
$selectedTables = filter_input(INPUT_POST, "selectedTables", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
$generateDAOBtn = filter_input(INPUT_POST, "generateDAOBtn");
$dlDAOBtn = filter_input(INPUT_POST, "dlDAOBtn");
$previewDAOBtn = filter_input(INPUT_POST, "previewDAOBtn");
$CRUD_C = filter_input(INPUT_POST, "CRUD_C");
$CRUD_R = filter_input(INPUT_POST, "CRUD_R");
$CRUD_U = filter_input(INPUT_POST, "CRUD_U");
$CRUD_D = filter_input(INPUT_POST, "CRUD_D");
$CRUD_PLUS = filter_input(INPUT_POST, "CRUD_+");
$CRUD_CDTO = filter_input(INPUT_POST, "CRUD_CDTO");
$CRUD_RDTO = filter_input(INPUT_POST, "CRUD_RDTO");
$CRUD_UDTO = filter_input(INPUT_POST, "CRUD_UDTO");
$CRUD_DDTO = filter_input(INPUT_POST, "CRUD_DDTO");
$CRUD_PLUSDTO = filter_input(INPUT_POST, "CRUD_+DTO");
$CRUD_CDTO_OFF = filter_input(INPUT_POST, "CRUD_C_OFF");
$CRUD_RDTO_OFF = filter_input(INPUT_POST, "CRUD_R_OFF");
$CRUD_UDTO_OFF = filter_input(INPUT_POST, "CRUD_U_OFF");
$CRUD_DDTO_OFF = filter_input(INPUT_POST, "CRUD_D_OFF");
$CRUD_PLUSDTO_OFF = filter_input(INPUT_POST, "CRUD_+_OFF");
$CRUD_C_OFF = filter_input(INPUT_POST, "CRUD_C_OFF");
$CRUD_R_OFF = filter_input(INPUT_POST, "CRUD_R_OFF");
$CRUD_U_OFF = filter_input(INPUT_POST, "CRUD_U_OFF");
$CRUD_D_OFF = filter_input(INPUT_POST, "CRUD_D_OFF");
$CRUD_PLUS_OFF = filter_input(INPUT_POST, "CRUD_+_OFF");
$OJAS_O = filter_input(INPUT_POST, "OJAS_O");
$OJAS_J = filter_input(INPUT_POST, "OJAS_J");
$OJAS_A = filter_input(INPUT_POST, "OJAS_A");
$OJAS_S = filter_input(INPUT_POST, "OJAS_S");
$OJAS_O_OFF = filter_input(INPUT_POST, "OJAS_O_OFF");
$OJAS_J_OFF = filter_input(INPUT_POST, "OJAS_J_OFF");
$OJAS_A_OFF = filter_input(INPUT_POST, "OJAS_A_OFF");
$OJAS_S_OFF = filter_input(INPUT_POST, "OJAS_S_OFF");
try {

    $pdo = new PDO("mysql:host=" . $host, $login, $pwd);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'UTF8'");
} catch (PDOException $e) {
    echo($e->getMessage());
}
if ($typeForm == "tableSelectForm") {
    require_once 'DAOGenerator.php';
    $opts = $CRUD_C . $CRUD_R . $CRUD_U . $CRUD_D . $CRUD_PLUS . $OJAS_O . $OJAS_J . $OJAS_A . $OJAS_S . $CRUD_CDTO . $CRUD_RDTO . $CRUD_UDTO . $CRUD_DDTO . $CRUD_PLUSDTO;

    if (file_exists("temp.zip")) {
        unlink("temp.zip");
    }
    for ($i = 0; $i < count($selectedTables); $i++) {
        if ($generateDAOBtn) {
            generateDAOFile(underscoreToCamelCase($selectedTables[$i], true) . ".php", DAOGenerator::generateDAO($pdo, $selectedDb, $selectedTables[$i], $opts));
        } else if ($dlDAOBtn) {
            zip(ucwords($selectedTables[$i]) . ".php", DAOGenerator::generateDAO($pdo, $selectedDb, $selectedTables[$i], $opts));
            $file_url = 'temp.zip';
            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $file_url . "\"");
            readfile($file_url);
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>DAO Generator</title>
        <style>
            html{
                text-align: center;
            }
            body{
                margin:auto;
                border:0px solid red;
            }
            .bordered{
                border-top:1px solid black;
                margin:auto;

                padding: 25px 25px;
            }
            .inlined{
                padding: 15px 15px;
                border:0px solid red;
                display: inline-block;
                text-align: left;
            }
            textarea{
                width: 90%;
                height:500px;
            }
            .descLabel{
                border:0px solid red;
                display: inline-block;
                width: 900px;
            }
            .descCrudLabel{
                display: inline-block;
                width: 70px;
            }
            #tableSelectForm h5{
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <h1>PHP DAO Generator for MySQL</h1>
        <div class="bordered">
            <h3>MySQL connection</h3>
            <form method="post" action="#dbForm">
                <label>Host :</label> <input type="text" id="host" name="host" value="localhost" required><br>
                <label>Login :</label> <input type="text" id="login" name="login" value="root" required><br>
                <label>Password :</label> <input type="password" id="pwd" name="pwd" value=""><br>
                <button id="connectBtn">Connect</button>
                <input type="hidden" name="typeForm" value="connectForm"></input>
            </form>
        </div>
        <?php
        if ($typeForm != "") {

            $dbList = getBases($pdo);
            ?>

            <div class="bordered" id="dbForm">
                <h3>Select a database</h3>
                <form method="post" action="#tableSelectForm">
                    <select name="selectedDb"  required>
                        <?php
                        //Génération dynamique d'éléments OPTION
                        foreach ($dbList as $val) {
                            echo "<option value='" . $val . "' ";
                            if ($selectedDb AND $selectedDb === $val) {
                                echo 'selected';
                            }
                            echo ">" . $val . "</option>";
                        }
                        ?>
                    </select>
                    <button id="dbSelectBtn">Select</button>
                    <input type="hidden" name="host" value="<?php echo $host; ?>"></input>
                    <input type="hidden" name="login" value="<?php echo $login; ?>"></input>
                    <input type="hidden" name="pwd" value="<?php echo $pwd; ?>"></input>
                    <input type="hidden" name="typeForm" value="dbSelectForm"></input>
                </form>
            </div>
            <?php
        }if ($selectedDb) {
            $tableList = getTables($pdo, $selectedDb);
            ?>
            <div class="bordered" id="tableSelectForm">
                <h3>Select tables and options for the DAO file generation</h3>
                <form method="post" action="#tableSelectForm">
                    <h4>Tables</h4>
                    <div class="inlined">
                        <select name="selectedTables[]" multiple size="11" required>
                            <?php
                            foreach ($tableList as $val) {
                                //Génération dynamique d'éléments OPTION
                                echo "<option ondblclick='dbSel.submit();' value='" . $val . "' ";
                                if ($selectedTables AND in_array($val, $selectedTables)) {
                                    echo 'selected';
                                }
                                echo ">" . $val . "</option>";
                            }
                            ?>
                        </select></div><br><h4>Options</h4><div class="inlined">
                        <h5>DAO</h5><br>
                        <input type="checkbox" <?php
                        if (!($CRUD_C == null && $CRUD_C_OFF)) {
                            echo "checked";
                        }
                        ?> name="CRUD_C" value="C" id="CRUDC"><label class="descCrudLabel">Create</label><label class="descLabel"><?php if (!(($OJAS_O == null && $CRUD_CDTO_OFF) || ($CRUD_CDTO_OFF && $CRUD_CDTO == null))) {
                               echo "insert(\$pdo, \$dto) static method that inserts a new line in table from a DTO instance non-ai columns values";
                           } else {
                               echo "insert(\$pdo, \$nonAIparams, ...) static method that inserts a new line in table from non-ai columns values";
                           } ?></label><input type="checkbox" <?php
                               if (!($CRUD_CDTO == null && $CRUD_CDTO_OFF)) {
                                   echo "checked";
                               }
                                if($OJAS_O == null && $CRUD_CDTO_OFF){
                                    echo " disabled";
                                }
                               
                               ?> name="CRUD_CDTO" value="1" id="CDTO"><label>receive DTO instance for argument</label><br>
                        <input type="checkbox" <?php
                               if (!($CRUD_R == null && $CRUD_R_OFF)) {
                                   echo "checked";
                               }
                               ?> name="CRUD_R" value="R" id="CRUDR"><label class="descCrudLabel">Read</label><label class="descLabel"><?php if (!(($OJAS_O == null && $CRUD_RDTO_OFF) || ($CRUD_RDTO_OFF && $CRUD_RDTO == null))) {
                        echo "selectAll(\$pdo) static method that selects all columns from all lines in table and returns an array of DTO instances";
                    } else {
                        echo "selectAll(\$pdo) static method that selects all columns from all lines in table and returns an array of associatives arrays";
                    } ?></label><input type="checkbox" <?php
                               if (!($CRUD_RDTO == null && $CRUD_RDTO_OFF)) {
                                   echo "checked";
                               }
                               if($OJAS_O == null && $CRUD_RDTO_OFF){
                                    echo " disabled";
                                }
                               ?> name="CRUD_RDTO" value="2" id="RDTO"><label>return DTO instances array</label><br>
                        <input type="checkbox" <?php
                                           if (!($CRUD_U == null && $CRUD_U_OFF)) {
                                               echo "checked";
                                           }
                                           ?> name="CRUD_U" value="U" id="CRUDU"><label class="descCrudLabel">Update</label><label class="descLabel"><?php if (!(($OJAS_O == null && $CRUD_UDTO_OFF) || ($CRUD_UDTO_OFF && $CRUD_UDTO == null))) {
                        echo "update(\$pdo, \$dto) static method that updates non-ai values of a line in table from a DTO instance primary columns values";
                    } else {
                        echo "update(\$pdo, \$primaryParams, ...) static method that updates non-ai values of a line in table from primary columns values";
                    } ?></label><input type="checkbox" <?php
                        if (!($CRUD_UDTO == null && $CRUD_UDTO_OFF)) {
                            echo "checked";
                        }
                        if($OJAS_O == null && $CRUD_UDTO_OFF){
                            echo " disabled";
                        }
                        ?> name="CRUD_UDTO" value="3" id="UDTO"><label>receive DTO instance for argument</label><br>
                        <input type="checkbox" <?php
                        if (!($CRUD_D == null && $CRUD_D_OFF)) {
                            echo "checked";
                        }
                        ?> name="CRUD_D" value="D" id="CRUDD"><label class="descCrudLabel">Delete</label><label class="descLabel"><?php if (!(($OJAS_O == null && $CRUD_DDTO_OFF) || ($CRUD_DDTO_OFF && $CRUD_DDTO == null))) {
                            echo "delete(\$pdo, \$dto) static method that deletes a line in table from a DTO instance primary columns values";
                        } else {
                            echo "delete(\$pdo, \$primaryParams, ...) static method that deletes a line in table from primary columns values";
                        } ?></label><input type="checkbox" <?php
                        if (!($CRUD_DDTO == null && $CRUD_DDTO_OFF)) {
                            echo "checked";
                        }
                        if($OJAS_O == null && $CRUD_DDTO_OFF){
                                    echo " disabled";
                                }
                        ?> name="CRUD_DDTO" value="4" id="DDTO"><label>receive DTO instance for argument</label><br>
                        <input type="checkbox" <?php
                        if (!($CRUD_PLUS == null && $CRUD_PLUS_OFF)) {
                            echo "checked";
                        }
                        ?> name="CRUD_+" value="+" id="CRUDPLUS"><label class="descCrudLabel">ReadOne</label><label class="descLabel"><?php if (!(($OJAS_O == null && $CRUD_PLUSDTO_OFF) || ($CRUD_PLUSDTO_OFF && $CRUD_PLUSDTO == null))) {
                            echo "selectOne(\$pdo, \$primaryValues, ...) static method that selects a line in table from primary columns values and returns a DTO instance";
                        } else {
                            echo "selectOne(\$pdo, \$primaryValues, ...) static method that selects a line in table from primary columns values and returns an associative array";
                        } ?></label><input type="checkbox" <?php
                        if (!($CRUD_PLUSDTO == null && $CRUD_PLUSDTO_OFF)) {
                            echo "checked";
                        }
                        if($OJAS_O == null && $CRUD_PLUSDTO_OFF){
                                    echo " disabled";
                                }
                        ?> name="CRUD_+DTO" value="5" id="PLUSDTO"><label>return DTO instance</label><br><br>
                        <h5>DTO</h5><input type="checkbox" <?php
        if (!($OJAS_O == null && $OJAS_O_OFF)) {
            echo "checked";
        }
        ?> name="OJAS_O" id="enableDTO" value="O"><label class="descCrudLabel"></label><label>Enable DTO. Generates constructor, properties, getters and setters.</label><br>                    
                        <input type="checkbox" <?php
            if (!($OJAS_J == null && $OJAS_J_OFF)) {
                echo "checked";
            }
            ?> name="OJAS_J" value="J" id="enableJSON"><label class="descCrudLabel">JSON</label><label>toJSONString() static method that returns a JSON string representation of the DTO instance</label><br>
                        <input type="checkbox" <?php
            if (!($OJAS_A == null && $OJAS_A_OFF)) {
                echo "checked";
            }
            ?> name="OJAS_A" value="A" id="enableArray"><label class="descCrudLabel">Array</label><label>toArray() static method that returns a associative array representation of the DTO instance</label><br>
                        <input type="checkbox" <?php
            if (!($OJAS_S == null && $OJAS_S_OFF)) {
                echo "checked";
            }
            ?> name="OJAS_S" value="S" id="enableString"><label class="descCrudLabel">String</label><label>toString() static method that returns a string representation of the DTO instance</label><br>
                    </div><br>
                    <button type="submit" name="generateDAOBtn" value="true">Generate DAO</button>
                    <button type="submit" name="dlDAOBtn" value="true">Download DAO</button>
                    <button type="submit" name="previewDAOBtn" value="true">Preview</button>
                    <input type="hidden" name="CRUD_C_OFF" value=1></input>
                    <input type="hidden" name="CRUD_R_OFF" value=1></input>
                    <input type="hidden" name="CRUD_U_OFF" value=1></input>
                    <input type="hidden" name="CRUD_D_OFF" value=1></input>
                    <input type="hidden" name="CRUD_+_OFF" value=1></input>
                    <input type="hidden" name="CRUD_CDTO_OFF" value=1></input>
                    <input type="hidden" name="CRUD_RDTO_OFF" value=1></input>
                    <input type="hidden" name="CRUD_UDTO_OFF" value=1></input>
                    <input type="hidden" name="CRUD_DDTO_OFF" value=1></input>
                    <input type="hidden" name="CRUD_+DTO_OFF" value=1></input>
                    <input type="hidden" name="OJAS_O_OFF" value=1></input>
                    <input type="hidden" name="OJAS_J_OFF" value=1></input>
                    <input type="hidden" name="OJAS_A_OFF" value=1></input>
                    <input type="hidden" name="OJAS_S_OFF" value=1></input>
                    <input type="hidden" name="host" value="<?php echo $host; ?>"></input>
                    <input type="hidden" name="login" value="<?php echo $login; ?>"></input>
                    <input type="hidden" name="pwd" value="<?php echo $pwd; ?>"></input>
                    <input type="hidden" name="selectedDb" value="<?php echo $selectedDb; ?>"></input>
                    <input type="hidden" name="typeForm" value="tableSelectForm"></input>
                </form>
            </div>
    <?php
    if ($previewDAOBtn) {
        ?>
                <div class="bordered">
                    <h3>Preview</h3>
                    <textarea><?php echo DAOGenerator::generateDAO($pdo, $selectedDb, $selectedTables[0], $opts); ?></textarea>
                </div>
        <?php
    }
}
?>
        <script type="text/javascript">
            CRUDC.addEventListener("change", function (e) {
                if(!e.target.checked){CDTO.disabled = true;}
                else if(enableDTO.checked){CDTO.disabled = false;}
            });
            CRUDR.addEventListener("change", function (e) {
                if(!e.target.checked){RDTO.disabled = true;}
                else if(enableDTO.checked){RDTO.disabled = false;}
            });
            CRUDU.addEventListener("change", function (e) {
                if(!e.target.checked){UDTO.disabled = true;}
                else if(enableDTO.checked){UDTO.disabled = false;}
            });
            CRUDD.addEventListener("change", function (e) {
                if(!e.target.checked){DDTO.disabled = true;}
                else if(enableDTO.checked){DDTO.disabled = false;}
            });
            CRUDPLUS.addEventListener("change", function (e) {
                if(!e.target.checked){PLUSDTO.disabled = true;}
                else if(enableDTO.checked){PLUSDTO.disabled = false;}
            });
            
            enableDTO.addEventListener("click", function (e) {
                document.getElementById("enableJSON").disabled = document.getElementById("enableArray").disabled = document.getElementById("enableString").disabled = !e.currentTarget.checked;
                if (!e.currentTarget.checked){
                    CDTO.disabled = RDTO.disabled = UDTO.disabled = DDTO.disabled = PLUSDTO.disabled = true;
                    CDTO.checked = false;CDTO.previousSibling.innerHTML = "insert($pdo, $nonAIparams, ...) static method that inserts a new line in table from non-ai columns values";
                    RDTO.checked = false;RDTO.previousSibling.innerHTML = "selectAll($pdo) static method that selects all columns from all lines in table and returns an array of associatives arrays";
                    UDTO.checked = false;UDTO.previousSibling.innerHTML = "update($pdo, $primaryParams, ...) static method that updates non-ai values of a line in table from primary columns values";
                    DDTO.checked = false;DDTO.previousSibling.innerHTML = "delete($pdo, $primaryParams, ...) static method that deletes a line in table from primary columns values";
                    PLUSDTO.checked = false;PLUSDTO.previousSibling.innerHTML = "selectOne($pdo, $primaryValues, ...) static method that selects a line in table from primary columns values and returns an associative array";
                }
                else {
                    if (CRUDC.checked)
                        CDTO.disabled = false;
                    if (CRUDR.checked)
                        RDTO.disabled = false;
                    if (CRUDU.checked)
                        UDTO.disabled = false;
                    if (CRUDD.checked)
                        DDTO.disabled = false;
                    if (CRUDPLUS.checked)
                        PLUSDTO.disabled = false;
                }
            });
        </script>
    </body>
</html>
<?php

function getBases(PDO $pdo) {
    $dbList = array();
    $dbListRequest = 'SELECT SCHEMA_NAME FROM information_schema.SCHEMATA';
    $result = $pdo->query($dbListRequest);
    $result->setFetchMode(PDO::FETCH_NUM);
    //On crée un tableau contenant les noms de bases à filtrer
    $exceptions = ["mysql", "test", "information_schema", "performance_schema", "phpmyadmin"];
    while ($row = $result->fetch()) {
        //Si le nom de la base n'est pas contenu dans le tableau de filtrage
        if (array_search($row[0], $exceptions) === false) {
            $dbList[] = $row[0];    //On construit un tableau de nom de bases
        }
    }
    return $dbList;
}

function getTables(PDO $pdo, $dbName) {
    $tableList = array();
    $tableListRequest = 'SELECT TABLE_NAME FROM information_schema.TABLES WHERE information_schema.TABLES.TABLE_SCHEMA = "' . $dbName . '"';
    $result = $pdo->query($tableListRequest);
    $result->setFetchMode(PDO::FETCH_NUM);
    while ($row = $result->fetch()) {
        $tableList[] = $row[0];     //On construit un tableau de nom de table
    }
    return $tableList;
}

function zip($fileName, $fileContent) {
    $zip = new ZipArchive();

    if ($zip->open("./temp.zip", ZipArchive::CREATE) !== TRUE) {
        exit("Impossible d'ouvrir le fichier <./temp.zip>\n");
    }

    $zip->addFromString($fileName, $fileContent);
    $zip->close();
}

function generateDAOFile($fileName, $str) {
    if (!file_exists("DAOExports")) {
        mkdir("DAOExports");
    }
    file_put_contents("DAOExports/" . $fileName, $str);
}

function underscoreToCamelCase($string, $capitalizeFirstCharacter = false) {

    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }
    return $str;
}
