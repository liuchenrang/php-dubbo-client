<?php
namespace Idiot\Registry;
interface Register {
     public function getProvider($path, $version = '',$protocol= "dubbo");
}