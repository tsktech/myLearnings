<?php 
// Reset auto increment of all tables in a mysql database
mysql -Nsr -e "SELECT t.table_name FROM INFORMATION_SCHEMA.TABLES t WHERE t.table_schema = 'DB_NAME'" | xargs -I {} mysql DB_NAME -e "ALTER TABLE {} AUTO_INCREMENT = 1;"

// How can I reset a mysql table auto-increment to 1 in phpMyAdmin?
// 
//In mysql you can easily reset auto increment value in a table using single query.

//ALTER TABLE tablename AUTO_INCREMENT = 1;
<!-- Recommended -->
/*phpmyadmin
Perhaps you could just select the phpMyAdmin Operations tab:

In phpMyAdmin, click on the table you want to reset or change the AUTO_INCREMENT value
Click on the Operations Tab
In the Table Options box find the auto_increment field.
Enter the new auto_increment starting value
Click on the Go button for the Table Options box.
Since this one of the most frequently asked questions for phpmyadmin, you can learn more about this in this blog : http://trebleclick.blogspot.com/2009/01/mysql-set-auto-increment-in-phpmyadmin.html

Supplemental Info
For an empty table, another way to reset the auto_increment attribute is to run

TRUNCATE TABLE mydb.tablename;
Don't run this if you have data in it. If you want to hose the data, then be my guest.

In phpmyadmin, just click the SQL tab, enter the command, and run it.

For a nonempty table, you may want to adjust the auto_increment attribute to the highest existing id in use in case higher entries were deleted.

First, optimize the table

OPTIMIZE TABLE mydb.mytable;
Next, locate the highest value for the auto_increment column (say it is id)

SELECT MAX(id) maxid FROM mydb.mytable;
Suppose the answer returns 27. Goto the Operations tab and enter 28.*/