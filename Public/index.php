<?php

// require the autoloader
require("../Private/Vendor/Polyfony/Loader.php");

// class loader
new Loader(null,"../Private/Vendor/");

// front end
new Polyfony\Front();

?>