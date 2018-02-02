<?php
$requestType = filter_input(INPUT_POST, "requestType");
if ($requestType) {
    $host = filter_input(INPUT_POST, "host");
    $login = filter_input(INPUT_POST, "login");
    $pwd = filter_input(INPUT_POST, "pwd");
    $pdo = connect($host, $login, $pwd);
    $dbName = filter_input(INPUT_POST, "dbName");
    $tableName = filter_input(INPUT_POST, "tableName");
    $tables = json_decode(filter_input(INPUT_POST, "tables"));
    $options = filter_input(INPUT_POST, "options");
    $activeRecord = filter_input(INPUT_POST, "separateFiles");
}
if ($requestType == "connection") {
    echo json_encode(getBases($pdo));
} else if ($requestType == "dbSelection") {
    echo json_encode(getTables($pdo, $dbName));
} else if ($requestType == "preview") {
    require_once 'DAODTOGenerator.php';
    $pvw = DAODTOGenerator::generate($pdo, $dbName, $tableName, $options, $activeRecord);
    if (gettype($pvw) == "array") {
        echo json_encode($pvw);
    } else {
        echo $pvw;
    }
} else if ($requestType == "generate") {
    require_once 'DAODTOGenerator.php';
    if (file_exists("generatedFiles.zip")) {
        unlink("generatedFiles.zip");
    }
    $max = count($tables);
    for ($i = 0; $i < $max; $i++) {
        $gf = DAODTOGenerator::generate($pdo, $dbName, $tables[$i], $options, $activeRecord);
        $fileName;
        if (strstr($gf, "DAO")) {
            $fileName = underscoreToCamelCase($tables[$i], true) . "DAO.php";
        } else if (strstr($gf, "DTO")) {
            $fileName = underscoreToCamelCase($tables[$i], true) . "DTO.php";
        } else {
            $fileName = underscoreToCamelCase($tables[$i], true) . ".php";
        }
        generateFile($fileName, $gf);
    }
    echo 1;
} else if ($requestType == "dl") {
    require_once 'DAODTOGenerator.php';
    if (file_exists("generatedFiles.zip")) {
        unlink("generatedFiles.zip");
    }
    $max = count($tables);
    for ($i = 0; $i < $max; $i++) {
        $gf = DAODTOGenerator::generate($pdo, $dbName, $tables[$i], $options, $activeRecord);
        $fileName;
        if (strstr($gf, "DAO")) {
            $fileName = underscoreToCamelCase($tables[$i], true) . "DAO.php";
        } else if (strstr($gf, "DTO")) {
            $fileName = underscoreToCamelCase($tables[$i], true) . "DTO.php";
        } else {
            $fileName = underscoreToCamelCase($tables[$i], true) . ".php";
        }
        zip($fileName, $gf);
    }
    echo 1;
} else {
    ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="UTF-8">
            <title>DAO Generator</title>
            <style>
                html{

                }
                body{        
                    border: 0px solid red;
                    text-align: left;

                }
                #connectionDiv{

                }
                #dbSelectionDiv{
                    display:none;
                }
                #tableSelectionDiv{
                    display:none;
                }
                #previewDiv{
                    display:none;
                }
                #tableDiv{
                    display:none;
                }
                #optionsDiv{
                    display:none;
                }
                .descCrudLabel{
                    display:inline-block;
                    width:100px;
                    border: 0px solid red;
                }
                #daoDiv{
                    border: 1px solid black;
                    display:inline-block;
                    text-align: left;
                    width:400px;
                    height:230px;
                }
                #dtoDiv{
                    border: 1px solid black;
                    display:inline-block;
                    text-align: left;
                    width:400px;
                    height:230px;
                }
                textarea{
                    width:500px;
                    height:600px;
                    resize: none;
                    white-space: pre;
                }
                h4{
                    text-align: center;
                    width:100%;
                }
                h5{
                    display:inline-block;
                    width:500px;
                    border: 0px solid black;
                }
                #navBar{
                    border: 0px solid black;
                    width:250px;
                    height: 100%;
                    float: left;
                    overflow: auto;
                    text-align: center;
                }
                #main{
                    border: 0px solid pink;
                    min-width: 1000px;
                    text-align: center;
                    height: 100%;
                    overflow: auto;
                }
                #mainContainer{
                    min-width: 1350px;
                    overflow: auto;
                }

            </style>
        </head>
        <body>
            <div id="mainContainer">
                <div id="navBar"></div><div id="main">
                    <h1 id="mainHeader">PHP DAO and DTO Generator for MySQL</h1>
                    <div id="connectionDiv">
                        <h3>MySQL connection</h3>
                        <form method="post">
                            <label>Host&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</label> <input type="text" id="host" name="host" value="localhost" required><br>
                            <label>Login&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</label> <input type="text" id="login" name="login" value="root" required><br>
                            <label>Password&nbsp;:</label> <input type="password" id="pwd" name="pwd" value=""><br>
                            <button id="connectBtn">Connect</button>
                        </form>
                    </div>

                    <div id="dbSelectionDiv">
                        <h3>Select a database</h3>
                        <form method="post">
                            <select name="selectedDb"  required>
                            </select>
                            <button id="dbSelectBtn">Select</button>
                        </form>
                    </div>
                    <div id="tableSelectionDiv">
                        <h3>Select tables</h3>
                        <form method="post">
                            <select multiple size="20" required>
                            </select><br>
                            <button id="connectBtn">Select</button>
                        </form>
                    </div>
                    <div id="optionsDiv">
                        <h3>Select options for the file generation</h3>
                        <div id="daoDiv">
                            <h4>DAO</h4>
                            <input type="checkbox" name="enableDAO" value="A" id="enableDAO" checked><label for="enableDAO" class="descCrudLabel">DAO</label><br><br><br>
                            <input type="checkbox" name="enableC" value="C" id="enableC" checked><label for="enableC" class="descCrudLabel">Create</label><input type="checkbox" name="enableCDTO" value="1" id="enableCDTO" checked><label for="enableCDTO">receive DTO instance for argument</label><br>
                            <input type="checkbox" name="enableR" value="R" id="enableR" checked><label for="enableR" class="descCrudLabel">Read</label><input type="checkbox" name="enableRDTO" value="2" id="enableRDTO" checked><label for="enableRDTO">return DTO instances array</label><br>
                            <input type="checkbox" name="enableU" value="U" id="enableU" checked><label for="enableU" class="descCrudLabel">Update</label><input type="checkbox" name="enableUDTO" value="3" id="enableUDTO" checked><label for="enableUDTO">receive DTO instance for argument</label><br>
                            <input type="checkbox" name="enableD" value="D" id="enableD" checked><label for="enableD" class="descCrudLabel">Delete</label><input type="checkbox" name="enableDDTO" value="4" id="enableDDTO" checked><label for="enableDDTO">receive DTO instance for argument</label><br>
                            <input type="checkbox" name="enablePLUS" value="+" id="enablePLUS"><label for="enablePLUS" class="descCrudLabel">ReadOne</label><input type="checkbox" name="enablePLUSDTO" value="5" id="enablePLUSDTO" checked><label for="enablePLUSDTO">return DTO instance</label>
                        </div>
                        <div id="dtoDiv">
                            <h4>DTO</h4>
                            <input type="checkbox" name="enableDTO" value="O" id="enableDTO"  checked><label for="enableDTO" class="descCrudLabel">DTO</label><br><br><br>
                            <input type="checkbox" name="enableJSON" value="J" id="enableJSON" checked><label for="enableJSON" class="descCrudLabel">toJSON()</label><br>
                            <input type="checkbox" name="enableArray" value="A" id="enableArray" checked><label for="enableArray" class="descCrudLabel">toArray()</label><br>
                            <input type="checkbox" name="enableString" value="S" id="enableString" checked><label for="enableString" class="descCrudLabel">toString()</label><br><br><br>
                        </div>
                        <br><br>
                        <input type="checkbox" name="enableAR" value="AR" id="enableAR" checked><label for="enableAR">Active Record pattern (DTO and static DAO in the same class)</label>
                        <br><br>
                        <button type="button" name="generateBtn" id="generateBtn" value="true">Generate files on the server</button>
                        <button type="button" name="dlBtn" id="dlBtn" value="true">Generate files and download</button>
                        <h3>Preview</h3>
                        <h5 id="headerDAO">.php</h5><h5 id="headerDTO">.php</h5><br>
                        <textarea readonly id="textareaDAO"></textarea>
                        <textarea readonly id="textareaDTO"></textarea>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                var connectionForm = document.querySelector("#connectionDiv form");
                var dbForm = document.querySelector("#dbSelectionDiv form");
                var tableForm = document.querySelector("#tableSelectionDiv form");
                var hostStr;
                var loginStr;
                var pwdStr;
                var dbStr;
                var tables = [];
                connectionForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    hostStr = host.value;
                    loginStr = login.value;
                    pwdStr = pwd.value;
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function (event) {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                var data = JSON.parse(this.responseText);
                                dbSelectionDiv.querySelector("select").innerHTML = "";
                                for (let k in data) {
                                    dbSelectionDiv.querySelector("select").innerHTML += "<option value='" + data[k] + "'>" + data[k] + "</option>";
                                }
                                //connectionDiv.style.position = "absolute";
                                navBar.appendChild(connectionDiv);
                                //connectionDiv.style.left = "0px";
                                //connectionDiv.style.top = "0px";
                                dbSelectionDiv.style.display = "block";console.log(main.childNodes[6]);
                                main.insertBefore(optionsDiv, mainHeader.nextSibling);
                                main.insertBefore(tableSelectionDiv, mainHeader.nextSibling);
                                main.insertBefore(dbSelectionDiv, mainHeader.nextSibling);
                                tableSelectionDiv.style.display = "none";
                                optionsDiv.style.display = "none";
                                //dbSelectionDiv.style.position = "";
                                
                            } else {
                                console.log("ajax error");
                            }
                        }
                    };
                    xhr.open('POST', 'index.php', true);
                    let formData = new FormData();
                    formData.append('requestType', 'connection');
                    formData.append('host', hostStr);
                    formData.append('login', loginStr);
                    formData.append('pwd', pwdStr);
                    xhr.send(formData);
                });
                dbForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    dbStr = dbSelectionDiv.querySelector("select").value;
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function (event) {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                var data = JSON.parse(this.responseText);
                                tableSelectionDiv.querySelector("select").innerHTML = "";
                                for (let k in data) {
                                    tableSelectionDiv.querySelector("select").innerHTML += "<option value='" + data[k] + "'>" + data[k] + "</option>";
                                }
                                dbSelectionDiv.style.position = "absolute";
                                dbSelectionDiv.style.left = "20px";
                                dbSelectionDiv.style.top = "160px";
                                tableSelectionDiv.style.display = "block";
                                tableSelectionDiv.style.display = "block";
                                tableSelectionDiv.style.position = "";
                                optionsDiv.style.display = "none";
                            } else {
                                console.log("ajax error");
                            }
                        }
                    };
                    xhr.open('POST', 'index.php', true);
                    let formData = new FormData();
                    formData.append('requestType', 'dbSelection');
                    formData.append('host', hostStr);
                    formData.append('login', loginStr);
                    formData.append('pwd', pwdStr);
                    formData.append('dbName', dbStr);
                    xhr.send(formData);
                });
                tableForm.addEventListener("submit", function (e) {
                    e.preventDefault();
                    tables = [];
                    var tempTables = tableSelectionDiv.querySelector("select").selectedOptions;
                    for (var i = 0; i < tempTables.length; i++) {
                        tables.push(tempTables[i].value);
                    }
                    tableSelectionDiv.style.position = "absolute";
                    tableSelectionDiv.style.left = "45px";
                    tableSelectionDiv.style.top = "230px";
                    optionsDiv.style.display = "block";
                    resetOptions();
                    getPreview();
                });
                generateBtn.addEventListener("click", function (e) {
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function (event) {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                console.log(this.responseText);
                            } else {
                                console.log("ajax error");
                            }
                        }
                    };
                    xhr.open('POST', 'index.php', true);
                    let formData = new FormData();
                    formData.append('requestType', 'generate');
                    formData.append('host', hostStr);
                    formData.append('login', loginStr);
                    formData.append('pwd', pwdStr);
                    formData.append('dbName', dbStr);
                    formData.append('tables', JSON.stringify(tables));
                    formData.append('options', getOptionString());
                    var boolToInt = 1;
                    if (enableAR.checked)
                        boolToInt = 0;
                    formData.append('separateFiles', boolToInt);
                    xhr.send(formData);
                });
                dlBtn.addEventListener("click", function (e) {
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function (event) {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                var dlLink = document.createElement("A");
                                dlLink.href = "generatedFiles.zip";
                                dlLink.setAttribute("download", "");
                                document.body.appendChild(dlLink);
                                dlLink.click();
                                document.body.removeChild(dlLink);
                            } else {
                                console.log("ajax error");
                            }
                        }
                    };
                    xhr.open('POST', 'index.php', true);
                    let formData = new FormData();
                    formData.append('requestType', 'dl');
                    formData.append('host', hostStr);
                    formData.append('login', loginStr);
                    formData.append('pwd', pwdStr);
                    formData.append('dbName', dbStr);
                    formData.append('tables', JSON.stringify(tables));
                    formData.append('options', getOptionString());
                    var boolToInt = 1;
                    if (enableAR.checked)
                        boolToInt = 0;
                    formData.append('separateFiles', boolToInt);
                    xhr.send(formData);
                });
                var cbs = document.querySelectorAll("#optionsDiv input");
                for (var i = 0; i < cbs.length; i++) {
                    cbs[i].addEventListener("change", function (e) {
                        updateOptions();
                    });
                }

                function updateOptions() {
                    enableC.disabled = enableR.disabled = enableU.disabled = enableD.disabled = enablePLUS.disabled = !enableDAO.checked;
                    enableJSON.disabled = enableArray.disabled = enableString.disabled = !enableDTO.checked;
                    if (!enableDAO.checked || !enableDTO.checked) {
                        enableCDTO.disabled = enableRDTO.disabled = enableUDTO.disabled = enableDDTO.disabled = enablePLUSDTO.disabled = true;
                    } else {
                        enableCDTO.disabled = enableRDTO.disabled = enableUDTO.disabled = enableDDTO.disabled = enablePLUSDTO.disabled = false;
                    }
                    getPreview();
                }
                function resetOptions() {
                    var cbs = document.querySelectorAll("#optionsDiv input");
                    for (var i = 0; i < cbs.length; i++) {
                        cbs[i].checked = true;
                        cbs[i].disabled = false;
                    }
                }
                function getPreview() {
                    let xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function (event) {
                        if (this.readyState === XMLHttpRequest.DONE) {
                            if (this.status === 200) {
                                if (this.responseText[0] === "[") {
                                    var jsonArray = JSON.parse(this.responseText);
                                    textareaDAO.style.display = "inline-block";
                                    headerDAO.style.display = "inline-block";
                                    headerDTO.innerHTML = camelize(tables[0], "_") + "DTO.php";
                                    headerDAO.innerHTML = camelize(tables[0], "_") + "DAO.php";
                                    textareaDAO.innerHTML = jsonArray[0];
                                    textareaDTO.innerHTML = jsonArray[1];
                                } else {
                                    textareaDAO.style.display = "none";
                                    headerDAO.style.display = "none";
                                    headerDTO.innerHTML = camelize(tables[0]) + ".php";
                                    textareaDTO.innerHTML = this.responseText;
                                }

                            } else {
                                console.log("ajax error");
                            }
                        }
                    };
                    xhr.open('POST', 'index.php', true);
                    let formData = new FormData();
                    formData.append('requestType', 'preview');
                    formData.append('host', hostStr);
                    formData.append('login', loginStr);
                    formData.append('pwd', pwdStr);
                    formData.append('dbName', dbStr);
                    formData.append('tableName', tables[0]);
                    formData.append('options', getOptionString());
                    var boolToInt = 1;
                    if (enableAR.checked)
                        boolToInt = 0;
                    formData.append('separateFiles', boolToInt);
                    xhr.send(formData);
                }
                function getOptionString() {
                    var optInputs = [enableC, enableR, enableU, enableD, enablePLUS, enableDTO, enableJSON, enableArray, enableString, enableCDTO, enableRDTO, enableUDTO, enableDDTO, enablePLUSDTO];
                    var str = "";
                    for (var i = 0; i < optInputs.length; i++) {
                        if (!optInputs[i].disabled && optInputs[i].checked) {
                            str += optInputs[i].value;
                        }
                    }
                    return(str);
                }
                function camelize(text, separator) {

                    // Assume separator is _ if no one has been provided.
                    if (typeof (separator) === "undefined") {
                        separator = "_";
                    }

                    // Cut the string into words
                    var words = text.split(separator);

                    // Concatenate all capitalized words to get camelized string
                    var result = "";
                    for (var i = 0; i < words.length; i++) {
                        var word = words[i];
                        var capitalizedWord = word.charAt(0).toUpperCase() + word.slice(1);
                        result += capitalizedWord;
                    }

                    return result;

                }
            </script>
        </body>
    </html>
    <?php
}

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
    if ($zip->open("./generatedFiles.zip", ZipArchive::CREATE) !== TRUE) {
        exit("Impossible d'ouvrir le fichier <./$fileName>\n");
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

function generateFile($fileName, $str) {
    if (!file_exists("generatedFiles")) {
        mkdir("generatedFiles");
    }
    file_put_contents("generatedFiles/" . $fileName, $str);
}

function connect($host, $login, $pwd) {
    try {

        $pdo = new PDO("mysql:host=" . $host, $login, $pwd);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("SET NAMES 'UTF8'");
        return($pdo);
    } catch (PDOException $e) {
        echo($e->getMessage());
    }
}
