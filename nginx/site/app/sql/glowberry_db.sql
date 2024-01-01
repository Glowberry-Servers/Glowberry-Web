#-*- coding: utf-8 -*-

# Creates the Glowberry database if it doesn't exist
DROP DATABASE IF EXISTS Glowberry;
CREATE DATABASE Glowberry;
USE Glowberry;

# Creates the web app permissions table, to allow for the management of app functionalities.
CREATE TABLE WebAppPermission
(
	permission_name        VARCHAR(32) NOT NULL,
	permission_description TEXT,
	permissions_integer    BIGINT      NOT NULL
);

# Fills up the permissions table with the administrative permissions for the web app.
INSERT INTO WebAppPermission VALUES
('No Permissions', 'Allows the user to do nothing, and overrides all other permissions.', 0),
('All Permissions', 'Allows the user to do everything.', -1),
('Manage User', 'Allows the user to edit other users\' profiles.', 2),
('Manage Roles', 'Allows the user to edit, create and give other users roles as high as their own.', 4),
('Allocate Resources', 'Allows the user to change the amount of resources allocated to other users.', 8),
('Reset Passwords', 'Allows the user to reset the passwords of other users, receiving the one-time usage code created.', 16),

('View Servers', 'Allows the user to view all other servers.',  32),
('Create Servers', 'Allows the user to create servers in place of other users.', 64),
('Delete Servers', 'Allows the user to delete servers in place of other users.', 128),
('Manage Servers', 'Allows the user to access the files of all servers.', 256),
('Start Servers', 'Allows the user to start servers in place of other users.', 512),
('Stop Servers', 'Allows the user to stop servers in place of other users.', 1024),
('Restart Servers', 'Allows the user to restart servers in place of other users.', 2048),
('Kill Servers', 'Allows the user to kill servers in place of other users.', 4096);

#Creates the server permissions table
CREATE TABLE ServerPermission
(
    permission_name        VARCHAR(32) NOT NULL,
    permission_description TEXT,
    permissions_integer    BIGINT      NOT NULL
);

#Fills up the server permissions table with the administrative permissions for the servers.
INSERT INTO ServerPermission VALUES
('No Permissions', 'Allows the user to do nothing, and overrides all other permissions.', 0),
('All Permissions', 'Allows the user to do everything.', -1),
('Manage Server', 'Allows the user to access a server\'s files.', 2),
('Start Server', 'Allows the user to start a server.', 4),
('Stop Server', 'Allows the user to stop a server.', 8),
('Restart Server', 'Allows the user to restart a server.', 16),
('Kill Server', 'Allows the user to kill a server.', 32);

# Creates the roles table
CREATE TABLE Role
(
	role_name           VARCHAR(32) NOT NULL,
	permissions_integer BIGINT      NOT NULL,

	PRIMARY KEY (role_name)
);

# Fills up the Role table with the default roles for the web app.
INSERT INTO Role VALUES
('Administrator', 1),
('Moderator', 2+8+32+64+128+256+512+1024),
('Helper', 256+512+1024),
('User', 0);

# Creates the user table
CREATE TABLE User
(
	nickname                    VARCHAR(32) NOT NULL,
	password                    VARCHAR(64) NOT NULL,
	display_name                VARCHAR(32) NOT NULL,

	profile_picture             TEXT,
	wallpaper                   TEXT,
	joined_date                 DATETIME    NOT NULL,
	role_name                   VARCHAR(32) NOT NULL,

	max_ram                     INT         NOT NULL,
	security_code               TEXT,
	web_app_permissions_integer BIGINT      NOT NULL,

	PRIMARY KEY (nickname),
	FOREIGN KEY (role_name) REFERENCES Role (role_name)
);

# Adds the administrator user to the database
INSERT INTO User VALUES ('admin', '$2y$10$vLqvYPi1vtsseYk8lCr3x.oaFkkdgG7NU423VzujZqXgA0VDl2tmi', 'Administration', NULL, NULL, NOW(), 'Administrator', -1, NULL, -1);

# Creates the servers table
CREATE TABLE Server
(
	server_uuid VARCHAR(128) NOT NULL,
	name        VARCHAR(32)  NOT NULL,

	PRIMARY KEY (server_uuid)
);

# Since a server can have multiple users registered to it, we need a mapping table to map users to servers.
CREATE TABLE ServerUser
(
	server_uuid         VARCHAR(128) NOT NULL,
	nickname            VARCHAR(32)  NOT NULL,
	permissions_integer BIGINT       NOT NULL,

	PRIMARY KEY (server_uuid, nickname, permissions_integer),
	FOREIGN KEY (server_uuid) REFERENCES Server (server_uuid),
	FOREIGN KEY (nickname) REFERENCES User (nickname)
);

# Creates the ApplicationSession table, which stores the session data for the web app.
# This table is meant to work together with the cookies stored on the client side.
CREATE TABLE ApplicationSession
(
	session_id VARCHAR(128) NOT NULL,
	nickname   VARCHAR(32)  NOT NULL,
	session_password   VARCHAR(64)  NOT NULL,

	PRIMARY KEY (session_id),
	FOREIGN KEY (nickname) REFERENCES User (nickname)
);


