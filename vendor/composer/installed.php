<?php return array(
    'root' => array(
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'type' => 'project',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => '65b90b00e90fbf3c03467281f85ff75ea0029c8d',
        'name' => 'root/pothen-esxes',
        'dev' => true,
    ),
    'versions' => array(
        'root/pothen-esxes' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'project',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => '65b90b00e90fbf3c03467281f85ff75ea0029c8d',
            'dev_requirement' => false,
        ),
        'sendgrid/php-http-client' => array(
            'pretty_version' => '3.10.7',
            'version' => '3.10.7.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sendgrid/php-http-client',
            'aliases' => array(),
            'reference' => 'f01e6fe7e33b811715ba93da3f85573e175bb801',
            'dev_requirement' => false,
        ),
        'sendgrid/sendgrid' => array(
            'pretty_version' => '6.2.0',
            'version' => '6.2.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../sendgrid/sendgrid',
            'aliases' => array(),
            'reference' => '4d500a972739ef2c596299f3ad822dd231aab4df',
            'dev_requirement' => false,
        ),
        'sendgrid/sendgrid-php' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
