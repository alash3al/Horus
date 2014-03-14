<?php
$st = microtime(1);
require_once 'Horus/Horus.php';

new Horus;

echo horus()->horusTemplate('Horus Version '.Horus::VERSION, 
'
<p>Every thing looks good, let\'s start coding</p>
<img src="https://fbcdn-photos-g-a.akamaihd.net/hphotos-ak-prn2/t1/1897797_303381643146235_802040592_n.png" />
<br />
<a target="__blank" href="'.asset('/wiki/version-1.4.0.html').'">Getting Started</a>
<div id="footer"> <span style="color:maroon; font-weight: bold">Elpased time:</span> '. round( microtime(1) - $st, 4 ) .' seconds</div>
',
'
body{font: 13px/20px normal Helvetica, Arial, sans-serif;text-align:center; max-width: 500px; margin:auto; color:#4F5155; border: 1px solid #ccc; padding:10px; margin-top: 8%; box-shadow: 0 0 5px #eee}
a{color:#003399}
img{padding:3px;border:1px solid #ccc}
h1{border-bottom: 1px solid #D0D0D0; color:#444; padding: 15px; font-size: 19px; font-weight: normal}
#footer{ border-top: 1px solid #ccc; padding: 5px; text-align:right; margin-top: 15px }
');
