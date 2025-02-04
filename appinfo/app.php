<?php

OC::$CLASSPATH['OC_Kubernetes_Util'] ='apps/user_pods/lib/util.php';

OCP\App::addNavigationEntry(
	array( 'id'    => 'batch',
		'order' => 7,
		'icon'  => OCP\Util::imagePath( 'batch', 'stack.svg' ),
		'href'  => OCP\Util::linkTo( 'index.php/apps/batch' , 'index.php' ),
		'name'  => 'Batch'
	)
);

OCP\App::registerPersonal('batch', 'personalsettings');
