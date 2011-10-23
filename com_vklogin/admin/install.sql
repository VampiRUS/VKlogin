CREATE TABLE IF NOT EXISTS `#__vklogin` (
`id` INT( 10 ) NOT NULL ,
`value` VARCHAR( 255 ) NOT NULL ,
UNIQUE (
`id`
)
) ENGINE = MYISAM ;


INSERT IGNORE INTO `#__vklogin` (
`id` ,
`value`
)
VALUES (
'2', 'sex'
), (
'3', 'bdate'
), (
'6', 'mobile_phone'
), (
'7', 'home_phone'
), (
'11', 'country'
), (
'10', 'city'
), (
'14', 'university_name'
), (
'15', 'graduation'
);

CREATE TABLE IF NOT EXISTS `#__vklogin_users` (
`userid` INT( 11 ) NOT NULL ,
`photo` VARCHAR( 255 ) NOT NULL ,
`vkid` INT( 11 ) NOT NULL ,
`email_hash` VARCHAR( 32 ) NOT NULL ,
`first_name` VARCHAR( 255 ) NOT NULL ,
`last_name` VARCHAR( 255 ) NOT NULL ,
`nickname` VARCHAR( 255 ) NOT NULL ,
`sex` INT( 2 ) NOT NULL ,
`bdate` VARCHAR( 10 ) NOT NULL ,
`city` VARCHAR( 255 ) NOT NULL ,
`country` VARCHAR( 255 ) NOT NULL ,
`timezone` INT( 4 ) NOT NULL ,
`photo_medium` VARCHAR( 255 ) NOT NULL ,
`photo_big` VARCHAR( 255 ) NOT NULL ,
`photo_rec` VARCHAR( 255 ) NOT NULL ,
`photo_medium_rec` VARCHAR( 255 ) NOT NULL ,
`home_phone` VARCHAR( 15 ) NOT NULL ,
`mobile_phone` VARCHAR( 15 ) NOT NULL ,
`university_name` VARCHAR( 255 ) NOT NULL ,
`faculty_name` VARCHAR( 255 ) NOT NULL ,
`graduation` VARCHAR( 4 ) NOT NULL ,
`domain` VARCHAR( 255 ) NOT NULL ,
UNIQUE (
`userid`
),
INDEX (
`vkid`
),
INDEX (
`email_hash`
)
) ENGINE = MYISAM ;

