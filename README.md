# DAOGenerator
## Generates DAO and DTO classes for your MySQL databases
### Basic usage:<br>
Only one static method :<br>
<br>
DAODTOGenerator::generate($pdo, $dbName, $tableName, $options = "CRUD+OJAS12345")

Parameters: 
- $pdo : A connected PDO object
- $dbName : The database name
- $tableName : The table name
- $options : The options to use for files generation in a string composed by the following allowed characters:<br>
    * C : Generates a static insert method (CREATE)
    * R : Generates a static selectAll method (READ)
    * U : Generates an static update method (UPDATE)
    * D : Generates a static delete method (DELETE)
    * \+ : Generates a static selectOne method (READ+)
    * O : Generates a DTO interface method (constructor, properties, getters and setters)
    * J : Generates a toJSONString method (JSON)
    * A : Generates a toArray method (associative array)
    * S : Generates a toString method (string)
    * 1 : The insert method will get DTO as parameter instead of values
    * 2 : The selectAll method will return a DTO instead of associative array
    * 3 : The update method will get DTO as parameter instead of values
    * 4 : The delete method will get DTO as parameter instead of values
    * 5 : The selectOne method will return a DTO instead of values
- $activeRecord : Instantiable DTO and static DAO into the same class and file

Returned values:
Will return a string containing the generated class or an array of two strings containing the generated DAO and DTO classes in that order depending on the activeRecord parameter and the selected options