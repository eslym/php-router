<?php
/**
 * Created by PhpStorm.
 * User: engshun
 * Date: 10/3/17
 * Time: 12:51 AM
 */

namespace onepeople\router;

include 'vendor/autoload.php';

GlobalRouter::get('/api/user/$id:int', function($id){
    echo 'This is user :'.$id;
});

GlobalRouter::get('/$type:enum[img|js|css|fonts]/$path:path.$!:string', function($type, $path){
?>
Type = <?=$type?>, Path = <?=$path?><br/>
<?php
});

GlobalRouter::route('/api/user/1465');
GlobalRouter::route('/img/background/default.jpg');