# DAOGenerator
## Generates DAO and DTO classes for your MySQL databases
### Basic usage:<br>
Only one static method :<br>
<br>
DAOGenerator::generateDAO($pdo, $dbName, $tableName, $options = "CRUD+OJAS12345")

Parameters: 
- $pdo : Un objet PDO déjà connecté à votre base de données MySQL
- $dbName : Le nom de votre base de données sous forme de chaine
- $tableName : Le nom de votre table sous forme de chaine
- $options : Les options à utiliser pour la génération de fichier sous forme de chaine formée des caractères suivants:<br>
    * C : Generates a static insert method (CREATE)
    * R : Generates a static selectAll method (READ)
    * U : Generates an static update method (UPDATE)
    * D : Generates a static delete method (DELETE)
    * + : Generates a static selectOne method (READ+)
    * O : Generates a DTO interface method (constructor, properties, getters and setters)
    * J : Generates a toJSONString method (JSON)
    * A : Generates a toArray method (associative array)
    * S : Generates a toString method (string)
