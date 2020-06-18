<?php
include "vendor/autoload.php";

$fmt = new Brille24\MarkdownFormatter\MarkdownFormatter(__DIR__);
echo $fmt->formatContext(['hello' => str_repeat('kc<lsdcm<sklcmkljzdvndfkjvnvwj4f093 u4jfoienvfdakjdfvnkejfnvdkjfvndkjfvnfve09rvjidofnvdlkfvnkjdfvnjksdfnv', 1000)]);

