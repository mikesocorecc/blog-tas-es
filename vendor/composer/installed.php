<?php return array(
    'root' => array(
        'name' => '__root__',
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'deployer/deployer' => array(
            'pretty_version' => 'v7.0.2',
            'version' => '7.0.2.0',
            'reference' => '99a57a9035cfe0eeae5dba5befe5411ac0f3ac72',
            'type' => 'library',
            'install_path' => __DIR__ . '/../deployer/deployer',
            'aliases' => array(),
            'dev_requirement' => true,
        ),
    ),
);
