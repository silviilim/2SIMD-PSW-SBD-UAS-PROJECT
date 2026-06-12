<?php

if(function_exists('sqlsrv_connect')){
    echo "SQLSRV aktif";
}else{
    echo "SQLSRV belum aktif";
}

?>