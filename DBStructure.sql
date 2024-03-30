CREATE TABLE taskList(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL, 
    deadline DATETIME,
    collab BOOLEAN NOT NULL DEFAULT false,
    `Priority` INT NOT NULL DEFAULT 2,
    ownerID INT NOT NULL
); 

CREATE TABLE task(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL, 
    deadline DATETIME,
    collab BOOLEAN NOT NULL DEFAULT false,
    `Priority` INT NOT NULL DEFAULT 2,
    taskListID INT NOT NULL
);

CREATE TABLE stage(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL, 
    weighting  DECIMAL(5,2),
    complete BOOLEAN NOT NULL DEFAULT false,
    dateTimeCompleted DATETIME,
    completedBy INT,
    taskID INT NOT NULL
); 

CREATE TABLE user(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    collabCode VARCHAR(255)
);
CREATE TABLE role(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL, 
    `createPermission` BOOLEAN NOT NULL DEFAULT false,
    `editPermission` BOOLEAN NOT NULL DEFAULT false,
    `deletePermission` BOOLEAN NOT NULL DEFAULT false
);


CREATE TABLE taskListCollab(
    ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
    taskListID INT NOT NULL,
    userID INT NOT NULL
    -- roleID INT NOT NULL
);


-- removed to simplify the system
-- CREATE TABLE taskCollabUser(
--     ID INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
--     taskID INT NOT NULL,
--     userID INT NOT NULL
-- );
