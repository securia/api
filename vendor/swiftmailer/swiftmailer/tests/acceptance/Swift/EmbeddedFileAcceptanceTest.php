<?php

require_once '../../../lib/swift_required.php';
require_once __DIR__.'/Mime/EmbeddedFileAcceptanceTest.php';

class Swift_EmbeddedFileAcceptanceTest extends Swift_Mime_EmbeddedFileAcceptanceTest
{
    protected function _createEmbeddedFile()
    {
        return Swift_EmbeddedFile::newInstance();
    }
}
