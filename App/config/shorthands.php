<?php

// ==================================================
// > PHP CONSOLE LOGGER
// ==================================================
function console_log($var, $tags = null) {
	PhpConsole\Connector::getInstance()->getDebugDispatcher()->dispatchDebug($var, $tags, 1);
}



