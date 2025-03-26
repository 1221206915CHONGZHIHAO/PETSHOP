<?php
session_start();
// 清空所有 session 变量
session_unset();
// 销毁当前 session
session_destroy();
// 重定向到主页
header("Location: userhomepage.php");
exit();
?>
