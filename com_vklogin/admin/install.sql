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

