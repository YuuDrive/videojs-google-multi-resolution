<?php require 'vendor/autoload.php';

Flight::route('', function() {
    echo "Hi";
});

Flight::route('/convert', function(){
    $request = Flight::request();
    $id = $request->query->id;
    $encrypted = \App\Lib\UnsafeCrypto::encrypt($id, "5e987a39b39e4", true);
    Flight::redirect(base_url() . '/' . $encrypted);
});

Flight::route('/@encrypted', function($encrypted) {
    try {
        $request = Flight::request();
        $decrypted_id = \App\Lib\UnsafeCrypto::decrypt($encrypted, "5e987a39b39e4", true);
        $s = new \App\Lib\ProxyPlayer();
        $s->setID($decrypted_id);
        $resolutions = $s->getResolution();
    
        if(count($resolutions) <= 0) die("VIdeo resolution not found");
    
        $file = file_info($decrypted_id, "name,thumbnailLink");

        if($file) {
            $data = [
                'id' => $encrypted,
                'file' => $file,
                'resolutions' => $resolutions,
                'csrf' => Flight::get('csrf'),
                'base_url' => base_url()
            ];
            Flight::render('index.view', $data);
        } else {
            Flight::notFound();
        }
    } catch (\Exception $e) {
        Flight::notFound();
    }
});

Flight::route('/get-resolution/@encrypted', function($encrypted=null){
    try {
        $request = Flight::request();
        $decrypted_id = \App\Lib\UnsafeCrypto::decrypt($encrypted, "5e987a39b39e4", true);
        $s = new \App\Lib\ProxyPlayer();
        $s->setID($decrypted_id);
        $resolutions = $s->getResolution();
        Flight::json($resolutions);
    } catch (\Exception $e) {
        Flight::notFound();
    }
});

Flight::route('/stream/@id', function($encrypted=null){
    try {
        $request = Flight::request();
        $decrypted_id = \App\Lib\UnsafeCrypto::decrypt($encrypted, "5e987a39b39e4", true);
    
        $file = file_info($decrypted_id, "name,thumbnailLink");
    
        if($file) {
            $s = new \App\Lib\ProxyPlayer();
            $s->setID($decrypted_id);
            $s->setDownload($request->query["alt"]  ?? "");
            $s->setResolution($request->query["res"]  ?? "sd");
            return $s->stream();
        } else {
            die("File not Found");
        }
    } catch (\Exception $e) {
        Flight::notFound();
    }
});

Flight::before('json', function () {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE');
    header('Access-Control-Allow-Headers: Content-Type');
});

Flight::start();

?>