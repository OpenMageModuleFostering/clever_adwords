<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('rodrido_keyword')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` varchar(11) NOT NULL,
  `keyword_title` varchar(255) NOT NULL,
  `keyword_type` Varchar(255) NOT NULL,
  `status` int(2) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=98 ;
");

$installer->endSetup();
 ?>