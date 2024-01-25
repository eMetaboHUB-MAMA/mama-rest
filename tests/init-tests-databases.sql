-- switch database
USE mama_db;                                                                       

-- init users
INSERT INTO users 
        (email, login, password, first_name, last_name, user_status, user_right,  email_reception, email_alert_new_user, email_alert_new_project, email_alert_new_event_followed_project, email_alert_new_message, created, last_activity, deleted)
VALUES ('nils.paulhe@email.fake', 'nils.paulhe@email.fake', 'm*****', 'Gnils', 'Gpaulhe', 10, 520, 0, 0, 0, 0, 0, curdate(), curdate(), 0),
        ('nils.paulhe@inrae.fake', 'npaulhe',                NULL,    'nils',  'paulhe',  10, 520, 0, 0, 0, 0, 0, curdate(), curdate(), 0);
-- update user #1: set password
UPDATE users SET password = '\$2y\$12\$d2VmbHdlZnBtd2VwbXdlZesaBIqV1ghB1p2YPl7ecig3qK3cERppm' WHERE id = 1; 

-- init keywords 
INSERT INTO thematic_words (word, created, deleted)                             
VALUES ('tw1', curdate(), 0), 
        ('tw2', curdate(), 0), 
        ('tw3', curdate(), 0);

INSERT INTO sub_thematic_words (word, created, deleted)
VALUES ('stw1', curdate(), 0), 
        ('stw2', curdate(), 0), 
        ('stw3', curdate(), 0);

INSERT INTO manager_thematic_words (word, created, deleted)
VALUES ('mtw1', curdate(), 0), 
        ('mtw2', curdate(), 0), 
        ('mtw3', curdate(), 0);

-- init platform
INSERT INTO metabohub_platform (platform_name, created, deleted) 
VALUES ('pf1', curdate(), 0),
        ('pf2', curdate(), 0);