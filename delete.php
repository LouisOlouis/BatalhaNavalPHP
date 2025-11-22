<?php
session_start();

if (!empty($_SESSION['serverc']) && file_exists($_SESSION['serverc'])) {
    unlink($_SESSION['serverc']);
    unset($_SESSION['serverc']);
    exit;
}
?>
