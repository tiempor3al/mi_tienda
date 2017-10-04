<?php

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$app = new Silex\Application();

//Para que se muestren los errores
$app['debug'] = true;

//Para conectarse a la base de datos, hay que registrar un proveedor
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
         'host'      => '192.168.1.126',
         'dbname'    => 'mi_tienda',
         'user'      => 'mi_tienda',
         'password'  => 'tienda01',
         'charset'   => 'utf8',
    ),
));

//Registramos el validador
$app->register(new Silex\Provider\ValidatorServiceProvider());

//Definimos el directorio de las vistas
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));


//Definimos una ruta de inicio
$app->get('/', function() use($app) {
	return $app['twig']->render('principal.twig');
});

//Ruta para crear cuenta
$app->get('/crear_cuenta', function() use($app) {
	return $app['twig']->render('crear_cuenta.twig');
});

//Ruta para validar los datos y guardar en base de datos
$app->post('/crear_cuenta', function(Request $request) use($app) {
	
	$data = $request->request->all();
	
	$constraint = new Assert\Collection(array(
		'nombre' => new Assert\NotBlank(array('message'=>'Nombre requerido')),
		'apellido' => new Assert\NotBlank(array('message'=>'Apellido requerido')),
		'correo' => array(
		new Assert\NotBlank(array('message'=>'Correo requerido')),
		new Assert\Email(array('message'=>'Email incorrecto'))
		),
		'calle' => new Assert\NotBlank(array('message'=>'Calle requerida')),
		'colonia' => new Assert\NotBlank(array('message'=>'Colonia requerida')),
		'cp' => new Assert\NotBlank(array('message'=>'Código postal requerido')),
		'telefono' => new Assert\NotBlank(array('message'=>'Teléfono requerido'))
    ));
	
	$errors = $app['validator']->validate($data, $constraint);
	
	if (count($errors) > 0) {
		return $app['twig']->render('crear_cuenta.twig',array('errors' => $errors, 'data' => $data));
	}
	
	$sql = "INSERT INTO clientes(nombre,apellido,correo,calle,colonia,cp,telefono) VALUES(?, ?, ?, ?, ? ,? ,?)";
	$app['db']->executeUpdate($sql, array($data['nombre'], $data['apellido'], $data['correo'], $data['calle'],$data['colonia'],$data['cp'],$data['telefono']));
	 		
	return $app['twig']->render('crear_cuenta.twig');
	
});


$app->run();