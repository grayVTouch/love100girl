<?php
/**
 * Created by PhpStorm.
 * User: grayVTouch
 * Date: 2019/1/6
 * Time: 23:45
 */

$str = <<<EOT
<!------------------- [頁數] ------------------->
<table border="0" width="100%" id="table1">
	<tr>
		<td align="left"><div id='wp_page_numbers'>
<ul><li class="page_info">第 1 頁 ※ 共  254</li><li class="active_page"><a href="https://love100girl.com/">1</a></li>
<li><a href="https://love100girl.com/page/2">2</a></li>
<li><a href="https://love100girl.com/page/3">3</a></li>
<li><a href="https://love100girl.com/page/4">4</a></li>
<li><a href="https://love100girl.com/page/5">5</a></li>
<li><a href="https://love100girl.com/page/6">6</a></li>
<li><a href="https://love100girl.com/page/7">7</a></li>
<li><a href="https://love100girl.com/page/8">8</a></li>
<li><a href="https://love100girl.com/page/9">9</a></li>
<li><a href="https://love100girl.com/page/10">10</a></li>
<li><a href="https://love100girl.com/page/11">11</a></li>
<li><a href="https://love100girl.com/page/12">12</a></li>
<li><a href="https://love100girl.com/page/13">13</a></li>
<li><a href="https://love100girl.com/page/14">14</a></li>
<li><a href="https://love100girl.com/page/15">15</a></li>
<li><a href="https://love100girl.com/page/16">16</a></li>
<li><a href="https://love100girl.com/page/17">17</a></li>
<li><a href="https://love100girl.com/page/18">18</a></li>
<li><a href="https://love100girl.com/page/19">19</a></li>
<li><a href="https://love100girl.com/page/20">20</a></li>
<li class="space">...</li>
<li class="first_last_page"><a href="https://love100girl.com/page/254">254</a></li>
<li><a href="https://love100girl.com/page/2">》</a></li>
</ul>
<div style='float: none; clear: both;'></div>
</div>
</td>
	</tr>
</table>
<!------------------- [頁數] ------------------->
EOT;

var_dump(mb_strlen($str));
