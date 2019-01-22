<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Error</title>
</head>
<body style="margin: 0">
<div class="error-header" style="padding:50px 0 100px 20px;background-color:#FF8888;">
<h3><?=$message?></h3>
</div>
<div class="error-body" style="padding:50px 0 100px 20px;background-color:#FFFFBB;line-height:30px">
<?php foreach ($trace as $error) {?>
<?php $message = trim($error);?>
<?php if ($message != '') {?>
<p><?=$message?></p>
<?php }?>
<?php }?>
</div>
</body>
</html>