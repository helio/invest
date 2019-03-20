<?php

require __DIR__ . '/src/bootstrap.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet(\Helio\Invest\Helper\DbHelper::getInstance()->get());

