<?php
$time_begins = microtime(true);
$project = 'PHP Simple API Test'; $version = '0.1'; $pack = 'Project';
$json_status = (extension_loaded('json')) ? 'available' : 'n/a';
$xml_status = (extension_loaded('xml')) ? 'available' : 'n/a';
$http_method = $_SERVER['REQUEST_METHOD']; $method_data = $http_method .' data';
function timeMarker($time) { return DateTime::createFromFormat('U.u', $time)->format('Y-m-d H:i:s.u'); }
function rowResult($title, $result, $style = '', $css = '') {
        $tr  = null; if ( ! empty($title) && ! empty($result) ) {
        $tr  = '<tr class="'. $style .'"><th class="el-title">'. $title .'</th>';
        $tr .= '<td class="el-sep">:</td><td class="'. $css .'">'. $result .'</td></tr>'; } return $tr;
}
function dataRequest() {
        switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST': $data = $_POST; break;
                case 'GET': $data = $_GET; break;
                default: $data = ['method' => 'unsupported']; break;
        } unset($data['mode']); unset($data['output']);
        return ($_GET['mode'] === 'plain') ? $data : htmlspecialchars(showResult($data, false));
}
function slugArrayKey(array $arr) {
    $arr = array_combine(array_map(function($str) { return str_replace(' ', '_', $str); },
           array_keys($arr)), array_values($arr));
    foreach ($arr as $key => $val) { (is_array($val)) ? slugArrayKey($arr[$key]) : $val; } return $arr;
}
function array_to_xml(array $arr, SimpleXMLElement $xml) {
    foreach ($arr as $k => $v) {
        $k = (is_numeric($k)) ? 'item' : $k;
        is_array($v) ? array_to_xml($v, $xml->addChild($k)) :
                $xml->addChild($k, htmlspecialchars($v));
    } return $xml->asXML();
}
function showResult($results, $plain = true) {
        $results = slugArrayKey(array_change_key_case($results));
        switch ($_GET['output']) {
                case 'xml': $contype = 'text/xml';
                $output = array_to_xml($results, new SimpleXMLElement('<root/>')); break;
                case 'json': default: $contype = 'application/json';
                $output = json_encode($results, JSON_PRETTY_PRINT); break;
        } if ( ! $plain) { return $output; }
          else { header('Content-Type: '. $contype); echo $output; }
}
$results = [
        $pack => $project .' v'. $version,
        'PHP version' => phpversion(),
        'JSON module' => $json_status,
        'XML module' => $xml_status,
        'Result mode' => ( $_GET['mode'] === 'plain' ) ? 'plain' : 'html',
        'Client IP' => $_SERVER['REMOTE_ADDR'],
        'Time begins' => timeMarker($time_begins) .' UTC',
        'HTTP method' => 'HTTP '. $http_method,
        'HTTP '. $method_data => dataRequest(),
        'Time ends' => timeMarker(microtime(true)) .' UTC',
        'Total time' => number_format((microtime(true) - $time_begins), 6) .' seconds',
];
?>
<?php if ( $_GET['mode'] === 'plain' ): ?>
<?php showResult($results) ?>
<?php else: ?>
<!DOCTYPE html>
<html>
        <title><?= $project .' v'. $version ?></title>
        <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/css/bootstrap.min.css" />
        <style>body { background-color: #1f1f1f; color: #5f9ea0; font-size: 12px; }
        code { background-color: #131313; color: #5f9ea0; font-size: 12px; }
        .btn-manual { background-color: #5f9ea0; color: #000; margin-bottom: 10px }
        .well { background-color: #1c1c1c; color: #5f9ea0 }
        .result { font-family: 'Consolas', 'Sans Serif'; color: #daeced }
        .el-title { width: 130px } .el-sep { width: 10px }</style>
</html>
<body>
        <div class="container-fluid">
                <div class="row">
                        <div class="col-md-12">
                                <h3><?= $project .' v'. $version ?></h3>
                                <button class="btn btn-manual btn-xs" type="button" data-toggle="collapse" data-target="#manual"
                                        aria-expanded="false" aria-controls="manual">Manual</button>
                                <div class="collapse" id="manual">
                                        <div class="well">
                                                This is a single php file to test API with HTTP GET and POST <code>method</code> only<br>
                                                Result mode is in default <code>html</code> format as you can see within this manual<br>
                                                You can see result in a <code>plain</code> format with a query string <code>?&mode=plain</code><br>
                                                HTTP <code>method</code> data is in default <code>json</code> format,
                                                you can have an <code>xml</code> format<br>
                                                Change data format with a query string <code>?&output=</code>
                                                whether <code>json</code> or <code>xml</code>
                                        </div>
                                </div>
                                <div class="table-responsive">
                                        <table class="table table-condensed">
                                                <?php foreach ( $results as $title => $result ): ?>
                                                        <?php if ($title == $pack): ?>
                                                                <?= rowResult($title, $result, 'hidden') ?>
                                                        <?php elseif ($title == $method_data): ?>
                                                                <?= rowResult($title, $result, '', 'result') ?>
                                                        <?php else: ?>
                                                                <?= rowResult($title, $result) ?>
                                                        <?php endif; ?>
                                                <?php endforeach; ?>
                                        </table>
                                </div>
                        </div>
                </div>
        </div>
        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="//cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
</body>
<?php endif; ?>
