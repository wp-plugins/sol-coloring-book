CREATE TABLE IF NOT EXISTS sol_colorpages (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  colorpagefile varchar(255) NOT NULL,
  colorpageurl varchar(255) NOT NULL,
  colorpagename varchar(50) NOT NULL,
  active tinyint unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
