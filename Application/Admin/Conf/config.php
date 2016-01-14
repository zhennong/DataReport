<?php
return array(
	/* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__STATIC__'    => __ROOT__ . '/Public/Static',
        '__PUBLIC__'    => __ROOT__ . '/Public/',
        '__COMMON__'    => __ROOT__ . '/Public/Common/',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
    ),
);