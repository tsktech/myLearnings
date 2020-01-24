### Reset auto increment of all tables in a mysql database ###

    mysql -Nsr -e "SELECT t.table_name FROM INFORMATION_SCHEMA.TABLES t WHERE t.table_schema = 'DB_NAME'" | xargs -I {} mysql DB_NAME -e "ALTER TABLE {} AUTO_INCREMENT = 1;"

### How can I reset a mysql table auto-increment to 1 in phpMyAdmin? ###

In mysql you can easily reset auto increment value in a table using single query.

    ALTER TABLE tablename AUTO_INCREMENT = 1;

### Recommended ###
**using phpmyadmin**: Perhaps you could just select the phpMyAdmin Operations tab:

1. In phpMyAdmin, click on the table you want to reset or change the AUTO_INCREMENT value
2. Click on the Operations Tab
3. In the Table Options box find the auto_increment field.
4. Enter the new auto_increment starting value
5. Click on the Go button for the Table Options box.

**frequently asked questions for phpmyadmin, you can learn more about this 
[blog](http://trebleclick.blogspot.com/2009/01/mysql-set-auto-increment-in-phpmyadmin.html)**

Supplemental Info
For an empty table, another way to reset the auto_increment attribute is to run

    TRUNCATE TABLE mydb.tablename;
Don't run this if you have data in it. If you want to hose the data, then be my guest.

In phpmyadmin, just click the SQL tab, enter the command, and run it.

### For a nonempty table, you may want to adjust the auto_increment attribute to the highest existing id in use in case higher entries were deleted. ###

1. First, optimize the table

    OPTIMIZE TABLE mydb.mytable;
2. Next, locate the highest value for the auto_increment column (say it is id)

    SELECT MAX(id) maxid FROM mydb.mytable;
    
    Suppose the answer returns 27. 
3. Goto the Operations tab and enter 28.

### Insert without duplicates ###

1. Create unique index on field: equipment

    ALTER TABLE equipment ADD UNIQUE equipment_unique (equipment);

2. do insert ignoring errors:

    foreach ($equipment as $key)
    {
        $name = $key['name'];
        $equipment_query = $this->conn->prepare("INSERT IGNORE INTO equipment_master (equipment) VALUES (:equipment_name)");
        $equipment_query->bindParam('equipment_name', $name);
        $equipment_query->execute();
    }

3. but if You want replace record that has same id so You can use:

    foreach ($equipment as $key)
    {
        $name = $key['name'];
        $id = $key['id'];
        $equipment_query = $this->conn->prepare("REPLACE INTO equipment_master (id, equipment) VALUES (:equipment_id, :equipment_name)");
        $equipment_query->bindParam('equipment_name', $name);
        $equipment_query->bindParam('equipment_id', $id);
        $equipment_query->execute();
    }

### Using WHERE NOT EXISTS ###

    INSERT INTO equipment_master (equipment) SELECT * FROM (SELECT 'Plate Compactor12') AS tmp WHERE NOT EXISTS ( SELECT equipment FROM equipment_master WHERE equipment = 'Plate Compactor12' or id=1)

this will not insert equipment=>Plate Compactor12 because id is already present in table.

### alternate ways ###

    $sql = $this->pdo->prepare("INSERT INGORE INTO ...");
    if ( $sql->execute() )
    {
        $last_id = $this->pdo->lastInsertId();
    }
    // $sql->execute() will always return true
    if ( $sql->execute() && $sql->rowCount() > 0 ) {
        // do something
    }

    INSERT IGNORE INTO mytable
        (primaryKey, field1, field2)
    VALUES
        ('abc', 1, 2),
        ('def', 3, 4),
        ('ghi', 5, 6);

## PDO ##

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND status=?');
    $stmt->execute([$email, $status]);
    $user = $stmt->fetch();
    // or
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND status=:status');
    $stmt->execute(['email' => $email, 'status' => $status]);
    $user = $stmt->fetch();
    $rowAffected = $stmt->rowCount();

Passing data into execute() (like shown above) should be considered default and most convenient method. When this method is used, all values will be bound as strings (save for NULL values, that will be sent to the query as is, i.e. as SQL NULL), but most of time it's all right and won't cause any problem.

However, sometimes it's better to set the data type explicitly. Possible cases are:

LIMIT clause (or any other SQL clause that just cannot accept a string operand) if emulation mode is turned ON.
complex queries with non-trivial query plan that can be affected by a wrong operand type
peculiar column types, like BIGINT or BOOLEAN that require an operand of exact type to be bound (note that in order to bind a BIGINT value with PDO::PARAM_INT you need a mysqlnd-based installation).
In such a case explicit binding have to be used, for which you have a choice of two functions, bindValue() and bindParam(). The former one has to be preferred, because, unlike bindParam() it has no side effects to deal with.

### Binding methods ###

    $data = [
        1 => 1000,
        5 =>  300,
        9 =>  200,
    ];
    $stmt = $pdo->prepare('UPDATE users SET bonus = bonus + ? WHERE id = ?');
    foreach ($data as $id => $bonus)
    {
        $stmt->execute([$bonus, $id]);
    }

### Running SELECT INSERT, UPDATE, or DELETE statements ###

### Return types. ###
Only when PDO is built upon mysqlnd and emulation mode is off, then PDO will return int and float values with respective types. Say, if we create a table

    create table typetest (string varchar(255), `int` int, `float` float, `null` int);
    insert into typetest values('foo',1,1.1,NULL);

And then query it from mysqlnd-based PDO with emulation turned off, the output will be

    array(4) {
      ["string"] => string(3) "foo"
      ["int"]    => int(1)
      ["float"]  => float(1.1)
      ["null"]   => NULL
    }

Otherwise the familiar mysql_fetch_array() behavior will be followed - all values returned as strings with only NULL returned as NULL.

If for some reason you don't like this behavior and prefer the old style with strings and NULLs only, then you can use the following configuration option to override it:

    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true); 

Note that for the DECIMAL type the string is always returned, due to nature of this type intended to retain the precise value, unlike deliberately non-precise FLOAT and DOUBLE types.


read more at [phpdelusions](https://phpdelusions.net/pdo)


## rowCount() ##

**Not to mention that the second most popular use case for this function should never be used at all. One should never use the rowCount() to count rows in database! Instead, one has to ask a database to count them, and return the result in a single row:**

    $count = $pdo->query("SELECT count(1) FROM t")->fetchColumn();
    
is the only proper way.

**In essence:**

1. if you need to know how many rows in the table, use SELECT COUNT(*) query.
2. if you need to know whether your query returned any data - check that data.
3. if you still need to know how many rows has been returned by some query (though I hardly can imagine a case), then you can either use rowCount() or simply call count() on the array returned by fetchAll() (if applicable).
4. Thus you could tell that the top answer for this question on Stack Overflow is essentially pointless and harmful - a call to rowCount() could be never substituted with SELECT count(*) query - their purpose is essentially different, while running an extra query only to get the number of rows returned by other query makes absolutely no sense.

# One other way (not sure) #
https://mariadb.com/kb/en/insert-on-duplicate-key-update/

    CREATE TABLE ins_duplicate (id INT PRIMARY KEY, animal VARCHAR(30));
    INSERT INTO ins_duplicate VALUES (1,'Aardvark'), (2,'Cheetah'), (3,'Zebra');

If there is no existing key, the statement runs as a regular INSERT:

    INSERT INTO ins_duplicate VALUES (4,'Gorilla') ON DUPLICATE KEY UPDATE animal='Gorilla';
Query OK, 1 row affected (0.07 sec)

A regular INSERT with a primary key value of 1 will fail, due to the existing key:

    INSERT INTO ins_duplicate VALUES (1,'Antelope');
    ERROR 1062 (23000): Duplicate entry '1' for key 'PRIMARY'

However, we can use an INSERT ON DUPLICATE KEY UPDATE instead:

    INSERT INTO ins_duplicate VALUES (1,'Antelope') ON DUPLICATE KEY UPDATE animal='Antelope';
    Query OK, 2 rows affected (0.09 sec)
    Note that there are two rows reported as affected, but this refers only to the UPDATE.

## PDO ##

    $sql = 'INSERT INTO users (`uuid`, `name`, `image_url`)
            VALUES(:uuid, :name, :image_url)
            ON DUPLICATE KEY UPDATE
              `site_name` = :site_name,
              `image_url` = :image_url';
    $statement = $this->pdo->prepare($sql)->execute(…);
    return $this->pdo->lastInsertId();

If a table contains an AUTO_INCREMENT column and INSERT ... ON DUPLICATE KEY UPDATE inserts or updates a row, the LAST_INSERT_ID() function returns the AUTO_INCREMENT value.
The problem is that in case of updating record LAST_INSERT_ID() from MySQL will return 0.

    $sql = 'INSERT INTO users (`uuid`, `name`, `image_url`)
            VALUES(:uuid, :name, :image_url)
            ON DUPLICATE KEY UPDATE
              `id` = LAST_INSERT_ID(`id`),     <----- Added to the query
              `proz_site_name` = :site_name,
              `proz_image_url` = :image_url';
    $statement = $this->pdo->prepare($sql)->execute(…);
    return $this->pdo->lastInsertId();

*also read [https://stackoverflow.com/questions/14089055/pdo-prepared-statements-for-insert-and-on-duplicate-key-update-with-named-placeh](https://stackoverflow.com/questions/14089055/pdo-prepared-statements-for-insert-and-on-duplicate-key-update-with-named-placeh)*
