/* To add owner location in DB*/
ALTER TABLE  `cabopen` ADD  `ownerLat` VARCHAR( 50 ) NOT NULL AFTER  `OwnerName` ,
ADD  `ownerLng` VARCHAR( 50 ) NOT NULL AFTER  `ownerLat` ;

ALTER TABLE  `cabopen` ADD  `status` TINYINT NOT NULL DEFAULT  '0' COMMENT  '0-not started;1-started;2-completed' AFTER  `FareDetails` ;