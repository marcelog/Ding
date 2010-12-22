CREATE TABLE Person(
    id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    username VARCHAR(50) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL
);
CREATE UNIQUE INDEX Person_username_uniq ON Person (username);
